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
    <link rel="stylesheet" href="/css/vip.css?v=1">
</head>

<body>
<div class="page-group">
    <!--vip-->
    <div class="page " id="vip">

        <!--列表-->
        <section class="">
            <!--<p>兑换</p>-->
            <div class="content infinite-scroll native-scroll vip-conent" data-distance="100">
                <ul class="vip-list list-container">
                    @foreach($products as $product)
                        <li class="@if($user->level >= $product->value)geted @endif" data-product="{{ $product->id }}">
                            @if($user->level >= $product->value)
                                <img src="/images/level/{{ $product->value }}-geted.png" alt="">
                            @else
                                <img src="/images/level/{{ $product->value }}.png" data-geted="/images/level/{{ $product->value }}-geted.png" alt="">
                            @endif
                            <div class="right">
                                <span class="js-level-name">{{ $product->name }}</span>
                                <span>{{ $levels[$product->name] }}</span>
                                <span><i class="icon-qian"></i>{{ $product->price }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <!--自定义弹窗-->
        <section class="vip-tips hide" id="js-tips-vip">
            <p class="close-p">
                <a href="javascript:;" class="icon-close js-close"></a>
            </p>
            <p class="msg-ok"><i class="icon-xiao"></i>恭喜你已成功获得<i class="js-level"></i>权限</p>
        </section>
        <!--自定义弹窗-->
        <section class="vip-tips hide" id="js-tips-error-vip">
            <p class="close-p">
                <a href="javascript:;" class="icon-close js-close"></a>
            </p>
            <div class="msg-error">
                <i class="icon-ku"></i>
                <div>
                    <strong>很抱歉 你没有购买<i class="js-level"></i>权限的资格</strong>
                    <span>你必须获得上一级别的权限才能继续购买。</span>
                </div>
            </div>
        </section>
    </div>

</div>

<script type='text/javascript' src='/js/zepto.min.js' charset='utf-8'></script>
<script type='text/javascript' src='/js/sm.min.js' charset='utf-8'></script>
<script>
    var token = '{{ session('token') }}';
    var nowLevel = '';
    var targetImg = '';
    function wxPay(jsStr) {
        console.log(jsStr);
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', jsStr,
            function(res){
                if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                    //成功
                    $('#js-tips-vip').removeClass('hide').find('.js-level').html(nowLevel);
                    targetImg.attr('src',targetImg.data('geted'));
                    targetImg.parents('li').addClass('geted')
                } else {
                    $.alert('购买失败，请重新发起！');
                }
            }
        );

    }

    // vip list
    var $vip =$('#vip');
    $vip.on('click','.js-close',function(e){
        e.preventDefault();
        e.stopPropagation();
        $vip.find('.vip-tips').addClass('hide');
    })
    //
    $vip.on('click','li',function(e){
        e.preventDefault();
        e.stopPropagation();
        var $this =$(this);
        $('.vip-tips').addClass('hide');
        if($this.hasClass('geted')){
            $.toast('您已购买，请勿重新购买~',2000,"success top")
            return;
        }
        if($this.hasClass('disabled') ){
            return;
        }
        targetImg = $this.find('img');
        nowLevel = $this.find('.js-level-name').html();
        // 如果不是第一级 切
        if($this.prev().length==0 || ($this.prev().length && $this.prev().hasClass('geted') )){
            $.showIndicator();
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
        }else{
            $('#js-tips-error-vip').removeClass('hide').find('.js-level').html(nowLevel);
        }
    })
</script>
</body>

</html>