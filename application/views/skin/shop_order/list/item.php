<div class="item">
	<div class="phone">
		<div class="header-time"><?=$row->time?></div>
		<div class="order-header">
			<?//include APPPATH.'views/skin/shop_order/list/item/cancel-button.php';?>
			<?include APPPATH.'views/skin/shop_order/list/item/order-id.php';?>
		</div>
		<?include APPPATH.'views/skin/shop_order/list/item/order-detail.php';?>
	</div>
	<div class="pc">
		<div class="order-header">
			<div class="col-l">
				<?//include APPPATH.'views/skin/shop_order/list/item/cancel-button.php';?>
				<?include APPPATH.'views/skin/shop_order/list/item/order-id.php';?>
			</div>
			<div class="col-r">
				<?//include APPPATH.'views/skin/shop_order/list/item/order-amount.php';?>
			</div>
		</div>
	</div>
	<div class="cols">
		<div class="col-l">
		<?
			$rows_cart = $row->rows_cart;
			$total = $row->total;
			include APPPATH.'views/skin/shop_order/form/cart-items.php';
		?>
		<?if (!empty($row->memo)) {?>
			<div class="order-memo"><?=$row->memo?></div>
		<?}?>

        <?if (($model->scaff_admin && $this->auth->is_staff_or_admin()) || $this->auth->is_admin()) {?>
		<div class="order-subinfo">

			<p>현재일시:&nbsp;&nbsp; <?=date('Y-m-d H:i:s', strtotime('+0 day +0 hours', time()))?></p>
			<p>결제일시:&nbsp;&nbsp; <?=date('Y-m-d H:i:s', strtotime($row->time_paid))?></p>
			<p>예약일시:&nbsp;&nbsp;
				<?foreach ($rows_cart as $row_cart) {?>

				<?
					switch ($row_cart->product_module) {
					case 'package':
						?>
						<span class="">
							<?=date('m월 d일', mysql_to_unix($row_cart->check_in.' 00:00:00'))?>
							~
							<?=date('m월 d일', mysql_to_unix($row_cart->check_out.' 00:00:00'))?>
						</span>
						<?
						break;
					case 'play':
						?>
						<span class="">
							<?=date('m월 d일 h:iA', mysql_to_unix($row_cart->time_schedule))?>
						</span>
						<?
						break;
					case 'festival':
						?>
						<span class="">
						<?
							if ($row_cart->is_pack == 'Y') {
								echo '전일권 ('.date('m월 d일', mysql_to_unix($row_cart->time_schedule)).' ~ '.date('m월 d일', mysql_to_unix($row_cart->fschedule_end.' 00:00:00')).')';
							} else if ($row_cart->is_pack == 'N') {
								echo '1일권 ('.date('m월 d일 h:iA', mysql_to_unix($row_cart->time_schedule)).')';
							}
						?>
						</span>
						<?
						break;
					default:
						break;
					}
				?>
				<span class="sep_subinfo">/</span>
				<?}?>
			</p>
			<p>환불조건:&nbsp;&nbsp;
				<?foreach ($rows_cart as $row_cart) {?>
				<?
					$day = 86400;
					$t = strtotime('+0 day +0 hours', time());
					switch ($row_cart->product_module) {
					case 'package':
						$t_paid = strtotime($row->time_paid);
						$t_begin = mysql_to_unix($row_cart->check_in.' 18:00:00');
						$diff = $t_begin - $t;
						switch ($row_cart->refund_policy) {
						case 'earlybird':
							if (date('Y-m-d', $t) == date('Y-m-d', $t_paid)) {
								echo '얼리버드 결제 당일 - 환불 100%';
							} else {
								echo '얼리버드 결제 당일 아님 - 환불 불가';
							}
							break;
						case 'default':
						default:
							if (date('Y-m-d', $t) == date('Y-m-d', $t_paid)) {
								echo '결제 당일 - 환불 100%';
							} else if ($diff < 0) {
								echo '체크인시간 이후 - 환불 불가';
							} else if ($diff > $day * 3) {
								echo '체크인 3일전 18시까지 - 환불 100%';
							} else {
								echo '체크인 3일전 18시이후 - 첫1박 요금 제외 환불';
							}
							break;
						}
						break;
					case 'play':
						$t_begin = mktime(23,59,59,date('m', mysql_to_unix($row_cart->time_schedule)),date('d', mysql_to_unix($row_cart->time_schedule)),date('Y', mysql_to_unix($row_cart->time_schedule)));
						$diff = $t_begin - $t;
						if ($diff > $day * 8) {
							echo '방문 7일이전 - 환불 100%';
						} else if ($diff > $day * 3) {
							echo '방문 7~3일전 - 환불 90%';
						} else if ($diff > $day * 2) {
							echo '방문 2일전 - 환불 70%';
						} else if ($diff > $day * 1) {
							echo '방문 1일전 - 환불 50%';
						} else {
							echo '방문 당일이후 - 환불 불가';
						}
						break;
					case 'festival':
						$t_begin = mktime(23,59,59,date('m', mysql_to_unix($row_cart->time_schedule)),date('d', mysql_to_unix($row_cart->time_schedule)),date('Y', mysql_to_unix($row_cart->time_schedule)));
						$diff = $t_begin - $t;
						// if ($diff > $day * 10) {
						// 	echo '방문 9일이전 - 환불 100%';
						// } else if ($diff > $day * 7) {
						// 	echo '방문 9~7일전 - 환불 90%';
						// } else if ($diff > $day * 3) {
						// 	echo '방문 6~3일전 - 환불 80%';
						// } else if ($diff > $day * 1) {
						// 	echo '방문 2~1일전 - 환불 70%';
						// } else {
						// 	echo '방문 당일이후 - 환불 불가';
						// }

						if ($diff > $day * 10) {
							echo '방문 9일이전 - 환불 100%';
						} else if ($diff > $day * 7) {
							echo '방문 9~7일전 - 환불 70%';
						} else if ($diff > $day * 3) {
							echo '방문 6~3일전 - 환불 50%';
						} else if ($diff > $day * 2) {
							echo '방문 2일전 - 환불 30%';
						} else {
							echo '방문 1일전,당일 - 환불 불가';
						}

						break;
					default:
						break;
					}
				?>
				<span class="sep_subinfo">/</span>
				<?}?>
			</p>
            <p>할인유형:&nbsp;&nbsp;
                <?foreach ($rows_cart as $row_cart) {?>
                    <?
                    $dc_default_text = ' 없음';
                    $dc_member_text='';
                    $dc_stay_text='';
                    $dc_text='';
                    $isDiscount = false;
                    $isMember = false;
                    $isSerial = false;
                    $isCoupon = false;
                    if($row->discount_by_member > 0 || $row->discount_by_serial_stay > 0 || $row->discount > 0){
                        $isDiscount = true;
                        if($row->discount_by_member > 0){
                            $isMember = true;
                            $dc_member_text = '회원할인 / '.($row_cart->member_dc_rate)."%";
                        }
                        if($row->discount_by_serial_stay > 0){
                            if ($row_cart->two_dc_rate > 0 || $row_cart->three_dc_rate > 0) {
                                $isSerial = true;
                                $dc_stay_text = '연박할인';
                            }
                        }
                        if($row->discount > 0){
                            $isCoupon = true;
                            $dc_text = ($row_cart->coupon_code). ' / '.($row_cart->dc_rate_coupon)."%";
                        }
                    }

                    if($isDiscount){
                        $dcText = $dc_member_text.", ".$dc_stay_text.", ".$dc_text;
                        if($isMember) {
                            if (!$isSerial && $isCoupon) {
                                $dcText =  $dc_member_text . ", " . $dc_text;
                            }else if ($isSerial && !$isCoupon) {
                                $dcText =  $dc_member_text . ", " . $dc_stay_text;
                            }else{
                                $dcText = $dc_member_text;
                            }
                        }else{
                            if (!$isSerial && $isCoupon) {
                                $dcText =  $dc_text;
                            }else if ($isSerial && !$isCoupon) {
                                $dcText =  $dc_stay_text;
                            }else if($isSerial && $isCoupon){
                                $dcText = $dc_stay_text . ", " .$dc_text;
                            }
                        }
                        echo $dcText;
                    }else{
                        echo $dc_default_text;
                    }
                    ?>
                    <span class="sep_subinfo">/</span>
                <?}?>
            </p>
			<p>결제금액:&nbsp;&nbsp;
				<?foreach ($rows_cart as $row_cart) {?>
					<?
						$item_amount = $model->item_amount($row, $row_cart);
						if( $item_amount < 0 ) {
							echo number_format($item_amount, $row->currency_rtdp);
							echo ' (실금액 0)';
						}
						else {
							echo number_format($item_amount, $row->currency_rtdp);
						}
					?>
				<span class="sep_subinfo">/</span>
				<?}?>
				<span>총 <?=number_format($row->amount, $row->currency_rtdp)?>
					<?if( $row->amount < 0 ){?>(실금액 0)<?}?>
				</span>
			</p>
            <p>할인전 데일리차지:<br/>
                <?foreach ($rows_cart as $row_cart) {
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
                        echo "<span style='margin-left:30px;'>".'-'."</span>&nbsp;($row_cart->product_title)<br>";
                    }else{
                        foreach ($rates as $rate){
                            foreach ($rate->RoomTypeList as $room) {
                                foreach ($room->DailyChargeList as $charge){
                                    //데일리차지 추출
                                    $replaceDate = preg_replace("/[^0-9]*/s", "", $charge->UseDate);
                                    $date = date("Y-m-d", substr($replaceDate, 0, 10));
                                    echo "<span style='margin-left:30px;'>".$date . " - ". number_format($charge->RoomRate) . "<span>원</span></span>&nbsp;($row_cart->product_title)<br>";

                                }
                            }
                        }
                    }
                }
                ?>
            </p>
			<p>환불금액:&nbsp;&nbsp;
				<?foreach ($rows_cart as $row_cart) {?>
					<?
						$cancel_amount = $model->cancel_amount($row, $row_cart);
						if( $item_amount < 0 ) {
							echo number_format($cancel_amount, $row->currency_rtdp);
							echo ' (실금액 0)';
						}
						else {
							echo number_format($cancel_amount, $row->currency_rtdp);
						}
						if ($cancel_amount > 0 && $row_cart->refundable_yn == 'Y') {
					?>
						<span>(예약 취소,환불 가능)</span>
					<?
						} else {
					?>
						<span>(예약 취소,환불 불가)</span>
					<?
						}
					?>
				<span class="sep_subinfo">/</span>
				<?}?>
			</p>
		</div>
		<?}?>

		<?if ($model->scaff_admin && $this->auth->is_staff_or_admin()) {?>
			<br />
			<div class="left">
				<div class="dcore-list-check-item">
					<input type="checkbox" value="<?=$row->uid?>" class="filled-in" id="dcore-list-check-item-<?=$row->uid?>" />
					<label for="dcore-list-check-item-<?=$row->uid?>"></label>
				</div>
			</div>
		<?}?>
		</div>
		<div class="col-r pc">
			<?include APPPATH.'views/skin/shop_order/list/item/order-detail.php';?>
		</div>
	</div>
</div>
