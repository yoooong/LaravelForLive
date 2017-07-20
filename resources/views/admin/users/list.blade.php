@extends('main')

@section('content')
    <div class="main-content">
        <div class="card bg-white">
            <div class="card bg-white m-b">
                <div class="card-block">
                    <form class="form-inline" action="{{url('admin/users/search')}}" method="get">
                        <div class="form-group">
                            <input type="text" class="form-control" name="nickname" placeholder="姓名">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="id" placeholder="id">
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
                        <th>ID</th>
                        <th>昵称</th>
                        <th>性别</th>
                        <th>年龄</th>
                        <th>城市</th>
                        <th>聊天价</th>
                        <th>等级</th>
                        <th>注册时间</th>
                        <th>认证</th>
                        <th>编辑</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $userdata)
                        <tr>
                            <td>{{$userdata->id}}</td>
                            <td>{{$userdata->nickname}}</td>
                            <td>{{$userdata->sex}}</td>
                            <td>{{$userdata->age}}</td>
                            <td>{{$userdata->city}}</td>
                            <td>{{$userdata->chat_price}}</td>
                            <td>{{$userdata->level}}</td>
                            <td>{{$userdata->created_at}}</td>
                            <td>@if(empty($userdata->media_resources))
                                    否
                                @else
                                    <label style="color: #CC0000">是</label>
                                @endif
                            </td>
                            <td>
                                <div class='btn-group'>
                                    {{--<a href="{{url('admin/edit/'.$userdata->id)}}"--}}
                                    <a href="{{url('admin/'.$userdata->id.'/edit')}}"
                                       class="btn btn-primary btn-xs">编辑</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <ul class="pagination pagination-sm clearfix block">
                    <div class="card-footer">
                        {{--{{ $data->links() }}--}}
                    </div>
                    <li>
                        <a href="javascript:;">←</a>
                    </li>
                    <li>
                        <a href="javascript:;">1</a>
                    </li>
                    <li>
                        <a href="javascript:;">→</a>
                    </li>
                </ul>
            </div>

        </div>
    </div>

@endsection

@section('javascript')

@endsection
