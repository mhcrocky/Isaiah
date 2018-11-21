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
                    <a class="navbar-brand" href="/"><img src="/images/isaiah-explained-logo.png" alt="IsaiahExplained.com"></a>
                </div>
                <div class="navbar-collapse collapse navbar-right">
                    <ul class="nav navbar-nav">
                        <li class="dropdown"><a title="<i class=&quot;fa fa-graduation-cap fa-fw&quot;></i>&nbsp;<span>Resources</span>" href="#" data-toggle="dropdown" class="dropdown-toggle" aria-haspopup="true"><i class="fa fa-graduation-cap fa-fw"></i>&nbsp;<span>Resources</span> <span class="caret"></span></a>
                            <ul role="menu" class="dropdown-menu">
                                <li><a title="Key Features of the Prophecy of Isaiah" href="/resources/key-features-of-the-prophecy-of-isaiah/">Key Features of the Prophecy of Isaiah</a></li>
                                <li><a title="Isaiah’s Layered Literary Structures" href="/resources/isaiahs-layered-literary-structures/">Isaiah’s Layered Literary Structures</a></li>
                                <li><a title="Isaiah’s Seven Spiritual Levels of Humanity" href="/resources/isaiahs-seven-spiritual-levels-of-humanity/">Isaiah’s Seven Spiritual Levels of Humanity</a></li>
                                <li><a title="Isaiah’s Ancient Types of End-Time Events" href="/resources/isaiahs-ancient-types-of-end-time-events/">Isaiah’s Ancient Types of End-Time Events</a></li>
                                <li><a title="Overviews of the Prophecy of Isaiah" href="/resources/overviews-of-the-prophecy-of-isaiah/">Overviews of the Prophecy of Isaiah</a></li>
                                <li><a title="Glossary of Terms Relating to Isaiah" href="/resources/glossary-of-terms-relating-to-isaiah/">Glossary of Terms Relating to Isaiah</a></li>
                            </ul>
                        </li>
                        <li><a href="/blog/"><i class="fa fa-rss fa-fw"></i> Blog</a></li>
                        <li><a href="/blog/contact/"><i class="fa fa-envelope fa-fw"></i> Contact</a></li>
                        <li class="show-hide nav-search">
                            {{ $mobile_search or '' }}
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

        <?php include(public_path() . "/blog/wp-content/themes/ie04/footer-universal.php"); ?>
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
    <script src="{{ asset('js/app.min.js') }}"></script>
    <?php } ?>
    @yield('scripts')
</body>
</html>