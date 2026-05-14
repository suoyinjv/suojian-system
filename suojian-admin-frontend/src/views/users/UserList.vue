<template>
  <div class="user-page">
    <div class="page-header">
      <div class="page-title">
        <h2>用户管理</h2>
        <p>管理系统用户账号与权限</p>
      </div>
      <el-button type="primary" @click="showDialog = true">
        <el-icon><Plus /></el-icon>添加用户
      </el-button>
    </div>

    <!-- Stats -->
    <el-row :gutter="20" class="user-stats">
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.total }}</span>
          <span class="stat-label">总用户</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.active }}</span>
          <span class="stat-label">活跃用户</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.admin }}</span>
          <span class="stat-label">管理员</span>
        </div>
      </el-col>
      <el-col :xs="12" :sm="6">
        <div class="stat-item">
          <span class="stat-num">{{ stats.new }}</span>
          <span class="stat-label">本月新增</span>
        </div>
      </el-col>
    </el-row>

    <!-- Filter -->
    <el-card class="filter-card" shadow="never">
      <el-form :model="filters" layout="inline">
        <el-form-item label="角色">
          <el-select v-model="filters.role" clearable style="width: 130px">
            <el-option label="全部" value="" />
            <el-option label="管理员" value="admin" />
            <el-option label="教师" value="teacher" />
            <el-option label="学生" value="student" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="filters.status" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="启用" value="active" />
            <el-option label="禁用" value="disabled" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="姓名/账号/手机" clearable style="width: 220px">
            <template #prefix><el-icon><Search /></el-icon></template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadUsers">查询</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Table -->
    <el-card class="table-card" shadow="never">
      <el-table :data="users" stripe>
        <el-table-column type="selection" width="45" />
        <el-table-column type="index" label="#" width="50" align="center" />
        <el-table-column label="用户信息" min-width="180">
          <template #default="{ row }">
            <div class="user-cell">
              <el-avatar :size="36" :style="{ background: row.color }">{{ row.name[0] }}</el-avatar>
              <div>
                <div class="user-name">{{ row.name }}</div>
                <div class="user-email">{{ row.email }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="phone" label="手机" width="130" />
        <el-table-column prop="role" label="角色" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="roleType(row.role)" size="small">{{ roleName(row.role) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="lastLogin" label="最后登录" width="160" />
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.active"
              :active-value="true"
              :inactive-value="false"
              @change="toggleActive(row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="160" fixed="right" align="center">
          <template #default="{ row }">
            <el-button size="small" text type="primary" @click="editUser(row)">
              <el-icon><Edit /></el-icon>编辑
            </el-button>
            <el-button size="small" text type="danger" @click="deleteUser(row)">
              <el-icon><Delete /></el-icon>删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="table-footer">
        <el-pagination
          v-model:current-page="page"
          :page-size="10"
          :total="total"
          layout="total, prev, pager, next"
          background
          small
        />
      </div>
    </el-card>

    <!-- Add/Edit Dialog -->
    <el-dialog v-model="showDialog" :title="editingId ? '编辑用户' : '添加用户'" width="520px">
      <el-form :model="userForm" label-width="80px" class="dialog-form">
        <el-form-item label="姓名" required>
          <el-input v-model="userForm.name" placeholder="请输入姓名" />
        </el-form-item>
        <el-form-item label="账号" required>
          <el-input v-model="userForm.username" placeholder="请输入账号" />
        </el-form-item>
        <el-form-item label="手机">
          <el-input v-model="userForm.phone" placeholder="请输入手机号" />
        </el-form-item>
        <el-form-item label="角色" required>
          <el-select v-model="userForm.role" style="width: 100%">
            <el-option label="管理员" value="admin" />
            <el-option label="教师" value="teacher" />
            <el-option label="学生" value="student" />
          </el-select>
        </el-form-item>
        <el-form-item label="初始密码" v-if="!editingId" required>
          <el-input v-model="userForm.password" type="password" show-password placeholder="请输入密码" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">取消</el-button>
        <el-button type="primary" @click="saveUser">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search, Edit, Delete } from '@element-plus/icons-vue'
import { getUserList, updateUser, deleteUser as apiDeleteUser } from '@/api/users'

const users = ref([])
const page = ref(1)
const total = ref(0)
const showDialog = ref(false)
const editingId = ref(null)
const loading = ref(false)

const filters = reactive({ role: '', status: '', keyword: '' })

const userForm = reactive({ name: '', username: '', phone: '', role: 'student', password: '' })

const stats = reactive({ total: 0, active: 0, admin: 0, new: 0 })

const colors = ['#4fc3f7', '#7c4dff', '#00c853', '#ff6b35', '#ffb300', '#e91e63']

function roleName(r) {
  return { admin: '管理员', teacher: '教师', student: '学生' }[r] || r
}
function roleType(r) {
  return { admin: 'danger', teacher: 'warning', student: 'success' }[r] || 'info'
}

function loadUsers() {
  loading.value = true
  const params = { page: page.value, pageSize: 10 }
  if (filters.keyword) params.keyword = filters.keyword
  getUserList(params)
    .then(res => {
      const list = Array.isArray(res.list) ? res.list : []
      users.value = list.map(u => ({
        ...u,
        name: u.username || '',
        email: '',
        phone: u.mobile || u.phone || '',
        mobile: u.mobile || '',
        lastLogin: u.login_time || '',
        active: true,
        color: colors[Math.floor(Math.random() * colors.length)],
      }))
      total.value = res.total || 0
      stats.total = total.value
      stats.active = list.filter(u => u.active !== false).length
      stats.admin = list.filter(u => u.role === 'admin').length
    })
    .catch(() => {
      ElMessage.error('加载用户列表失败')
    })
    .finally(() => {
      loading.value = false
    })
}

function resetFilters() {
  filters.role = ''
  filters.status = ''
  filters.keyword = ''
  page.value = 1
  loadUsers()
}

function toggleActive(row) {
  ElMessage.success(`用户已${row.active ? '启用' : '禁用'}`)
}

function editUser(row) {
  editingId.value = row.id
  userForm.name = row.username || ''
  userForm.username = row.username || ''
  userForm.phone = row.mobile || row.phone || ''
  userForm.role = row.role || 'student'
  userForm.password = ''
  showDialog.value = true
}

function deleteUser(row) {
  ElMessageBox.confirm(`确定删除用户「${row.username || row.name}」吗？`, '确认', { type: 'warning' })
    .then(() => {
      apiDeleteUser(row.id)
        .then(() => {
          ElMessage.success('删除成功')
          loadUsers()
        })
        .catch(() => {
          ElMessage.error('删除失败')
        })
    })
    .catch(() => {})
}

function saveUser() {
  if (editingId.value) {
    updateUser(editingId.value, {
      username: userForm.username,
      phone: userForm.phone,
      role: userForm.role,
    })
      .then(() => {
        ElMessage.success('编辑成功')
        showDialog.value = false
        editingId.value = null
        Object.keys(userForm).forEach(k => userForm[k] = '')
        loadUsers()
      })
      .catch(() => {
        ElMessage.error('编辑失败')
      })
  } else {
    ElMessage.info('添加用户功能待对接')
    showDialog.value = false
  }
}

// 切换页码时重新加载
watch(page, () => { loadUsers() })

// 初始化加载
loadUsers()
</script>

<style scoped>
.user-page { animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.page-title h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-title p { font-size: 13px; color: #909399; }
.user-stats { margin-bottom: 20px; }
.stat-item { background: #fff; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #ebeef5; }
.stat-num { display: block; font-size: 28px; font-weight: 700; color: #4fc3f7; }
.stat-label { font-size: 13px; color: #909399; }
.filter-card, .table-card { border-radius: 12px; border: 1px solid #ebeef5; margin-bottom: 16px; }
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-name { font-size: 14px; color: #303133; font-weight: 500; }
.user-email { font-size: 12px; color: #909399; }
.table-footer { display: flex; justify-content: flex-end; padding: 16px 0 0; }
.dialog-form { padding: 10px 0; }
</style>
