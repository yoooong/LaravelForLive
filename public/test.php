<?php

$data = 'http://oss.aliyun.com/201703/21141759.mp4@http://oss.aliyun.com/201703/21141836.png@http://oss.aliyun.com/201703/21141844.png';

 $media = preg_split("/[@]/",$data);

 print_r($media[0]);
