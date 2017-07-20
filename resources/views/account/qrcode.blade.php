<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>二维码</title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <link rel="shortcut icon" href="/favicon.ico">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <script src="/js/flexible.js"></script>
    <style>
        *{padding: 0;margin: 0;}
        .icon-success{padding:1.05rem;text-align: center}
        img{width: 1.76rem;}
        p{text-align: center;font-size: .36rem;color:#383a47;}
    </style>
</head>

<body>
<p class="icon-success">
    <img src="/images/success.png" alt="">
</p>
<p>二维码已成功发送到您的微信~</p>

<script type='text/javascript' src='/js/zepto.min.js' charset='utf-8'></script>
<script>
    var token = '{{ session('token') }}';

    $(function() {
        $.ajax({
            url: '/api/qrcode/create',
            headers: {'Authorization': 'Bearer ' + token}
        })
    });

</script>
</body>

</html>