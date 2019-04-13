<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{ $robot_meta or '' }}
    {{ $meta or '' }}
    <title>{{ $title }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <!-- Stylesheets -->
    <!-- FontAwesome 4.1.0 -->
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" />
    <!-- Bootstrap 3.1.1 -->
    <link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet" />
    <!-- IsaiahExplained -->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" type="text/css" media="screen" />
    <link href="{{ asset('css/style-master.css') }}" rel="stylesheet" type="text/css" media="screen" />

    <!-- Support for full multi-stop gradients with IE9 (using SVG) -->
    <!--[if gte IE 9]>
    <style type="text/css">
        .gradient {
            filter: none;
        }
    </style>
    <![endif]-->

    <!-- Fonts -->
    <link href="http://fonts.googleapis.com/css?family=Crimson+Text:400,400italic,600,600italic,700,700italic" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Roboto+Slab:400,300,700,100|Roboto+Condensed:300italic,400italic,700italic,400,300,700|Roboto:400,100,100italic,300,300italic,500,500italic,400italic,700,700italic,900,900italic" rel="stylesheet" type="text/css">
    <!-- End Stylesheets -->

    @if (!App::environment('local', 'staging'))
        {{ $tracking_code }}
    @endif

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body id="{{ $body_id }}" class="{{ $body_css }}">

    {{ $alpha_modal or '' }}

    <div class="header-container">
        <header class="navbar navbar-static-top" role="navigation">
            <nav class="container main-nav">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ $app_url }}/"><img src="{{ asset('images/isaiah-explained-logo.png') }}" alt="IsaiahExplained.com"></a>
                </div>
                <?php //include(public_path() . "/blog/wp-content/themes/ie04/nav.php"); ?>
                <div id="main-nav" class="navbar-collapse collapse navbar-right">
                    <ul class="nav navbar-nav">
                        <li><a title="Home" href="{{ $app_url }}/"><i class="fa fa-home fa-fw"></i>&nbsp;<span>Home</span></a></li>
                        <li class="dropdown"><a title="<i class=&quot;fa fa-graduation-cap fa-fw&quot;></i>&nbsp;<span>Resources</span>" href="#" data-toggle="dropdown" class="dropdown-toggle" aria-haspopup="true"><i class="fa fa-graduation-cap fa-fw"></i>&nbsp;<span>Resources</span> <span class="caret"></span></a>
                            <ul role="menu" class="dropdown-menu">
                                <li><a title="Key Features of the Prophecy of Isaiah" href="{{ $app_url }}/resources/key-features-of-the-prophecy-of-isaiah/">Key Features of the Prophecy of Isaiah</a></li>
                                <li><a title="Isaiah's Layered Literary Structures" href="{{ $app_url }}/resources/isaiahs-layered-literary-structures/">Isaiah's Layered Literary Structures</a></li>
                                <li><a title="Isaiah's Seven Spiritual Levels of Humanity" href="{{ $app_url }}/resources/isaiahs-seven-spiritual-levels-of-humanity/">Isaiah's Seven Spiritual Levels of Humanity</a></li>
                                <li><a title="Isaiah's Ancient Types of End-Time Events" href="{{ $app_url }}/resources/isaiahs-ancient-types-of-end-time-events/">Isaiah's Ancient Types of End-Time Events</a></li>
                                <li><a title="Overviews of the Prophecy of Isaiah" href="{{ $app_url }}/resources/overviews-of-the-prophecy-of-isaiah/">Overviews of the Prophecy of Isaiah</a></li>
                                <li><a title="Glossary of Terms Relating to Isaiah" href="{{ $app_url }}/resources/glossary-of-terms-relating-to-isaiah/">Glossary of Terms Relating to Isaiah</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ $app_url }}/bible/"><i class="fa fa-book fa-fw"></i> <span>KJV Bible</span></a></li>
                        <li><a href="{{ $app_url }}/store"><i class="fa fa-shopping-cart fa-fw"></i> <span>Store</span></a></li>
                        <li><a href="{{ $app_url }}/contact/"><i class="fa fa-envelope fa-fw"></i> <span>Contact</span></a></li>
                        <li class="show-hide nav-search">
                            <form role="search">
                                <div class="input-group">
                                    <input name="search-box" type="text" class="form-control search-field" placeholder="Search Isaiah" value="">
                                    <span class="input-group-btn">
                                        <button class="btn btn-warning" type="submit"><i class="fa fa-search"></i></button>
                                    </span>
                                    <span name="search-error"></span>
                                </div><!-- /search -->
                            </form>
                        </li>
                    </ul>
                </div>
            </nav><!-- End Container -->
        </header>
        {{ $heading }}
    </div>

    <div class="container">

        {{ $top_nav or '' }}

        <div class="row wrapper">
{{ $content }}
        </div>

        {{ $bottom_nav or '' }}

        <?php //include(public_path() . "/blog/wp-content/themes/ie04/footer-universal.php"); ?>
        <div class="row footer-wrapper">
            <footer>
                <div class="row">
                    <div class="col-xs-12 col-sm-3 col-list">
                        <h3>Isaiah Explained</h3>
                        <ul>
                            <li><a href="{{ $app_url }}/"><i class="fa fa-home fa-fw"></i>&nbsp;<span>Home</span></a></li>
                            <li><a href="{{ $app_url }}/bible"><i class="fa fa-book fa-fw"></i>&nbsp;<span>KJV Bible</span></a></li>
                            <li><a href="{{ $app_url }}/store"><i class="fa fa-shopping-cart fa-fw"></i> <span>Store</span></a></li>
                            <li><a href="{{ $app_url }}/about"><i class="fa fa-info-circle fa-fw"></i>&nbsp;<span>About</span></a></li>
                            <!-- 					<li><a href="http://isaiahexplained.com/blog/contact/"><i class="fa fa-envelope fa-fw"></i>&nbsp;<span>Contact</span></a></li> -->
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-3 col-list">
                        <h3>Study Tools</h3>
                        <?php //wp_nav_menu( array( 'theme_location' => 'menu-resources' ) ); ?>
                        <ul>
                            <li><a href="{{ $app_url }}/1#one_col">Isaiah Institute Translation</a></li>
                            <li><a href="{{ $app_url }}/1#three_col">Comparative Translation</a></li>
                            <li><a href="{{ $app_url }}/1#commentary">Apocalyptic Commentary</a></li>
                            <li><a href="{{ $app_url }}/1#concordance">Interactive Concordance</a></li>
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-3 col-list">
                        <h3>Network</h3>
                        <ul>
                            <li><a href="http://isaiahinstitute.com/" target="_blank">IsaiahInstitute.com</a></li>
                            <li><a href="http://isaiahreport.com/" target="_blank">IsaiahReport.com</a></li>
                            <li><a href="http://isaiahprophecy.com/" target="_blank">IsaiahProphecy.com</a></li>
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-3">
                        <h3>Social</h3>
                        <ul>
                            <!--
        <li><a class="btn btn-facebook" href=""><i class="fa fa-facebook fa-fw"></i>&nbsp;&nbsp;Facebook</a></li>
                            <li><a class="btn btn-twitter" href=""><i class="fa fa-twitter fa-fw"></i>&nbsp;&nbsp;twitter</a></li>
        -->
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <hr>
                        <p class="copyright">&copy;<?php echo date("Y"); ?> Isaiah Explained, All Rights Reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    </div> <!-- End Container -->

    <!-- Placed at the end of the document so the pages load faster -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="{{ asset('node_modules/underscore/underscore-min.js') }}"></script>
    <script src="{{ asset('node_modules/backbone/backbone-min.js') }}"></script>
    <script src="{{ asset('node_modules/mustache/mustache.js') }}"></script>
    <!-- Bootstrap 3.1.1 -->
    <script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <!--<script src="{{ asset('js/bootstrap.min.js') }}"></script>-->
    <?php if (Config::get('app.debug') == 'debug') { ?>
    <script src="{{ asset('js/jquery.cookie.js') }}"></script>
    <script src="{{ asset('js/findAndReplaceDOMText.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <?php } else { ?>
    <script src="{{ asset('js/jquery.cookie.min.js') }}"></script>
    <script src="{{ asset('js/findAndReplaceDOMText.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script>
    <?php } ?>
    @yield('scripts')
</body>
</html>