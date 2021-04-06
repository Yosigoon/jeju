<?if (!$model->scaff_admin && $row) {?>
<script>
    $(function () {
        $('.shop_order_check').click(function(e) {
            e.preventDefault();

            $.dmodal({
                htmlClone: false,
                callback: function (c) {
                    var div = $('<div />').addClass('').appendTo(c);

                    var form = $('<form />').attr({
                        id: '_price_change_form',
                        action: '/user/update_change_price',
                        method: 'post'
                    }).appendTo(div);
                    var field;
                    field = $('<div />').addClass('dmodal-content alert- lg-').appendTo(form);
                    $('<h1 />').text('객실변동 안내').appendTo(field);
                    $('<label />').text('선택하신 객실은 요금 변동 또는 예약 마감으로 구매하실 수 없습니다.').appendTo(field);
                    $('<br />').appendTo(field);
                    $('<label />').text('예약함 갱신을 눌러 진행해 주세요.').appendTo(field);
                    $('<br />').appendTo(field);
                    $('<label />').text('※ 예약 마감된 해당 상품은 예약함에서 자동 삭제됩니다.').appendTo(field);
                    $('<br />').appendTo(field);
                    $('<label />').text('자세한 사항은 고객센터 (064-766-3000)으로 문의주세요.').appendTo(field);
                    var buttons = $('<div />').addClass('buttons').appendTo(field);
                    $('<button />').addClass('button lg- primary-').text('예약함 갱신').attr({id: 'shop_order_refresh'}).appendTo(buttons);

                    $('<input type="hidden" />').attr({ id: 'cart_uids', name: 'cart_uids' }
                    ).val( <?= json_encode($total->cart_uids) ?> ).appendTo(field);
                    return div;
                }
            });

            $('#shop_order_refresh').click(function() {
                if(<?= $total->is_change_price ?> == 1){
                    $("#_price_change_form").submit();
                    <?= $total->is_change_price = 0 ?>
                }
            });
        });


    });

</script>
<?}?>
