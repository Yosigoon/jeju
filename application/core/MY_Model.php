<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// https://github.com/jesseterry/CodeIgniter-CRUD-Model
// https://github.com/dennisbot/codeIgniter-HMVC-CRUD-Model
class MY_Model extends CI_Model {
    public $table;

    public $title_field;
    public $description_field;
    public $owner_field;
    public $module_field;
    public $slug_field;
    public $slug_field_from;
    public $lang_field;
    public $hit_field;
    public $date_created_field;
    public $date_modified_field;
    public $delete_reject_flag;
    public $delete_reject_productName;

    public $perm_create = array('__LOGGEDIN__');
    public $perm_list = true;
    public $perm_list_csv = array('ADMIN');
    public $perm_view = true;
    public $perm_search = true;
    public $perm_update = array('__OWNER__', 'STAFF', 'ADMIN');
    public $perm_deletes = array('STAFF', 'ADMIN');
    public $perm_delete = array('__OWNER__', 'STAFF', 'ADMIN');
    public $perm_moderators = array();
    public $perm_recaptcha = false;//array('__OTHER__');
    public $perm_set = array('ADMIN');
    public $perm_email_notification = false; //array('__OTHER__', 'USER');

    public $skin_with_wrapper = true;
    public $skin_header = 'skin/default/header';
    public $skin_footer = 'skin/default/footer';
    public $skin_create = 'skin/default/form';
    public $skin_list = 'skin/default/list';
    public $skin_view = 'skin/default/view';
    public $skin_update = 'skin/default/form';
    public $skin_delete = 'skin/default/delete';
    public $skin_error = 'skin/default/error';
    public $skin_data = array();

    public $view_with_list = false;

    public $scaff_search_keys = array();
    public $scaff_pages_per_set = 10;
    public $scaff_rows_per_page = 15;
    public $scaff_show_page_always = false;
    public $scaff_admin = false;
    public $permalink_base_url;


    /* ---- */
    public $primary_key = 'uid';
    public $primary_key_pattern = '/^[0-9]+$/';
    public $primary_key_original;
    public $debug = false;
    public $debug_last_query;

    public $query;
    public $form_values = array();
    protected $default_validation_rules = 'validation_rules';
    protected $validation_rules;
    public $validation_errors;

    public $native_methods = array(
        'select', 'select_max', 'select_min', 'select_avg', 'select_sum', 'join',
        'where', 'or_where', 'where_in', 'or_where_in', 'where_not_in', 'or_where_not_in',
        'like', 'or_like', 'not_like', 'or_not_like', 'group_by', 'distinct', 'having',
        'or_having', 'order_by', 'limit'
    );
    public $reserved_segments = array('create', 'deletes', 'search', 'download');
    public $uid_parent;
    public $filter = array();

    public $date_relative = false;
    public $date_relative_sec;
    public $date_relative_ago;
    public $date_format = 'Y. n. j.'; // 'Y-m-d'

    public $scaff_mode;
    public $scaff_module;
    protected $scaff_base_url;
    protected $scaff_opt = array();
    public $scaff_opt_default = array();
    public $scaff_opt_custom = array();
    public $scaff_opt_fixed = array();
    public $scaff_sync_lang = false;
    public $cleanup_pending_files = true;

    public $use_markdown = false;
    public $markdown_fields = array();

    public $upload_modules = array(
        'featured_image' => array(
            'image_only' => true,
            'single' => true,
        ),
        'photo' => array(
            'image_only' => true,
        ),
        'attachment' => array(),
        'editor_file' => array(
            'cleanup' => false,
        ),
        'editor_image' => array(
            'image_only' => true,
            'cleanup' => false,
        ),
        'cover' => array(
            'image_only' => true,
            'single' => true,
        ),
        'icon' => array(
            'image_only' => true,
            'single' => true,
        ),
        'host' => array(
            'image_only' => true,
            'single' => true,
        ),
    );

    public $upload_options = array(
        'key' => 'upload',
        'image_only' => false,
        'single' => false,
        'cleanup' => true, /* duplicate when update */
    );

    /**
     *  최종접속시간 2년 지난 회원 탈퇴처리
     */
    public function url_withdrawal_delete() {
        return '/admin/withdrawalUser';
    }

    /**
     *  탈퇴안내 메일 발송
     */
    public function url_withdrawal_send_email() {
        return '/admin/withdrawalSendEmail';
    }
}
