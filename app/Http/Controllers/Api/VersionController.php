<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VersionController extends Controller
{
    public function check()
    {
        $version = '2';
        $update_content = <<<EOF
1.增加投诉功能
2.优化等待界面
3.增加钻石内购
4.修复BUG
EOF;
        $update_url = 'http://cdn.xiaoyaotec.com/lpsp/package/lpsp_1.1.0.apk';
        $is_enforce = false;

        $data = ['version' => $version, 'update_content' => $update_content, 'update_url' => $update_url, 'is_enforce' => $is_enforce];

        return response()->json($data);
    }
}
