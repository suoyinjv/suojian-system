#!/bin/bash
# ============================================================
# SchoolCMS 多租户 SaaS 迁移脚本
# 执行方式: bash migrate_multitenant.sh
# ============================================================

set -e
DB_NAME="schoolcms"
DB_USER="schoolcms"
DB_PASS="SchoolCMS@2024"
BACKUP_FILE="/tmp/schoolcms_pre_migrate_$(date +%Y%m%d_%H%M%S).sql"

echo "=========================================="
echo "SchoolCMS 多租户 SaaS 迁移"
echo "=========================================="

# 1. 备份
echo "[1/6] 备份数据库..."
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
echo "  备份完成: $BACKUP_FILE"

# 2. 批量加 campus_id
echo "[2/6] 为所有业务表添加 campus_id 字段..."

TABLES=$(mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SHOW TABLES LIKE 'sc_%';" 2>/dev/null)

SKIP_TABLES="sc_admin sc_campus sc_campus_config sc_config sc_power sc_role sc_role_power sc_subject sc_region"
COUNT=0

for t in $TABLES; do
  # 跳过系统表
  skip=0
  for s in $SKIP_TABLES; do
    [ "$t" = "$s" ] && skip=1 && break
  done
  [ "$skip" -eq 1 ] && continue

  # 检查是否已有 campus_id
  has=$(mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SHOW COLUMNS FROM $t LIKE 'campus_id';" 2>/dev/null | wc -l)
  if [ "$has" -eq 0 ]; then
    idx_name="idx_${t#sc_}_campus"
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
      "ALTER TABLE $t ADD COLUMN campus_id INT(11) UNSIGNED DEFAULT 0 COMMENT '所属校区' AFTER id, ADD INDEX $idx_name (campus_id);" 2>/dev/null
    echo "  + $t"
    COUNT=$((COUNT + 1))
  fi
done
echo "  共添加 $COUNT 张表"

# 3.5 数据迁移: 把旧数据分配到第一个校区(campus_id=1)，避免新加campus_id后数据消失
echo "[3.5/6] 迁移旧数据到默认校区..."
BUSINESS_TABLES=$(mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
  SELECT TABLE_NAME FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA='$DB_NAME'
    AND COLUMN_NAME='campus_id'
    AND TABLE_NAME LIKE 'sc_%'
  ORDER BY TABLE_NAME;
" 2>/dev/null)
for t in $BUSINESS_TABLES; do
  # 跳过系统表
  case "$t" in
    sc_admin|sc_campus|sc_campus_config|sc_config|sc_power|sc_role|sc_role_power|sc_region|sc_article_class|sc_custom_view|sc_layout|sc_layout_module|sc_interval|sc_subject|sc_week|sc_semester) continue ;;
  esac
  affected=$(mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "UPDATE $t SET campus_id=1 WHERE campus_id=0 OR campus_id IS NULL; SELECT ROW_COUNT();" 2>/dev/null)
  [ "$affected" -gt 0 ] && echo "  $t: $affected 条"
done

# 3. 确保扩展字段存在
echo "[3/6] 确保 sc_campus 扩展字段..."
for col_sql in \
  "ALTER TABLE sc_campus ADD COLUMN IF NOT EXISTS domain VARCHAR(100) DEFAULT '' COMMENT '绑定的子域名' AFTER code;" \
  "ALTER TABLE sc_campus ADD COLUMN IF NOT EXISTS site_name VARCHAR(60) DEFAULT '' COMMENT '机构站点名称' AFTER name;" \
  "ALTER TABLE sc_campus ADD COLUMN IF NOT EXISTS theme_color VARCHAR(7) DEFAULT '#4e73df' COMMENT '主题色' AFTER logo;" \
  "ALTER TABLE sc_campus ADD COLUMN IF NOT EXISTS icp VARCHAR(60) DEFAULT '' COMMENT '备案号' AFTER theme_color;" \
  "ALTER TABLE sc_campus ADD COLUMN IF NOT EXISTS expire_date INT(11) DEFAULT 0 COMMENT '到期时间戳' AFTER status;" \
  "ALTER TABLE sc_admin ADD COLUMN IF NOT EXISTS campus_id INT(11) UNSIGNED DEFAULT 0 COMMENT '所属校区' AFTER role_id;" \
  "ALTER TABLE sc_admin ADD COLUMN IF NOT EXISTS is_super TINYINT(1) DEFAULT 0 COMMENT '是否超管' AFTER campus_id;"; do
  mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$col_sql" 2>/dev/null
done

# 4. 确保 sc_campus_config 存在
echo "[4/6] 确保 sc_campus_config 表..."
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
CREATE TABLE IF NOT EXISTS sc_campus_config (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  campus_id INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '校区ID(0=全局)',
  name VARCHAR(60) NOT NULL COMMENT '配置键名',
  value TEXT COMMENT '配置值',
  create_time INT(11) UNSIGNED DEFAULT 0,
  update_time INT(11) UNSIGNED DEFAULT 0,
  UNIQUE KEY uk_campus_name (campus_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='租户配置';
" 2>/dev/null
echo "  sc_campus_config 已就绪"

# 5. 设置校区域名
echo "[5/6] 设置示例校区域名..."
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
UPDATE sc_campus SET domain='beijing' WHERE id=1;
UPDATE sc_campus SET domain='shanghai' WHERE id=2;
UPDATE sc_campus SET domain='shenzhen' WHERE id=3;
UPDATE sc_campus SET domain='youxue' WHERE id=4;
UPDATE sc_campus SET site_name=name WHERE site_name='';
UPDATE sc_campus SET theme_color='#10b981' WHERE id=4;
SELECT id, name, domain FROM sc_campus;
" 2>/dev/null

# 6. 验证
echo "[6/6] 验证迁移结果..."
echo "  campus_id 字段统计:"
for t in sc_student sc_order sc_teacher sc_course sc_class sc_attendance sc_expense; do
  cnt=$(mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$DB_NAME' AND TABLE_NAME='$t' AND COLUMN_NAME='campus_id';" 2>/dev/null)
  echo "    $t: $([ "$cnt" -gt 0 ] && echo '✅' || echo '❌')"
done

echo ""
echo "=========================================="
echo "迁移完成！"
echo ""
echo "测试方式："
echo "  超管后台: http://yourdomain/admin.php"
echo "  优学教育: http://youxue.yourdomain/admin.php?m=Admin&c=Admin&a=LoginInfo"
echo "  注册页面: http://yourdomain/index.php?m=Home&c=Register&a=index"
echo ""
echo "备份文件: $BACKUP_FILE"
echo "=========================================="
