@extends('main')

@section('content')
    <div class="main-content">
        <div class="card bg-white">
            <div class="card bg-white m-b">
                <div class="card-block">
                    <form class="form-inline" action="{{url('admin/users/search')}}" method="get">
                        <div class="form-group">
                            <input type="text" class="form-control" name="id" placeholder="订单号">
                        </div>
                        <div class="btn-group mr15">
                            {{--<button type="button" class="btn btn-default" data-toggle="dropdown">消费类型--}}
                                {{--<span class="caret"></span>--}}
                            {{--</button>--}}
                            <select name="consume" class="form-control">
                                <option>充值</option>
                                <option>打赏</option>
                                <option>聊天订单</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">搜索</button>
                    </form>
                </div>
            </div>

            <div class="card-header">
                消费详情
            </div>
            <div class="card-block">
                <table class="table table-bordered table-striped datatable editable-datatable responsive align-middle bordered">
                    <thead>
                    <tr>
                        <th class="col-md-1">用户</th>
                        <th>消费内容</th>
                        <th>消费金额</th>
                        {{--<th>消费金额</th>--}}
                        <th>数量</th>
                        <th>时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach( $orders as $order)
                        <tr>
                            <td>{{$order->user_id}}</td>
                            {{--@foreach( $products as $product)--}}
                            <td>{{$order->items[0]->product_id }}</td>
                            {{--@endforeach--}}
                            <td>{{$order->total}}</td>
                            <td>{{$order->items[0]->quantity}}</td>
                            <td>{{$order->created_at}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <ul class="pagination pagination-sm clearfix block">
                    <div class="card-footer">
                    </div>

                </ul>
            </div>

        </div>
    </div>

@endsection

@section('javascript')

@endsection