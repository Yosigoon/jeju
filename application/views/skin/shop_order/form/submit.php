<?
	$currency = $model->scaff_mode == 'checkout' ? $this->lang->line('shop_currency') : $row->currency;
	$currency_rtdp = $model->scaff_mode == 'checkout' ? $this->lang->line('shop_currency_rtdp') : $row->currency_rtdp;
?>
<div class="dcore-checkout-cart-items">
	<div class="title">상품 금액</div>
	<div class="items">
	<?foreach ($rows_cart as $row_cart) {
		$link_url = ($model->scaff_admin ? $row_cart->admin_url : $row_cart->base_url).'/'.$row_cart->slug;
		$link_tag_open = $row_cart->product_status == 'PUBLIC' ? '<a href="'.$link_url.'">' : '<span>';
		$link_tag_open_text = $row_cart->product_status == 'PUBLIC' ? '<a href="'.$link_url.'">' : '<span>';
		$link_tag_close = $row_cart->product_status == 'PUBLIC' ? '</a>' : '</span>';
		if ($row_cart->product_module == 'package') {
			$days = (mysql_to_unix($row_cart->check_out.' 00:00:00') - mysql_to_unix($row_cart->check_in.' 00:00:00')) / 86400;
			if ($days < 1) {
				$days = 1;
			}
		}
	?>
		<div class="item">
			<div class="item-title">
				<?=$link_tag_open_text?><?=$row_cart->product_title?><?=$row_cart->product_subtitle ? ' <small><em>'.$row_cart->product_subtitle.'&nbsp;</em></small>' : ''?><?=$link_tag_close?>
			</div>
			<div class="item-info">
				<div class="t">
					<?=$currency?>
					<?=number_format($row_cart->price, $currency_rtdp)?>
					<i class="xi-close-thin"></i>
					<?=$row_cart->quantity?>개
				</div>
				<div class="d">
					<?=$currency?>
					<?=number_format($row_cart->quantity * $row_cart->price, $currency_rtdp)?>
				</div>
			</div>
			<?if ($row_cart->product_module == 'package') {?>

						<?
							$series=0;
							if($row_cart->no_series_dc == 'N' && $days > 1){?>
							 	<?if($days != 1){
                                    if($days ==2 ){
                                        $series=round(((($row_cart->price / $days)*2 )*($row_cart->two_dc_rate/ 100)));
                                        $days = 2;

                                    }else if($days >2){
                                        $series=round(((($row_cart->price / $days)*3 )*($row_cart->three_dc_rate/ 100)));
                                        $days = 3;
                                    }
                                }?>
								<?if($days > 1 && $series > 0 ){?>
								<div class="item-discount ">
									<div class="t">
										연박 할인
										(<?=$days?>박)
									</div>
								<div class="d">
								<small>(-)</small>
								<?=$currency?>
								<?=number_format(round($series , $currency_rtdp))?>
								</div>
								</div>

								<?}?>
							<?}?>
					<?}?>


			<?

			?>
		<?
			if($row_cart->sale_schedule_jeju =='Y'){
			if ($row->dc_type == 'local' && $row_cart->dc_rate_local > 0 &&
				(time() > mysql_to_unix($row_cart->date_sale_begin_jeju.' 00:00:00')&&
				 (time() < mysql_to_unix($row_cart->date_sale_end_jeju.' 00:00:00') + (60*60*24))))  {?>
			<div class="item-discount local-">
				<div class="t">
					제주도민 할인
				</div>
				<div class="d">
					<small>(-)</small>
					<?=$currency?>
					<?=number_format(round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_local / 100)), $currency_rtdp)?>
				</div>
			</div>
			<?}?>
		<?}else if($row->dc_type == 'local' && $row_cart->dc_rate_local > 0 && $row_cart->sale_schedule_jeju =='N'){?>
			<div class="item-discount local-">
				<div class="t">
					제주도민 할인
				</div>
				<div class="d">
					<small>(-)</small>
					<?=$currency?>
					<?=number_format(round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_local / 100)), $currency_rtdp)?>
				</div>
			</div>

		<?}?>
		<?if($row_cart->sale_schedule_guest=='Y'){?>
		<?if ($row->dc_type == 'guest' && $row_cart->dc_rate_guest > 0 &&
				(time() > mysql_to_unix($row_cart->date_sale_begin_guest.' 00:00:00')&&
				(time() < mysql_to_unix($row_cart->date_sale_end_guest.' 00:00:00') + (60*60*24)))) {?>
			<div class="item-discount guest-">
				<div class="t">
					투숙객 할인
				</div>
				<div class="d">
					<small>(-)</small>
					<?=$currency?>
					<?=number_format(round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_guest / 100)), $currency_rtdp)?>
				</div>
			</div>
			<?}?>
		<?}else if($row->dc_type == 'guest' && $row_cart->dc_rate_guest> 0 && $row_cart->sale_schedule_guest=='N'){?>
			<div class="item-discount local-">
				<div class="t">
					투숙객 할인
				</div>
				<div class="d">
					<small>(-)</small>
					<?=$currency?>
					<?=number_format(round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_guest/ 100)), $currency_rtdp)?>
				</div>
			</div>
		<?}?>
		<?if ($row_cart->dc_rate_coupon > 0) {?>
			<div class="item-discount coupon-">
				<div class="t">
					프로모션 코드 할인
				</div>
				<div class="d">
					<small>(-)</small>
					<?=$currency?>
					<?=number_format(round($row_cart->quantity * $row_cart->price * ($row_cart->dc_rate_coupon / 100)), $currency_rtdp)?>
				</div>
			</div>
		<?}?>
		<?if($this->auth->is_login()){?>
            <?if($row_cart->product_module == 'package' || $row->dc_type =='none' || $row_cart->duplicate_dc == 'N' ){?>
            <div class="item-discount dis_member-">
                <div class="t">
                    회원 할인
                </div>
                <div class="d">
                    <small>(-)</small>
                    <?=$currency?>
                    <?=number_format(round($row_cart->price * $row_cart->quantity * ($row_cart->member_dc_rate/ 100)), $currency_rtdp)?>
                </div>
            </div>
            <?}?>
        <?}?>
		</div>
	<?}?>
	</div>
</div>
<?if (($model->scaff_mode == 'checkout' || $model->scaff_mode == 'view') && empty($model->for_cart_count)) {?>
<div class="dcore-checkout-cart-discount">
	<div class="title">할인 금액</div>
	<div class="items">
		<div class="item">
			<div class="item-info">
				<div class="t"><span class="dcore-discount-by-type-title"><?=$row->dc_type == 'guest' ? '투숙객 ' : ($row->dc_type == 'local' ? '제주도민 ' : '')?>할인</span></div>
				<div class="d">
					<small>(−)</small> <?=$currency?>
					<span class="dcore-cart-discount-by-type-value"><?=number_format($row->discount_by_type, $currency_rtdp)?></span>
				</div>
			</div>
			<?if($total->no_series_dc == 'N'){?>
			<div class="item-info">
				<div class="t"><span class="dcore-discount-by-type-title-serial-stay">연박 할인</span></div>
				<div class="d">
					<small>(−)</small> <?=$currency?>
					<span class="dcore-cart-discount-serial-stay-value">
					<?
						echo number_format($total->discount_by_serial_stay, $currency_rtdp);
					?>
					</span>
				</div>
			</div>
			<?}?>
				<div class="item-info" id="item-member-dc">
                <div class="t">회원 할인</div>
                <div class="d">
                    <small>(−)</small> <?=$currency?>
                    <span class="dcore-cart-discount-member-dc">
                    <?if($this->auth->is_login()){?>
                        <?if($row_cart->product_module == 'package' || $row->dc_type =='none' || $row_cart->duplicate_dc == 'N' ){?>
                            <? echo number_format($row->discount_by_member,$currency_rtdp);?>
                        <?}else{?>
                            <? echo number_format(0,$currency_rtdp);?>
                        <?}?>
                    <?}else{?>
                        <? echo number_format(0,$currency_rtdp);?>
                    <?}?>
                    </span>
                </div>
            </div>

			<div class="item-info">
				<div class="t">프로모션 코드 할인</div>
				<div class="d">
					<small>(−)</small> <?=$currency?>
					<span class="dcore-cart-discount-value"><?=number_format($row->discount, $currency_rtdp)?></span>
				</div>
			</div>

		</div>
	</div>
</div>
<div class="dcore-checkout-cart-total">
	<div class="items">
		<div class="item">
			<div class="item-info strong-">
				<div class="t">결제금액</div>
				<div class="d">
					<?=$currency?>
					<span class="dcore-cart-amount-value"><?=number_format($model->scaff_mode == 'checkout' ? $total->price - $row->discount - $row->discount_by_type - $total->discount_by_serial_stay - $row->discount_by_member : $row->amount, $currency_rtdp)?></span>
				</div>
			</div>
		</div>
	</div>
</div>
<?}?>

<?if ($model->scaff_mode == 'checkout') {?>
    <div class="dcore-checkout-submit">
        <button class="button lg- block- primary-" id="checkout-submit" type="button">
            결제하기
        </button>
    </div>

    <script>
        $('#checkout-submit').click(function() {
            if(<?= $total->is_change_price ?> == 1){
                $(".shop_order_check").click();
            }else{
                $("#f-shop-checkout").submit();
            }
        });
    </script>
<?}?>