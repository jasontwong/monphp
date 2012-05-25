<?php $head = !isset($head) ? array() : $head; ?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo ake('title', $head) ? $head['title'] : ''; ?></title>

    <?php 
    if (ake('meta', $head))
    { 
        foreach ($head['meta'] as &$_meta)
        {
            $_m = '<meta ';
            foreach ($_meta as $attr => &$_val)
            {
                $_m .= $attr . '="' . $_val . '" ';
            }
            echo $_m . '>';
        }
    };
    ?>

    <meta name="viewport" content="width=device-width">
    <meta http-equiv="imagetoolbar" content="false" />

    <link rel="canonical" href="<?php echo ake('HTTPS', $_SERVER) ? 'https://' : 'http://'; ?><?php echo  $_SERVER['HTTP_HOST'] . URI_PATH; ?>" />

    <?php 
    if (ake('link', $head))
    { 
        foreach ($head['link'] as &$_link)
        {
            $_l = '<link ';
            foreach ($_link as $attr => &$_val)
            {
                $_l .= $attr . '="' . $_val . '" ';
            }
            echo $_l . '>';
        }
    };
    ?>

    <script>
        var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>

    <?php MPModule::h('mpsystem_print_head'); ?>

</head>
<body>
    <!--[if lt IE 7]><p class="chromeframe">Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
    <div id="container">
        <header>
        </header>
        <div id="content" role="main">
