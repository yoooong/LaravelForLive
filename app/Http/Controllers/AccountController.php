<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function charge()
    {
        $data = [];

        $data['user'] = auth()->user();
        $data['products'] = Product::where('type', 1)->get();

        return view('account.charge', $data);
    }

    public function vip()
    {
        $data = [];

        $data['user'] = auth()->user();
        $data['products'] = Product::where('type', 2)->orderBy('value', 'asc')->get();
        $data['levels'] = [
            '骑士' => 'Knight',
            '公爵' => 'Duke',
            '子爵' => 'Viscount',
            '男爵' => 'Baron',
            '伯爵' => 'Count',
            '侯爵' => 'Marquis',
            '国王' => 'King'
        ];

        return view('account.vip', $data);
    }

    public function qrcode()
    {

        return view('account.qrcode');
    }
}
