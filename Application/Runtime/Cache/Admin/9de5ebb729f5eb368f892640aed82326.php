<?php if (!defined('THINK_PATH')) exit(); include dirname(__FILE__) . '/../Public/Header.html'; ?>

<!-- right content start  -->
<div class="content-right">
  <div class="content">
    <!-- form start -->
    <form class="am-form m-b-20" action="<?php echo U('Admin/Bubble/Index'); ?>" method="POST">
      <div class="am-g">
        <input type="text" class="am-radius form-keyword" placeholder="<?php echo L('bubble_so_keyword_tips'); ?>" name="keyword" <?php if(isset($param['keyword'])): ?> value="<?php echo $param['keyword']; ?>"<?php endif; ?> />
        <button type="submit" class="am-btn am-btn-secondary am-btn-sm am-radius form-submit"><?php echo L('common_operation_query'); ?></button>
        <label class="fs-12 m-l-5 c-p fw-100 more-submit">
          <?php echo L('common_more_screening'); ?>
          <input type="checkbox" name="is_more" value="1" id="is_more" <?php if(isset($param['is_more']) && $param['is_more'] == 1): ?>checked<?php endif; ?> />
          <i class="am-icon-angle-down"></i>
        </label>

        <div class="more-where <?php if(!isset($param['is_more']) || $param['is_more'] != 1): ?>none<?php endif; ?>">
          <div class="param-date param-where">
            <input type="text" name="time_start" readonly="readonly" class="am-radius m-t-10" placeholder="<?php echo L('common_time_start_name'); ?>" id="time_start" <?php if(isset($param['time_start'])): ?>value="<?php echo $param['time_start']; ?>"<?php endif; ?>/>
            <span>~</span>
            <input type="text" readonly="readonly" class="am-radius m-t-10" placeholder="<?php echo L('common_time_end_name'); ?>" name="time_end" id="time_end" <?php if(isset($param['time_end'])): ?>value="<?php echo $param['time_end']; ?>"<?php endif; ?>/>
          </div>
        </div>
      </div>
    </form>
    <!-- form end -->

    <!-- list start -->
    <?php if(!empty($data)): ?>
      <!-- mood start -->
      <?php foreach($data as $v): ?>
        <div class="am-panel am-panel-default am-radius list-content data-list-mood-<?php echo $v['id']; ?>">
          <div class="am-panel-bd">
            <div class="list-title o-h">
              <img src="/Public/Common/Images/user-img-sm.gif" class="am-circle am-fl" width="48" height="48" />
              <div class="am-fl m-l-10 m-t-5">
                <span class="block">
                  <a href="javascript:;"><?php echo $v['nickname']; ?></a>
                </span>
                <span class="block cr-999"><?php echo $v['add_time']; ?></span>
              </div>
              <div class="am-fr">
                <i class="am-icon-trash-o c-p cr-999 submit-delete" data-am-popover="{content: '<?php echo L('common_operation_delete'); ?>', trigger: 'hover focus'}" data-id="<?php echo $v['id']; ?>" data-url="<?php echo U('Admin/Bubble/MoodDelete'); ?>" data-list-tag=".data-list-mood-<?php echo $v['id']; ?>"></i>
              </div>
            </div>
            <div class="m-t-5"><?php echo $v['content']; ?></div>
            <div class="m-t-5 o-h">
              <span class="am-fr cr-999"><?php echo $v['comments_count']; ?></span>
              <i class="am-icon-commenting am-icon-sm am-fr m-l-30 cr-ccc"></i>
              <span class="am-fr cr-999"><?php echo $v['praise_count']; ?></span>
              <i class="am-icon-thumbs-up am-icon-sm am-fr <?php if(empty($v['praise_list'])): ?>cr-ccc<?php else: ?>c-p praise-submit<?php endif; ?>" data-id="<?php echo $v['id']; ?>"></i>
            </div>
            <?php if(!empty($v['praise_list'])): ?>
              <table class="am-table am-table-striped am-table-hover am-table-bordered am-table-radius am-table-compact m-t-5 m-b-0 praise-table" id="praise-popup-<?php echo $v['id']; ?>" style="display:none;">
                <thead>
                  <tr>
                    <th><?php echo L('bubble_praise_table_nickname'); ?></th>
                    <th><?php echo L('bubble_praise_table_add_time'); ?></th>
                    <th><?php echo L('common_operation_name'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($v['praise_list'] as $vp): ?>
                    <tr class="praise-list-<?php echo $vp['id']; ?>">
                      <td><a href="javascript:;"><?php echo $vp['nickname']; ?></a></td>
                      <td><?php echo date('Y-m-d H:i:s', $vp['add_time']); ?></td>
                      <td>
                        <button class="am-btn am-btn-default am-btn-xs am-radius am-icon-trash-o submit-delete" data-url="<?php echo U('Admin/Bubble/MoodPraiseDelete'); ?>" data-am-popover="{content: '<?php echo L('common_operation_delete'); ?>', trigger: 'hover focus'}" data-id="<?php echo $vp['id']; ?>" data-list-tag=".praise-list-<?php echo $vp['id']; ?>"></button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
            <?php if(!empty($v['comments'])): ?>
              <div class="o-h data-list-mood-<?php echo $v['id']; ?>">
                <hr data-am-widget="divider" class="am-divider am-divider-dashed m-t-10 m-b-10" />
                <?php foreach($v['comments'] as $vc): ?>
                  <div class="o-h m-t-10 am-radius comments-user data-comments-<?php echo $v['id']; ?>-<?php echo $vc['id']; ?>">
                    <img src="/Public/Common/Images/user-img-sm.gif" class="am-circle user-portrait" width="30" height="30" />
                    <a href="javascript:;"><?php echo $vc['nickname']; ?></a>
                    <span class="cr-999"><?php echo $vc['add_time']; ?></span>
                    <div class="am-fr comments-item-tools">
                      <span class="c-p cr-999 m-l-20 submit-delete" data-id="<?php echo $vc['id']; ?>" data-url="<?php echo U('Admin/Bubble/MoodCommentsDelete'); ?>" data-list-tag=".data-comments-<?php echo $v['id']; ?>-<?php echo $vc['id']; ?>">
                        <i class="am-icon-trash-o"></i><?php echo L('common_operation_delete'); ?>
                      </span>
                    </div>
                    <div><?php echo $vc['content']; ?></div>
                  </div>
                  <?php if(!empty($vc['item'])): ?>
                    <?php foreach($vc['item'] as $vcs): ?>
                      <div class="o-h m-t-10 am-radius comments-user m-l-30 data-comments-<?php echo $v['id']; ?>-<?php echo $vc['id']; ?> data-comments-<?php echo $v['id']; ?>-<?php echo $vc['id']; ?>-<?php echo $vcs['id']; ?> <?php if($vcs['reply_id'] > 0): ?>data-comments-reply-<?php echo $v['id']; ?>-<?php echo $vcs['reply_id']; endif; ?>">
                        <img src="/Public/Common/Images/user-img-sm.gif" class="am-circle user-portrait" width="30" height="30" />
                        <a href="javascript:;"><?php echo $vcs['nickname']; ?></a>
                        <span><?php echo L('common_operation_reply'); ?></span>
                        <a href="javascript:;"><?php echo $vcs['reply_nickname']; ?></a>
                        <span class="cr-999"><?php echo $vcs['add_time']; ?></span>
                        <div class="am-fr comments-item-tools">
                          <span class="c-p cr-999 m-l-20 submit-delete" data-id="<?php echo $vcs['id']; ?>" data-url="<?php echo U('Admin/Bubble/MoodCommentsDelete'); ?>" data-list-tag=".data-comments-<?php echo $v['id']; ?>-<?php echo $vc['id']; ?>-<?php echo $vcs['id']; ?>, .data-comments-reply-<?php echo $v['id']; ?>-<?php echo $vcs['id']; ?>">
                            <i class="am-icon-trash-o"></i><?php echo L('common_operation_delete'); ?>
                          </span>
                        </div>
                        <div><?php echo $vcs['content']; ?></div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <!-- mood end -->

      <!-- page start -->
      <?php echo $page_html; ?>
      <!-- page end -->

      <!-- comments win start -->
      <div class="am-modal am-modal-prompt" tabindex="-1" id="bubble-comments" data-url="<?php echo U('Admin/Bubble/MoodComments'); ?>" data-mood-praise-msg="<?php echo L('bubble_mood_praise_error'); ?>" data-mood-comments-msg="<?php echo L('bubble_mood_comments_error'); ?>">
        <div class="am-modal-dialog am-radius">
          <div class="am-modal-hd"></div>
          <div class="am-modal-bd">
            <textarea rows="3" minlength="1" maxlength="255" class="am-radius am-modal-prompt-input" placeholder="<?php echo L('bubble_comments_placeholder'); ?>" data-validation-message="<?php echo L('bubble_comments_format'); ?>"></textarea>
          </div>
          <div class="am-modal-footer">
            <span class="am-modal-btn" data-am-modal-cancel><?php echo L('common_operation_cancel'); ?></span>
            <span class="am-modal-btn" data-am-modal-confirm><?php echo L('common_operation_comments'); ?></span>
          </div>
        </div>
      </div>
      <!-- comments win end -->
    <?php else: ?>
        <hr data-am-widget="divider" style="" class="am-divider am-divider-default" />
      <?php include dirname(__FILE__) . '/../Public/NoData.html'; ?>
    <?php endif; ?>
    <!-- list start -->
  </div>
</div>
<!-- right content end  -->

<!-- footer start -->
<?php include dirname(__FILE__) . '/../Public/Footer.html'; ?>
<!-- footer end