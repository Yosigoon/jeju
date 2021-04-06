<?php
include_once APPPATH.'third_party/iamport.php';

class Shop_order extends MY_Model {
    public $is_notifying = false;
    public $table = 'd_shop_order';

    public $owner_field = 'uid_user';
    public $date_created_field = 'time';
    public $date_modified_field = 'time_last_edit';

    public $perm_create = false;
    public $perm_list = array('__OWNER__', '__LOGGEDIN__');
    public $perm_view = array('__OWNER__', 'STAFF', 'ADMIN');
    public $perm_update = false;
    public $perm_delete = array('STAFF', 'ADMIN');
    public $perm_set = array('STAFF', 'ADMIN');
    public $perm_recaptcha = false;

    public $skin_with_wrapper = true;
    public $skin_list = 'skin/shop_order/list';
    public $skin_view = 'skin/shop_order/view';
    public $skin_update = 'skin/shop_order/form';
    public $skin_error = 'skin/default/error';
    public $view_with_list = false;
    public $scaff_rows_per_page = 5;
    public $scaff_search_keys = array(
            'order_id',
            'payment_id',
            'email',
            'billing_name',
            'billing_phone',
            'billing_address1',
            'billing_address2',
            'shipping_name',
            'shipping_phone',
            'shipping_address1',
            'shipping_address2',
    );

    public $date_format_list = 'Y.m.d';
    public $date_format_view = 'Y.m.d';

    public $available_statuses;
    public $available_payment_methods;
    public $available_banks;

    public function __construct() {
            parent::__construct();

            $this->lang->load('order', detect_lang());
            $this->config->load('shipping_companies');

            $this->available_statuses = array(
                    'pending_order' => $this->lang->line('order_status_pending_order'), // 결제 시도 전
            //  'pending_payment' => $this->lang->line('order_status_pending_payment'), // 주문 취소 가능
                    'received' => $this->lang->line('order_status_received'), // 주문 취소 가능, 배송지 정보 변경 가능
            //  'pending_shipment' => $this->lang->line('order_status_pending_shipment'), // 주문 취소 불가, 송장번호 미입력, 배송지 정보 변경 불가능
            //  'shipped' => $this->lang->line('order_status_shipped'), // 주문 취소 불가, 송장번호 입력
            //  'completed' => $this->lang->line('order_status_completed'), // 반품 가능
                    'canceled' => $this->lang->line('order_status_canceled'), // 취소됨
            );

            $this->available_payment_methods = array(
                    'card' => $this->lang->line('payment_method_card'),
            //  'trans' => $this->lang->line('payment_method_trans'),
            //  'vbank' => $this->lang->line('payment_method_vbank'),
            //  'phone' => $this->lang->line('payment_method_phone'),
            );

            $this->available_dc_types = array(
                    'none' => '없음',
                    'local' => '제주도민 할인',
                    'guest' => '투숙객 할인',
            );

            $this->available_banks = array(
                    '11' => 'NH농협은행',
                    '04' => 'KB국민은행',
                    '88' => '신한은행',
                    '20' => '우리은행',
                    '03' => '기업은행',
                    '81' => 'KEB하나은행',
                    '31' => '대구은행',
                    '32' => '부산은행',
                    '71' => '우체국',
                    '34' => '광주은행',
                    '23' => 'SC은행',
                    '39' => '경남은행',
                    '27' => '한국씨티은행',
                    '07' => '수협중앙회',
                    /*
                    '02' => '한국산업은행',
                    '05' => '외환은행',
                    '12' => '단위농축협',
                    '35' => '제주은행',
                    '37' => '전북은행',
                    '38' => '강원은행',
                    '45' => '새마을금고',
                    '48' => '신협중앙회',
                    '50' => '상호저축은행',
                    '53' => '씨티은행',
                    '64' => '산림조합중앙회',
                    '83' => '평화은행',
                    */
            );

            if (!$this->db->table_exists($this->table)) {
                    $this->db->trans_start();
                    $this->db->query("
                            CREATE TABLE IF NOT EXISTS `{$this->table}` (
                                    `uid`               BIGINT UNSIGNED         NOT NULL AUTO_INCREMENT,
                                    `uid_user`          BIGINT UNSIGNED         NOT NULL    DEFAULT 0,

                                    `order_id`          VARCHAR(64)             NOT NULL    DEFAULT '',
                                    `payment_id`        VARCHAR(255)            NOT NULL    DEFAULT '',
                                    `merchant_uid`      VARCHAR(255)            NOT NULL    DEFAULT '',
                                    `payment_method`    VARCHAR(64)             NOT NULL    DEFAULT '',
                                    `passwd`            CHAR(40),
                                    `status`            VARCHAR(64)             NOT NULL    DEFAULT 'pending_order',

                                    `time`              DATETIME                NOT NULL,
                                    `time_last_edit`    DATETIME,
                                    `time_paid`         DATETIME,

                                    `currency`          VARCHAR(8),
                                    `currency_name`     VARCHAR(8),
                                    `currency_rtdp`     INT                     NOT NULL    DEFAULT 0,
                                    `amount`            FLOAT                   NOT NULL    DEFAULT 0,
                                    `price`             FLOAT                   NOT NULL    DEFAULT 0,
                                    `discount`          FLOAT                   NOT NULL    DEFAULT 0,
                                    `discount_by_type`  FLOAT                   NOT NULL    DEFAULT 0,
                                    `shipping_cost`     FLOAT                   NOT NULL    DEFAULT 0,
                                    `shipping_company`  VARCHAR(255)            NOT NULL    DEFAULT '',
                                    `shipping_id`       VARCHAR(255)            NOT NULL    DEFAULT '',
                                    `coupon_code`       VARCHAR(64)             NOT NULL    DEFAULT '',

                                    `email`             TEXT,
                                    `billing_name`      VARCHAR(64),
                                    `billing_phone`     TEXT,
                                    `billing_zipcode`   VARCHAR(16),
                                    `billing_country`   TEXT,
                                    `billing_city`      TEXT,
                                    `billing_address1`  TEXT,
                                    `billing_address2`  TEXT,
                                    `shipping_name`     VARCHAR(64),
                                    `shipping_phone`    TEXT,
                                    `shipping_zipcode`  VARCHAR(16),
                                    `shipping_country`  TEXT,
                                    `shipping_city`     TEXT,
                                    `shipping_address1` TEXT,
                                    `shipping_address2` TEXT,

                                    `vbank_date`        VARCHAR(255),
                                    `vbank_holder`      VARCHAR(255),
                                    `vbank_name`        VARCHAR(255),
                                    `vbank_num`         VARCHAR(255),
                                    `refund_bank`       VARCHAR(255),
                                    `refund_account`    VARCHAR(255),
                                    `refund_holder`     VARCHAR(255),

                                    `memo`              TEXT,
                                    `dc_type`           VARCHAR(64)             NOT NULL    DEFAULT 'none',
                                    `discount_by_serial_stay`   FLOAT                   NOT NULL    DEFAULT 0,

                                    INDEX (`uid_user`, `status`),
                                    INDEX (`order_id`),
                                    INDEX (`order_id`, `passwd`),
                                    INDEX (`merchant_uid`(64)),
                                    INDEX (`payment_method`),
                                    PRIMARY KEY (`uid`)
                            ) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
                    ");
                    $this->db->trans_complete();
            }

            $this->scaff_opt_default = array(
                    'status' => 'all',
                    'method' => 'all',
                    'sortby' => 'time',
                    'direction' => 'desc',
            );

            $this->load_model('shop_cart', 'cart');
    }

    public function cart($uid_order = 0) {
            $this->load_model('shop_cart', 'cart');
            $this->cart->uid_order = $uid_order;
            return $this->cart;
    }

    public function url_cart($uid) {
            return $this->url_view($uid, false).'/cart';
    }

    public function scaff($n) {
            $first_segment = $this->first_segment($n);
            if (preg_match($this->primary_key_pattern, $first_segment)) {
                $uid = $first_segment;
                $this->scaff_mode = $this->uri->segment($n + 1);
                switch ($this->uri->segment($n + 1)) {
                case 'cart':
                    if ($this->scaff_admin) {
                            $this->scaff_base_url = uri_segs(1, $n - 1);
                            $cart = $this->cart($uid);
                            $cart->scaff_admin = true;
                            $cart->perm_create = false;
                            $cart->perm_update = false;
                            $cart->perm_search = false;
                            $cart->perm_list = false;
                            $cart->perm_view = false;
                            $cart->perm_set = array('STAFF', 'ADMIN');
                            $cart->scaff($n + 2);
                            return;
                    }
                    break;
                case 'notify':
                    if ($this->scaff_admin) {
                            switch ($this->uri->segment($n + 2)) {
                            case 'status':
                                    echo json_encode($this->notify_status_email($uid));
                                    return;
                            }
                    }
                    break;
                case 'cancel':
                    $this->db->insert( "wings_log", array('res' =>json_encode("======== cancel start========")) );
                    if ($this->input->method() == 'post') {
                        $row = $this->get_by_id($uid);
                        if (!empty($row) && $row->status == 'received') {
                            $uid_cart = $this->uri->segment($n + 2);
                            if ($uid_cart) {
                                $this->load_model('shop_cart', 'cart');
                                $this->cart->uid_order = $row->uid;
                                $row_cart = $this->cart->get_by_id($uid_cart);
                                if (!empty($row_cart) && $row_cart->status == 'normal') {
                                    $error = $this->cancel_order_item($row, $row_cart);
                                    if ($error) {
                                        echo json_encode(array('error' => $error));
                                        return;
                                    }
                                    $this->scaff_base_url = uri_segs(1, $n - 1);
                                    echo json_encode(array('redirect' => $this->url_view($row->order_id, false)));
                                    return;
                                }
                            }
                        }
                    }
                    break;
                case 'zero-cancel':
                    $this->db->insert( "wings_log", array('res' =>json_encode("======== zero-cancel start========")) );
                    $row = $this->get_by_id($uid);
                    if (!empty($row) && $row->status == 'received') {
                        $uid_cart = $this->uri->segment($n + 2);
                        if ($uid_cart) {
                            $this->load_model('shop_cart', 'cart');
                            $this->cart->uid_order = $row->uid;
                            $row_cart = $this->cart->get_by_id($uid_cart);
                            if (!empty($row_cart) && $row_cart->status == 'normal') {
                                $this->db->insert( "wings_log", array('res' =>json_encode("======== first_segment========")) );
                                $this->db->insert( "wings_log", array('res' =>json_encode($this->first_segment(6))) );

                                // cms 예약번호가 있는지 확인
                                $cmd = "SELECT length(cms_reserv_id) as cnt FROM dev_playcecampjeju.d_shop_cart where uid =" . $this->first_segment(6);
                                $query = $this->db->query($cmd);
                                $r_cart = $query->row();

                                $cart_has_cms_reserv = ($r_cart->cnt == 0) ? FALSE : TRUE;


                                $this->load->model('wings');

                                $memo_cms = $row_cart->memo_cms;

                                $price_adult_count = 0;
                                $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                                if ($days < 1) {
                                    $days = 1;
                                }
                                $rates = $this->wings->get_rooms(array(
                                    'arrival_date' => $row_cart->check_in,
                                    'departure_date' => $row_cart->check_out,
                                    'room_count' => (int)$row_cart->quantity,
                                    'adult_count' => (int)$row_cart->adult_count,
                                    'child_count' => (int)$row_cart->child_count,
                                    'rate_type_code' => $row_cart->rate_type_code,
                                    'room_type_code' => $row_cart->room_type_code,
                                ));

                                if (empty($rates)) {
                                } else {
                                    foreach ($rates as $rate) {
                                        if (!empty($rate->RoomTypeList)) {
                                            foreach ($rate->RoomTypeList as $room) {
                                                if (!empty($room->RoomTypeCode)) {
                                                    $MinAdultPersons = $room->MinAdultPersons;
                                                    $MaxAdultPersons = $room->MaxAdultPersons;
                                                    if ($MinAdultPersons <= 0) {
                                                        $MinAdultPersons = 1;
                                                    }

                                                    if (strpos($row_cart->room_type_code,'STD') !== false || strpos($row_cart->room_type_code,'SPD') !== false || strpos($row_cart->room_type_code,'CSD') !== false || strpos($row_cart->room_type_code,'CAD') !== false) {
                                                        for ($c = $MinAdultPersons; $c <= $MaxAdultPersons; $c++) {
                                                            if ($c == $row_cart->adult_count) {
                                                                $price_adult_count += ($c - 1) * 10000 * $days;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $room_rate = $row_cart->price * $row_cart->quantity;

                                if ($row_cart->dc_rate_coupon > 0) {
                                    $room_rate = round($room_rate * (1 - ($row_cart->dc_rate_coupon / 100)));
                                }

                                if($row_cart->no_series_dc == 'N')
                                {
                                    $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                                    if ($days < 1) {
                                        $days = 1;
                                    }

                                    if($days == 2){
                                        $dis_serial_stay= round(((($row_cart->price / $days)*2 )*($row_cart->two_dc_rate/ 100)));
                                        $days  = 2;
                                    }else if($days > 2){
                                        $dis_serial_stay= round(((($row_cart->price / $days)*3 )*($row_cart->three_dc_rate/ 100)));
                                        $days  = 3;
                                    }
                                }else{
                                    $dis_serial_stay = 0;
                                }

                                $cmd = "SELECT order_id FROM dev_playcecampjeju.d_shop_order where uid = ".$row_cart->uid_order;
                                $query = $this->db->query($cmd);
                                $r_order = $query->row()->order_id;
                                if($this->auth->is_login() && substr( $r_order, 0, 5 ) !== "GUEST"){
                                    $room_rate = $room_rate-$dis_serial_stay-($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate / 100));
                                }else{
                                    $room_rate = $room_rate-$dis_serial_stay;
                                }
                                if($room_rate < 0)
                                    $room_rate = 0;

                                $this->db->insert( "wings_log", array('res' =>json_encode($row_cart->daily_charges)));

                                // 윙스 remark 수정
                                if (!empty($row_cart->memo_cms)) {

                                    $wing_param = array (
                                        'is_modify'        => true,
                                        'uid_order'        => $row->order_id,
                                        'uid_cart'         => $this->first_segment(6),
                                        'name'             => $row->billing_name,
                                        'phone'            => $row->billing_phone,
                                        'email'            => $row->email,
                                        'daily_charge'     => $row_cart->daily_charges,
                                        'arrival_date'     => $row_cart->check_in,
                                        'departure_date'   => $row_cart->check_out,
                                        'room_count'       => $row_cart->quantity,
                                        'adult_count'      => $row_cart->adult_count,
                                        'child_count'      => $row_cart->child_count,
                                        'room_type_code'   => $row_cart->room_type_code,
                                        'rate_type_code'   => $row_cart->rate_type_code,
                                        'total_room_rate'  => $room_rate,
                                    );

                                    $memo_cms = $memo_cms . "\n[0원취소됨]";
                                    $wing_param += ['memo' => $memo_cms];
                                    // echo json_encode($wing_param);

                                    $res = $this->wings->make_reservation($wing_param);
                                    $this->db->insert( "wings_log", array('res' =>json_encode($res)));
                                    $this->db->insert( "wings_log", array('res' =>json_encode("======== zero-cancel end========")) );
                                    // echo json_encode($res);
                                }
                                else {
                                    // $wing_param += [ 'memo' => '[0원취소됨]' ];
                                    // echo json_encode($wing_param);
                                }

                                $res = $this->wings->make_reservation(array(
                                    'is_cancel' => true,
                                    // 윙스 강제 취소
                                    'uid_order' => $row->order_id,
                                    'uid_cart' => $this->first_segment(6),
                                ));

                                $requestParam = array(
                                    "admin_id"              => $this->auth()->userinfo->userid,
                                    "param_uid_order"       => $row->order_id,
                                    "param_uid_cart"        => $this->first_segment(6),
                                    "status"                => ($res->ReservationResponse->ReservationResult[0]->Success) ? "success" : "fail",
                                    "wings_error_code"      => '',
                                    "wings_error_message"   => json_encode($res)
                                );

                                $this->db->insert('wings_force_log', $requestParam);
                                // echo json_encode($requestParam);

                                /* 웹 취소처리 */
                                $cmd = "update  dev_playcecampjeju.d_shop_cart
                                        set     status = 'canceled_zero', time_return_req = CURRENT_TIMESTAMP(), return_amount = 0, memo_cms = '" . $memo_cms . "'
                                        WHERE   uid = " . $this->first_segment(6);
                                $this->db->query($cmd);

                                /* send email */
                                // if (!$this->scaff_admin) {
                                        $this->notify_status_email($row->uid, $row_cart->uid, -2);
                                // }

                                echo json_encode(array('redirect' => '/admin/shop/orders' , 'msg' => '0원취소 처리되었습니다.'));
                            }
                        }
                    }
                    return;
                    break;
                case 'force-cancel':
                    $this->db->insert( "wings_log", array('res' =>json_encode("======== force-cancel start========")) );
                    $row = $this->get_by_id($uid);
                    if (!empty($row) && $row->status == 'received') {
                        $uid_cart = $this->uri->segment($n + 2);
                        if ($uid_cart) {
                            $this->load_model('shop_cart', 'cart');
                            $this->cart->uid_order = $row->uid;
                            $row_cart = $this->cart->get_by_id($uid_cart);
                            if (!empty($row_cart) && $row_cart->status == 'normal') {


                                // cms 예약번호가 있는지 확인
                                $cmd = "SELECT length(cms_reserv_id) as cnt FROM dev_playcecampjeju.d_shop_cart where uid =" . $this->first_segment(6);
                                $query = $this->db->query($cmd);
                                $r_cart = $query->row();

                                $cart_has_cms_reserv = ($r_cart->cnt == 0) ? FALSE : TRUE;


                                $memo_cms = $row_cart->memo_cms;

                                $this->load->model('wings');

                                $price_adult_count = 0;
                                $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                                if ($days < 1) {
                                    $days = 1;
                                }
                                $rates = $this->wings->get_rooms(array(
                                    'arrival_date' => $row_cart->check_in,
                                    'departure_date' => $row_cart->check_out,
                                    'room_count' => (int)$row_cart->quantity,
                                    'adult_count' => (int)$row_cart->adult_count,
                                    'child_count' => (int)$row_cart->child_count,
                                    'rate_type_code' => $row_cart->rate_type_code,
                                    'room_type_code' => $row_cart->room_type_code,
                                ));

                                if (empty($rates)) {
                                } else {
                                    foreach ($rates as $rate) {
                                        if (!empty($rate->RoomTypeList)) {
                                            foreach ($rate->RoomTypeList as $room) {
                                                if (!empty($room->RoomTypeCode)) {
                                                    $MinAdultPersons = $room->MinAdultPersons;
                                                    $MaxAdultPersons = $room->MaxAdultPersons;
                                                    if ($MinAdultPersons <= 0) {
                                                        $MinAdultPersons = 1;
                                                    }

                                                    if (strpos($row_cart->room_type_code,'STD') !== false || strpos($row_cart->room_type_code,'SPD') !== false || strpos($row_cart->room_type_code,'CSD') !== false || strpos($row_cart->room_type_code,'CAD') !== false) {
                                                        for ($c = $MinAdultPersons; $c <= $MaxAdultPersons; $c++) {
                                                            if ($c == $row_cart->adult_count) {
                                                                $price_adult_count += ($c - 1) * 10000 * $days;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $room_rate = $row_cart->price * $row_cart->quantity;

                                if ($row_cart->dc_rate_coupon > 0) {
                                    $room_rate = round($room_rate * (1 - ($row_cart->dc_rate_coupon / 100)));
                                }

                                if($row_cart->no_series_dc == 'N')
                                {
                                    $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                                    if ($days < 1) {
                                        $days = 1;
                                    }

                                    if($days == 2){
                                        $dis_serial_stay= round(((($row_cart->price / $days)*2 )*($row_cart->two_dc_rate/ 100)));
                                        $days  = 2;
                                    }else if($days > 2){
                                        $dis_serial_stay= round(((($row_cart->price / $days)*3 )*($row_cart->three_dc_rate/ 100)));
                                        $days  = 3;
                                    }
                                }else{
                                    $dis_serial_stay = 0;
                                }

                                $cmd = "SELECT order_id FROM dev_playcecampjeju.d_shop_order where uid = ".$row_cart->uid_order;
                                $query = $this->db->query($cmd);
                                $r_order = $query->row()->order_id;
                                if($this->auth->is_login() && substr( $r_order, 0, 5 ) !== "GUEST"){
                                    $room_rate = $room_rate-$dis_serial_stay-($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate / 100));
                                }else{
                                    $room_rate = $room_rate-$dis_serial_stay;
                                }
                                if($room_rate < 0)
                                    $room_rate = 0;

                                $this->db->insert( "wings_log", array('res' =>json_encode($row_cart->daily_charges)));

                                // 윙스 remark 수정
                                if (!empty($row_cart->memo_cms)) {

                                    $wing_param = array (
                                        'is_modify'        => true,
                                        'uid_order'        => $row->order_id,
                                        'uid_cart'         => $this->first_segment(6),
                                        'name'             => $row->billing_name,
                                        'phone'            => $row->billing_phone,
                                        'email'            => $row->email,
                                        'daily_charge'     => $row_cart->daily_charges,
                                        'arrival_date'     => $row_cart->check_in,
                                        'departure_date'   => $row_cart->check_out,
                                        'room_count'       => $row_cart->quantity,
                                        'adult_count'      => $row_cart->adult_count,
                                        'child_count'      => $row_cart->child_count,
                                        'room_type_code'   => $row_cart->room_type_code,
                                        'rate_type_code'   => $row_cart->rate_type_code,
                                        'total_room_rate'  => $room_rate,
                                    );

                                    $memo_cms = $memo_cms . "\n[전액환불됨]";

                                    $wing_param += ['memo' => $memo_cms];
                                    // echo json_encode($wing_param);

                                    $res = $this->wings->make_reservation($wing_param);
                                    $this->db->insert( "wings_log", array('res' =>json_encode($res)));
                                    $this->db->insert( "wings_log", array('res' =>json_encode("======== force-cancel end========")) );
                                    // echo json_encode($res);
                                }else {
                                    // $wing_param += [ 'memo' => '[전액환불됨]' ];
                                    // echo json_encode($wing_param);
                                }

                                $res = $this->wings->make_reservation(array(
                                    'is_cancel' => true,
                                    // 윙스 강제 취소
                                    'uid_order' => $row->order_id,
                                    'uid_cart' => $this->first_segment(6),
                                ));

                                $requestParam = array(
                                    "admin_id"              => $this->auth()->userinfo->userid,
                                    "param_uid_order"       => $row->order_id,
                                    "param_uid_cart"        => $this->first_segment(6),
                                    "status"                => ($res->ReservationResponse->ReservationResult[0]->Success) ? "success" : "fail",
                                    "wings_error_code"      => '',
                                    "wings_error_message"   => json_encode($res)
                                );

                                $this->db->insert('wings_force_log', $requestParam);
                                // echo json_encode($requestParam);

                                $amount = $this->item_amount($row, $row_cart);

                                if ($amount < 0) {
                                    $amount = 0;
                                }

                                /* cancel payment */
                                if ($amount > 0 && $row->payment_id && $row->merchant_uid) {
                                    $iamport = new Iamport($this->config->item('imp_api_key'), $this->config->item('imp_api_secret'));
                                    $result = $iamport->cancel(array(
                                        'imp_uid' => $row->payment_id,
                                        'merchant_uid' => $row->merchant_uid,
                                        'amount' => $amount,
                                        'reason' => '전액환불',
                                        'refund_bank' => $row->refund_bank,
                                        'refund_account' => $row->refund_account,
                                        'refund_holder' => $row->refund_holder,
                                    ));
                                    if (!empty($result->error)) {
                                    //  return $result->error['code'].': '.$result->error['message'];
                                    }
                                }

                                /* 웹 취소처리 */
                                $cmd = "update  dev_playcecampjeju.d_shop_cart
                                        set     status = 'canceled_force', time_return_req = CURRENT_TIMESTAMP(), return_amount = '" . $amount . "', memo_cms = '" . $memo_cms . "'
                                        WHERE   uid = " . $this->first_segment(6);
                                $this->db->query($cmd);

                                /* send email */
                                // if (!$this->scaff_admin) {
                                        $this->notify_status_email($row->uid, $row_cart->uid, -1);
                                // }

                                echo json_encode(array('redirect' => '/admin/shop/orders' , 'msg' => '전액환불 처리되었습니다.'));
                            }
                        }
                    }
                    return;
                    break;
                }
            }

            parent::scaff($n);
    }

    public function validation_rules_checkout() {
            $rules = array();

            $rules['merchant_uid'] = array(
                    'field' => 'merchant_uid',
                    'label' => 'Merchant UID',
                    'rules' => 'trim||required',
            );

            $rules['payment_method'] = array(
                    'field' => 'payment_method',
                    'label' => $this->lang->line('payment_method'),
                    'rules' => 'trim||required',
            );

            if ($this->input->post('payment_method') == 'vbank') {
                    $rules['refund_bank'] = array(
                            'field' => 'refund_bank',
                            'label' => '환불 은행',
                            'rules' => 'trim||required',
                    );
                    $rules['refund_account'] = array(
                            'field' => 'refund_account',
                            'label' => '환불 계좌',
                            'rules' => 'trim||required',
                    );
                    $rules['refund_holder'] = array(
                            'field' => 'refund_holder',
                            'label' => '예금주',
                            'rules' => 'trim||required',
                    );
            }
/*
            if (!$this->auth->is_login()) {
                    $rules['passwd'] = array(
                            'field' => 'passwd',
                            'label' => $this->lang->line('auth_password'),
                            'rules' => 'trim||required||regex_match['.REGXPAT_PASSWD.']||sha1',
                    );
            }
*/
            $rules['email'] = array(
                    'field' => 'email',
                    'label' => $this->lang->line('auth_email'),
                    'rules' => 'trim||required||valid_email',
            );
            $rules['billing_name'] = array(
                    'field' => 'billing_name',
                    'label' => $this->lang->line('auth_name'),
                    'rules' => 'trim||required',
            );
            $rules['billing_phone'] = array(
                    'field' => 'billing_phone',
                    'label' => $this->lang->line('auth_mphone'),
                    'rules' => 'trim||required',
            );

            $rules['shipping_name'] = array(
                    'field' => 'shipping_name',
                    'label' => $this->lang->line('auth_name'),
                    'rules' => 'trim',
            );
            $rules['shipping_phone'] = array(
                    'field' => 'shipping_phone',
                    'label' => $this->lang->line('auth_mphone'),
                    'rules' => 'trim',
            );

            $rules['memo'] = array(
                    'field' => 'memo',
                    'label' => '요청사항',
                    'rules' => 'trim',
            );

            $rules['dc_type'] = array(
                    'field' => 'dc_type',
                    'label' => '할인 유형',
                    'rules' => 'trim',
            );

            return $rules;
    }

    public function validation_rules_status() {
            return $this->_validation_rule('status');
    }
    public function validation_rules_shipping_company() {
            return $this->_validation_rule('shipping_company');
    }
    public function validation_rules_shipping_id() {
            return $this->_validation_rule('shipping_id');
    }

    public function scaff_do_checkout($uid) {
            $this->scaff_save('checkout', $uid, 'validation_rules_checkout');
    }

    public function scaff_db_array($mode, $uid = null, $validation_rules = null) {
        $db_array = array();

        if ($uid > 0 && $mode == 'checkout') {
            if (true || $this->input->post('ship_to_billing') == 'Y') {
                $db_array['shipping_name'] = $this->input->post('billing_name');
                $db_array['shipping_phone'] = $this->input->post('billing_phone');
                $db_array['shipping_country'] = $this->input->post('billing_country');
                $db_array['shipping_city'] = $this->input->post('billing_city');
                $db_array['shipping_zipcode'] = $this->input->post('billing_zipcode');
                $db_array['shipping_address1'] = $this->input->post('billing_address1');
                $db_array['shipping_address2'] = $this->input->post('billing_address2');
            }

            if (false && $this->input->post('update_my_info') == 'Y' && $this->auth->is_login()) {
                $this->db->update($this->auth->table, array(
                    'name' => $this->input->post('billing_name'),
                    'phone' => $this->input->post('billing_phone'),
                    'country' => $this->input->post('billing_country'),
                    'city' => $this->input->post('billing_city'),
                    'zipcode' => $this->input->post('billing_zipcode'),
                    'address1' => $this->input->post('billing_address1'),
                    'address2' => $this->input->post('billing_address2'),
                ), array(
                    $this->auth->primary_key => $this->auth->userinfo->{$this->auth->primary_key}
                ));
            }

            $this->load_model('shop_cart', 'cart');
            $this->cart->update_order_tmp($uid);
            $total = $this->cart->get_total($this->input->post('dc_type'));
            $db_array['currency'] = $this->lang->line('shop_currency');
            $db_array['currency_name'] = $this->lang->line('shop_currency_name');
            $db_array['currency_rtdp'] = $this->lang->line('shop_currency_rtdp');
            $db_array['price'] = $total->price;
            $db_array['shipping_cost'] = $total->shipping_cost;
            //$db_array['time'] = unix_to_human(local_to_gmt(time()), true, '');
            $db_array['time'] = $this->auth->get_database_now();

        }

        return $db_array;
    }

    public function scaff_list_filter() {
        $this->filter_where($this->table.'.status !=', 'pending_order');

        if ($this->scaff_opt('status') && $this->scaff_opt('status') != 'all') {
            $this->filter_where($this->table.'.status', $this->scaff_opt('status'));
        }

        if ($this->scaff_opt('method') && $this->scaff_opt('method') != 'all') {
            $this->filter_where($this->table.'.payment_method', $this->scaff_opt('method'));
        }

        return parent::scaff_list_filter();
    }

    public function complete($by_user) {
            $imp_uid = $this->input->method() == 'post' ?
                    $this->input->post('imp_uid') :
                    $this->input->get('imp_uid');
            $merchant_uid = $this->input->method() == 'post' ?
                    $this->input->post('merchant_uid') :
                    $this->input->get('merchant_uid');
            if (empty($imp_uid) || empty($merchant_uid)) {
                    $this->_error('결제 정보가 충분하지 않습니다.');
                    return;
            }
            $this->db->query("SELECT GET_LOCK('".$merchant_uid."', 3)");
            $row = $this->get_by_merchant_uid($merchant_uid);
            if (empty($row)) {
                    $this->db->query("SELECT RELEASE_LOCK('".$merchant_uid."')");
                    $iamport = new Iamport($this->config->item('imp_api_key'), $this->config->item('imp_api_secret'));
                    $result = $iamport->findByImpUID($imp_uid);
                    if (!$result->success) {
                            $this->_error($result->error['code'].': '.$result->error['message']);
                            return;
                    }
                    $payment_data = $result->data;
                    $msg = '다른 브라우저 창에 결제창이 열린 경우 현재 창에서 다시 시도 바랍니다.';
                    $result = $iamport->cancel(array(
                            'imp_uid' => $imp_uid,
                            'merchant_uid' => $payment_data->merchant_uid,
                            'reason' => '해당 주문 없음',
                    ));
                    $msg .= $result->success ?
                            ' 자동취소처리 하였습니다.' :
                            ' 취소에 실패했습니다. '.$result->error['message'];
                    $this->_error($msg);
                    return;
            }

            if (!$by_user && $row->payment_method != 'vbank') {
                    $this->_error('잘못된 접근입니다.');
                    return;
            }

            switch ($row->status) {
            case 'pending_order':
            case 'pending_payment':
                $amount = $row->price + $row->shipping_cost - $row->discount - round($row->discount_by_type) - round($row->discount_by_serial_stay) - $row->discount_by_member;
                $amount = round($amount);
                if ($amount > 0) {
                    $iamport = new Iamport($this->config->item('imp_api_key'), $this->config->item('imp_api_secret'));
                    $result = $iamport->findByImpUID($imp_uid);
                    if (!$result->success) {
                        $this->db->query("SELECT RELEASE_LOCK('".$merchant_uid."')");
                        $this->_error($result->error['code'].': '.$result->error['message']);
                        return;
                    }

                    $payment_data = $result->data;

                    $row_chk = $this->db
                    ->select('*')
                    ->where("uid_order_tmp ",$row->uid)
                    ->where("product_module", 'package')
                    ->get('d_shop_cart')->result();
                    $no_room_chk = 0;

                    if(!(empty($row_chk))){
                        $msg = "해당객실은 판매 완료되었습니다.\n다른 객실을 선택해주세요.";
                        foreach($row_chk as $row_c){
                            $this->load->model('wings');
                            $rates = $this->wings->get_rooms(array(
                            'arrival_date' => $row_c->check_in,
                            'departure_date' => $row_c->check_out,
                            'room_count' => (int)$row_c->quantity,
                            'adult_count' => (int)$row_c->adult_count,
                            'child_count' => (int)$row_c->child_count,
                            'rate_type_code' => $row_c->rate_type_code,
                            'room_type_code' => $row_c->room_type_code,
                            ));


                            if (empty($rates)) {
                                $no_room_chk ++ ;
                            }
                        }
                        if ($no_room_chk > 0) {
                                 $result = $iamport->cancel(array(
                                'imp_uid' => $imp_uid,
                                'merchant_uid' => $payment_data->merchant_uid,
                                'reason' => '이미 결제된 객실',
                                'refund_bank' => $row->refund_bank,
                                'refund_account' => $row->refund_account,
                                'refund_holder' => $row->refund_holder,
                                 ));
                                if( $result->success ) {
                                     $this->cancel_wings_by_orderid($row->uid);
                                }
                                $msg .= $result->success ?
                                    ' 자동취소처리 하였습니다. 3초후에 내 예약함으로 이동합니다.@@MM' :
                                    ' 취소에 실패했습니다. 3초후에 내 예약함으로 이동합니다.@@MM ';


                                $this->_error($msg);
                                return;
                        }
                    }

                    if ($payment_data->pay_method !== 'vbank' && $payment_data->status !== 'paid') {
                        $this->db->query("SELECT RELEASE_LOCK('".$merchant_uid."')");
                        $this->_error('결제가 정상적으로 이루어지지 않았습니다.');
                        return;
                    }
                    if ($payment_data->amount != $amount) {
                        $this->db->query("SELECT RELEASE_LOCK('".$merchant_uid."')");
                        $msg = '결제 되어야 할 금액이 맞지 않습니다.';
                        $result = $iamport->cancel(array(
                                'imp_uid' => $imp_uid,
                                'merchant_uid' => $payment_data->merchant_uid,
                                'reason' => '결제 금액 불일치',
                                'refund_bank' => $row->refund_bank,
                                'refund_account' => $row->refund_account,
                                'refund_holder' => $row->refund_holder,
                        ));
                        $msg .= $result->success ?
                                ' 자동취소처리 하였습니다.' :
                                ' 취소에 실패했습니다. '.$result->error['message'];
                        $this->_error($msg);
                        return;
                    }
                } else {
                    $payment_data = new stdclass;
                    $payment_data->amount = $amount;
                    $payment_data->pay_method = '없음';
                    $payment_data->status = 'paid';
                    $payment_data->paid_at = time();
                }

    //echo $imp_uid;
    //echo json_encode($row);

                $this->load_model('shop_cart', 'cart');
                $ret = $this->cart->update_order($row->uid, $imp_uid);
                if ( ! $ret ) {
                    $this->db->query("SELECT RELEASE_LOCK('".$merchant_uid."')");
                    $msg = '결제하시는 사이 마감된 상품이 있습니다. 예약함을 다시 확인 바랍니다.';
                    if ($amount > 0) {
                        $result = $iamport->cancel(array(
                                'imp_uid' => $imp_uid,
                                'merchant_uid' => $payment_data->merchant_uid,
                                'reason' => '예약 마감',
                                'refund_bank' => $row->refund_bank,
                                'refund_account' => $row->refund_account,
                                'refund_holder' => $row->refund_holder,
                        ));
                        if( $result->success ) {
                            // 결제취소가 된 경우 산하 Wings 예약도 취소한다.
                            $this->cancel_wings_by_orderid($row->uid);
                        }

                        $msg .= $result->success ?
                                ' 자동취소처리 하였습니다.' :
                                ' 취소에 실패했습니다. '.$result->error['message'];
                        $this->_error($msg);
                        return;
                    }
                }

                $db_array = array();
                $db_array['payment_id'] = $imp_uid;
                $db_array['amount'] = $payment_data->amount;
                $db_array['payment_method'] = $payment_data->pay_method;
                if ($payment_data->pay_method == 'vbank' && $payment_data->status == 'ready') {
                    $db_array['vbank_date'] = $payment_data->vbank_date;
                    $db_array['vbank_holder'] = $payment_data->vbank_holder;
                    $db_array['vbank_name'] = $payment_data->vbank_name;
                    $db_array['vbank_num'] = $payment_data->vbank_num;
                    $db_array['status'] = 'pending_payment';
                }
                if ($payment_data->status == 'paid') {
                    //$db_array['time_paid'] = unix_to_human(local_to_gmt($payment_data->paid_at), true, '');
                    $db_array['time_paid'] = $this->auth->get_database_now(); //주문일시 수정 추가
                    $db_array['status'] = 'received';
                }
                $this->save($row->uid, $db_array);
                $this->notify_status_email($row->uid);
                break;
            }

            // if ($payment_data->status == 'paid') {
            //     if ($row->status != 'received') {
            //         $this->db->query("SELECT RELEASE_LOCK('".$merchant_uid."')");
            //         $msg = '결제 정보가 일치하지 않습니다.';
            //         $result = $iamport->cancel(array(
            //                 'imp_uid' => $imp_uid,
            //                 'merchant_uid' => $payment_data->merchant_uid,
            //                 'reason' => '결제 정보 불일치',
            //                 'refund_bank' => $row->refund_bank,
            //                 'refund_account' => $row->refund_account,
            //                 'refund_holder' => $row->refund_holder,
            //         ));

            //         if( $result->success ) {
            //             // 결제취소가 된 경우 산하 Wings 예약도 취소한다.
            //             $this->cancel_wings_by_orderid($row->uid);
            //         }

            //         $msg .= $result->success ?
            //                 ' 자동취소처리 하였습니다.' :
            //                 ' 취소에 실패했습니다. '.$result->error['message'];
            //         $this->_error($msg);
            //         return;
            //     }
            // }

            $this->db->query("SELECT RELEASE_LOCK('".$merchant_uid."')");

            // 푸시메세지
            $this->load->model("messageSend");
            $push_param = array(
                "uid" => $row->uid_user,
                "title" => "[플레이스 캠프 제주] 예약 완료 안내",
                "contents" => "예약이 완료 되었습니다.",
                "target_url" => "https://www.playcegroup.com/user/orders"
            );
            $this->messageSend->push($push_param);

            // 알림톡 메세지 전송
            // $this->messageSend->alimtalk_reserv($row->uid);

            //  $url = '/user/orders/'.$row->order_id;
//            $url = '/user/checkout/'.$row->order_id;
        $url = '';
            if($this->auth->is_login()) {
                // 회원일때
                $url = '/user/checkout/'.$row->order_id.'/'.$payment_data->amount;
            } else {
                // 비회원일때
                $url = '/user/checkout_nonmember/'.$row->order_id.'/'.$payment_data->amount;
            }

            if ($this->agent->is_mobile()) {
                    redirect($url);
            } else {
                    echo json_encode(array('redirect' => $url));
            }
    }

    // 해당 주문번호의 모든 윙스 예약 취소 및 카트 삭제
    public function cancel_wings_by_orderid($order_uid) {
        $cmd = "    SELECT  dsc.cms_reserv_id as cms_reserv_id
                    FROM    dev_playcecampjeju.d_shop_cart as dsc
                            INNER JOIN dev_playcecampjeju.d_shop_order as dso ON dsc.uid_order = dso.uid
                    WHERE   dsc.cms_reserv_id != '' AND dsc.pms_reserv_id != '' AND dsc.status = 'normal' AND
                            dsc.product_module = 'package' AND dsc.check_in >= current_date
                            and dsc.uid_order = " . $order_uid;
        $query = $this->db->query( $cmd );
        //debug_var($cmd);

        $result = $query->result_array();
        $query->free_result();

        $this->load->model('wings');

        foreach ($result as $order_cart) {
            $wings_segment = explode('-', $order_cart["cms_reserv_id"]);
            $wing_param = array (
                    'is_cancel' => true,
                    'uid_order' => $wings_segment[0],
                    'uid_cart'  => $wings_segment[1],
                );
            $res = $this->wings->make_reservation($wing_param);
        }

        // 카트 삭제
        $cmd = "    DELETE
                    FROM    dev_playcecampjeju.d_shop_cart
                    WHERE   uid_order = " . $order_uid;
        $this->db->query( $cmd );
        // 주문 삭제
        $cmd = "    DELETE
                    FROM    dev_playcecampjeju.d_shop_order
                    WHERE   uid = " . $order_uid;
        $this->db->query( $cmd );
    }

    public function notify_status_email($uid, $canceled_uid = 0, $canceled_type = 0) {
        $this->is_notifying = true;
        $row = $this->get_by_id_as($uid);
        $this->is_notifying = false;
        if (empty($row)) {
            return array('error' => '해당 주문을 찾을 수 없습니다.');
        }

        $title = null;
        $title2 = null;
        switch ($row->_status) {
        case 'received':
            if ($canceled_uid > 0) {
                if ($canceled_type == 0) {
                    $title = '결제 취소 완료';
                    $title2 = '결제 취소가 되어 환불되었습니다.';
                }
                if ($canceled_type == -1) {
                    $title = '결제 취소 완료';
                    $title2 = '전액 환불 취소가 되어 환불되었습니다.';
                }
                if ($canceled_type == -2) {
                    $title = '결제 취소 완료';
                    $title2 = '환불 규정에 의해 취소 되었습니다.';
                }
            } else {
                $title = '예약하신 내역을 안내해 드립니다.';
                $title2 = $title;
            }
            break;
        }
        if (!$title) {
            return array('error' => $row->status.' 상태는 예약자에게 알릴 필요가 없습니다.');
        }

        $this->load->helper('email');

        $country_list = $this->config->item('country_list');
        $content_items = '';

        foreach ($row->rows_cart as $row_cart) {
            if ($canceled_uid > 0 && $canceled_uid != $row_cart->uid && $row_cart->status != 'canceled') {
                    continue;
            }

            $content_items .= '
                    <tr>
                        <td width="25%" valign="top">'.($row_cart->featured_image ? '<img src="'.http_host().$row_cart->base_url.'/download/'.$row_cart->featured_image->uid.'/w/300'.urlencode($row_cart->featured_image->fname).'" alt="" width="100%" />' : '').'</td>
                        <td valign="top" style="padding-left: 20px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <th style="font-size: 14px;" colspan="2" align="left">'.$row_cart->product_title.($row_cart->product_subtitle ? ' <small><em>'.$row_cart->product_subtitle.'&nbsp;</em></small>' : '').'</th>
                                </tr>';
            switch ($row_cart->product_module) {
            case 'package':
                $content_items .= '
                        <tr><td colspan="2" height="5"></td></tr>
                        <tr>
                            <td style="font-size: 14px; color: #e2007e;" colspan="2">'.date('m월 d일', mysql_to_unix($row_cart->check_in.' 00:00:00')).' ~ '.date('m월 d일', mysql_to_unix($row_cart->check_out.' 00:00:00')).'<br /><small>'.$row_cart->room_type_name;
                                if ($row_cart->adult_count > 0 || $row_cart->child_count > 0) {
                                    $arr = array();
                                    if ($row_cart->adult_count > 0) {
                                        $arr[] = '성인 '.$row_cart->adult_count.'명';
                                    }
                                    if ($row_cart->child_count > 0) {
                                        $arr[] = '소인 '.$row_cart->child_count.'명';
                                    }
                                    $content_items .= ($row_cart->room_type_name ? ', ' : '').join($arr);
                                }
                                $content_items .= '</small></td>
                        </tr>';
                break;
            case 'play':
                $content_items .= '
                        <tr><td colspan="2" height="5"></td></tr>
                        <tr>
                            <td style="font-size: 14px; color: #e2007e;" colspan="2">'.date('m월 d일 h:iA', mysql_to_unix($row_cart->time_schedule)).'</td>
                        </tr>';
                break;
            case 'festival':
                if ($row_cart->is_pack == 'Y') {
                    $content_items .= '
                            <tr><td colspan="2" height="5"></td></tr>
                            <tr>
                                <td style="font-size: 14px; color: #e2007e;" colspan="2">전일권 ('.date('m월 d일', mysql_to_unix($row_cart->time_schedule)).' ~ '.date('m월 d일', mysql_to_unix($row_cart->fschedule_end.' 00:00:00')).')</td>
                            </tr>';
                } else if ($row_cart->is_pack == 'N') {
                    $content_items .= '
                            <tr><td colspan="2" height="5"></td></tr>
                            <tr>
                                <td style="font-size: 14px; color: #e2007e;" colspan="2">1일권 ('.date('m월 d일 h:iA', mysql_to_unix($row_cart->time_schedule)).')</td>
                            </tr>';
                }
                break;
            default:
                if ($row_cart->options) {
                    $content_items .= '
                            <tr><td colspan="2" height="12"></td></tr>
                            <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                            <tr><td colspan="2" height="12"></td></tr>
                            <tr>
                                <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('product_options').'</th>
                                <td style="font-size: 14px;">'.$row_cart->options.'</td>
                            </tr>';
                }
                break;
            }

            $content_items .= '
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_quantity').'</th>
                        <td style="font-size: 14px;">'.$row_cart->quantity.'</td>
                    </tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('product_price').'</th>
                        <td style="font-size: 14px;">'.$row->currency.' '.number_format($row_cart->price, $row->currency_rtdp).'</td>
                    </tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>';

            $local_dis = 0;
            $dis_series = 0 ;
            $guest_dis = 0;

            if($row_cart->product_module == 'package'){
                if($row_cart->no_series_dc == 'N'){//N이면 연박할인이 있다
                    $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                    if($days < 1)
                        $days = 1;

                    if($days ==2){
                        $dis_series = round(((($row_cart->price / $days)*2 )*($row_cart->two_dc_rate/ 100)));
                    }else if($days > 2){
                        $dis_series = round(((($row_cart->price / $days)*3 )*($row_cart->three_dc_rate/ 100)));
                    }
                }else{
                    $dis_series = 0 ;
                }

                if(!($row->uid_user == 0)){
                    $content_items .= '
                            <tr>
                                <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$local_dis-$dis_series-($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)), $row->currency_rtdp).'</td>
                            </tr>';
                }else{
                    $content_items .= '
                            <tr>
                                <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$local_dis-$dis_series, $row->currency_rtdp).'</td>
                            </tr>';
                }

                if($dis_series != 0){
                    $content_items .= '
                            <tr><td colspan="2" height="12"></td></tr>
                            <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                            <tr><td colspan="2" height="12"></td></tr>
                            <tr>
                                <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">연박 할인</th>
                                <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format($dis_series, $row->currency_rtdp).'</td>
                            </tr>';
                }
            }

            if ($row->dc_type != 'none' && $row->discount_by_type > 0 && $row_cart->product_module != "package") {
                switch ($row->dc_type) {
                case 'local':
                    if($row_cart->sale_schedule_jeju == 'Y'){
                        $reser_day_local= mysql_to_unix($row->time_paid.' 00:00:00');
                        $begin_local = mysql_to_unix($row_cart->date_sale_begin_jeju.' 00:00:00');
                        $end_local = mysql_to_unix($row_cart->date_sale_end_jeju.' 00:00:00') + (60*60*24);

                        if(($reser_day_local > $begin_local && $reser_day_local <$end_local)){
                            $local_dis = round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_local / 100));
                        }else{
                            $local_dis = 0;
                        }
                    }else{
                        $local_dis = round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_local / 100));
                    }

                    if($row->discount_by_member > 0 && ($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)) > 0 && !($row->uid_user == 0)){
                        if($row_cart->duplicate_dc == 'N'){
                            $content_items .= '
                                    <tr>
                                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                        <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$local_dis-$dis_series-($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)), $row->currency_rtdp).'</td>
                                    </tr>';
                        }else{
                            $content_items .= '
                                    <tr>
                                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                        <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$local_dis-$dis_series, $row->currency_rtdp).'</td>
                                    </tr>';
                        }
                    }else{
                        $content_items .= '
                                <tr>
                                    <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                    <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$local_dis-$dis_series, $row->currency_rtdp).'</td>
                                </tr>';
                    }

                    if($local_dis != 0 ){
                        $content_items .= '
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr>
                                    <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">제주도민 할인</th>
                                    <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format($local_dis, $row->currency_rtdp).'</td>
                                </tr>';
                    }
                    break;
                case 'guest':
                    if($row_cart->sale_schedule_guest== 'Y'){
                        $reser_day_guest= mysql_to_unix($row->time_paid.' 00:00:00');
                        $begin_guest= mysql_to_unix($row_cart->date_sale_begin_guest.' 00:00:00');
                        $end_guest= mysql_to_unix($row_cart->date_sale_end_guest.' 00:00:00') + (60*60*24);

                        if(($reser_day_guest> $begin_guest&& $reser_day_guest<$end_guest)){
                            $guest_dis= round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_guest/ 100));
                        }else{
                            $guest_dis = 0;
                        }
                    }else{
                        $guest_dis = round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_guest/ 100));
                    }

                    if($row->discount_by_member > 0 && ($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)) > 0 && !($row->uid_user == 0)){
                        if($row_cart->duplicate_dc == 'N'){
                            $content_items .= '
                                    <tr>
                                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                        <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$guest_dis, $row->currency_rtdp).'</td>
                                    </tr>';
                        }else{
                            $content_items .= '
                                    <tr>
                                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                        <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$guest_dis, $row->currency_rtdp).'</td>
                                    </tr>';
                        }
                    }else{
                        $content_items .= '
                                <tr>
                                    <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                    <td style="font-size: 14px;">'.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity)-$guest_dis, $row->currency_rtdp).'</td>
                                </tr>';
                    }

                    if($guest_dis != 0 ){
                        $content_items .= '
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr>
                                    <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">투숙객 할인</th>
                                    <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format($guest_dis, $row_cart->currency_rtdp).'</td>
                                </tr>';
                    }
                    break;
                }
            }else{
                if($row_cart->product_module !="package"){
                    $content_items .= '
                            <tr>
                                <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_total_price').'</th>
                                <td style="font-size: 14px;">'.$row->currency.' '.number_format($row_cart->price * $row_cart->quantity - ($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)), $row->currency_rtdp).'</td>
                            </tr>';
                }
            }

            if(($row->discount_by_member > 0 && ($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)) > 0 && !($row->uid_user == 0)) || ($row_cart->product_module =="package" && !($row->uid_user == 0))){
                if($row_cart->duplicate_dc == 'N' || $row_cart->product_module =="package" ){
                    $content_items .= '
                            <tr><td colspan="2" height="12"></td></tr>
                            <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                            <tr><td colspan="2" height="12"></td></tr>
                            <tr>
                                <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">회원 할인</th>
                                <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)), $row->currency_rtdp).'</td>
                            </tr>';
                }else{
                    if($row->dc_type =="none"){
                        $content_items .= '
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr>
                                    <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">회원 할인</th>
                                    <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format(($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100)), $row->currency_rtdp).'</td>
                                </tr>';
                    }
                }
            }

            if ($canceled_uid > 0) {
                $content_items .= '
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr>
                            <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">취소 일시</th>
                            <td style="font-size: 14px;">'.date('Y년 m월 d일 h:iA', mysql_to_unix($row_cart->time_return_req)).'</td>
                        </tr>
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr>
                            <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">환불 금액</th>
                            <td style="font-size: 14px; color: #e2007e;">'.$row->currency.' '.number_format($row_cart->return_amount, $row->currency_rtdp).'</td>
                        </tr>';
            }

            $content_items .= '
                        </table>
                    </td>
                </tr>
                <tr><td colspan="2" height="20"></td></tr>
                <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                <tr><td colspan="2" height="20"></td></tr>';
        }

        $content = '
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('order_id').'</th>
                                    <td style="font-size: 14px;">'.$row->order_id.'</td>
                                </tr>
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                                <tr><td colspan="2" height="12"></td></tr>
                                <tr>
                                    <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">예약 일시</th>
                                    <td style="font-size: 14px;">'.$row->time_paid.'</td>
                                </tr>
                                <tr><td colspan="2" height="40"></td></tr>
                                <tr>
                                    <td colspan="2" style="font-size: 20px;">'.($canceled_uid > 0 ? '취소 내역' : '상세 예약 내역').'</td>
                                </tr>
                                <tr><td colspan="2" height="10"></td></tr>
                                <tr><td colspan="2" height="1" bgcolor="#666"></td></tr>
                                <tr><td colspan="2" height="20"></td></tr>
                                <tr>
                                    <td colspan="2">
                                        <table width="100%" cellpadding="0" cellspacing="0">'.$content_items.'</table>
                                    </td>
                                </tr>';

        if ($canceled_uid <= 0) {
            $content .= '
                    <tr><td colspan="2" height="20"></td></tr>
                    <tr>
                        <td colspan="2" style="font-size: 20px;">'.$this->lang->line('billing_info').'</td>
                    </tr>
                    <tr><td colspan="2" height="10"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#666"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('auth_name').'</th>
                        <td style="font-size: 14px;">'.$row->billing_name.'</td>
                    </tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('auth_mphone').'</th>
                        <td style="font-size: 14px;">'.$row->billing_phone.'</td>
                    </tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('auth_email').'</th>
                        <td style="font-size: 14px;">'.$row->email.'</td>
                    </tr>
                    <tr><td colspan="2" height="40"></td></tr>
                    <tr>
                        <td colspan="2" style="font-size: 20px;">'.$this->lang->line('payment_info').'</td>
                    </tr>
                    <tr><td colspan="2" height="10"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#666"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('paid_at').'</th>
                        <td style="font-size: 14px;">'.date('Y년 m월 d일 h:iA', mysql_to_unix($row->time_paid)).'</td>
                    </tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$this->lang->line('payment_method').'</th>
                        <td style="font-size: 14px;">'.$this->payment_method($row->payment_method).'</td>
                    </tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">총 상품금액</th>
                        <td style="font-size: 14px;">'.$row->currency.' '.number_format($row->price, $row->currency_rtdp).'</td>
                    </tr>';

            if ($row->discount_by_type > 0) {
                switch ($row->dc_type) {
                case 'local':
                    $dc_type = '제주도민 할인';
                    break;
                case 'guest':
                    $dc_type = '투숙객 할인';
                    break;
                case 'none':
                default:
                    $dc_type = '할인';
                    break;
                }

                $content .= '
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr>
                            <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">'.$dc_type.'</th>
                            <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format($row->discount_by_type, $row->currency_rtdp).'</td>
                        </tr>';
            }

            if($row->discount_by_member > 0 && !($row->uid_user == 0)){
                $content .= '
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr>
                            <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">회원 할인</th>
                            <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format($row->discount_by_member, $row->currency_rtdp).'</td>
                        </tr>';
            }

            if ($row->discount > 0) {
                $content .= '
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                        <tr><td colspan="2" height="12"></td></tr>
                        <tr>
                            <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">프로모션 코드 할인</th>
                            <td style="font-size: 14px;">(-) '.$row->currency.' '.number_format($row->discount, $row->currency_rtdp).'</td>
                        </tr>';
            }

            $content .= '
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr><td colspan="2" height="1" bgcolor="#ebebeb"></td></tr>
                    <tr><td colspan="2" height="12"></td></tr>
                    <tr>
                        <th align="left" valign="top" style="width: 1%; white-space: nowrap; padding-right: 15px; font-size: 14px;">결제 금액</th>
                        <td style="font-size: 14px; color: #e2007e;"><b>'.$row->currency.' '.number_format($row->amount > 0 ? $row->amount : 0, $row->currency_rtdp).'</b></td>
                    </tr>';
        }

        $content .= '
                            <tr><td colspan="2" height="60"></td></tr>
                            <tr>
                                <td colspan="2" align="left">
                                    <a href="'.http_host().'/user/orders/'.$row->order_id.'" target="_blank" style="text-decoration: none; color: #000000;">'.($canceled_uid > 0 ? '취소' : '예약').' 내역 확인 &gt;</a>
                                </td>
                            </tr>
                            <tr><td colspan="2" height="25"></td></tr>
                        </table>
                    </td>
                </tr>
            </table>';

        email($title, $title2, $content, $row->email, $row->billing_name);

        /* send to admin */
        /*
        switch ($row->_status) {
        case 'received':
        case 'canceled':
            $q = $this->db->select('email, name')
                ->where('kind', 'ADMIN')
                ->where('email_subscribe', 'Y')
                ->get($this->auth->table);
            foreach ($q->result() as $r) {
                email($title, $content, $r->email, $r->name);
            }
            break;
        }
        */

        return array('message' => $row->billing_name.'('.$row->email.')님께 알림 메일을 전송했습니다.');
    }

    private function _error($msg) {
            if ($this->agent->is_mobile()) {
                    echo '<script type="text/javascript">alert("'.htmlspecialchars(addslashes($msg)).'"); location.href="/user/checkout"</script>';
            } else {
                    echo json_encode(array('error' => $msg));
            }
    }

    // user 의 uid 를 받아 하나씩 떼어 대문자로 치환
    public function engString_by_userId($user_uid) {
        if ($this->auth->is_login()) {
            $engString = "";
            for($i=0;$i<strlen($user_uid);$i++){
                $cut=mb_substr($user_uid,$i,1);
                if( (int)$cut == 0 ) {
                    $engString .= 'O';
                } else {
                    $engString .= chr((int)$cut + 64);
                }
            }
            return $engString;
        } else {
            return "GUEST";
        }
    }
    public function gen_order_id($uid) {
        return $this->engString_by_userId($this->auth->userinfo->uid). "_" . $uid;
//      $delim = '2';
//      $base = strlen(d2a_index());
//      $id = d2a($uid);
//      $max = strlen(d2a(PHP_INT_MAX));
//      $len = $max - strlen($id);
//      $suffix = '';
//      if ($len >= 1) {
//          $suffix .= $delim;
//      }
//      if ($len >= 2) {
//          $suffix .= d2a(rand(pow($base, $len - 2), pow($base, $len - 1) - 1));
//      }
//      $str = $id.$suffix;
//      $n = ceil($max / 2);
//      $arr = array();
//      for ($i = 0; $i <= $n; $i++) {
//          $part = substr($str, $i * $n, $n);
//          if (!empty($part)) {
//              $arr[] = $part;
//          }
//      }
//      return implode('-', $arr);
    }

    public function default_where() {
            parent::default_where();

            if (!$this->scaff_admin && !$this->is_notifying) {
                    if ($this->auth->is_login()) {
                            $this->filter_where($this->table.'.'.$this->owner_field, $this->auth->userinfo->uid);
                    } else {
                            $order_id = $this->guest_order_id();
                            $this->filter_where($this->table.'.'.$this->owner_field, $this->guest_order_id());
                            if (empty($order_id)) {
                                    $this->filter_where($this->table.'.order_id = "__NOT_DEFINED__"');
                            } else {
                                    $this->filter_where('BINARY('.$this->table.'.order_id)', $order_id);
                            }
                    }
            }
    }

    public function cleanup_old_entries() {
            $now = unix_to_human(local_to_gmt(time()), true, '');
            $rows = $this->db->select($this->primary_key)
                    ->where($this->table.'.'.$this->owner_field, '0')
                    ->where($this->table.'.status', 'pending_order')
                    ->where($this->table.'.'.$this->date_created_field.' < ADDDATE("'.$now.'", "-3")')
                    ->get($this->table)->result();
            foreach ($rows as $row) {
                    $this->delete($row->{$this->primary_key});
            }
    }

    public function my_pending_row($create = true) {
            $this->filter_where($this->table.'.status', 'pending_order');
            $this->filter_order_by($this->table.'.'.$this->primary_key, 'desc');
            $row = $this->get()->row();
            if (empty($row) && $create) {
                    $this->cleanup_old_entries();

                    /* insert empty row */
                    $db_array = array();
                    $db_array[$this->owner_field] = $this->auth->is_login() ? $this->auth->userinfo->uid : '0';
                    $uid = $this->save(null, $db_array);

                    /* assign order id */
                    do {
                            $order_id = $this->gen_order_id($uid);
                            $n = $this->db->where('BINARY(order_id)', $order_id)->count_all_results($this->table);
                    } while ($n > 0);
                    $this->db->where($this->primary_key, $uid)->update($this->table, array(
                            'order_id' => $order_id,
                    ));
                    if (!$this->auth->is_login()) {
                            $this->guest_login($order_id);
                    }

                    return $this->my_pending_row();
            }
            return $row;
    }

    public function delete($uid) {
            $this->load_model('shop_cart', 'cart');
            $this->cart->delete_by_order($uid);

            parent::delete($uid);
    }

    public function get_by_order_id($order_id, $include_defaults = true) {
            return $this->where('BINARY('.$this->table.'.order_id)', $order_id)->get($include_defaults)->row();
    }

    public function get_by_merchant_uid($merchant_uid) {
            return $this->db->select('*')
                    ->where('merchant_uid', $merchant_uid)
                    ->get($this->table)
                    ->row();
    }

    public function order_id_to_uid($order_id) {
        $uid = 0;
        // 이거 고쳐야 함
        //if (preg_match('/^[0-9a-zA-Z]{6}-[0-9a-zA-Z]{5}$/', $order_id)) {
        $row = $this->get_by_order_id($order_id);
        if (!empty($row)) {
            $uid = $row->{$this->primary_key};
        }
        //}

        return $uid;
    }

    public function first_segment($n) {
        $first_segment = $this->uri->segment($n);

        $uid = $this->order_id_to_uid($first_segment);
        if ($uid > 0) {
            $first_segment = $uid;
        }

        return $first_segment;
    }
    public function guest_test_and_login($order_id, $email) {
        $row= $this->db->select('email,billing_name')
                ->where('BINARY(order_id)', $order_id)
                ->where('status !=', 'pending_order')
                ->where('email =', $email)
                ->get($this->table)
                ->row();
        if (empty($row)) {
            return $this->lang->line('order_id_not_found');
        }
        if ($row->email != $email) {
            return $this->lang->line('auth_wrong_password');
        }
        $this->guest_login($order_id,$row->billing_name);
        return true;
    }
    public function guest_login($order_id,$b_name) {
        $this->session->set_userdata('order_id', $order_id);
        $this->session->set_userdata('guest_name', $b_name);
    }

    public function guest_logout() {
        $this->session->unset_userdata('order_id');
        $this->session->unset_userdata('guest_name');
    }

    public function guest_order_id() {
        return $this->session->userdata('order_id');
    }
    public function guest_user_name() {
        return $this->session->userdata('guest_name');
    }


    public function is_owner($row) {
        if ($row &&
            $row->{$this->owner_field} == '0' &&
            !empty($row->order_id) &&
            $this->guest_order_id() == $row->order_id) {
            return true;
        }
        return parent::is_owner($row);
    }

    public function payment_method($key) {
        return $key == 'all' || $key == '' ?
            $this->lang->line('all_payment_methods') : (
                empty($this->available_payment_methods[$key]) ?
                    $key : $this->available_payment_methods[$key]
            );
    }

    public function status($key) {
        return $key == 'all' || $key == '' ?
            $this->lang->line('board_all_status') : (
                empty($this->available_statuses[$key]) ?
                    $key : $this->available_statuses[$key]
            );
    }

    public function prep_row_list(&$row) {
        parent::prep_row_list($row);
        $this->prep_row_status($row);
        $this->prep_row_time($row);
        $this->prep_row_time_paid($row);
        $this->prep_row_cart($row);
        $this->prep_row_has_packages($row);
    }

    public function prep_row_view(&$row) {
        parent::prep_row_view($row);
        $this->prep_row_status($row);
        $this->prep_row_time($row);
        $this->prep_row_time_paid($row);
        $this->prep_row_cart($row);
        $this->prep_row_has_packages($row);
    }

    public function prep_row_status(&$row) {
        if (!empty($row->status)) {
            $row->_status = $row->status;
            $row->status = $this->status($row->status);
        }
    }

    public function prep_row_time(&$row) {
        if (!empty($row->time)) {
            $row->_time = $row->time;
            $yyyy = substr($row->time,0,4).".";
            $mm = substr($row->time,5,2).".";
            $dd = substr($row->time,8,2);
            $row->time = $yyyy.$mm.$dd;
        }
    }

    public function prep_row_time_paid(&$row) {
        if (!empty($row->time_paid)) {
            $row->_time_paid = $row->time_paid;
            $row->time_paid = date('Y-m-d H:i:s', mysql_to_unix($row->time_paid));
        }
    }

    public function prep_row_cart(&$row) {
        $this->cart->uid_order = $row->uid;
        $row->rows_cart = $this->cart->scaff_list(true);
        $row->total = $this->cart->get_total_by_rows($row->rows_cart, $row->dc_type);
    }

    public function prep_row_has_packages(&$row) {
        if (empty($row->rows_cart)) {
            return;
        }
        $row->has_packages = false;
        foreach ($row->rows_cart as $row_cart) {
            if ($row_cart->product_module == 'package' && $row_cart->play_count > 0) {
                $row->has_packages = true;
                break;
            }
        }
    }

    /*
    public function scaff_set($uid) {
            if ($this->scaff_opt('rules') == 'validation_rules_status' &&
                    $this->input->post('status') == 'canceled') {
                    $row = $this->get_by_id($uid);
                    if (!empty($row)) {
                            $error = $this->cancel_order($row);
                            if ($error) {
                                    $this->scaff_save_result($uid, $error);
                                    return;
                            }
                    }
            }

            parent::scaff_set($uid);
    }
    */

    public function item_amount(&$row_order, &$row_cart) {
        $amount = $row_cart->price * $row_cart->quantity;

        //연박할인
        $dis_serial_stay = 0;
        $days = 0;

        if ($row_cart->product_module == 'package') {
            if($row_cart->no_series_dc == 'N'){
                $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                if ($days < 1) {
                    $days = 1;
                }
                if($days == 2)
                {
                    $dis_serial_stay += ((($row_cart->price / $days)*2 )*($row_cart->two_dc_rate/ 100)) ;
                }else if($days > 2)
                {
                    $dis_serial_stay += ((($row_cart->price / $days)*3 )*($row_cart->three_dc_rate/ 100)) ;
                }
            }
        }

        $discount = 0;
        if ($row_cart->dc_rate_coupon > 0) {
        $discount += round($amount * ($row_cart->dc_rate_coupon / 100));
        }

        if ($row_order->dc_type != 'none' && $row_order->discount_by_type > 0 ) {
        if ($row_order->dc_type == 'local') {

                                if($row_cart->sale_schedule_jeju == 'N'){
                        $discount += round($amount * ($row_cart->dc_rate_local / 100));
                                }else{

                                        $reservation_day= mysql_to_unix($row_order->time_paid.' 00:00:00');
                                        $begin = mysql_to_unix($row_cart->date_sale_begin_jeju.' 00:00:00');
                                        $end = mysql_to_unix($row_cart->date_sale_end_jeju.' 00:00:00') + (60*60*24);

                                        if (($reservation_day< $begin || $reservation_day> $end)){
                                $discount += round($amount) *0;
                                        }else{
                                $discount += round($amount * ($row_cart->dc_rate_local / 100));
                                        }

                                }


        } else if ($row_order->dc_type == 'guest') {

                                if($row_cart->sale_schedule_guest == 'N'){
                        $discount += round($amount * ($row_cart->dc_rate_guest / 100));
                                }else{


                                        $reservation_day= mysql_to_unix($row_order ->time_paid.' 00:00:00');
                                        $begin = mysql_to_unix($row_cart->date_sale_begin_guest.' 00:00:00');
                                        $end = mysql_to_unix($row_cart->date_sale_end_guest.' 00:00:00') + (60*60*24);

                                        if (($reservation_day< $begin || $reservation_day> $end)){
                                $discount += round($amount) * 0;
                                        }else{
                                $discount += round($amount * ($row_cart->dc_rate_guest/ 100));
                                        }

                                }
        }
        }
        $amount -= $discount;
        $amount -= $dis_serial_stay;

        if (!($row_cart->product_module == 'package')){//play , festival 일 경우
            if(!($row_order->uid_user == 0)){
                if($row_cart->duplicate_dc == 'N'){//중복 할인이 된다면 할인
                    $amount -= round($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100));
                }else{
                    if($row_order->dc_type == "none")
                        $amount -= round($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100));
                }
            }else{//비회원은 가격 할인안됨
            }
        }else{//package 일땐 무조건 중복 할인되기때문에 값을 뺀다
            if(!($row_order->uid_user == 0)){
                $amount -= round($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/100));
            }else{
            }
        }

        return round($amount);
    }


    public function cancel_amount(&$row_order, &$row_cart, $refund_all = false) {
        if ((!empty($row_cart->_status) && $row_cart->_status == 'canceled') ||
            $row_cart->status == 'canceled') {
            return 0;
        }
        $amount = $this->item_amount($row_order, $row_cart);

        $day = 86400;
        // $t = time();
        $t = strtotime('+0 day +0 hours', time());
        switch ($row_cart->product_module) {
        case 'package':
            if (!preg_match(REGXPAT_DATE, $row_cart->check_in)) {
                return 0;
            }
                // $t_paid = gmt2local($row_order->time_paid);
                $t_paid = strtotime($row_order->time_paid);
                $t_begin = mysql_to_unix($row_cart->check_in.' 18:00:00');
                $diff = $t_begin - $t;
                switch ($row_cart->refund_policy) {
                case 'earlybird':
                    if (date('Y-m-d', $t) == date('Y-m-d', $t_paid)) {
                        $amount *= 1;
                    } else {
                        return 0;
                    }
                    break;
                case 'default':
                default:
                    if (date('Y-m-d', $t) == date('Y-m-d', $t_paid)) {
                        $amount *= 1;
                    } else if ($diff < 0) {
                        return 0;
                    } else if ($diff > $day * 3) {
                        $amount *= 1;
                    } else {
                        if (empty($row_cart->daily_charges)) {
                            return 0;
                        }
                        // $daily_charges = @json_decode($row_cart->daily_charges);
                        $daily_charges = json_decode(stripslashes(html_entity_decode($row_cart->daily_charges)));
                        if (empty($daily_charges) || !is_array($daily_charges) || count($daily_charges) == 0) {
                            return 0;
                        }
                        $first_charge = $daily_charges[0];
                        if (empty($first_charge) || empty($first_charge->RoomRate)) {
                            return 0;
                        }
                        $amount -= $first_charge->RoomRate;
                    }
                    break;
                }
                break;
            case 'play':
                if (!preg_match(REGXPAT_DATETIME, $row_cart->time_schedule)) {
                    return 0;
                }
                // $t_begin = mysql_to_unix($row_cart->time_schedule);
                $t_begin = mktime(23,59,59,date('m', mysql_to_unix($row_cart->time_schedule)),date('d', mysql_to_unix($row_cart->time_schedule)),date('Y', mysql_to_unix($row_cart->time_schedule)));
                $diff = $t_begin - $t;
                if ($diff > $day * 8) {
                    $amount *= 1;
                } else if ($diff > $day * 3) {
                    $amount *= 0.9;
                } else if ($diff > $day * 2) {
                    $amount *= 0.7;
                } else if ($diff > $day * 1) {
                    $amount *= 0.5;
                } else {
                    $amount = 0;
                }
                break;
            case 'festival':
                if (!preg_match(REGXPAT_DATETIME, $row_cart->time_schedule)) {
                    return 0;
                }
                // $t_begin = mysql_to_unix($row_cart->time_schedule);
                $t_begin = mktime(23,59,59,date('m', mysql_to_unix($row_cart->time_schedule)),date('d', mysql_to_unix($row_cart->time_schedule)),date('Y', mysql_to_unix($row_cart->time_schedule)));
                $diff = $t_begin - $t;
                // if ($diff > $day * 10) {
                //     $amount *= 1;
                // } else if ($diff > $day * 7) {
                //     $amount *= 0.9;
                // } else if ($diff > $day * 3) {
                //     $amount *= 0.8;
                // } else if ($diff > $day * 1) {
                //     $amount *= 0.7;
                // } else {
                //     $amount = 0;
                // }

                if ($diff > $day * 10) {
                    $amount *= 1;
                } else if ($diff > $day * 7) {
                    $amount *= 0.7;
                } else if ($diff > $day * 3) {
                    $amount *= 0.5;
                } else if ($diff > $day * 2) {
                    $amount *= 0.3;
                } else {
                    $amount = 0;
                }

                break;
        }

        if ($refund_all) {
        return $amount;
        }

        return round($amount);
    }

    public function cancel_order_item(&$row, &$row_cart, $refund_all = false) {
        $amount = $this->cancel_amount($row, $row_cart, $refund_all);
        $memo_cms = $row_cart->memo_cms;
        if ($amount <= 0 && !$refund_all) {
                return null;
        }
        try {
            /* restore stock */
            switch ($row_cart->product_module) {
            case 'package':
                $this->load->model('wings');

                $segment_A = "";
                $segment_B = "";

                if( $row_cart->cms_reserv_id == '' ) {
                    $str_paid = strtotime($row->time_paid);
                    $str_change = strtotime("2018-04-05 11:07:16");
                    if( $str_paid < $str_change ) {
                        $segment_A = $row->uid;
                        $segment_B = $row_cart->uid;
                    } else {
                        $segment_A = explode('-', $row->order_id)[0];
                        $segment_B = 'P' .(string)((int)$row_cart->uid + 100000);
                    }
                } else {
                    if( substr_count($row_cart->cms_reserv_id, "-") == 3 ) {
                        $segment_A = explode('-', $row_cart->cms_reserv_id)[0] . "-" . explode('-', $row_cart->cms_reserv_id)[1];
                        $segment_B = explode('-', $row_cart->cms_reserv_id)[2];
                    } else {
                        $segment_A = explode('-', $row_cart->cms_reserv_id)[0];
                        $segment_B = explode('-', $row_cart->cms_reserv_id)[1];
                    }

                }

                $day = 86400;
                $t = strtotime('+0 day +0 hours', time());

                $price_adult_count = 0;
                $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                if ($days < 1) {
                    $days = 1;
                }
                $rates = $this->wings->get_rooms(array(
                    'arrival_date' => $row_cart->check_in,
                    'departure_date' => $row_cart->check_out,
                    'room_count' => (int)$row_cart->quantity,
                    'adult_count' => (int)$row_cart->adult_count,
                    'child_count' => (int)$row_cart->child_count,
                    'rate_type_code' => $row_cart->rate_type_code,
                    'room_type_code' => $row_cart->room_type_code,
                ));

                if (empty($rates)) {
                } else {
                    foreach ($rates as $rate) {
                        if (!empty($rate->RoomTypeList)) {
                            foreach ($rate->RoomTypeList as $room) {
                                if (!empty($room->RoomTypeCode)) {
                                    $MinAdultPersons = $room->MinAdultPersons;
                                    $MaxAdultPersons = $room->MaxAdultPersons;
                                    if ($MinAdultPersons <= 0) {
                                        $MinAdultPersons = 1;
                                    }

                                    if (strpos($row_cart->room_type_code,'STD') !== false || strpos($row_cart->room_type_code,'SPD') !== false || strpos($row_cart->room_type_code,'CSD') !== false || strpos($row_cart->room_type_code,'CAD') !== false) {
                                        for ($c = $MinAdultPersons; $c <= $MaxAdultPersons; $c++) {
                                            if ($c == $row_cart->adult_count) {
                                                $price_adult_count += ($c - 1) * 10000 * $days;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $room_rate = $row_cart->price * $row_cart->quantity;

                if ($row_cart->dc_rate_coupon > 0) {
                    $room_rate = round($room_rate * (1 - ($row_cart->dc_rate_coupon / 100)));
                }

                if($row_cart->no_series_dc == 'N')
                {
                    $days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
                    if ($days < 1) {
                        $days = 1;
                    }

                    if($days == 2){
                        $dis_serial_stay= round(((($row_cart->price / $days)*2 )*($row_cart->two_dc_rate/ 100)));
                        $days  = 2;
                    }else if($days > 2){
                        $dis_serial_stay= round(((($row_cart->price / $days)*3 )*($row_cart->three_dc_rate/ 100)));
                        $days  = 3;
                    }
                }else{
                    $dis_serial_stay = 0;
                }

                $cmd = "SELECT order_id FROM dev_playcecampjeju.d_shop_order where uid = ".$row_cart->uid_order;
                $query = $this->db->query($cmd);
                $r_order = $query->row()->order_id;
                if($this->auth->is_login() && substr( $r_order, 0, 5 ) !== "GUEST"){
                    $room_rate = $room_rate-$dis_serial_stay-($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate / 100));
                }else{
                    $room_rate = $room_rate-$dis_serial_stay;
                }
                if($room_rate < 0)
                    $room_rate = 0;

                $this->db->insert( "wings_log", array('res' =>json_encode($row_cart->daily_charges)));

                // 윙스 remark 수정
                if (!empty($row_cart->memo_cms)) {

                    $wing_param = array (
                        'is_modify'        => true,
                        'uid_order'        => $segment_A,
                        'uid_cart'         => $segment_B,
                        'name'             => $row->billing_name,
                        'phone'            => $row->billing_phone,
                        'email'            => $row->email,
                        'daily_charge'     => $row_cart->daily_charges,
                        'arrival_date'     => $row_cart->check_in,
                        'departure_date'   => $row_cart->check_out,
                        'room_count'       => $row_cart->quantity,
                        'adult_count'      => $row_cart->adult_count,
                        'child_count'      => $row_cart->child_count,
                        'room_type_code'   => $row_cart->room_type_code,
                        'rate_type_code'   => $row_cart->rate_type_code,
                        'total_room_rate'  => $room_rate,
                    );

                    $t_paid = strtotime($row->time_paid);
                    $t_begin = mysql_to_unix($row_cart->check_in.' 18:00:00');
                    $diff = $t_begin - $t;
                    switch ($row_cart->refund_policy) {
                    case 'earlybird':
                        if (date('Y-m-d', $t) == date('Y-m-d', $t_paid)) {
                            $refund_terms = '얼리버드 결제당일 환불100%';
                        } else {
                            $refund_terms = '얼리버드 결제당일외 환불불가';
                        }
                        break;
                    case 'default':
                    default:
                        if (date('Y-m-d', $t) == date('Y-m-d', $t_paid)) {
                            $refund_terms = '결제당일 환불100%';
                        } else if ($diff < 0) {
                            $refund_terms = '체크인당일이후 환불불가';
                        } else if ($diff > $day * 3) {
                            $refund_terms = '체크인3일전18시까지 환불100%';
                        } else {
                            $refund_terms = '체크인3일전18시이후 첫1박요금제외환불';
                        }
                        break;
                    }

                    $memo_cms = $memo_cms . "\n[주문자취소됨] 환불조건(" . $refund_terms . ")에 따라 " . number_format($amount) . "원 환불";
                    $wing_param += ['memo' => $memo_cms];
                    // echo json_encode($wing_param);

                    $res = $this->wings->make_reservation($wing_param);
                    // echo json_encode($res);
                    $this->db->insert( "wings_log", array('res' =>json_encode($res)));
                    $this->db->insert( "wings_log", array('res' =>json_encode("======== cancel end========")) );
                }
                else {
                    // $wing_param += [ 'memo' => '[주문자취소됨]' ];
                    // echo json_encode($wing_param);
                }

                $wing_param = array (
                    'is_cancel' => true,
                    'uid_order' => $segment_A,
                    'uid_cart'  => $segment_B,
                );
                $res = $this->wings->make_reservation($wing_param);

                if (empty($res) ||
                    empty($res->ReservationResponse) ||
                    empty($res->ReservationResponse->ReservationResult) ||
                    empty($res->ReservationResponse->ReservationResult[0]) ||
                    empty($res->ReservationResponse->ReservationResult[0]->Success) ||
                    empty($res->ReservationResponse->ReservationResult[0]->Success->PMSReservationID)
                ) {
                    $tranLog = array(
                        "obj_cart"          => json_encode($row_cart),
                        "obj_order"         => json_encode($row),
                        "obj_wings"         => json_encode($wing_param),
                        "wings_response"    => json_encode($res)
                    );
                    $this->db->insert('dev_playcecampjeju.transaction_log',$tranLog);

                    // CMS 취소 오류 일 경우 주말오후 개발팀으로 메일 보내도록 설정함------------------------------
                    $bodyContents = "orderRecord : " . json_encode($row) . "<br><br>cartRecord : " . json_encode($row_cart) .
                                    "<br><br>결제자 : " . $row->billing_name . "<br>고객명 : " . $row_cart->owner_name . "<br>결제자 연락처: " . $row->billing_phone .
                                    "<br>Order UID : " . $row->uid . "<br>Order ID : " . $row->order_id . "<br>IMP Payment id : " . $row->payment_id .
                                    "<br>Cart UID : " . $row_cart->uid . "<br>Product : " . $row_cart->product_title . "<br>Wings 입실일 : " . $row_cart->check_in .
                                    "<br>환불가능 여부 : " . $row_cart->refundable_yn . "<br>Product : " . $row_cart->product_title .
                                    "<br><br>PMS 예약번호 : " . $row_cart->pms_reserv_id .
                                    "<br>Order Amount : " . $row->amount . "<br>Cart Amount : " . $row_cart->price.
                                    "<br><br>segment_A : " . $segment_A . "<br>segment_B : " . $segment_B;

                    $emailParam = array(
                        "user_email"    => "dev@afternoonofweekend.kr",
                        "display_name"  => "주말오후개발",
                        "title"         => " [긴급] CMS 취소오류가 검출되었습니다.",
                        "body"          => $bodyContents,
                    );
                    $this->load->helper("email_helper");
                    email($emailParam["title"], $emailParam["title"], $emailParam["body"], $emailParam["user_email"], $emailParam["display_name"], '', '');
                    // CMS 취소 오류 일 경우 주말오후 개발팀으로 메일 보내도록 설정함------------------------------

                    return 'CMS 취소오류가 홈페이지 운영팀에 전달되었습니다.<br>운영팀에서 환불 및 예약취소가 진행됩니다.';
                }

                break;
            case 'play':
            case 'festival':
                /* anything to do */
                break;
            default:
                $this->load_model('shop_variations');
                $this->db->trans_start();
                $this->db->query('UPDATE `'.$this->shop_variations->table.'` SET `stock` = `stock` + '.$row_cart->quantity.' WHERE `uid` = "'.$row_cart->uid_variation.'"');
                $this->db->trans_complete();
                break;
            }
        } catch ( Exception $e ) {
            return "구매 취소 오류";
        }

        /* cancel payment */
        if ($amount > 0 && $row->payment_id && $row->merchant_uid) {
            $iamport = new Iamport($this->config->item('imp_api_key'), $this->config->item('imp_api_secret'));
            $result = $iamport->cancel(array(
                'imp_uid' => $row->payment_id,
                'merchant_uid' => $row->merchant_uid,
                'amount' => $amount,
                'reason' => '주문자 취소',
                'refund_bank' => $row->refund_bank,
                'refund_account' => $row->refund_account,
                'refund_holder' => $row->refund_holder,
            ));
            if (!empty($result->error)) {
            //  return $result->error['code'].': '.$result->error['message'];
            }
        }

        /* set status */
        $this->db->trans_start();
        $this->db->where('uid_parent', $row_cart->uid)->delete('d_shop_cart');
        $this->db->where('uid', $row_cart->uid)->update('d_shop_cart', array(
                'time_return_req' => unix_to_human(time(), true, ''),
                'return_amount' => $amount,
                'status' => 'canceled',
                'memo_cms' => $memo_cms,
        ));
        $this->db->trans_complete();

        /* send email */
        // if (!$this->scaff_admin) {
                $this->notify_status_email($row->uid, $row_cart->uid);
        // }

        // 푸시메세지
        $this->load->model("messageSend");
        $push_param = array(
            "uid" => $row->uid_user,
            "title" => "[플레이스 캠프 제주] 예약 취소 안내",
            "contents" => "예약이 취소되었습니다.",
            "target_url" => "https://www.playcegroup.com/user/orders"
        );
        $this->messageSend->push($push_param);

        return null;
    }

    public function cancel_order(&$row) {
        /* cancel payment */
        if ($row->payment_id && $row->merchant_uid) {
            $iamport = new Iamport($this->config->item('imp_api_key'), $this->config->item('imp_api_secret'));
            /*
            $result = $iamport->findByImpUID($row->payment_id);
            if (!$result->success) {
                    return $result->error['code'].': '.$result->error['message'];
            }
            $payment_data = $result->data;
            if ($payment_data->status == 'paid') {
            */
            $result = $iamport->cancel(array(
                    'imp_uid' => $row->payment_id,
                    'merchant_uid' => $row->merchant_uid,
                    'reason' => '주문자 취소',
                    'refund_bank' => $row->refund_bank,
                    'refund_account' => $row->refund_account,
                    'refund_holder' => $row->refund_holder,
            ));
            if (!empty($result->error)) {
            //  return $result->error['code'].': '.$result->error['message'];
            }
            /*
                }
                */
        }

        /* unset coupon */
        $this->load->model('shop_coupon');
        $this->db->trans_start();
        $this->db->where('uid_order', $row->{$this->primary_key})
                ->update($this->shop_coupon->table, array(
                        'uid_order' => '0'
                ));
        $this->db->trans_complete();

        /* restore stock */
        $row = $this->get_by_id_as($row->uid);
        if (!empty($row->rows_cart)) {
            $this->load_model('shop_variations');
            $this->db->trans_start();
            foreach ($row->rows_cart as $row_cart) {
                $this->db->query('UPDATE `'.$this->shop_variations->table.'` SET `stock` = `stock` + '.$row_cart->quantity.' WHERE `uid` = "'.$row_cart->uid_variation.'"');
            }
            $this->db->trans_complete();
        }

        /* set status and send email */
        if (!$this->scaff_admin) {
            $this->save($row->uid, array('status' => 'canceled'));
            $this->notify_status_email($row->uid);
        }

        return null;
    }

    public function update_dc_type($uid, $dc_type, $discount_by_type) {
            $this->db->trans_start();
            $this->db->where('uid', $uid)->update($this->table, array(
                    'dc_type' => $dc_type,
                    'discount_by_type' => $discount_by_type,
            ));
            $this->db->trans_complete();
    }
        //momenti new function 상품 정보 모두 가져옴
    public function update_dis_serial_stay($uid,$discount_by_serial_stay)
    {

        $this->db->trans_start();
        $this->db->where('uid', $uid)->update($this->table, array(
            'discount_by_serial_stay' => $discount_by_serial_stay,
            ));
        $this->db->trans_complete();

    }
    public function get_order_info($uid_order)
    {
            return  $this->db->select('*')
                    ->where($this->table.'.'.'uid', $uid_order)
                    ->get($this->table)->row();

        }
        public function update_dis_member($uid,$discount_by_member)
        {
            $this->db->trans_start();
            $this->db->where('uid', $uid)->update($this->table, array(
                'discount_by_member' => $discount_by_member,
                ));
            $this->db->trans_complete();

        }


}
