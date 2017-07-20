<?php

namespace App\Http\Controllers\Admin;

use App\Handlers\UploadHandler;
use App\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    protected $disk;

    public function __construct()
    {

        $this->disk = Storage::disk('oss');
    }

    public function index()
    {
        $data = DB::table('user')->paginate(20);
        return view('admin.users.list', $data);
    }

    public function edit($id)
    {
        $data = User::findOrFail($id);
        $media = preg_split("/[@]/", $data['media_resources']);
        return view('admin.users.user', ['data' => $data, 'media' => $media]);
    }

    //后台查找需要修改资料的用户（暂用）

    public function search(Request $request)
    {

        $info = $request->only(['nickname', 'id']);
        $data = User::where('nickname', trim($info['nickname']))->orWhere('id', $info['id'])->get();
        if (empty($info)) {
            $data = $data = DB::table('user')->simplePaginate(10);
        }
        return view('admin.users.list', ['data' => $data]);
    }


    public function upload(Request $request)
    {
        $upload = new UploadHandler();

        $file = $request->file('file');
        $data = $upload->upload($file);

        return response()->json(['code' => '1000','data'=>$data]);
    }


    public function update($id,Request $request)
    {
        $info = User::findOrFail($id);

    }

    public function store(Request $request)
    {
        $medias = $request->all();
//        dd($medias);
        if (!empty($medias['galleries']) || !empty($medias['cover'])) {
            array_unshift($medias['galleries'], $medias['cover']);
            $media_url = implode('@', $medias['galleries']);
            User::where('id', $medias['id'])->update(['media_resources' => $media_url]);
            return redirect()->back()->with('msg','success');

        }
//        array_unshift($medias['galleries'], $medias['cover']);
//        $media_url = implode('@', $medias['galleries']);

//        $result = User::where('id', $medias['id'])->update(['media_resources' => $media_url]);
//        if ($result) {
//
//            return redirect()->back()->with('msg','success');
//        }
        return redirect()->back()->with('msg','fail');
    }


    //消费详情
    public function details($id)
    {


        $orders = Order::where('user_id', $id)->get();
        foreach ($orders as $order) {
            $order->items = Order::find($order->id)->items;
        }
        return view('admin.product.consume', compact('orders', 'products'));
    }
}
