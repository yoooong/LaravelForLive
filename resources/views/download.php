<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>下载</title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <link rel="shortcut icon" href="/favicon.ico">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <style>
        *{padding:0;margin:0;}
        img{border:0 none;}
        p{text-align: center;}
        p img{width: 15%;padding: 40px 0;}
        p a{margin:0 auto;text-decoration:none;display: block;width: 75%;font-size:30px;height: 50px;line-height: 50px;color:#fff;background: #0cd4c0;border-radius: 30px;color:#fff;}
        .float{

            position: fixed;
            top:0;
            bottom:0;
            right:0;
            width: 100%;
        }
        .hide{
            display: none;
        }
        .mask{
            position: absolute;
            top:0;
            left:0;
            bottom: 0;
            width: 100%;
            background: #000;
            opacity: 0.7;
        }
        .float img{
            position: relative;
            width: 100%;
        }
    </style>
</head>

<body>
<!--lunpan_1.0.0_android.apk-->
<!--lunpan_1.0.0_ios.ipa-->
<p><img src="/github.jpeg" alt=""></p>
<p>
    <a href="http://tcpan.oss-cn-hangzhou.aliyuncs.com/lpsp/package/lpsp_1.0.1.apk" id="js-href" target="_blank" >立即打开</a>
</p>
<div class="float hide" id="js-float">
    <div class="mask"></div>
    <img src="/images/live_weixin.png" alt=" ">
</div>
<script type="text/javascript">
    var u = navigator.userAgent;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    window.onload = function(){
        if(isWeiXin()){
            document.getElementById('js-float').className = 'float';

        }
    }
    function isWeiXin(){
        var ua = window.navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i) == 'micromessenger'){
            return true;
        }else{
            return false;
        }
    }
</script>
</body>

</html>