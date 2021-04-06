<div class="container">
    <?if ($this->config->item('use_signup')) {?>
        <?$currkey = strtoupper($model->scaff_opt('kind'));?>
        <nav class="blue">
            <div class="nav-wrapper">
                <div class="dcore-tabs row">
                    <div class="scroller-wrap">
                        <div class="scroller-h">
                            <div class="dcore-tab" style="min-width: <?=100 / (count($model->available_kinds) + 1)?>%;">
                                <?
                                $url = $model->url_list(array('kind' => ''));
                                $n = $this->db->where('active', 'Y')->count_all_results($model->table);
                                ?>
                                <a href="<?=$url?>" class="waves-effect<?=$currkey == '' ? ' active' : ''?>">
                                    모든 사용자&nbsp;
                                    <span class="chip white blue-text"><?=$n?></span>
                                </a>
                            </div>
                            <?foreach ($model->available_kinds as $k => $v) {?>
                                <?
                                $url = $model->url_list(array('kind' => strtolower($k)));
                                $n = $this->db->where('active', 'Y')->where('kind', $k)->count_all_results($model->table);
                                ?>
                                <?if ($k == 'USER' || $k == 'ADMIN' || $k == 'ADMINPERM') {?>
                                    <div class="dcore-tab" style="min-width: <?=100 / (count($model->available_kinds) + 1)?>%;">
                                        <a href="<?=$url?>" class="waves-effect<?=$currkey == $k ? ' active' : ''?>">
                                            <?=htmlspecialchars($v)?>&nbsp;
                                            <span class="chip white blue-text"><?=$n?></span>
                                        </a>
                                    </div>
                                <?}?>
                            <?}?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <hr class="hr1 transparent" />
    <?}?>

    <?include APPPATH.'views/skin/posts/list/header.php';?>
    <?if (count($rows) == 0) {?>
        <?include APPPATH.'views/skin/default/no-entry.php';?>
    <?} else {?>
        <div class="overflow-x-outer">
            <div class="overflow-x">
                <table class="table bordered highlight dcore-list">
                    <thead>
                    <tr>
                        <?if ($model->has_perm_deletes()) {?>
                            <th class="dcore-list-check-item">
                                <span>
                                    <input type="checkbox" id="dcore-list-checkbox-toggle" class="filled-in" />
                                    <label for="dcore-list-checkbox-toggle"></label>
                                </span>
                            </th>
                        <?}?>
                        <th class="kind packed"><?=$model->html_sortby('kind', $this->lang->line('auth_user_type'))?></th>
                        <?if (!$this->config->item('userid_as_email')) {?>
                            <th class="userid packed"><?=$model->html_sortby('userid', $this->lang->line('auth_userid'))?></th>
                        <?}?>
                        <th class="email packed"><?=$model->html_sortby('email', $this->lang->line('auth_email'))?></th>
                        <th class="name"><?=$model->html_sortby('name', $this->lang->line('auth_name'))?></th>
                        <th class="name"><?=$model->html_sortby('nickname', $this->lang->line('auth_nickname'))?></th>
                        <th class="time packed"><?=$model->html_sortby('time', $this->lang->line('auth_date_signup'))?></th>
                        <th class="time-last-login packed"><?=$model->html_sortby('time_last_login', $this->lang->line('auth_date_last_login'))?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?foreach ($rows as $row) {?>
                        <tr class="
                            <?=!empty($current_row) && $current_row_uid == $row->uid ? 'current' : ''?>
                        ">
                            <?if ($model->has_perm_deletes()) {?>
                                <td class="dcore-list-check-item">
                                <span>
                                    <input type="checkbox" id="dcore-list-checkbox-toggle-<?=$row->uid?>" value="<?=$row->uid?>"<?=$this->auth->userinfo->uid == $row->uid ? ' disabled' : ''?> class="filled-in" />
                                    <label for="dcore-list-checkbox-toggle-<?=$row->uid?>"></label>
                                </span>
                                </td>
                            <?}?>
                            <td class="kind packed">
                                <a href="<?=$model->url_view($row->uid)?>"><?=
                                    $this->lang->line('auth_'.strtolower($row->kind))
                                    ?></a>
                            </td>
                            <?if (!$this->config->item('userid_as_email')) {?>
                                <td class="userid">
                                    <a href="<?=$model->url_view($row->uid)?>"><?=$row->userid?></a>
                                </td>
                            <?}?>
                            <td class="email">
                                <a href="<?=$model->url_view($row->uid)?>"><?=$row->email?></a>
                            </td>
                            <td class="name">
                                <a href="<?=$model->url_view($row->uid)?>"><?=$row->name?></a>
                            </td>
                            <td class="nickname">
                                <a href="<?=$model->url_view($row->uid)?>"><?=$row->nickname?></a>
                            </td>
                            <td class="time packed"><a href="<?=$model->url_view($row->uid)?>"><?=$row->time?></a></td>
                            <td class="time-last-login packed"><a href="<?=$model->url_view($row->uid)?>"><?=empty($row->time_last_login) ? '-' : $row->time_last_login?></a></td>
                        </tr>
                    <?}?>
                    </tbody>
                </table>
            </div>
        </div>
    <?}?>

    <hr class="hr3" />

    <div class="row">
        <div class="col s12 m6" style="margin-bottom: 10px;">
            <?=$pagination->html('class="pagination dcore-pagination"')?>
            <div class="hide-on-med-and-up"><hr class="hr1 transparent" /></div>
        </div>
        <div class="col s12 m6 right-align dcore-buttons">
            <?if ($this->config->item('use_signup')) {?>
                <!-- 탈퇴안내 이메일 발송 추가  -->
                <button type="button" class="button weak-" id="sendEmail">
                    <span class="fa fa-file-text"></span>탈퇴안내 메일발송
                </button>
                <!-- 최종접속시간 2년이상 회원 탈퇴처리 추가  -->
                <button type="button" class="button danger-" id="withdrawal">
                    <span class="fa fa-ban"></span>휴면회원 탈퇴처리
                </button>
                <!-- ----------------------------------- -->
                <?if ($model->has_perm_deletes()) {?>
                <?}?>
                <button type="button" class="button danger- delete require-check" disabled>
                    <span class="fa fa-ban"></span>
                    <?=$this->lang->line('auth_withdrawal')?>
                </button>
                <a href="<?=$model->url_list(array('format' => 'csv'))?>/user.csv" class="button weak- no-pjax">
                    <span class="fa fa-download"></span> CSV
                </a>
            <?}?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        var checks = $('td.dcore-list-check-item input[type="checkbox"]').change(function() {
            if (checks.filter(':checked').length > 0) {
                $('button.require-check').prop('disabled', false);
            } else {
                $('button.require-check').prop('disabled', true);
            }
        });

        $('th.dcore-list-check-item input[type="checkbox"]').change(function(e) {
            checks.not(':disabled').prop('checked', $(this).is(':checked')).change();
        });

        $('button.delete').click(function(e) {
            e.preventDefault();

            if ($(this).is(':disabled')) {
                return;
            }

            $.dconfirm('<?=$this->lang->line('msg_are_you_sure')?>', function () {
                $(this).prop('disabled', true);

                var arr = [];
                $('td.dcore-list-check-item input:checked').each(function() {
                    arr.push($(this).val());
                });

                $.post('<?=$model->url_deletes()?>', {
                    uid: arr
                }, function (data) {
                    $.dcoreSubmitResult(data);
                }, 'json');
            });
        });
    });

    /* 마지막 접속경과 2년이상 회원 탈퇴처리 */
    $('#withdrawal').click(function(e) {
        e.preventDefault();
        $.dconfirm('<?=$this->lang->line('msg_are_you_sure_delete')?>', function () {
            $.ajax({
                url: '<?=$model->url_withdrawal_delete()?>',
                type: 'POST',
                timeout: 2000,
                dataType: 'json',
                data: {},
                success: function(data, status, xhr) {
                    console.log(data.row);
                    $.dcoreSubmitResult(data.row);
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });
        });
    });

    /* 탈퇴안내 메일발송 */
    $('#sendEmail').click(function(e) {
        e.preventDefault();
        $.dconfirm('탈퇴안내 메일을 발송하시겠습니까?', function () {
            $.ajax({
                url: '<?=$model->url_withdrawal_send_email()?>',
                type: 'POST',
                timeout: 2000,
                dataType: 'json',
                data: {},
                success: function(data, status, xhr) {
                    //console.log(data);
                    $.dcoreSubmitResult(data);
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                }
            });
        });
    });
</script>
