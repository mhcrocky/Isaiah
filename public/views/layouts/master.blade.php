<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>{{ $title }}</title>

    <!-- Stylesheets -->
        <!-- FontAwesome 4.1.0 -->
        <link href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" />
        <!-- Bootstrap 3.1.1 -->
        <link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet" />
        <!-- IsaiahExplained -->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" type="text/css" media="screen" />

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

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body id="{{ $body_id }}" class="{{ $body_css }}">

    {{ $verse_modal or '' }}
    {{ $chapter_modal or '' }}
    {{ $keyword_modal or '' }}

    <div class="header-container">
        <header class="navbar navbar-static-top" role="navigation">
            <nav class="container main-nav">
                <div class="navbar-header">
                    <a class="navbar-brand" href="/"><img src="//dev.isaiahexplained.com/wp-content/themes/ie04/images/isaiah-explained-logo.png" alt="IsaiahExplained.com"></a>
                </div>
                <div class="navbar-collapse collapse navbar-right">
                    <ul class="nav navbar-nav">
                        <li><a href="http://dev.isaiahexplained.com/"><i class="fa fa-home fa-fw"></i> Back to Home</a></li>
                        <li><a href="/"><i class="fa fa-book fa-fw"></i> Chapter Index</a></li>
                        <li><a href="/Concordance/"><i class="fa fa-list fa-fw"></i> Concordance Index</a></li>
                        <li class="show-hide nav-search">
                            <div class="input-group">
                                <input type="text" class="form-control search-field" placeholder="Search Isaiah">
                                <span class="input-group-btn">
                                    <button class="btn btn-warning" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div><!-- End nav-collapse -->
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

        <div class="row footer-wrapper">
            <footer>
                <div>
                    <div class="col-xs-12 col-sm-3 col-list">
                        <h3>Isaiah Explained</h3>
                        <ul>
                            <li><a href="">Home</a></li>
                            <li><a href="">Resources</a></li>
                            <li><a href="">About</a></li>
                            <li><a href="">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-3 col-list">
                        <h3>Network</h3>
                        <ul>
                            <li><a href="//josephandjudah.com" target="_blank">JosephAndJudah.com</a></li>
                            <li><a href="//isaiahreport.com" target="_blank">IsaiahReport.com</a></li>
                            <li><a href="//isaiahprophecy.com" target="_blank">IsaiahProphecy.com</a></li>
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-3 col-list">
                        <h3>Read Isaiah</h3>
                        <ul>
                            <li><a href="/">Isaiah Chapter Index</a></li>
                            <li><a href="/Search">Search Isaiah</a></li>
                            <li><a href="/Concordance/">Isaiah Concordance Index</a></li>
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-3">
                        <h3>Social</h3>
                        <ul>
                            <li><a class="btn btn-facebook" href=""><i class="fa fa-facebook fa-fw"></i>&nbsp;&nbsp;Facebook</a></li>
                            <!--<li><a class="btn btn-twitter" href=""><i class="fa fa-twitter fa-fw"></i>&nbsp;&nbsp;twitter</a></li>-->
                        </ul>
                    </div>
                </div>
                <div>
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
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')

</body>
</html>