<html>
<head></head>
<body>
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<style type="text/css">
    html {
        overflow-x: hidden;
        overflow-y: scroll;
    }
    html,
    body {
        /*width:100%;height:100%;*/
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    body,
    div,
    p,
    dl,
    dt,
    dd,
    ul,
    ol,
    li,
    table,
    th,
    td,
    textarea,
    form,
    fieldset,
    legend,
    input,
    select,
    button {
        margin: 0;
        padding: 0;
    }
    ul,
    ol,
    li {
        list-style: none;
    }
    img,
    fieldset,
    iframe {
        border: 0 none;
    }
    input,
    select,
    button,
    textarea {
        vertical-align: middle;
    }
    button::-moz-focus-inner,
    input::-moz-focus-inner {
        border: 0;
        padding: 0;
    }
    textarea {
        overflow: auto;
    }
    img {
        max-width: 100%; /*width:100%;*/
        height: auto;
        vertical-align: top;
        -ms-interpolation-mode: bicubic;
    }
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        margin: 0;
        padding: 0;
        font-size: 1em;
        font-weight: normal;
    }
    address,
    em,
    strong,
    th {
        font-style: normal;
        font-weight: normal;
    }
    table {
        border-spacing: 0;
        border-collapse: collapse;
    }
    img[usemap],
    map area {
        outline: 0;
    }
    sub,
    sup {
        font-size: 75%;
        line-height: 0;
        position: relative;
        vertical-align: baseline;
    }
    sup {
        top: -0.5em;
    }
    sub {
        bottom: -0.25em;
    }
    caption,
    legend,
    hr,
    .hid {
        position: absolute;
        left: -9999px;
        width: 0;
        height: 0;
        font-size: 0;
        overflow: hidden;
    }

    button,
    input[type="button"],
    input[type="reset"],
    input[type="submit"] {
        -webkit-appearance: button;
        cursor: pointer;
    }
    button[disabled],
    input[disabled] {
        cursor: default;
    }
    button {
        -webkit-appearance: none;
        -moz-appearance: none;
        background: transparent;
        padding: 0;
        border: 0;
        line-height: 1;
    }
    .button {
        display: inline-block;
        text-align: center;
        line-height: 1;
        cursor: pointer;
        vertical-align: middle;
        border: 1px solid transparent;
        border-radius: 3px;
        background-color: #2ba6cb;
        color: #fff;
        -webkit-appearance: none;
        transition: opacity 0.25s ease-out, background-color 0.25s ease-out,
        color 0.25s ease-out;
    }
    button:focus {
        outline: 0;
    }
    .button:after {
        transition: opacity 0.25s ease-out, background-color 0.25s ease-out,
        color 0.25s ease-out;
    }
    .button span {
        position: relative;
        z-index: 1;
    }

    [type="text"],
    [type="password"],
    [type="date"],
    [type="datetime"],
    [type="datetime-local"],
    [type="month"],
    [type="week"],
    [type="email"],
    [type="number"],
    [type="search"],
    [type="tel"],
    [type="time"],
    [type="url"],
    [type="color"],
    textarea {
        box-sizing: border-box;
        width: 100%;
        height: auto;
        padding: 4px 4px;
        background-color: #fefefe;
        font-size: 0.9em;
        border: 1px solid #d0d0d0; /*box-shadow:inset 0 1px 2px rgba(10, 10, 10, 0.1);*/
        -webkit-appearance: none;
        -moz-appearance: none;
        transition: box-shadow 0.5s, border-color 0.25s ease-in-out;
    }
    [type="text"]:focus,
    [type="password"]:focus,
    [type="date"]:focus,
    [type="datetime"]:focus,
    [type="datetime-local"]:focus,
    [type="month"]:focus,
    [type="week"]:focus,
    [type="email"]:focus,
    [type="number"]:focus,
    [type="search"]:focus,
    [type="tel"]:focus,
    [type="time"]:focus,
    [type="url"]:focus,
    [type="color"]:focus,
    textarea:focus {
        border: 1px solid #2ba6cb;
        background-color: #fefefe;
        outline: none; /*box-shadow:0 0 5px #d0d0d0;*/
        transition: box-shadow 0.5s, border-color 0.25s ease-in-out;
    }
    [type="file"],
    [type="checkbox"],
    [type="radio"] {
        padding: 0;
        margin: 0;
        vertical-align: middle;
    }
    [type="checkbox"] + label,
    [type="radio"] + label {
        display: inline-block;
        vertical-align: middle;
        margin: 0 15px 0 3px;
    }
    label [type="checkbox"] + span,
    label [type="radio"] + span {
        display: inline-block;
        padding: 4px 0;
        vertical-align: middle;
        margin: 0 15px 0 3px;
    }
    label span + [type="checkbox"],
    label span + [type="radio"] {
        display: inline-block;
        padding: 4px 0;
        vertical-align: middle;
        margin: 0 15px 0 3px;
    }
    label [type="checkbox"],
    label [type="radio"],
    label span {
        vertical-align: middle;
    }
    select {
        box-sizing: border-box;
        width: 100%;
        height: auto;
        padding: 3px 4px;
        background-color: #fefefe;
        font-size: 0.9em;
        border: 1px solid #cacaca;
        box-shadow: inset 0 1px 2px rgba(10, 10, 10, 0.1);
        transition: box-shadow 0.5s, border-color 0.25s ease-in-out;
    }
    select:focus {
        border: 1px solid #2ba6cb;
        background-color: #fefefe;
        outline: none;
        box-shadow: 0 0 5px #cacaca;
        transition: box-shadow 0.5s, border-color 0.25s ease-in-out;
    }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        height: auto;
    }

    input::-webkit-input-placeholder,
    textarea::-webkit-input-placeholder {
        color: #aaa;
    }
    input:-moz-placeholder,
    textarea:-moz-placeholder {
        color: #aaa;
    }
    input::-moz-placeholder,
    textarea::-moz-placeholder {
        color: #aaa;
    }
    input:-ms-input-placeholder,
    textarea:-ms-input-placeholder {
        color: #aaa;
    }
    input:placeholder-shown,
    textarea:placeholder-shown {
        color: #aaa;
    }

    /*input:focus::-webkit-input-placeholder, textarea:focus::-webkit-input-placeholder {color:transparent;}
      input:focus:-moz-placeholder, textarea:focus:-moz-placeholder {color:transparent;}
      input:focus::-moz-placeholder, textarea:focus::-moz-placeholder {color:transparent;}
      input:focus:-ms-input-placeholder, textarea:focus:-ms-input-placeholder {color:transparent;}
      input:focus:placeholder-shown, textarea:focus:placeholder-shown {color:transparent;}*/

    .clearfix {
        zoom: 1;
    }
    .clearfix:before,
    .clearfix:after {
        content: "";
        display: table;
    }
    .clearfix:after {
        clear: both;
    }

    .fleft {
        float: left;
    }
    .fright {
        float: right;
    }

    .tac {
        text-align: center;
    }
    .dib {
        display: inline-block;
        zoom: 1;
        *display: inline;
        vertical-align: middle;
    }
    .mb0 {
        margin-bottom: 0 !important;
    }

    body,
    input,
    select,
    textarea,
    button {
        font-family: "Noto Sans KR", "Nanum Barun Gothic", "Malgun Gothic",
        Helvetica, "Apple SD Gothic Neo", Dotum, sans-serif;
        font-size: 14px;
        color: #818181;
        letter-spacing: -0.05em;
        line-height: 26px;
    }
    a {
        text-decoration: none;
        color: #898989;
        background-color: transparent;
    }
    a:hover,
    a:focus {
        color: #003745;
        outline: 0;
    }

    html,
    body,
    .wrap,
    .container,
    .form,
    fieldset {
        height: 80%;
    }
    body {
        background-color: #464646;
    }
    .wrap {
        width: 100%;
    }
    .container {
        max-width: 724px;
        margin: 0 auto;
        color: #00ff21;
        padding: 0 10px;
    }
    .title_promotion {
        padding-top: 90px;
        margin-bottom: 80px;
        text-align: center;
        color:#fff;
    }
    .title_promotion .tit {
        font-size: 26px;
        font-weight: bold;
        line-height:1.5;
        letter-spacing: -1.31px;
    }
    .title_promotion .dsc {
        margin:9px 0 0 0;
        font-size: 14px;
        font-weight: normal;
        line-height: 1.71;
        letter-spacing: -0.94px;
    }

    .btn_wrap {
        margin-top: 30px;
    }
    .btn_wrap .buttons {
        display: block;
        width: 100%;
        max-width: 338px;
        margin: 0 auto;
        height: 60px;
        line-height: 60px;
        font-size: 14px;
        font-weight:normal;
        background-color: #1b1b1b;
        color: #fff;
        border: none;
        font-weight: 600;
    }
    .buttons.is-gray {
        width: 120px;
        height: 40px;
        line-height: 40px;
        color:#fff;
        font-size:14px;
        font-weight: bold;
        letter-spacing: -0.94px;
        background-color: #3c3c3c;
    }

    .mark_ok {
        position: absolute;
        right: 10px;
        top: 14px;
        display: block;
        width: 20px;
        height: 22px;
        display: none;
        background-color: transparent;
    }
    .mark_ok:after {
        content: "";
        position: absolute;
        left: 7px;
        top: 2px;
        width: 5px;
        height: 11px;
        border: solid #0eb722;
        border-width: 0 2px 2px 0;
        -webkit-transform: rotate(45deg);
        -moz-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        -o-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    .mark_no {
        position: absolute;
        right: 10px;
        top: 14px;
        display: block;
        width: 20px;
        height: 22px;
        display: none;
        background-color: transparent;
    }
    .mark_no::after,
    .mark_no::before {
        position: absolute;
        top: 10px;
        left: 2px;
        content: "";
        display: block;
        width: 17px;
        height: 2px;
        background-color: #ed093a;
    }
    .mark_no::after {
        -webkit-transform: rotate(45deg);
        -moz-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        -o-transform: rotate(45deg);
        transform: rotate(45deg);
    }
    .mark_no::before {
        -webkit-transform: rotate(-45deg);
        -moz-transform: rotate(-45deg);
        -ms-transform: rotate(-45deg);
        -o-transform: rotate(-45deg);
        transform: rotate(-45deg);
    }

    .user_email .mark_ok,
    .user_email .mark_no {
        right: 155px;
    }


    .form-group {

    }
    .form-group ~ .form-group{
        margin: 50px 0 0 0;
        padding: 47px 0 0 0;
        border-top:1px solid #fff;
    }
    .form-group .form-title {
        font-size: 14px;
        font-weight: bold;
        line-height: 1.71;
        letter-spacing: -0.5px;
        color: #fff;
    }
    .form-group .form-text {
        font-size: 14px;
        line-height: 1.71;
        letter-spacing: -0.5px;
        color: #959595;
    }

    .input {
        max-width:337px;
        height:40px;
        line-height:40px;
        padding:0 16px;
        border:none;
        font-size:14px;
        letter-spacing: 0;
        background:#7e7e7e;
        color:#fff;
    }
    .input:hover,
    .input:focus {
        background:#7e7e7e;
    }
    .input-group {
        display:flex;
        justify-content: space-between;
        margin:43px 0 0 0;
    }
    .input-box {
        display:flex;
        align-items: center;
        margin:30px 0 0 0;
        padding:0 30px;
        color:#fff;
    }
    .input-box .buttons{
        margin: 0 15px;
    }

</style>

<div class="wrap">
    <div class="container">
        <div class="title_promotion">
            <p class="tit">???????????? ?????? DB ??????</p>
            <p class="dsc">
                ??? ?????? ??????????????? ????????? ?????? ???????????? CMS ?????? ??????, ?????? ??????
                ??????, ?????? ????????? ?????? ?????? ?????? ????????? ?????? ????????????. <br />
                ?????? ????????? ?????? ?????? ????????? ???????????? ????????????.
            </p>
        </div>
        <form name="form" method="post" action="/wings_force" id="form_wings_force" class="form" autocomplete="off">
            <fieldset>
                <div class="form-group">
                    <strong class="form-title">CMS ?????? ??????</strong>
                    <p class="form-text">????????? ??????????????? CMS?????? ????????? ????????? ?????? <br> ?????? ) ABCDE_12345-12312390 ??? ????????? ???????????? {?????? ????????????} - {CMS ?????? ????????? ????????????}</p>
                    <div class="input-group">
                        <input type="text" class="input" name="f_seg" id="f_seg" placeholder="???????????? ?????? ?????? ??????" value="">
                        <input type="text" class="input" name="s_seg" id="s_seg" placeholder="CMS ?????? ??????" value="">
                    </div>
                    <div class="btn_wrap">
                        <button type="button" class="buttons" id="btn_force">CMS ?????? ??????</button>
                    </div>
                </div>
            </fieldset>
        </form>
        <form name="form" method="post" id="form_user_delete" class="form" autocomplete="off">
            <fieldset>
                <div class="form-group">
                    <strong class="form-title">?????? ?????? ??????</strong>
                    <p class="form-text">?????? ???????????? ?????? ??? ?????? ????????? ????????? ?????????, ???????????? ????????? ????????? ?????? ?????? ?????? ?????? DB ?????? <br> ?????? ????????? ???????????? ?????????, ???????????? ?????? ?????? ?????????</p>
                    <div class="input-box">
                        <input type="text" class="input" name="email" id="email" placeholder="?????????">
                        <button type="button" class="buttons is-gray" id="user_check">??????</button>
                        <span class="vaildation" style="display: none" id="user_check_msg">????????? ?????? ?????? ???????????????.</span>
                        <span class="vaildation" style="display: none" id="user_check_msg_fail">????????? ?????? ??? ????????????.</span>
                    </div>
                    <div class="btn_wrap">
                        <button type="button" class="buttons" id="btn_user">?????? ?????? ??????</button>
                    </div>
                </div>
            </fieldset>
        </form>
        <form name="form" method="post" id="form_order_delete" class="form" autocomplete="off">
            <fieldset>
                <div class="form-group">
                    <strong class="form-title">?????? DB ?????? ??????</strong>
                    <p class="form-text">???????????? ?????? ??????????????? ??? ????????? ????????? ???????????? ?????? ??????????????? ?????? ????????? ?????? ?????? ??? ?????? ????????? ????????? ??? ?????? <br> ?????? ????????? ????????? ???????????? ?????? ??????</p>
                    <div class="input-box">
                        <input type="text" class="input" name="phone" id="phone" placeholder="???????????? ??????( ' - ' ?????? )">
                        <button type="button" class="buttons is-gray" id="order_check">??????</button>
                        <span class="vaildation" style="display: none" id="order_check_msg">????????? ?????? ?????? ???????????????</span>
                        <span class="vaildation" style="display: none" id="order_check_msg_fail">???????????? ?????? ??? ????????????.</span>
                    </div>
                    <div class="btn_wrap">
                        <button type="button" class="buttons" id="btn_order">DB ?????? ??????</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

<script language="javascript">
    $(document).ready(function () {
        //CMS ????????????
        $("#btn_force").click(function () {
            if($('#f_seg').val() == null || $('#f_seg').val() == ''){
                alert("?????? ?????? ????????? ??????????????????.");
                $('#f_seg').focus();
                return false;
            }

            if($('#s_seg').val() == null || $('#s_seg').val() == ''){
                alert("CMS ?????? ????????? ??????????????????.");
                $('#s_seg').focus();
                return false;
            }

            if (confirm("WING CMS, PMS ??? ???????????? ?????????. ?????? ?????????????????????????")) {
                $.ajax({
                    type: "POST",
                    url: "/wings_force/cms_cancle",
                    data: $("#form_wings_force").serialize(),
                    dataType: "json",
                    success: function (data) {
                        alert(data.MSG);
                    },
                });
            }
            location.reload();
        });

        //????????????
        $('#user_check').click(function () {
            var email = $('#email').val();
            if(email == null || email == ''){
                alert("???????????? ??????????????????.");
                $('#email').focus();
                return false;
            }
            $.ajax({
                type: "POST",
                url: "/user/user_check",
                data: $('#form_user_delete').serialize(),
                dataType: "json",
                success: function (data) {
                    //console.log(data.cnt);
                    if(data.cnt > 0){
                        $('#user_check_msg').show();
                        $('#user_check_msg_fail').hide();
                    }else{
                        $('#user_check_msg_fail').show();
                        $('#user_check_msg').hide();
                    }

                }
            });
        });

        //????????????
        $('#btn_user').click(function () {
            if($('#email').val() == null || $('#email').val() == ''){
                alert("???????????? ??????????????????.");
                $('#email').focus();
                return false;
            }

            if(!$('#user_check_msg').is(':visible')){
                alert("????????? ?????? ????????????.");
                return false;
            }

            if (confirm("??????????????? ???????????????. ?????? ?????????????????????????")) {
                $.ajax({
                    type: "POST",
                    url: "/user/user_delete",
                    data: $('#form_user_delete').serialize(),
                    dataType: "json",
                    success: function (data) {
                        alert(data.msg);
                        location.reload();
                    }
                });
            }
        });

        //????????????
        $('#order_check').click(function () {
            var phone = $('#phone').val();
            if(phone == null || phone == ''){
                alert("????????????????????? ??????????????????.");
                $('#phone').focus();
                return false;
            }
            $.ajax({
                type: "POST",
                url: "/user/order_check",
                data: $('#form_order_delete').serialize(),
                dataType: "json",
                success: function (data) {
                    //console.log(data.cnt);
                    if(data.cnt > 0){
                        $('#order_check_msg').show();
                        $('#order_check_msg_fail').hide();
                    }else{
                        $('#order_check_msg_fail').show();
                        $('#order_check_msg').hide();
                    }

                }
            });
        });

        //????????????
        $('#btn_order').click(function () {
            if($('#phone').val() == null || $('#phone').val() == ''){
                alert("???????????? ????????? ??????????????????.");
                $('#phone').focus();
                return false;
            }

            if(!$('#order_check_msg').is(':visible')){
                alert("????????? ?????? ????????????.");
                return false;
            }

            if (confirm("??????????????? ???????????????. ?????? ?????????????????????????")) {
                $.ajax({
                    type: "POST",
                    url: "/user/order_delete",
                    data: $('#form_order_delete').serialize(),
                    dataType: "json",
                    success: function (data) {
                        alert(data.msg);
                        location.reload();
                    }
                });
            }

        });



    });
</script>
</body>
</html>
