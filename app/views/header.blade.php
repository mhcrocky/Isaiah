<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <meta charset="utf-8">
    <title>The Book of Isaiah</title>
    <!-- FontAwesome -->
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- Bootstrap 3 -->
    <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>-->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width; initial-scale=1; user-scalable=no;">
    <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <?php
    $ajax->Run();
    ?>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="styles.css" type="text/css" media="screen" />
    <!-- Fonts -->
    <link href="http://fonts.googleapis.com/css?family=Crimson+Text:400,400italic,600,600italic,700,700italic" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Roboto+Slab:400,300,700,100|Roboto+Condensed:300italic,400italic,700italic,400,300,700|Roboto:400,100,100italic,300,300italic,500,500italic,400italic,700,700italic,900,900italic" rel="stylesheet" type="text/css">

    <!--[if gte IE 9]>
    <style type="text/css">
        .gradient {
            filter: none;
        }
    </style>
    <![endif]-->

    <script>
        $(function () {
            $('.ttip').tooltip('hide')
        });
    </script>

    <script>
        $(function () {
            $('.modal-trigger').modal('hide')
        });
    </script>

    <script>
        $(function () {
            $('[data-toggle="popover"]').popover();
            $('body').on('click', function (e) {
                $('[data-toggle="popover"]').each(function () {
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });
        });
    </script>

    <script type="text/javascript">
        $(function () {
            $('a.nav-right-disabled').click(function() { return false; });
            $('a.nav-left-disabled').click(function() { return false; });
        });
        function showPrevChapter(){
            var info = {"Chapter": document.getElementById("email").value};
            showOutput(info, {"preloader": "pr", "target": "info"});
        }
        function showNextChapter(){
            var info = {"Chapter": document.getElementById("email").value};
            showOutput(info, {"preloader": "pr", "target": "info"});
        }
    </script>
</head>