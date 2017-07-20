<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>会员中心</title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <link rel="shortcut icon" href="/favicon.ico">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/css/sm.min.css">
    <link rel="stylesheet" href="/css/account.css?v=1">
</head>

<body>
<div class="page-group">
    <!--我的账号-->
    <div class="page " id="account">
        <!--头部-->
        <header class="account-header ">
            <h3 id="js-now-total">{{ $user->count->ZS }}</h3>
            <p>我的余额</p>
        </header>
        <!--列表-->
        <section class="content">
            <!--<p>兑换</p>-->
            <div class="content infinite-scroll native-scroll account-conent" data-distance="100">
                <ul class="account-list account-container">
                    @foreach($products as $product)
                        <li>
                            <span class="icon-zhuansi"></span>
                            <span  data-value="{{ $product->value }}">{{ $product->value }}</span>
                            <a href="javascript:;" class="js-exchange" data-product="{{ $product->id }}">￥<span>{{ $product->price }}</span></a>
                        </li>
                    @endforeach
                </ul>

            </div>
        </section>
        <div class="account-bottom"><a href="#">充值问题，点此联系客服</a></div>

        <!--自定义弹窗-->
        <section class="account-tips hide" id="js-tips">
            <p class="close-p">
                <a href="javascript:;" class="icon-close js-close"></a>
            </p>
            <p class="msg-ok"><i class="icon-xiao"></i>充值成功,您的账户余额为<span class="js-total"></span>！</p>
        </section>
    </div>
</div>

<script type='text/javascript' src='/js/zepto.min.js' charset='utf-8'></script>
<script type='text/javascript' src='/js/sm.min.js' charset='utf-8'></script>

<script>
var token = '{{ session('token') }}';
var nowCharge =0;

$('.js-exchange').on('click',function() {
    $.showIndicator();
    nowCharge = parseInt($(this).prev().html());
    $('#js-tips').addClass('hide');
    $.ajax({
        url: '/api/wechat/pay',
        type: 'POST',
        dataType: 'json',
        headers: {'Authorization': 'Bearer ' + token},
        data: {'product': $(this).data('product')},
        success: function (data) {
            $.hideIndicator();
            if(data.code == 1000){
                wxPay(JSON.parse(data.data));
            } else {
                $.alert(data.msg);
            }
        }
    })
});

function wxPay(jsStr) {
    console.log(jsStr);
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest', jsStr,
        function(res){
//            alert(res.err_msg);
            if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                //成功
                var _total = nowCharge+parseInt($("#js-now-total").html());
                $('#js-tips').removeClass('hide').find('.js-total').html(_total);
                $("#js-now-total").html(_total);
            } else {
                $.alert('充值失败，请重新发起！');
            }
        }
    );

}


</script>
</body>

</html>