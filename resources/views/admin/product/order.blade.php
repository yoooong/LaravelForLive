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
                        <button type="submit" class="btn btn-success">搜索</button>
                    </form>
                </div>
            </div>
            <div class="card-header">
                用户列表
            </div>
            <div class="card-block">
                <table class="table table-bordered table-striped datatable editable-datatable responsive align-middle bordered">
                    <thead>
                    <tr>
                        <th>订单号</th>
                        <th>消费用户</th>
                        <th>消费金额（元）</th>
                        <th>下单时间</th>
                        <th>消费情况</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach( $data as $order)
                        <tr>
                            <td>{{$order->id}}</td>
                            <td><a href="/admin/{{$order->user_id}}/consume">{{$order->user_id}}</a></td>
                            <td>{{$order->total}}</td>
                            <td>{{$order->created_at}}</td>
                            <td>
                                <div class='btn-group'>
                                    <a href="/admin/{{$order->user_id}}/consume"
                                       class="btn btn-primary btn-xs">详情</a>
                                </div>
                            </td>
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
