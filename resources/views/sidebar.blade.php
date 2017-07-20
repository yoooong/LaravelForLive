<div class="app layout-fixed-header">
    <!-- sidebar panel -->
    <div class="sidebar-panel offscreen-left">
        <div class="brand">
            <div class="toggle-offscreen">
                <a href="javascript:;" class="visible-xs hamburger-icon" data-toggle="offscreen" data-move="ltr">
                    <span></span>
                    <span></span>
                    <span></span>
                </a>
            </div>
            <a class="brand-logo">
                <span>轮盘LIVE</span>
            </a>
            <a href="/admin" class="small-menu-visible brand-logo">LPSP</a>
            <!-- /logo -->
        </div>
        <!-- main navigation -->
        <nav role="navigation">
            <ul class="nav">

                <li class="">
                    <a href="javascript:;">
                        <i class="icon-user"></i>
                        <span>用户</span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="/admin">
                                <span>用户列表</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin">
                                <span>黑名单</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="">
                    <a href="javascript:;">
                        <i class="icon-badge"></i>
                        <span>VIP</span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="">
                                <span>vip</span>
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="">
                    <a href="/order">
                        <i class="icon-basket"></i>
                        <span>订单</span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="/admin/order">
                                <span>订单列表</span>
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="">
                    <a href="javascript:;">
                        <i class="icon-present"></i>
                        <span>产品</span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="">
                                <span>列表</span>
                            </a>
                        </li>

                    </ul>
                </li>


                <li class="">
                    <a href="javascript:;">
                        <i class=" icon-ghost"></i>
                        <span>投诉</span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="">
                                <span>投诉列表</span>
                            </a>
                        </li>

                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /main navigation -->
    </div>
    <!-- /sidebar panel -->
    <!-- content panel -->
    <div class="main-panel">
        <!-- top header -->
        <div class="header navbar">
            {{--<div class="brand visible-xs">--}}
                {{--<!-- toggle offscreen menu -->--}}
                {{--<div class="toggle-offscreen">--}}
                    {{--<a href="javascript:;" class="hamburger-icon visible-xs" data-toggle="offscreen" data-move="ltr">--}}
                        {{--<span></span>--}}
                        {{--<span></span>--}}
                        {{--<span></span>--}}
                    {{--</a>--}}
                {{--</div>--}}
                {{--<!-- /toggle offscreen menu -->--}}
                {{--<!-- logo -->--}}
                {{--<a class="brand-logo">--}}
                    {{--<span>REACTOR</span>--}}
                {{--</a>--}}
                {{--<!-- /logo -->--}}
            {{--</div>--}}
            <ul class="nav navbar-nav hidden-xs">
                <li>
                    <a href="javascript:;" class="small-sidebar-toggle ripple" data-toggle="layout-small-menu">
                        <i class="icon-toggle-sidebar"></i>
                    </a>
                </li>
                <li class="searchbox">
                    <a href="javascript:;" data-toggle="search">
                        <i class="search-close-icon icon-close hide"></i>
                        <i class="search-open-icon icon-magnifier"></i>
                    </a>
                </li>
                <li class="navbar-form search-form hide">
                    <input type="search" class="form-control search-input" placeholder="Start typing...">
                    <div class="search-predict hide">
                        <a href="#">Searching for 'purple rain'</a>
                        <div class="heading">
                            <span class="title">People</span>
                        </div>
                        <ul class="predictive-list">
                            <li>
                                <a class="avatar" href="#">
                                    <img src="/images/face1.jpg" class="img-circle" alt="">
                                    <span>Tammy Carpenter</span>
                                </a>
                            </li>
                            <li>
                                <a class="avatar" href="#">
                                    <img src="/images/face2.jpg" class="img-circle" alt="">
                                    <span>Catherine Moreno</span>
                                </a>
                            </li>
                            <li>
                                <a class="avatar" href="#">
                                    <img src="/images/face3.jpg" class="img-circle" alt="">
                                    <span>Diana Robertson</span>
                                </a>
                            </li>
                            <li>
                                <a class="avatar" href="#">
                                    <img src="/images/face4.jpg" class="img-circle" alt="">
                                    <span>Emma Sullivan</span>
                                </a>
                            </li>
                        </ul>
                        <div class="heading">
                            <span class="title">Page posts</span>
                        </div>
                        <ul class="predictive-list">
                            <li>
                                <a class="avatar" href="#">
                                    <img src="/images/unsplash/img2.jpeg" class="img-rounded" alt="">
                                    <span>The latest news for cloud-based developers </span>
                                </a>
                            </li>
                            <li>
                                <a class="avatar" href="#">
                                    <img src="/images/unsplash/img2.jpeg" class="img-rounded" alt="">
                                    <span>Trending Goods of the Week</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right hidden-xs">
                <li>
                    <a href="javascript:;" class="ripple" data-toggle="dropdown">
                        <img src="/images/avatar.jpg" class="header-avatar img-circle" alt="user" title="user">
                        <span>Sean Carpenter</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:;">Settings</a>
                        </li>
                        <li>
                            <a href="javascript:;">Upgrade</a>
                        </li>
                        <li>
                            <a href="javascript:;">
                                <span class="label bg-danger pull-right">34</span>
                                <span>Notifications</span>
                            </a>
                        </li>
                        <li role="separator" class="divider"></li>
                        <li>
                            <a href="javascript:;">Help</a>
                        </li>
                        <li>
                            <a href="extras-signin.html">Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        @yield('content')

    </div>

</div>