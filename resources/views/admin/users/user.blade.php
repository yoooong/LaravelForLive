@extends('main')
@section('css')
    <link rel="stylesheet" href="/vendor/sweetalert/dist/sweetalert.css">
    <style type="text/css">
        .gallery .row {
            margin-top: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        @include('admin/layouts/msg')
        <form class="form-horizontal" id="form" role="form" method="post" action="/admin/store">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="id" value="{{$data->id}}">
            <div class="card bg-white">
                <div class="card-header">
                    用户详情
                </div>
                <div class="card-block">
                    <div class="row m-a-0">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="col-sm-1 control-label">昵称</label>
                                <div class="col-sm-3">
                                    <label class="control-label">{{$data->nickname}}</label>

                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label">头像</label>
                                <div class="col-sm-3">

                                    <img class="img-responsive" src="{{$data->avatar}}" width="100" height="100">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label">性别</label>
                                <div class="col-sm-3">
                                    <label class="control-label">{{$data->sex}}</label>

                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label">年龄</label>
                                <div class="col-sm-3">
                                    <label class="control-label">{{$data->age}}</label>

                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label">城市</label>
                                <div class="col-sm-3">

                                    <label class="control-label">{{$data->city}}</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label">等级</label>
                                <div class="col-sm-3">

                                    <label class="control-label">{{$data->level}}</label>
                                </div>
                            </div>
                            @if(!empty($data->media_resources))
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">认证</label>
                                    <div class="col-sm-3">
                                        <label class="control-label" style="color: #CC0000">已认证⭐️</label>

                                    </div>
                                </div>
                                <video width="460" height="340" class="video-responsive" controls>
                                <source src="{{$media[0]}}" type="video/mp4">
                                </video>
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">修改视频</label>
                                    <div class="col-sm-2 cover">
                            <span class="btn btn-success fileinput-button">
                                <span>上传</span>
                                <input type="file" class="fileupload" id="cover" name="file">
                            </span>
                                    </div>
                                </div>
                            
                            <div class="form-group">
                                <div class="gallery row">
                                    @for($i = 1;$i<count($media);$i++)
                                    <a class="col-sm-1">
                                        <img src="{{$media[$i]}}" class="row">
                                    </a>
                                    @endfor
                                </div>

                            </div>
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">修改图片</label>
                                    <div class="col-sm-11 gallery">
                            <span class="btn btn-success fileinput-button">
                            <span>上传</span>
                            <input type="file" class="fileupload" id="gallery" name="file">
                            </span>
                                    </div>
                                </div>
                            @else()
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">认证视频</label>
                                    <div class="col-sm-2 cover">
                            <span class="btn btn-success fileinput-button">
                                <span>上传</span>
                                <input type="file" class="fileupload" id="cover" name="file">
                            </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">认证图片</label>
                                    <div class="col-sm-11 gallery">
                            <span class="btn btn-success fileinput-button">
                            <span>上传</span>
                            <input type="file" class="fileupload" id="gallery" name="file">
                            </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm btn-icon">
                        <i class="icon-cursor mr5"></i>
                        <span>提交</span>
                    </button>
                </div>

            </div>
        </form>

    </div>

@endsection

@section('javascript')
    <script src="/vendor/chosen_v1.4.0/chosen.jquery.min.js"></script>
    <script src="/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    <script src="/vendor/summernote/dist/summernote.min.js"></script>
    <script src="/vendor/jquery.ui/ui/widget.js"></script>
    <script src="/vendor/blueimp-file-upload/js/jquery.iframe-transport.js"></script>
    <script src="/vendor/blueimp-file-upload/js/jquery.fileupload.js"></script>
    <script src="/vendor/multiselect/js/jquery.multi-select.js"></script>
    <script src="/vendor/jquery-labelauty/source/jquery-labelauty.js"></script>
    <script src="/vendor/jquery.tagsinput/src/jquery.tagsinput.js"></script>
    <script src="/vendor/sweetalert/dist/sweetalert.min.js"></script>
    <script src="/scripts/ui/alert.js"></script>
    <script>
        var host = 'Http://chrrsdemo.oss-cn-shenzhen.aliyuncs.com';

        $('#cover').fileupload({
            url: '/admin/upload',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            formData: {},
            done: function (e, data) {
                var result = data.result;
                if (result.code == 1000) {
                    console.log(result.data);
                    changeCover(result.data);
                } else {
                    console.log('出错');
                    noty({
                        theme: 'app-noty',
                        text: result.message,
                        type: 'error'
                    });
                }
            }
        });
        $('#gallery').fileupload({
            url: '/admin/upload',
            headers: {'X-CSRF-Token': $('[name="_token"]').val()},
            formData: {},
            done: function (e, data) {
                var result = data.result;
                console.log(result.data);
                if (result.code == 1000) {
                    addGallery(result.data);
                } else {
                    noty({
                        theme: 'app-noty',
                        text: result.message,
                        type: 'error'
                    });
                }
            }
        });

        function changeCover($path) {
            $('.cover div').remove();
            $('.cover span').hide();
            $('<div/>').append(
                $('<a/>').click(function () {
                    $('#cover').trigger('click');
                }).append(
                    $('<video width="320" height="140" controls>').addClass('img-responsive').prop('src', host + $path)
                )
            ).append(
                $('<input>').prop('type', 'hidden').prop('name', 'cover').prop('value', host + $path)
            ).appendTo('.cover');
        }

        function addGallery($path) {
            $('.gallery .row').length || $('<div/>').addClass('row').appendTo('.gallery');
            $('<div/>').addClass('col-sm-1').append(
                $('<a/>').click(removeGallery).append(
                    $('<img>').prop('src', host + $path)
                )
            ).append(
                $('<input>').prop('type', 'hidden').prop('name', 'galleries[]').prop('value', host + $path)
            ).appendTo('.gallery .row');
        }

        function removeGallery() {
            if (confirm('删除本张图片?')) {
                $(this).closest('div').remove();
                $('.gallery .row').children().length || $('.gallery .row').remove();
            }
        }



    </script>
@stop
