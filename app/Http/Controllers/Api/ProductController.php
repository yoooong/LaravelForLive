<?php

namespace App\Http\Controllers\Api;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $data = [];

        $query = Product::orderBy('price', 'asc')->select(['id', 'name', 'price']);
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $data['products'] = $query->get();

        return response()->json(['code' => 1000, 'data' => $data]);
    }
}
