<?php
class Shop_cart extends MY_Model {
	public $table = 'd_shop_cart';

	public $owner_field = 'uid_user';
	public $date_created_field = 'time';

	public $perm_create = true;
	public $perm_list = true;
	public $perm_view = true;
	public $perm_update = true;
	public $perm_delete = true;
	public $perm_set = array('STAFF', 'ADMIN');
	public $perm_recaptcha = false;

	public $skin_with_wrapper = true;
	public $skin_list = 'skin/shop_cart/list';
	public $skin_view = 'common/empty';
	public $skin_update = 'common/empty';
	public $skin_delete = 'common/empty';
	public $view_with_list = false;
	public $scaff_rows_per_page = 0;

	public $uid_order = 0;
	public $uid_parent = 0;
	public $product_module="";

	public function __construct() {
		$this->lang->load('shop', detect_lang());
		$this->lang->load('cart', detect_lang());

		parent::__construct();

		$this->available_statuses = array(
			'normal' => $this->lang->line('cart_status_normal'),
			'canceled' => $this->lang->line('cart_status_canceled'),
            'canceled_zero' => $this->lang->line('cart_status_canceled_zero'),
            'canceled_force' => $this->lang->line('cart_status_canceled_force'),
		);

		if (!$this->db->table_exists($this->table)) {
			$this->db->trans_start();
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `{$this->table}` (
					`uid`				BIGINT UNSIGNED			NOT NULL AUTO_INCREMENT,
					`uid_parent`		BIGINT UNSIGNED			NOT NULL	DEFAULT 0,
					`uid_user`			BIGINT UNSIGNED			NOT NULL	DEFAULT 0,
					`uid_product`		BIGINT UNSIGNED			NOT NULL	DEFAULT 0,
					`uid_variation`		BIGINT UNSIGNED			NOT NULL	DEFAULT 0,
					`time_schedule`		DATETIME,
					`time_schedule_end`	DATETIME,
					`fschedule_end`		DATE,
					`is_pack`			ENUM('Y', 'N', 'UNKNOWN') NOT NULL	DEFAULT 'UNKNOWN',
					`uid_order`			BIGINT UNSIGNED			NOT NULL	DEFAULT 0,
					`uid_order_tmp`		BIGINT UNSIGNED			NOT NULL	DEFAULT 0,
					`lang`				VARCHAR(64),

					`session_id`		VARCHAR(40)				NOT NULL	DEFAULT '',
					`time`				DATETIME				NOT NULL,

					`status`			VARCHAR(64)				NOT NULL	DEFAULT 'normal',
					`time_return_req`	DATETIME,
					`return_req`		TEXT,
					`return_amount`		FLOAT					NOT NULL	DEFAULT 0,
					`time_return_resp`	DATETIME,
					`return_resp`		TEXT,

					`product_module`	TEXT					NOT NULL	DEFAULT '',
					`product_title`		TEXT					NOT NULL	DEFAULT '',
					`product_subtitle`	TEXT					NOT NULL	DEFAULT '',
					`dc_rate_local`		FLOAT UNSIGNED			NOT NULL	DEFAULT 0,
					`dc_rate_guest`		FLOAT UNSIGNED			NOT NULL	DEFAULT 0,
					`dc_rate_coupon`	FLOAT UNSIGNED			NOT NULL	DEFAULT 0,
					`coupon_code`		VARCHAR(64)				NOT NULL	DEFAULT '',
					`options`			TEXT					NOT NULL	DEFAULT '',
					`price`				FLOAT					NOT NULL	DEFAULT 0,
					`mileage`			FLOAT					NOT NULL	DEFAULT 0,
					`quantity`			INT UNSIGNED			NOT NULL	DEFAULT 0,

					`check_in`			DATE,
					`check_out`			DATE,
					`rate_type_code`	VARCHAR(64)				NOT NULL	DEFAULT '',
					`room_type_name`	VARCHAR(128)			NOT NULL	DEFAULT '',
					`room_type_code`	VARCHAR(64)				NOT NULL	DEFAULT '',
					`is_room_only`		ENUM('Y', 'N', 'UNKNOWN') NOT NULL	DEFAULT 'UNKNOWN',
					`adult_count`		INT						NOT NULL	DEFAULT 0,
					`child_count`		INT						NOT NULL	DEFAULT 0,
					`daily_charges`		LONGTEXT				NOT NULL	DEFAULT '',
					`pms_reserv_id`		VARCHAR(128)			NOT NULL	DEFAULT '',
					`play_count`		INT						NOT NULL	DEFAULT 0,
					`play_people`		INT						NOT NULL	DEFAULT 0,
					`refund_policy`		VARCHAR(64)				NOT NULL	DEFAULT 'default',
					`sale_schedule_jeju`        ENUM('Y', 'N')          NOT NULL    DEFAULT 'N',
                    `date_sale_begin_jeju`  DATE,
                    `date_sale_end_jeju`        DATE,
                    `sale_schedule_guest`       ENUM('Y', 'N')          NOT NULL    DEFAULT 'N',
                    `date_sale_begin_guest` DATE,
                    `date_sale_end_guest`       DATE,
                    `activity_cal_rate`     FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `FnB_cal_rate`      FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `outsourcing_cal_rate`      FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `no_cal_rate`       ENUM('Y', 'N')          NOT NULL    DEFAULT 'Y',
                    `room_cal_rate`     FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `two_dc_rate`       FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `three_dc_rate`     FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `no_series_dc`      ENUM('Y', 'N')          NOT NULL    DEFAULT 'Y',
                    `no_sale_membership`        ENUM('Y', 'N')          NOT NULL    DEFAULT 'N',
                    `festival_cal_rate`      FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `duplicate_dc`        ENUM('Y', 'N')          NOT NULL    DEFAULT 'N',
                    `member_dc_rate`      FLOAT UNSIGNED          NOT NULL    DEFAULT 0,
                    `memo_cms`          LONGTEXT				NOT NULL	DEFAULT '',

					INDEX (`time_schedule`),
					INDEX (`is_pack`),
					INDEX (`uid_order`),
					INDEX (`uid_order_tmp`),
					INDEX (`lang`),
					INDEX (`session_id`),
					PRIMARY KEY (`uid`)
				) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
			");
			$this->db->trans_complete();
		}

		$this->scaff_opt_default = array(
			'sortby' => 'time',
			'direction' => 'asc',
		);
	}

	public function validation_rules_status() {
		return $this->_validation_rule('status');
	}

	public function default_where() {
		parent::default_where();

		if (empty($this->uid_order)) {
			$this->filter_where($this->table.'.lang', detect_lang());
			if ($this->auth->is_login()) {
				$this->filter_where($this->table.'.'.$this->owner_field, $this->auth->userinfo->uid);
			} else {
				$this->filter_where($this->table.'.session_id', session_id());
			}
		}
		$this->filter_where($this->table.'.uid_order', $this->uid_order);
		$this->filter_where($this->table.'.uid_parent', $this->uid_parent);
	}

	public function delete_by_user($uid_user) {
		$rows = $this->db->select($this->primary_key)
			->where($this->table.'.'.$this->owner_field, $uid_user)
			->where($this->table.'.uid_order', '0')
			->get($this->table)->result();
		foreach ($rows as $row) {
			$this->delete($row->{$this->primary_key});
		}
	}

	public function delete_by_product($uid_product) {
		$rows = $this->db->select($this->primary_key)
			->where($this->table.'.uid_product', $uid_product)
			->where($this->table.'.uid_order', '0')
			->get($this->table)->result();
		foreach ($rows as $row) {
			$this->delete($row->{$this->primary_key});
		}
	}

	public function delete_by_order($uid_order) {
		$rows = $this->db->select($this->primary_key)
			->where($this->table.'.uid_order', $uid_order)
			->get($this->table)->result();
		foreach ($rows as $row) {
			$this->delete($row->{$this->primary_key});
		}
	}

	public function update_owner($uid_user) {
		$rows = $this->db->select('*')
			->where('session_id', session_id())
			->where($this->owner_field, '0')
			->get($this->table)->result();
		foreach ($rows as $row) {
			$uid_row = 0;
			switch ($row->product_module) {
			case 'package':
				break;
			case 'play':
				if (preg_match(REGXPAT_DATETIME, $row->time_schedule)) {
					$uid_row = $this->merge_quantity_schedule($row->uid_product, $row->time_schedule, $row->quantity);
				}
				break;
			case 'festival':
				if (preg_match(REGXPAT_DATETIME, $row->time_schedule) && preg_match(REGXPAT_BOOL, $row->is_pack)) {
					$uid_row = $this->merge_quantity_fschedule($row->uid_product, $row->time_schedule, $row->is_pack, $row->quantity);
				}
				break;
			default:
				$uid_row = $this->merge_quantity($row->uid_product, $row->uid_variation, $row->quantity);
				break;
			}
			if ($uid_row == 0) {
				$this->db->trans_start();
				$this->db
					->where($this->primary_key, $row->{$this->primary_key})
					->update($this->table, array(
						'session_id' => '',
						$this->owner_field => $uid_user,
					));
				$this->db->trans_complete();
			} else {
				$this->delete($row->uid);
			}
		}
	}

	public function update_order_tmp($uid_order) {
		$rows = $this->scaff_list(true);
		$this->db->trans_start();
		foreach ($rows as $row) {
			$this->db->update($this->table, array(
				$this->table.'.uid_order_tmp' => $uid_order,
			), array(
				$this->table.'.'.$this->primary_key => $row->uid,
			));
		}
		$this->db->trans_complete();
	}

	public function update_order($uid_order, $imp_uid = null) {
		$rows_variation = array();
		$rows = $this->db->select('*'/*'uid, uid_product, uid_variation, quantity, product_module, time_schedule, is_pack'*/)
			->where($this->table.'.uid_order_tmp', $uid_order)
			->get($this->table)
			->result();
		foreach ($rows as $row) {
			switch ($row->product_module) {
			case 'package':
				$this->load->model('wings');
				$row_order = $this->db->select('*')->where('uid', $uid_order)->get('d_shop_order')->row();
				if (empty($row_order)) {
                    return false;
				}

                $price_adult_count = 0;
                $days = (mysql_to_unix($row->check_out.' 00:00:00') - mysql_to_unix($row->check_in.' 00:00:00')) / 86400;
                if ($days < 1) {
                    $days = 1;
                }
                $rates = $this->wings->get_rooms(array(
                    'arrival_date' => $row->check_in,
                    'departure_date' => $row->check_out,
                    'room_count' => (int)$row->quantity,
                    'adult_count' => (int)$row->adult_count,
                    'child_count' => (int)$row->child_count,
                    'rate_type_code' => $row->rate_type_code,
                    'room_type_code' => $row->room_type_code,
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

                                    if (strpos($row->room_type_code,'STD') !== false || strpos($row->room_type_code,'SPD') !== false || strpos($row->room_type_code,'CSD') !== false || strpos($row->room_type_code,'CAD') !== false) {
                                        for ($c = $MinAdultPersons; $c <= $MaxAdultPersons; $c++) {
                                            if ($c == $row->adult_count) {
                                                $price_adult_count += ($c - 1) * 10000 * $days;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $room_rate = $row->price * $row->quantity;

				if ($row->dc_rate_coupon > 0) {
					$room_rate = round($room_rate * (1 - ($row->dc_rate_coupon / 100)));
				}

				if($row->no_series_dc == 'N')
				{
					$days = (mysql_to_unix($row->check_out.' 00:00:00') - mysql_to_unix($row->check_in.' 00:00:00')) / 86400;
					if ($days < 1) {
						$days = 1;
					}

					if($days == 2){
						$dis_serial_stay= round(((($row->price / $days)*2 )*($row->two_dc_rate/ 100)));
						$days  = 2;
					}else if($days > 2){
						$dis_serial_stay= round(((($row->price / $days)*3 )*($row->three_dc_rate/ 100)));
						$days  = 3;
					}
				}else{
					$dis_serial_stay = 0;
				}

				if($this->auth->is_login()){
                    $room_rate = $room_rate-$dis_serial_stay-($row->price * $row->quantity * ($row->member_dc_rate / 100));
                }else{
                    $room_rate = $room_rate-$dis_serial_stay;
                }
                if($room_rate < 0)
                    $room_rate = 0;

                // memo_cms
                $cond_dc = (!empty($row->coupon_code) || $row_order->discount_by_serial_stay > 0 || ($row->price * $row->quantity * ($row->member_dc_rate / 100) > 0 && $this->auth->is_login())) ? true : false;
                $cond_ec = ($price_adult_count > 0) ? true : false;

                $memo_cms = $row_order->memo;
                $memo_cms .= "\n(";
                if ($cond_dc) {
                    if (!empty($row->coupon_code)) {
                        $memo_cms .= "프로모션코드: ".$row->coupon_code."/".($row->dc_rate_coupon)."% , ";
                    }
                    if ($row->two_dc_rate > 0 || $row->three_dc_rate > 0) {
                        $memo_cms .= "연박할인".$days."박, ";
                    }
                    if ($row->price * $row->quantity * ($row->member_dc_rate / 100) > 0 && $this->auth->is_login()) {
                        $memo_cms .= "회원할인/".($row->member_dc_rate)."% ,";
                    }
                } else {
                    $memo_cms .= "할인없음";
                }
                if ($cond_ec) {
                    if (!$cond_dc) {
                        $memo_cms .= ", ";
                    }
                    $memo_cms .= "인원할증_".$row->adult_count."명, ";
                }
                if ($cond_dc || $cond_ec) {
                    $memo_cms .= "실결제금액: ".number_format($room_rate)."원)";
                } else {
                    $memo_cms .= ")";
                }

                /* DalyCharge 계산 및 실결제금액 CMS 인입 */
                $i = 0; // 박 수
                if (!empty($room->DailyChargeList)) {
                    foreach ($room->DailyChargeList as $charge) {
                        ++$i;
                    }
                }

                $daily_charges_original = json_encode($room->DailyChargeList);
                //$this->db->insert( "wings_log", array('res' =>json_encode("<<  cart.update_order--daily_charges_original--get_rooms.start------")) );
                //$this->db->insert( "wings_log", array('res' =>json_encode($daily_charges_original)) );
                //$this->db->insert( "wings_log", array('res' =>json_encode(">>  cart.update_order--daily_charges_original--get_rooms.end---------")) );

                $RoomReate = ($room_rate / $row->quantity) / $i;
                if (!empty($room->DailyChargeList)) {
                    $arrCnt = count($room->DailyChargeList);
                    $tempSum = 0;
                    foreach ($room->DailyChargeList as $index => $charge) {
                        //예약날짜 표시
                        $replaceDate = preg_replace("/[^0-9]*/s", "", $charge->UseDate);
                        $date = date("Y-m-d", substr($replaceDate, 0, 10));
                        $memo_cms .= "\n".$date." : ".number_format($charge->RoomRate)."원";
                        if($arrCnt == $index + 1){ //마지막 금액계산은 할인된 총 금액 - 마지막 박수 전 합산금액
                            $charge->RoomRate = ($room_rate / $row->quantity) - $tempSum;
                            $charge->Net = $RoomReate - round($RoomReate / 11);  // 부가세 뺀 금액
                            $charge->Vat = round($RoomReate / 11);               // 부가세
                        }else{
                            $charge->RoomRate = $RoomReate;                           // 결제 금액
                            $charge->Net = $RoomReate - round($RoomReate / 11);  // 부가세 뺀 금액
                            $charge->Vat = round($RoomReate / 11);               // 부가세
                            $tempSum += $RoomReate;
                        }
                    }
                }
                $row->daily_charges = json_encode($room->DailyChargeList);
                //$this->db->insert( "wings_log", array('res' =>json_encode("<<  cart.update_order--daily_charges--get_rooms.start------")) );
                //$this->db->insert( "wings_log", array('res' =>json_encode($row->daily_charges)) );
                //$this->db->insert( "wings_log", array('res' =>json_encode(">>  cart.update_order--daily_charges--get_rooms.end---------")) );

				$res = $this->wings->make_reservation(array(
					'name' => $row_order->billing_name,
					'phone' => $row_order->billing_phone,
					'email' => $row_order->email,
					'daily_charge' => $row->daily_charges,
					'arrival_date' => $row->check_in,
					'departure_date' => $row->check_out,
					'room_count' => $row->quantity,
					'adult_count' => $row->adult_count,
					'child_count' => $row->child_count,
					'room_type_code' => $row->room_type_code,
					'rate_type_code' => $row->rate_type_code,
					//'total_room_rate' => ($row->price - $price_adult_count) * $row->quantity,
					'total_room_rate' => $room_rate,
					// 기존 산하 wings 예약번호가 orderid - cartid 였던 것을 아임포트결제고유번호와 홈페이지 예약번호로 변경
                    'uid_order' => $row_order->order_id,
                    'uid_cart' => $row->uid,
					'memo' => $memo_cms,

				));

				if (empty($res) || empty($res->ReservationResponse) || empty($res->ReservationResponse->ReservationResult) || empty($res->ReservationResponse->ReservationResult[0]) ||
					empty($res->ReservationResponse->ReservationResult[0]->Success) || empty($res->ReservationResponse->ReservationResult[0]->Success->PMSReservationID) ) {
                    $this->db->insert( "wings_log", array('res' =>json_encode($res)) );
                    return false;
				}
				$this->db->trans_start();
				$this->db->update($this->table, array(
					$this->table.'.pms_reserv_id' => $res->ReservationResponse->ReservationResult[0]->Success->PMSReservationID,
                    $this->table.'.cms_reserv_id' => $res->ReservationResponse->OTAReservationID . "-" . $res->ReservationResponse->ReservationResult[0]->OTAReservationRoomKeyID . "-1",
                    $this->table.'.memo_cms' => $memo_cms,
                    $this->table.'.daily_charges' => $row->daily_charges,    //카트테이블 업데이트
                    $this->table.'.daily_charges_original' => $daily_charges_original
				), array(
					$this->table.'.uid' => $row->uid,
				));
				$this->db->trans_complete();
				break;
			case 'play':
				$this->load_model('shop_products', 'products');
				$this->products->module_field = null;
				$row_product = $this->products->filter_select('max_people')
					->filter_where($this->products->primary_key, $row->uid_product)
					->get(false)->row();
				if (empty($row_product)) {
					return false;
				}
				if (preg_match(REGXPAT_DATETIME, $row->time_schedule)) {
					$q = $this->get_quantity_of_schedule($row->uid_product, $row->time_schedule);
					$stock = $row_product->max_people - $q;
					if ($stock < 0) {
						return false;
					}
				} else {
					return false;
				}
				break;
			case 'festival':
				$this->load_model('shop_products', 'products');
				$this->products->module_field = null;
				$row_product = $this->products->filter_select('max_people')
					->filter_where($this->products->primary_key, $row->uid_product)
					->get(false)->row();
				if (empty($row_product)) {
					return false;
				}
                $this->load_model('shop_fschedule');
				if (preg_match(REGXPAT_DATETIME, $row->time_schedule) && preg_match(REGXPAT_BOOL, $row->is_pack)) {
					$stock = $this->shop_fschedule->get_stock($row->uid_product, $row->time_schedule, $row->is_pack, $row->uid_child_product);
                    if ($stock <= 0) {
						return false;
					}
				} else {
					return false;
				}
				break;
			default:
				$row_variation = $this->is_in_stock(
					$row->uid_product,
					$row->uid_variation,
					$row->quantity,
					true
				);
				if (empty($row_variation)) {
					return false;
				}
				$row_variation->stock -= $row->quantity;
				if ($row_variation->stock < 0) {
					return false;
				}
				$rows_variation[] = $row_variation;
				break;
			}
		}
		$this->load_model('shop_variations', 'variation');
		$this->db->trans_start();
		foreach ($rows_variation as $row_variation) {
			$this->db->update($this->variation->table, array(
				'stock' => $row_variation->stock
			), array(
				$this->variation->primary_key => $row_variation->uid
			));
		}
		$this->db->update($this->table, array(
			$this->table.'.uid_order' => $uid_order,
			$this->table.'.uid_order_tmp' => '0',
		), array(
			$this->table.'.uid_order_tmp' => $uid_order,
		));
		$this->db->trans_complete();
		return true;
	}

	public function cleanup_old_entries() {
		$now = unix_to_human(local_to_gmt(time()), true, '');
		$rows = $this->db->select($this->primary_key)
			->where($this->table.'.'.$this->owner_field, '0')
			->where($this->table.'.uid_order', '0')
			->where($this->table.'.'.$this->date_created_field.' < ADDDATE("'.$now.'", "-3")')
			->get($this->table)->result();
		foreach ($rows as $row) {
			$this->delete($row->{$this->primary_key});
		}

		$this->load->model('shop_coupon', 'coupon');
		$this->coupon->cancel_coupon();
	}

	public function scaff_create() {
		show_404();
		return;
	}

	public function scaff_update($uid) {
		show_404();
		return;
	}

	public function scaff_delete($uid) {
		show_404();
		return;
	}

	public function scaff_do_delete($uid) {
		$this->db->select($this->primary_key);
		$this->db->where($this->primary_key, $uid);
		$this->db->where('uid_order', $this->uid_order);
		if ($this->auth->is_login()) {
			$this->db->where($this->owner_field, $this->auth->userinfo->uid);
		} else {
			$this->db->where('session_id', session_id());
		}
		$row = $this->db->get($this->table)->row();
		if ($row) {
			parent::scaff_do_delete($row->uid);
			$this->update_dc_type();
			$this->load->model("shop_order",'order');
			$row_order = $this->order->my_pending_row(false);
			//$this->order->update_dis_serial_stay($row_order->uid,0);
		}

		$this->load->model('shop_coupon', 'coupon');
		$this->coupon->cancel_coupon();
	}

	public function validation_rules() {
		$rules = array();

		$rules['uid_product'] = array(
			'field' => 'uid_product',
			'rules' => 'trim||required||is_natural',
		);

		$rules['uid_variation'] = array(
			'field' => 'uid_variation',
			'rules' => 'trim||is_natural',
		);

		$rules['time_schedule'] = array(
			'field' => 'time_schedule',
			'rules' => 'trim||datetime',
		);

		$rules['fschedule_end'] = array(
			'field' => 'fschedule_end',
			'rules' => 'trim||date',
		);

		$rules['is_pack'] = array(
			'field' => 'is_pack',
			'rules' => 'trim||regex_match['.REGXPAT_BOOL_UNKNOWN.']',
		);

		$rules['quantity'] = array(
			'field' => 'quantity',
			'label' => $this->lang->line('order_quantity'),
			'rules' => 'trim||required||is_natural||greater_than[0]',
		);

		$rules['check_in'] = array(
			'field' => 'check_in',
			'rules' => 'trim||date',
		);
		$rules['check_out'] = array(
			'field' => 'check_out',
			'rules' => 'trim||date',
		);
		$rules['rate_type_code'] = array(
			'field' => 'rate_type_code',
			'rules' => 'trim',
		);
		$rules['room_type_name'] = array(
			'field' => 'room_type_name',
			'rules' => 'trim',
		);
		$rules['room_type_code'] = array(
			'field' => 'room_type_code',
			'rules' => 'trim',
		);
		$rules['adult_count'] = array(
			'field' => 'adult_count',
			'rules' => 'trim||numeric',
		);
		$rules['child_count'] = array(
			'field' => 'child_count',
			'rules' => 'trim||numeric',
		);

		return $rules;
	}

	public function scaff_create_hook($uid = null) {
		$this->cleanup_old_entries();


		$uid_product = $this->input->post('uid_product');
		$uid_variation = $this->input->post('uid_variation');
		$time_schedule = $this->input->post('time_schedule');
		$is_pack = $this->input->post('is_pack');
		$quantity = $this->input->post('quantity');
		$replaceQuantity = $this->input->post('replaceQuantity') == 'true' ? true : false;

		if ($quantity <= 0) {
			return true;
		}

		$uid_row = 0;

		$this->load_model('shop_products', 'products');
		$this->products->module_field = null;
		$this->products->scaff_mode = 'view';
		$row_product = $this->products->get_by_id($uid_product);

		if (!$row_product) {
			$this->validation_errors = 'No product';
			return false;
		}
		switch ($row_product->module) {
            case 'package':
                $_POST['price'] = -1;
                $_POST['daily_charges'] = null;
                $this->load->model('wings');

                $rates = $this->wings->get_rooms(array(
                    'arrival_date' => $this->input->post('check_in'),
                    'departure_date' => $this->input->post('check_out'),
                    'room_count' => (int)$this->input->post('quantity'),
                    'adult_count' => (int)$this->input->post('adult_count'),
                    'child_count' => (int)$this->input->post('child_count'),
                    'rate_type_code' => $this->input->post('rate_type_code'),
                    'room_type_code' => $this->input->post('room_type_code'),
                ));
                if (empty($rates)) {
                    $this->validation_errors = '해당 일자는 마감되었습니다. 다른 일자를 선택해주세요.';
                    return false;
                }
                foreach ($rates as $rate) {
                    if (!empty($rate->RoomTypeList)) {
                        foreach ($rate->RoomTypeList as $room) {
                            $price_adult_count = 0;
                            if (!empty($room->RoomTypeCode)) {
                                $MinAdultPersons = $room->MinAdultPersons;
                                $MaxAdultPersons = $room->MaxAdultPersons;
                                if ($MinAdultPersons <= 0) {
                                    $MinAdultPersons = 1;
                                }

                                if (strpos($this->input->post('room_type_code'),'STD') !== false || strpos($this->input->post('room_type_code'),'SPD') !== false || strpos($this->input->post('room_type_code'),'CSD') !== false || strpos($this->input->post('room_type_code'),'CAD') !== false) {
                                    for ($c = $MinAdultPersons; $c <= $MaxAdultPersons; $c++) {
                                        if ($c == (int)$this->input->post('adult_count')) {
                                            $price_adult_count += ($c - 1) * 10000;
                                        }
                                    }
                                }
                            }

                            if (!empty($room->DailyChargeList)) {
                                $_POST['price'] = 0;
                                $_POST['daily_charges'] = json_encode($room->DailyChargeList);
                                foreach ($room->DailyChargeList as $charge) {
                                    $_POST['price'] += $charge->RoomRate + $price_adult_count;
                                }
                            }
                            break;
                        }
                    }
                    break;
                }
                if ($_POST['price'] < 0 || empty($_POST['daily_charges'])) {
                    $this->validation_errors = '금액 정보를 가져오는데 실패했습니다.';
                    return false;
                }
                break;
            case 'play':
                if (preg_match(REGXPAT_DATETIME, $time_schedule)) {
                    $uid_row = $this->merge_quantity_schedule($uid_product, $time_schedule, $quantity, $replaceQuantity);
                }
                break;
            case 'festival':
                if (preg_match(REGXPAT_DATETIME, $time_schedule) && preg_match(REGXPAT_BOOL, $is_pack)) {
                    // 페스티벌 개별 상품의 uid 전달
                    //(int)$this->input->post('uid_selected_shop_fschedule');
                    $uid_row = $this->merge_quantity_fschedule($uid_product, $time_schedule, $is_pack, $quantity, $replaceQuantity);
                }
                break;
            default:
                $uid_row = $this->merge_quantity($uid_product, $uid_variation, $quantity, $replaceQuantity);
                break;
		}



		if ($uid_row < 0) {
			$this->validation_errors = $this->lang->line('inventory_out_of_stock');
			return false;
		} else if ($uid_row > 0) {
            $this->scaff_save_result($uid_row);
            return false;
		}
        return true;
	}

	public function merge_quantity_fschedule($uid_product, $time_schedule, $is_pack, $quantity, $replaceQuantity = false) {
		$this->load_model('shop_products', 'products');
		$this->products->module_field = null;
		$row_product = $this->products->filter_select('max_people')
			->filter_where($this->products->primary_key, $uid_product)
			->get(false)->row();
		if ( empty( $row_product ) ) { return -1; }

		$this->load_model('shop_fschedule');
        $stock = $this->shop_fschedule->get_stock($uid_product, $time_schedule, $is_pack);

		if ($stock < 0) {
			$stock = 0;
		}
		if ($stock > $row_product->max_people) {
			$stock = $row_product->max_people;
		}

		$fixed_quantity = false;
		if ($replaceQuantity) {
			if ($stock < $quantity) {
				$quantity = $stock;
				$fixed_quantity = true;
			}
		}

		$row = $this
			->filter_where($this->table.'.time_schedule', $time_schedule)
			->filter_where($this->table.'.is_pack', $is_pack)
			->get()->row();
		if ($row) {
			if (!$replaceQuantity) {
				$quantity += $row->quantity;
			}
			if ($stock < $quantity) {
				$quantity = $stock;
			}
			$this->db->trans_start();
			$this->db->update($this->table, array(
				'quantity' => $quantity,
			), array(
				$this->primary_key => $row->{$this->primary_key}
			));
			$this->db->trans_complete();
			return $fixed_quantity ? -1 : $row->{$this->primary_key};
		}

		return 0;
	}

	public function merge_quantity_schedule($uid_product, $time_schedule, $quantity, $replaceQuantity = false) {
		$this->load_model('shop_products', 'products');
		$this->products->module_field = null;
		$row_product = $this->products->filter_select('max_people')
			->filter_where($this->products->primary_key, $uid_product)
			->get(false)->row();
		if (empty($row_product)) {
			return -1;
		}

		$q = $this->get_quantity_of_schedule($uid_product, $time_schedule);
		$stock = $row_product->max_people - $q;
		if ($stock < 0) {
			$stock = 0;
		}

		$fixed_quantity = false;
		if ($replaceQuantity) {
			if ($stock < $quantity) {
				$quantity = $stock;
				$fixed_quantity = true;
			}
		}

		$row = $this
			->filter_where($this->table.'.time_schedule', $time_schedule)
			->get()->row();
		if ($row) {
			if (!$replaceQuantity) {
				$quantity += $row->quantity;
			}
			if ($stock < $quantity) {
				$quantity = $stock;
			}
			$this->db->trans_start();
			$this->db->update($this->table, array(
				'quantity' => $quantity,
			), array(
				$this->primary_key => $row->{$this->primary_key}
			));
			$this->db->trans_complete();
			return $fixed_quantity ? -1 : $row->{$this->primary_key};
		}

		return 0;
	}

	public function merge_quantity($uid_product, $uid_variation, $quantity, $replaceQuantity = false) {
		$row_variation = $this->is_in_stock($uid_product, $uid_variation, $quantity, $replaceQuantity);
		if (empty($row_variation)) {
			return -1;
		}

		$fixed_quantity = false;
		if ($replaceQuantity) {
			if ($row_variation->stock < $quantity) {
				$quantity = $row_variation->stock;
				$fixed_quantity = true;
			}
		} else {
			$quantity += $this->get_quantity($uid_product, $uid_variation);
		}

		$row = $this
			->filter_where($this->table.'.uid_product', $uid_product)
			->filter_where($this->table.'.uid_variation', $uid_variation)
			->get()->row();
		if ($row) {
			$this->db->trans_start();
			$this->db->update($this->table, array(
				'quantity' => $quantity,
			), array(
				$this->primary_key => $row->{$this->primary_key}
			));
			$this->db->trans_complete();
			return $fixed_quantity ? -1 : $row->{$this->primary_key};
		}

		return 0;
	}

	public function get_quantity($uid_product, $uid_variation) {
		$row = $this
			->select($this->table.'.quantity AS quantity')
			->filter_where($this->table.'.uid_product', $uid_product)
			->filter_where($this->table.'.uid_variation', $uid_variation)
			->get()->row();
		return $row ? $row->quantity : 0;
	}

	public function get_quantity_of_schedule($uid_product, $time_schedule) {
		$q = $this->db
			->select('SUM(d_shop_cart.quantity) AS n')
			->join('d_shop_order', 'd_shop_order.uid = d_shop_cart.uid_order', 'left')
			->where('d_shop_order.status', 'received')
			->where('d_shop_cart.uid_product', $uid_product)
			->where('d_shop_cart.time_schedule', $time_schedule)
			->where('d_shop_cart.status', 'normal')
			->get('d_shop_cart')->row();
		return (empty($q) || empty($q->n) ? 0 : $q->n);
	}
    // 페스티벌 개별 자식일정의 남은 수량을 받아오는 함수
    public function get_quantity_of_fschedule_individual($uid_product, $time_schedule, $is_pack, $uid_child_product) {
        if ( $uid_child_product == 0 ) {
            return get_quantity_of_fschedule($uid_product, $time_schedule, $is_pack);
        } else {
            $q = $this->db
			->select('SUM(d_shop_cart.quantity) AS n')
			->join('d_shop_order', 'd_shop_order.uid = d_shop_cart.uid_order', 'left')
			->where('d_shop_order.status', 'received')
			->where('d_shop_cart.uid_product', $uid_product)
            ->where('d_shop_cart.$uid_child_product', $$uid_child_product)
			->where('d_shop_cart.time_schedule', $time_schedule)
			->where('d_shop_cart.is_pack', $is_pack)
			->where('d_shop_cart.status', 'normal')
			->get('d_shop_cart')->row();
		return (empty($q) || empty($q->n) ? 0 : $q->n);
        }
    }

	public function get_quantity_of_fschedule($uid_product, $time_schedule, $is_pack) {
        $uid_child_product = (int)$this->input->post('uid_selected_shop_fschedule');

        if( $uid_child_product == 0 ) {
            $q = $this->db
                ->select('SUM(d_shop_cart.quantity) AS n')
                ->join('d_shop_order', 'd_shop_order.uid = d_shop_cart.uid_order', 'left')
                ->where('d_shop_order.status', 'received')
                ->where('d_shop_cart.uid_product', $uid_product)
                ->where('d_shop_cart.time_schedule', $time_schedule)
                ->where('d_shop_cart.is_pack', $is_pack)
                ->where('d_shop_cart.status', 'normal')
                ->get('d_shop_cart')->row();
        } else {
            $q = $this->db
                ->select('SUM(d_shop_cart.quantity) AS n')
                ->join('d_shop_order', 'd_shop_order.uid = d_shop_cart.uid_order', 'left')
                ->where('d_shop_order.status', 'received')
                ->where('d_shop_cart.uid_product', $uid_product)
                ->where('d_shop_cart.uid_child_product', $uid_child_product)
                ->where('d_shop_cart.time_schedule', $time_schedule)
                ->where('d_shop_cart.is_pack', $is_pack)
                ->where('d_shop_cart.status', 'normal')
                ->get('d_shop_cart')->row();
        }
        //echo $this->db->last_query();
        return (empty($q) || empty($q->n) ? 0 : $q->n);
    }

    public function get_total_by_rows($rows, $dc_type = 'none') {
        //momenti
        $total = new stdclass;
        $total->count = 0;
        $total->discount_by_type = 0;
        $total->price = 0;
        $total->shipping_cost = $this->conf->shipping_cost;
        $total->discount_by_serial_stay=0;
        $total->two_dc_rate=0;
        $total->three_dc_rate=0;
        $total->member_dc_rate =0;
        $total->no_series_dc="";
        $total->pro_uid = 0;
        $total->duplicate_dc= "";
        $total->discount_by_member = 0;
        $total->price_adult_count = 0;
        $total->is_change_price = 0;
        $total->daily_price_sum = 0;
        $total->cart_uids = array();

        $cart_uid_arr = array();

        $this->load->model('shop_products');

        $total->no_room= 1;

        if (!empty($rows)) {
            foreach ($rows as $row) {
                //$this->db->insert( "wings_log", array('res' =>json_encode("<< show cart log")) );
                //$this->db->insert( "wings_log", array('res' =>json_encode($row)) );
                //$this->db->insert( "wings_log", array('res' =>json_encode($row->uid)) );
                array_push($cart_uid_arr, $row->uid);

                switch ($row->product_module) {
                    case 'package':
                        $this->load->model('wings');

                        $days = (mysql_to_unix($row->check_out.' 00:00:00') - mysql_to_unix($row->check_in.' 00:00:00')) / 86400;
                        if ($days < 1) {
                            $days = 1;
                        }

                        $rates = $this->wings->get_rooms(array(
                            'arrival_date' => $row->check_in,
                            'departure_date' => $row->check_out,
                            'room_count' => (int)$row->quantity,
                            'adult_count' => (int)$row->adult_count,
                            'child_count' => (int)$row->child_count,
                            'rate_type_code' => $row->rate_type_code,
                            'room_type_code' => $row->room_type_code,
                    ));

                    if (empty($rates)) {
                        $total->no_room= 0;
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

                                        if (strpos($row->room_type_code,'STD') !== false || strpos($row->room_type_code,'SPD') !== false || strpos($row->room_type_code,'CSD') !== false || strpos($row->room_type_code,'CAD') !== false) {
                                            for ($c = $MinAdultPersons; $c <= $MaxAdultPersons; $c++) {
                                                if ($c == $row->adult_count) {
                                                    $total->price_adult_count += ($c - 1) * 10000 * $row->quantity * $days;
                                                }
                                            }
                                        }

                                        if (!empty($room->DailyChargeList)) {
                                            foreach ($room->DailyChargeList as $charge) {
                                                $total->daily_price_sum += $charge->RoomRate * $row->quantity;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $total->no_series_dc=$row->no_series_dc;
                $total->two_dc_rate=$row->two_dc_rate;
                $total->three_dc_rate=$row->three_dc_rate;
                $total->member_dc_rate=$row->member_dc_rate;

                if ($row->product_module == 'package') {
                    if($row->no_series_dc == 'N'){
                        $days = (mysql_to_unix($row->check_out.' 00:00:00') - mysql_to_unix($row->check_in.' 00:00:00')) / 86400;
                        if ($days < 1) {
                            $days = 1;
                        }
                        if($days == 2)
                        {
                            $total->discount_by_serial_stay += ((($row->price / $days)*2 )*($row->two_dc_rate/ 100)) ;
                        }else if($days > 2)
                        {
                            $total->discount_by_serial_stay += ((($row->price / $days)*3 )*($row->three_dc_rate/ 100)) ;

                        }
                        $total->discount_by_member += $row->price * $row->quantity * ($row->member_dc_rate/ 100);
                    }else{
                        $total->discount_by_member += $row->price * $row->quantity * ($row->member_dc_rate/ 100);
                    }
                }
                $total->count += $row->quantity;
                $total->price += $row->quantity * $row->price;

                if ($dc_type == 'local') {
                    //momenti 없음 제주도민 투숙객 할인 라디오 버튼 시 할인 금액 변경
                    if($row->sale_schedule_jeju == 'N'){//제주도민 할인 미체크시 상시 할인적용
                        $total->discount_by_type += ($row->quantity * $row->price) * ($row->dc_rate_local / 100);

                        if (!($row->product_module == 'package')) {
                            if($row->duplicate_dc == 'N')//중복 할인 됨
                                $total->discount_by_member += $row->price * $row->quantity * ($row->member_dc_rate/ 100);
                            else
                                $total->discount_by_member +=0 ;
                        }
                    }else
                    {
                        $now = time();
                        $begin = mysql_to_unix($row->date_sale_begin_jeju.' 00:00:00');
                        $end = mysql_to_unix($row->date_sale_end_jeju.' 00:00:00') + (60*60*24);
                        if (($now< $begin || $now > $end)) {//제주도민 할인 날짜에 포함되지 않으면 할인율을 계산하지않는다
                            $total->discount_by_type += ($row->quantity * $row->price) * 0;
                        }
                        else
                        {
                            $total->discount_by_type += ($row->quantity * $row->price) * ($row->dc_rate_local / 100);
                        }
                        if (!($row->product_module == 'package')) {
                            if($row->duplicate_dc == 'N')//중복 할인 됨
                                $total->discount_by_member += $row->price * $row->quantity * ($row->member_dc_rate/ 100);
                            else
                                $total->discount_by_member +=0 ;
                        }
                    }
                } else if ($dc_type == 'guest') {
                //	$p_row = $this->shop_products->get_total_product($row->uid_product);
                    if($row->sale_schedule_guest == 'N'){//투숙객 할인 미체크시 상시 할인적용
                        $total->discount_by_type += ($row->quantity * $row->price) * ($row->dc_rate_guest / 100);

                        if (!($row->product_module == 'package')) {
                            if($row->duplicate_dc == 'N')//중복 할인 됨
                                $total->discount_by_member += $row->price * $row->quantity * ($row->member_dc_rate/ 100);
                            else
                                $total->discount_by_member +=0 ;
                        }

                    }else
                    {
                        $now = time();
                        $begin = mysql_to_unix($row->date_sale_begin_guest.' 00:00:00');
                        $end = mysql_to_unix($row->date_sale_end_guest.' 00:00:00') + (60*60*24);
                        if (($now< $begin || $now > $end)) {//투숙객 할인 날짜에 포함되지 않으면 할인율을 계산하지않는다
                            $total->discount_by_type += ($row->quantity * $row->price) * 0;
                        }
                        else
                        {
                            $total->discount_by_type += ($row->quantity * $row->price) * ($row->dc_rate_guest/ 100);
                        }

                        if (!($row->product_module == 'package')) {
                            if($row->duplicate_dc == 'N')//중복 할인 됨
                                $total->discount_by_member += $row->price * $row->quantity * ($row->member_dc_rate/ 100);
                            else
                                $total->discount_by_member +=0 ;
                        }

                    }
                }else{
                    if (!($row->product_module == 'package')) {
                        $total->discount_by_member += $row->price * $row->quantity * ($row->member_dc_rate/ 100);

                    }
                }
            }

            if($total->daily_price_sum + $total->price_adult_count != $total->price){
                $total->is_change_price = 1;
            }
        }
        $total->discount_by_type = round($total->discount_by_type);
        $total->discount_by_serial_stay = round($total->discount_by_serial_stay);

        if ($this->conf->shipping_cost_free > 0 &&
            $this->conf->shipping_cost_free <= $total->price) {
            $total->shipping_cost = 0;
        }

        $total->cart_uids = $cart_uid_arr;

        return $total;
    }

	public function get_total($dc_type = 'none') {
		return $this->get_total_by_rows($this->scaff_list(true), $dc_type);
	}

	public function is_in_stock($uid_product, $uid_variation, $quantity, $replaceQuantity = false) {
		$this->load_model('shop_products', 'products');
		$this->products->module_field = null;
		$this->products->variations($this->input->post('uid_product'))->scaff_mode = 'list';
		$row_variation = $this->products->variations->get_by_id($uid_variation);
		if (!$row_variation) {
			return false;
		}

		$this->products->variations->prep_row($row_variation);
		if (!$replaceQuantity) {
			$quantity += $this->get_quantity($uid_product, $uid_variation);
			if ($row_variation->stock < $quantity) {
				return false;
			}
		}
		return $row_variation;
	}

	public function scaff_db_array($mode, $uid = null, $validation_rules = null) {
		$db_array = array();

		if ($this->scaff_mode == 'create') {
			if ($this->auth->is_login()) {
				$db_array[$this->owner_field] = $this->auth->userinfo->uid;
			} else {
				$db_array['session_id'] = session_id();
			}

			$db_array['lang'] = detect_lang();

			/* set price */
			$uid_product = $this->input->post('uid_product');

			$this->load_model('shop_products', 'products');
			$this->products->module_field = null;
			$this->products->scaff_mode = 'view';
			$row_product = $this->products->get_by_id($uid_product);
			if (!$row_product) {
				$this->validation_errors = 'No product';
				return false;
			}
			$this->products->prep_row($row_product);
			$db_array['product_module'] = $row_product->module;
			$db_array['product_title'] = $row_product->title;
			$db_array['product_subtitle'] = $row_product->subtitle;
			$db_array['refund_policy'] = $row_product->refund_policy;
			switch ($row_product->module) {
			case 'package':
				$db_array['price'] = $this->input->post('price');
				$db_array['daily_charges'] = $this->input->post('daily_charges');
				$db_array['play_count'] = $row_product->play_count;
				$db_array['play_people'] = $row_product->play_people;
				$db_array['is_room_only'] = $row_product->room_type_code ? 'Y' : 'N';
				break;
			case 'festival':
				$this->load_model('shop_fschedule');
				$db_array['price'] = $this->shop_fschedule->get_price($uid_product, $this->input->post('time_schedule'), $this->input->post('is_pack'));
				break;
			case 'play':
				$db_array['time_schedule_end'] = date('Y-m-d H:i:s', mysql_to_unix($this->input->post('time_schedule')) + ($row_product->duration_h * 3600 + $row_product->duration_m * 60));
				$db_array['price'] = $row_product->price_sale;
				break;
			default:
				$db_array['price'] = $row_product->price_sale;
				break;
			}

			/* dc rate */
			$db_array['dc_rate_local'] = $row_product->dc_rate_local;
			$db_array['dc_rate_guest'] = $row_product->dc_rate_guest;

			$db_array['sale_schedule_jeju'] = $row_product->sale_schedule_jeju;
			$db_array['date_sale_begin_jeju'] = $row_product->date_sale_begin_jeju;
			$db_array['date_sale_end_jeju'] = $row_product->date_sale_end_jeju;
			$db_array['sale_schedule_guest'] = $row_product->sale_schedule_guest;
			$db_array['date_sale_begin_guest'] = $row_product->date_sale_begin_guest;
			$db_array['date_sale_end_guest'] = $row_product->date_sale_end_guest;
			$db_array['two_dc_rate'] = $row_product->two_dc_rate;
			$db_array['three_dc_rate'] = $row_product->three_dc_rate;
			$db_array['no_series_dc'] = $row_product->no_series_dc;
			$db_array['duplicate_dc'] = $row_product->duplicate_dc;
			$db_array['member_dc_rate'] = $row_product->member_dc_rate;
			$db_array['activity_cal_rate'] = $row_product->activity_cal_rate;
            $db_array['FnB_cal_rate'] = $row_product->FnB_cal_rate;
            $db_array['outsourcing_cal_rate'] = $row_product->outsourcing_cal_rate;
            $db_array['room_cal_rate'] = $row_product->room_cal_rate;
            $db_array['festival_cal_rate'] = $row_product->festival_cal_rate;
            $db_array['no_cal_rate'] = $row_product->no_cal_rate;

            $db_array['time'] = $this->auth->get_database_now(); //주문일시 수정 추가

			/* set options */
			$row_variation = $this->is_in_stock(
				$uid_product,
				$this->input->post('uid_variation'),
				$this->input->post('quantity'));
			if (!empty($row_variation)) {
				$db_array['options'] = $row_variation->options;
			}

			/* set total price */
			if (!empty($row_variation->uids)) {
				$this->products->options($uid_product)->scaff_mode = 'view';
				foreach (explode(',', $row_variation->uids) as $uid_option) {
					$row_option = $this->products->options->get_by_id($uid_option);
					$db_array['price'] += $row_option->price;
				}
			}
		}

		return $db_array;
	}

	public function status($key) {
		return empty($this->available_statuses[$key]) ?
			$key : $this->available_statuses[$key];
	}

	public function prep_row_list(&$row) {
		parent::prep_row_list($row);
		$this->prep_row_status($row);
		$this->prep_row_variation($row);
		$this->prep_row_in_stock($row);
		$this->prep_row_product($row);
		$this->prep_row_nights($row);
		$this->prep_row_schedule_selectable($row);
		$this->prep_row_children($row);
	}

	public function prep_row_view(&$row) {
		parent::prep_row_view($row);
		$this->prep_row_status($row);
		$this->prep_row_variation($row);
		$this->prep_row_in_stock($row);
		$this->prep_row_product($row);
		$this->prep_row_nights($row);
		$this->prep_row_schedule_selectable($row);
		$this->prep_row_children($row);
	}

	public function prep_row_status(&$row) {
		if (!empty($row->status)) {
			$row->_status = $row->status;
			$row->status = $this->status($row->status);
		}
	}

	public function prep_row_variation(&$row) {
		$this->load_model('shop_variations', 'variations');
		$row->variation = $this->db
			->select('*')
			->where($this->variations->primary_key, $row->uid_variation)
			->get($this->variations->table)
			->row();
		$row->in_stock = false;
	}

	public function prep_row_in_stock(&$row) {
		$row->in_stock = false;
		switch ($row->product_module) {
		case 'package':
			$row->in_stock = true;
			break;
		case 'play':
			if (preg_match(REGXPAT_DATETIME, $row->time_schedule) && mysql_to_unix($row->time_schedule) > time()) {
                $this->load_model('shop_products', 'products');
				$this->products->module_field = null;
				$row_product = $this->products->filter_select('max_people')->filter_where($this->products->primary_key, $row->uid_product)->get(false)->row();
				if (!empty($row_product)) {
					$q = $this->get_quantity_of_schedule($row->uid_product, $row->time_schedule);
					$stock = $row_product->max_people - $q;
					if ($stock < 0) {
						$stock = 0;
					}
					if ($row->quantity <= $stock) {
						$row->in_stock = true;
					}
				}

                $closing_time = $this->products->filter_select('closing_time')->filter_where($this->products->primary_key, $row->uid_product)->get(false)->row();
                if (empty($closing_time)) { $closing_time = 0; }

                //var_dump($closing_time->closing_time);

                //  현재 시간이 시작시간 - 마감시간보다 큰 경우 예약못함
                if ( strtotime(date("Y-m-d H:i:s")) > strtotime( (string)$row->time_schedule . " -" . $closing_time->closing_time . " hours") ) {
                    $row->in_stock = false;
                }

			}
			break;
		case 'festival':
            if (preg_match(REGXPAT_DATETIME, $row->time_schedule) && preg_match(REGXPAT_BOOL, $row->is_pack)) {
                $is_valid = true;
                if( $row->is_pack == "N" ) {
                    $is_valid = false;
                    // 공연시작시간이 현재시간보다 크면(미래면)
                    if( mysql_to_unix($row->time_schedule) > time() ) {
                        $is_valid = true;
                    }
                }
                if( $is_valid ) {
                    $this->load_model('shop_fschedule');
                    $stock = $this->shop_fschedule->get_stock($row->uid_product, $row->time_schedule, $row->is_pack, $row->uid_child_product);

                    if ($row->quantity <= $stock) {
                        $row->in_stock = true;
                    }
                }
			}
			break;
		default:
			if (!empty($row->variation) && $row->quantity <= $row->variation->stock) {
				$row->in_stock = true;
			}
			break;
		}
	}

	public function prep_row_product(&$row) {
		$this->load_model('shop_products', 'products');
		$this->products->module_field = null;

		/* set image */
		$f = $this->files->get_rows($row->uid_product, $this->products->table, 'product_image')->row();
		if (!empty($f)) {
			$f->furl = $this->url_download($f);
			$row->featured_image = $f;
		}

		/* set slug */
		$row->slug = $row->uid;
		$row->product_status = 'PRIVATE';
		$map = $this->config->item('shop_base_url_map');
		if (!empty($map)) {
			foreach ($map as $v) {
				$row->base_url = $v;
				break;
			}
		}
		$map = $this->config->item('shop_admin_url_map');
		if (!empty($map)) {
			foreach ($map as $v) {
				$row->admin_url = $v;
				break;
			}
		}
		if (preg_match(REGXPAT_DATETIME, $row->time_schedule)) {
			$ts = mysql_to_unix($row->time_schedule);
			$row->time_schedule_date = date('m월 d일 ', $ts);
			$w = date('w', $ts);
			switch ($w) {
			case 0: $row->time_schedule_date .= '일요일'; break;
			case 1: $row->time_schedule_date .= '월요일'; break;
			case 2: $row->time_schedule_date .= '화요일'; break;
			case 3: $row->time_schedule_date .= '수요일'; break;
			case 4: $row->time_schedule_date .= '목요일'; break;
			case 5: $row->time_schedule_date .= '금요일'; break;
			case 6: $row->time_schedule_date .= '토요일'; break;
			}
			$row->schedule_times = date('H:i', $ts);
		}

		switch ($row->product_module) {
		case 'play':
			$row->review_expired_duration = '';
			if (preg_match(REGXPAT_DATETIME, $row->time_schedule)) {
				$t = mysql_to_unix($row->time_schedule) + (180 * 86400) - time();
				if ($t > 0) {
					$d = floor($t / 86400);
					$h = floor(($t % 86400) / 3600);
					$m = floor(($t % 3600) / 60);
					$s = $t % 60;
					$str = '';
					if ($d > 0) {
						$str .= $d.'일 ';
					}
					if ($h > 0) {
						$str .= $h.'시간 ';
					} else {
						if ($m > 0) {
							$str .= $m.'분 ';
						}
						if ($s > 0) {
							$str .= $s.'초 ';
						}
					}
					$row->review_expired_duration = $str;
				}
			}
			break;
		case 'festival':
			$row->review_expired_duration = '';
			if (preg_match(REGXPAT_DATETIME, $row->time_schedule)) {
				$t = mysql_to_unix($row->time_schedule) + (5 * 86400) - time();
				if ($t > 0) {
					$d = floor($t / 86400);
					$h = floor(($t % 86400) / 3600);
					$m = floor(($t % 3600) / 60);
					$s = $t % 60;
					$str = '';
					if ($d > 0) {
						$str .= $d.'일 ';
					}
					if ($h > 0) {
						$str .= $h.'시간 ';
					} else {
						if ($m > 0) {
							$str .= $m.'분 ';
						}
						if ($s > 0) {
							$str .= $s.'초 ';
						}
					}
					$row->review_expired_duration = $str;
				}
			}
			break;
		}

		$row_product = $this->products->get_by_id($row->uid_product);
		if (!empty($row_product)) {
			$this->products->scaff_mode = 'list';
			$this->products->prep_row($row_product);
			$row->product_status = $row_product->_status;
			if (!empty($row_product->slug)) {
				$row->slug = $row_product->slug;
			}
			$row->product_module = $row_product->module;
			$row->product_title = $row_product->title;
			$row->product_subtitle = $row_product->subtitle;
			$row->refund_policy = $row_product->refund_policy;
			$map = $this->config->item('shop_base_url_map');
			if (!empty($map) && !empty($map[$row_product->module])) {
				$row->base_url = $map[$row_product->module];
			}
			$map = $this->config->item('shop_admin_url_map');
			if (!empty($map) && !empty($map[$row_product->module])) {
				$row->admin_url = $map[$row_product->module];
			}
			if (!empty($row_product->schedule_times)) {
				$row->schedule_times = $row_product->schedule_times;
			}
			if (!empty($row_product->location)) {
				$row->location = $row_product->location;
			}
			if (!empty($row_product->files_icon_image) && !empty($row_product->files_icon_image[0])) {
				$row->icon = $row_product->files_icon_image[0];
			}
			if ($row->uid_order == '0') {
				switch ($row_product->module) {
				case 'package':
					/* DO NOT change price */
					break;
				case 'festival':
					$this->load_model('shop_fschedule');
					$row->price = $this->shop_fschedule->get_price($row_product->uid, $row->time_schedule, $row->is_pack);
					break;
				default:
					$row->price = $row_product->price_sale;
					break;
				}
				$row_variation = $this->is_in_stock(
					$row->uid_product,
					$row->uid_variation,
					$row->quantity,
					true
				);
				if (!empty($row_variation) && !empty($row_variation->uids)) {
					$this->products->options($row->uid_product)->scaff_mode = 'view';
					foreach (explode(',', $row_variation->uids) as $uid_option) {
						$row_option = $this->products->options->get_by_id($uid_option);
						$row->price += $row_option->price;
					}
				}
			}
		}
	}

	public function prep_row_nights(&$row) {
		$row->nights = 0;
		if (
			$row->product_module == 'package' &&
			preg_match(REGXPAT_DATE, $row->check_in) &&
			preg_match(REGXPAT_DATE, $row->check_out)
		) {
			$row->nights = (
				mysql_to_unix($row->check_out.' 00:00:00') -
				mysql_to_unix($row->check_in.' 00:00:00')
			) / 86400;
			if ($row->nights < 1) {
				$row->nights = 1;
			}
		}
	}

	public function prep_row_schedule_selectable(&$row) {
		$row->schedule_selectable = false;
		if (
			$row->product_module == 'package' &&
			preg_match(REGXPAT_DATE, $row->check_out) &&
			mysql_to_unix($row->check_out.' 00:00:00') >=
			mysql_to_unix(date('Y-m-d').' 00:00:00')
		) {
			$row->schedule_selectable = true;
		}
	}

	public function prep_row_children(&$row) {
		if ($row->product_module == 'package' && $this->auth->is_login()) {
			$row->children = $this->db
				->select('*')
				->where('d_shop_cart.uid_user', $this->auth->userinfo->uid)
				->where('d_shop_cart.uid_parent', $row->uid)
				->where('d_shop_cart.status', 'normal')
				->order_by('d_shop_cart.time_schedule', 'asc')
				->get('d_shop_cart')->result();
			$this->prep_rows($row->children);
		}
	}

	private function update_dc_type() {
		$this->load->model('shop_order');
		$this->load->model('auth');
		$row_order = $this->shop_order->my_pending_row(false);
		if (!empty($row_order)) {
			$total = $this->get_total($row_order->dc_type);
			if (!empty($total) && isset($total->discount_by_type)) {
				$this->shop_order->update_dc_type($row_order->uid, $row_order->dc_type, $total->discount_by_type);
				if ($this->auth->is_login()) {
                    $this->shop_order->update_dis_member($row_order->uid,$total->discount_by_member);
                }else{
                    $this->shop_order->update_dis_member($row_order->uid,0);

                }
                $this->shop_order->update_dis_serial_stay($row_order->uid,$total->discount_by_serial_stay);
			}
		}
	}

	protected function scaff_save_result($uid, $error = null) {
		if ($uid && !$error) {
			switch ($this->scaff_mode) {
			case 'create':
			case 'update':
				$this->update_dc_type();
				break;
			}
		}
		parent::scaff_save_result($uid, $error);
	}
	public function p_module($m)
	{
		return	 $this->db
				->select('*')
				->where('product_module',$m )
				->get('d_shop_cart')->result();
	}

    public function update_change_price($cart_uids) {

        $cart_uid_arr = array();
        $cart_uid_arr = explode(",", $cart_uids);

        $cmd =  "select * from d_shop_cart where uid in (".implode(",", $cart_uid_arr).") and status = 'normal'";
        $row_order = $this->db->query($cmd)->result();
        $this->db->insert( "wings_log", array('res' =>json_encode("======== row_order @@@@@@@ ========")));
        $this->db->insert( "wings_log", array('res' =>json_encode($row_order)));

        $this->load->model('wings');
        /*$row_order = $this->db->select('*')
            -> where('uid_user', $this->auth->userinfo->uid)
            -> where('uid_order', 0)
            -> where('status', 'normal')
            ->get('d_shop_cart')->result();*/
        if (empty($row_order)) {
            return false;
        }
        foreach ($row_order as $rowOrder) {
            $rates = $this->wings->get_rooms(array(
                'arrival_date' => $rowOrder->check_in,
                'departure_date' => $rowOrder->check_out,
                'room_count' => (int)$rowOrder->quantity,
                'adult_count' => (int)$rowOrder->adult_count,
                'child_count' => (int)$rowOrder->child_count,
                'rate_type_code' => $rowOrder->rate_type_code,
                'room_type_code' => $rowOrder->room_type_code,
            ));

            $dailyCharge = "";
            $roomRate = 0;
            $isSoldOut = 0;

            if(empty($rates) || $rates == ''){
                $isSoldOut = 1; //예약마감 체크
            }else{
                foreach ($rates as $rate){
                    foreach ($rate->RoomTypeList as $room) {
                        $dailyCharge = $room->DailyChargeList;
                        foreach ($room->DailyChargeList as $Charge){
                            $roomRate += $Charge->RoomRate;
                        }
                    }
                }
                $dailyCharge = json_encode($dailyCharge);

                //날짜 계산
                $days = (mysql_to_unix($rowOrder->check_out.' 00:00:00') - mysql_to_unix($rowOrder->check_in.' 00:00:00')) / 86400;

                if ($days < 1) {
                    $days = 1;
                }
                $adult_Rate = 0;
                // 방 종류 별 인원수 추가 금액 계산
                if (strpos($rowOrder->room_type_code,'STD') !== false || strpos($rowOrder->room_type_code,'SPD') !== false || strpos($rowOrder->room_type_code,'CSD') !== false || strpos($rowOrder->room_type_code,'CAD') !== false) {
                    $adult_Rate = ($rowOrder->adult_count - 1) * 10000 * $days; // 인원수 추가 금액
                }
                //방 금액 + 인원수 추가 금액
                $roomRate = $roomRate + $adult_Rate;
            }

            if($isSoldOut == 1){
                $this->db->insert( "wings_log", array('res' =>json_encode("======== delete ========")) );
                $this->db->insert( "wings_log", array('res' =>json_encode("uid : ".$rowOrder->uid)) );
                $this->db->insert( "wings_log", array('res' =>json_encode("user : ".$this->auth->userinfo->uid)) );
                $this->db->insert( "wings_log", array('res' =>json_encode("title : ".$rowOrder->product_title)) );
                $cmd = "delete from d_shop_cart where uid = ".$rowOrder->uid;
                $this->db->query( $cmd );
            }else{
                $this->db->insert( "wings_log", array('res' =>json_encode("======== update ========")) );
                $this->db->insert( "wings_log", array('res' =>json_encode("uid : ".$rowOrder->uid)) );
                $this->db->insert( "wings_log", array('res' =>json_encode("title : ".$rowOrder->product_title)) );
                $cmd = "update d_shop_cart set price=" . $roomRate . " where uid = ".$rowOrder->uid;
                $this->db->query( $cmd );
                $this->db->trans_start();
                $this->db->update($this->table, array(
                    $this->table.'.daily_charges' => $dailyCharge,    //카트테이블 업데이트
                ), array(
                    $this->table.'.uid' => $rowOrder->uid,
                ));
                $this->db->trans_complete();
            }
            $this->update_dc_type(); //할인 갱신

        }
    }
}
