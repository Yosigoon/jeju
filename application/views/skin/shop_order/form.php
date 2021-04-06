<?= css('/css/keyboard.css') ?>
<?= js('/js/vkeyboard/keyboard.js') ?>
<?= js('/js/vkeyboard/hangul.js') ?>
<form action="/user/checkout" method="post" id="f-shop-checkout" data-complete="/user/checkout/complete-by-user">
  <input type="hidden" name="billing_name" value="<?= $row && $row->billing_name ? $row->billing_name : ($this->auth->is_login() ? $this->auth->userinfo->name : '') ?>" />
  <input type="hidden" name="billing_phone" value="<?= $row && $row->billing_phone ? $row->billing_phone : ($this->auth->is_login() ? $this->auth->userinfo->phone : '') ?>" />
  <input type="hidden" name="email" value="<?= $row && $row->email ? $row->email : ($this->auth->is_login() ? $this->auth->userinfo->email : '') ?>" />
  <div class="container">
    <div class="user-space-margin-top"></div>
    <div class="page-title">
      <div class="en-ko-lg title-">예약상품 결제</div>
    </div>
    <div class="dcore-cart" data-deletable="false">
      <div class="dcore-cart-list"></div>
    </div>
    <hr class="transparent" />
    <div class="relative">
      <div class="dcore-order">
        <div class="dcore-order-left">
          <input type="hidden" name="merchant_uid" value="" />
          <div class="form-sub-header">
            <div class="title-">결제정보</div>
            <button type="button" class="button bordered- shop_order_check pull-right" style="display: none">모달</button>
          </div>
          <div class="if inline- has-border-">
            <label>구매자 정보</label>
            <div class="data">
              <div class="name"><?= $this->auth->userinfo->name ?><a href="/user/info" class="button bordered-">정보변경</a></div>
              <div class="phone"><?= $this->auth->userinfo->phone ?></div>
              <div class="email"><?= $this->auth->userinfo->email ?></div>
            </div>
          </div>
          <div class="if inline- has-border-">
            <label>필수 조건</label>
            <div class="data">
              미성년자는 참여가 불가합니다.
              <span class="help">(미성년자: 만 18세 이하)</span>
            </div>
          </div>
          <div class="if inline- has-border-">
            <label for="f-memo">요청사항</label>
            <input type="text" name="memo" id="f-memo" class="dform" data-dform-required="false" data-dform-lang="<?= $_lang ?>" />
          </div>
          <div class="if inline- has-border-">
            <label>할인 선택</label>
            <div class="data limited-width-">
              <div id="dc-types" data-apply-url="/user/checkout/dc">
                <? foreach ($model->available_dc_types as $k => $v) { ?>
                  <div>
                    <input type="radio" name="dc_type" id="f-dc_type_<?= $k ?>" value="<?= $k ?>" <?= $row && $row->dc_type == $k ? ' checked' : ($k == 'none' ? ' checked' : '') ?> />
                    <label for="f-dc_type_<?= $k ?>"><?= $v ?></label>
                  </div>
                <? } ?>
              </div>
            </div>
          </div>
          <div class="if inline- has-border-">
            <label>프로모션 코드</label>
            <div class="data limited-width- no-padding-">


              <!-- 원본소스 select : s -->
              <!-- <select id="f-coupon_code" name="coupon_code" class="select2"
							placeholder="코드 입력"
							data-place-holder="코드를 입력해주세요."
							data-apply-url="/user/coupons/apply"
							data-cancel-url="/user/coupons/cancel"
							data-ajax-url="/user/coupons/json"
							data-tags="false"
							data-search="true"
							data-id="row.code"
							data-text="row.title + ' (' + row.discount + ' <?= $this->lang->line('coupon_discount') ?>)'"
						>
						<? if (!empty($row_coupon)) { ?>
							<option value="<?= $row_coupon->code ?>" selected><?= $row_coupon->title ?> (<?= $row_coupon->discount ?> <?= $this->lang->line('coupon_discount') ?>)</option>
						<? }
            ?>
						</select> -->
              <!-- 원본소스 select : e -->

              <!-- 대체소스 : s -->
                <?
                $data_dc = '';
                foreach ($rows_cart as $row_cart) {
                    if ($row_cart->member_dc_rate > 0 && $row_cart->duplicate_dc != 'N') {
                        $data_dc = 'dis_by_mem_only';
                    }
                }
                ?>
              <div class="dcore-checkout-coupon">
                <!-- <div>코드입력</div> -->
                <div id="keyboard_field_wrap">
                  <!-- <input type="text" name="keyboardInput" id="keyboardInput" class="keyboardInput" placeholder="코드를 입력해주세요"> -->
                  <div name="keyboarddiv" id="keyboarddiv" class="keyboarddiv" data-dc="<?=$data_dc?>">코드를 입력해주세요.</div>
                  <div class="keyboardvalue" id="keyboardvalue">결과가 없습니다.</div>
                </div>
                <div>
                  <!-- <? if (!empty($row_coupon)) { ?>
									<input type="text" name="coupon_code" id="coupon_code" class="" value="<?= $row_coupon->code ?>">
									<label for=""><?= $row_coupon->title ?> (<?= $row_coupon->discount ?> <?= $this->lang->line('coupon_discount') ?>)</label> -->
                  <!-- <? } ?>  -->
                </div>
                <button type="button" class="button bordered- cancel hide" id='btn_cancel'><?= $this->lang->line('btn_cancel') ?></button>
              </div>
              <!-- 대체소스 : e -->


            </div>
          </div>
          <div class="if inline- has-border-">
            <label>결제방법</label>
            <div class="data">
              <? foreach ($model->available_payment_methods as $k => $v) { ?>
                <div>
                  <input type="radio" name="payment_method" id="f-payment_method_<?= $k ?>" value="<?= $k ?>" <?= $row && $row->payment_method == $k ? ' checked' : ($k == 'card' ? ' checked' : '') ?> />
                  <label for="f-payment_method_<?= $k ?>"><?= $v ?></label>
                </div>
              <? } ?>
            </div>
          </div>
          <div class="if inline- has-border-">
            <label>약관 동의 및 <br />환불 정책</label>
            <div class="data limited-width-">
              <? include APPPATH . 'views/skin/shop_order/form/agree.php'; ?>
            </div>
          </div>
        </div>
        <div class="dcore-order-right dsticky">
          <div class="form-sub-header">
            <div class="title-">&nbsp;</div>
          </div>
          <? include APPPATH . 'views/skin/shop_order/form/submit.php'; ?>
        </div>
      </div>
    </div>
  </div>
</form>

<script type="text/javascript">
  $(function() {
    // var input = $('#keyboardInput');
    var keydiv = $('#keyboarddiv');
    var discount = <?= $row->discount ?>;
    var discountByType = <?= $total->discount_by_type ?>;
    var totalPrice = <?= $total->price ?>;
    var totalAmount = <?= $total->price - $row->discount - $total->discount_by_type - $total->discount_by_serial_stay - $total->discount_by_member ?>;
    var dis_by_serial_stay = <?= $total->discount_by_serial_stay ?>;
    var dis_by_mem = <?= $total->discount_by_member ?>;
    var is_change_price = <?= $total->is_change_price ?>;
    var daily_price_sum = <?= $total->daily_price_sum ?>;
    var cart_uids = <?= json_encode($total->cart_uids) ?>;
    var dup_dc = '<?= $row_cart->duplicate_dc ?>';


    check_in = '<?= $row_cart->check_in ?>';
    check_out = '<?= $row_cart->check_out ?>';
    product_module = '<?= $row_cart->product_module ?>';
    time_schedule = '<?= $row_cart->time_schedule ?>';
    input_value('');

    // if (dis_by_mem > 0 && dup_dc != 'N') {
    //   keydiv.attr('data-dc','dis_by_mem_only');
    // }

    function updateTotalAmount() {
      totalAmount = totalPrice - discount - discountByType - dis_by_serial_stay - dis_by_mem;
      if (totalAmount < 0)
        totalAmount = 0;
      var dcType = $('#dc-types input:checked').val();
      var dcTypeTitle = '';
      switch (dcType) {
        case 'local':
          dcTypeTitle = '제주도민 할인';
          break;
        case 'guest':
          dcTypeTitle = '투숙객 할인';
          break;
        default:
          dcTypeTitle = '할인';
          break;
      }
      $('.dcore-discount-by-type-title').text(dcTypeTitle);
      $('.dcore-cart-discount-value').text(discount).dformCurrency();
      $('.dcore-cart-discount-by-type-value').text(discountByType).dformCurrency();
      $('.dcore-cart-discount-serial-stay-value').text(dis_by_serial_stay).dformCurrency();
      $('.dcore-cart-discount-member-dc').text(dis_by_mem).dformCurrency();
      $('.dcore-cart-amount-value').text(totalAmount).dformCurrency();
    }

    function updateCartItems() {
      $.get('/user/checkout', {
        t: moment().format('X')
      }, function(data) {
        var tmp = $('<div />').html(data);
        var cartItems = tmp.find('.dcore-checkout-cart-items');
        if (cartItems.length > 0) {
          $('.dcore-checkout-cart-items').html(cartItems.html());
        }
      }, 'text');
    }

    function input_value(value) {
      if (value) {
        var url = '/user/coupons/apply';
        $.post(url, {
          code: value
        }, function(data) {
          if (data.error) {
            $.dalert(data.error);
            return;
          }
          // console.log(data);
          updateTotal(data);
          updateCartItems();
        }, 'json');
      } else {
        var url = '/user/coupons/cancel';
        $.post(url, function(data) {
          // console.log(data);
          keydiv.html('코드를 입력해주세요.');
          updateTotal(data);
          updateCartItems();
        }, 'json');
      }
      $(document).on('focus', '.select2-selection.select2-selection--single', function(e) {
        // $(this).closest('.select2-container').siblings('select:enabled').select2('open');
        // $('.select2-search__field').focus();
        // VKI_buildKeyboardInputs();
        $('.select2-search__field').trigger('click');
      });

      function updateTotal(data) {

        if (data.discount) {
          $('.dcore-cart-discount-value').text(data.discount);
        }
        if (data.discount) {
          discount = parseFloat(data.discount.replace(/,/g, ''));
          updateTotalAmount();
        }
      }
    }

    $('form#f-shop-checkout').each(function() {
      var form = $(this);
      var submit = form.find('button[type="submit"]');
      <? if (in_array('vbank', array_keys($model->available_payment_methods))) { ?>
        var vbankRefund = $('.dcore-checkout-vbank-refund');
        var paymentMethods = form.find('[name="payment_method"]');

        function showHideVbankRefund() {
          if (paymentMethods.filter('[value="vbank"]').is(':checked')) {
            vbankRefund.removeClass('hide');
          } else {
            vbankRefund.addClass('hide');
            vbankRefund.find('.dform-error').each(function() {
              var input = $(this);
              input.val('').change().removeClass('valid');
              $.each(input.data('dform-error-keyarray'), function(k, v) {
                input.dformError(k, '');
              });
            });
          }
        }
        showHideVbankRefund();
        paymentMethods.change(showHideVbankRefund);
      <? } ?>

      <? if (!$this->auth->is_login()) { ?>
        var passwd = form.find('[name="passwd"]').dform({
          change: isPasswordMatch
        });

        var passwd_confirm = form.find('[name="passwd_confirm"]').dform({
          change: isPasswordMatch
        });

        function isPasswordMatch(e, cb) {
          if (passwd.val() != passwd_confirm.val()) {
            passwd_confirm.dformError('confirm', '<?= $this->lang->line('auth_check_your_password') ?>');
          } else {
            passwd_confirm.dformError('confirm');
            cb();
          }
        }
      <? } ?>

      form.find('#dc-types').each(function() {
        var me = $(this);
        var inputs = me.find('[name="dc_type"]');
        inputs.change(function() {
          $.post(me.attr('data-apply-url'), form.serialize(), function(data) {
            if (data.error) {
              $.dalert(data.error);
              return;
            }
            if (data && data.total) {
              discountByType = data.total.discount_by_type;
              dis_by_mem = data.total.discount_by_member;
              updateTotalAmount();
              updateCartItems();
            }
          }, 'json');
        });
      });

      $("#btn_cancel").on("click", function() {
        var button = $('.cancel');
        //var input = $('#keyboardInput');
        var div = $('#keyboardvalue');

        //input.val('');
        keydiv.html('');
        div.removeClass("active");
        div.html('결과가 없습니다.');
        //var option = select.finddiv('option:selected');
        button.addClass('hide');
        input_value(keydiv.html());
      });

      $(".keyboardvalue").on("click", function() {
        var button = $('.cancel');
        var div = $('.keyboardvalue');
        $('#keyboardInputClose').click();
        text_arr = new Array();
        //var option = select.finddiv('option:selected');
        if (div.html() != '결과가 없습니다.') {
          if (keydiv.html()) {
            div.removeClass("active");
            div.hide();
            button.removeClass('hide');
            input_value(keydiv.html());
            keydiv.html(div.html());
          }
        }
      });

      form.dform({
        submit: function() {
          if (!$('#f-agree').is(':checked')) {
            $.dtoast('<?= $this->lang->line('auth_you_must_agree_to_the_terms') ?>');
            return false;
          }
          //var merchant_uid = '<?= $row->uid ?>_' + new Date().getTime();
          var merchant_uid = '<?= $row->order_id ?>';
          submit.prop('disabled', true);
          form.find('[name="merchant_uid"]').val(merchant_uid);
          $.post(form.attr('action'), form.serialize(), function(data) {
            submit.prop('disabled', false);
            if (data.error) {
              $.dtoast('<span class="fa fa-frown-o"></span>&nbsp;&nbsp;' + data.error);
            } else if (data.message) {
              $.dtoast('<span class="fa fa-smile-o"></span>&nbsp;&nbsp;' + data.message);
            } else if (data.redirect) {
              if (totalAmount == 0) {
                $.post(form.attr('data-complete'), {
                  referer: 'dcore',
                  imp_uid: '__FREE__',
                  merchant_uid: merchant_uid
                }, function(data) {
                  if (data && data.error) {
                    $.dalert(data.error);
                    return;
                  }
                  submit.prop('disabled', false);
                  $(document).trigger('hide-loading');
                  $('.dcore-cart-count-outer').trigger('update-cart');
                  $.dcoreSubmitResult(data);
                }, 'json');
                return;
              }
              IMP.init('<?= $this->config->item('imp_user_code') ?>');
              totalAmount = Math.round(totalAmount);
              IMP.request_pay({
                pg: '<?= $this->config->item('imp_pg_provider') ?>',
                pay_method: form.find('[name="payment_method"]:checked').val(),
                merchant_uid: merchant_uid,
                language: '<?= $_lang == 'korean' ? 'ko' : 'en' ?>',
                m_redirect_url: '<?= http_host() ?>' + form.attr('data-complete'),
                name: '<?
                        $arr = array();
                        foreach ($rows_cart as $row_cart) {
                          $arr[] = preg_replace('/\'/', '\\\'', $row_cart->product_title);
                        }
                        echo strcut(implode(', ', $arr), 12, '...');
                        ?>',
                amount: totalAmount,
                buyer_email: form.find('[name="email"]').val(),
                buyer_name: form.find('[name="billing_name"]').val(),
                buyer_tel: form.find('[name="billing_phone"]').val()
              }, function(rsp) {
                if (rsp.success) {
                  function complete() {
                    $.ajax({
                      url: form.attr('data-complete'),
                      type: 'POST',
                      timeout: 20000,
                      dataType: 'json',
                      data: {
                        referer: 'dcore',
                        imp_uid: rsp.imp_uid,
                        merchant_uid: rsp.merchant_uid
                      },
                      success: function(data, status, xhr) {
                        submit.prop('disabled', false);
                        $(document).trigger('hide-loading');
                        if (data && data.error) {
                          var data_split = data.error.split('@@');
                          if (data_split.length > 1) {
                            if (data_split[1] == 'MM') {
                              $.dalert(data_split[0]);
                              setTimeout(function() {
                                location.href = "/user/cart";
                              }, 3000);
                              return;
                            } else {
                              if (data && data_split[0]) {
                                $.dalert(data_split[0]);
                                return;
                              }
                            }
                          } else {
                            if (data && data.error) {
                              $.dalert(data.error);
                              return;
                            }
                          }
                        }

                        $('.dcore-cart-count-outer').trigger('update-cart');
                        $.dcoreSubmitResult(data);
                      },
                      error: function(xhr, status, error) {
                        console.log(xhr);
                        submit.prop('disabled', true);
                        $.dtoast('결제는 완료되었으나 서버 부하량이 많아 처리를 완료하지 못했습니다.<br />재시도 중이오니 창을 닫지 마시고 기다려 주세요.');
                        setTimeout(function() {
                          complete();
                        }, 3000);
                      }
                    });
                  }
                  submit.prop('disabled', true);
                  $(document).trigger('show-loading');
                  complete();
                } else {
                  $.dtoast(rsp.error_msg);
                }
              });
            }
          }, 'json');
          return false;
        }
      });
    });
  });

</script>

<? include APPPATH . 'views/skin/shop_cart/list/js.php'; ?>
<? include APPPATH.'views/skin/shop_order/form/shop_order_check.php'; ?>

<!-- Sojern Container Tag -->
<script>
  params.pt = "SHOPPING_CART",
    params.hpid = "playce",
    params.hd1 = "",
    params.hd2 = "",
    params.hcu = "",
    params.hp = ""
</script>
<!-- End Sojern Tag -->