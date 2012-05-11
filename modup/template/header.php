<?php $head = !isset($head) ? array() : $head; ?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <link rel="dns-prefetch" href="//ajax.googleapis.com">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title></title>

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

    <link rel="stylesheet" href="/css/style.css">
    <link rel="canonical" href="<?php echo ake('HTTPS', $_SERVER) ? 'https://' : 'http://'; ?><?php echo  $_SERVER['HTTP_HOST'] . URI_PATH; ?>" />

    <?php if (ake('rss', $head)): foreach ($head['rss'] as &$_rss): ?>
        <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $_rss; ?>" />
    <?php endforeach; endif; ?>

    <?php if (ake('atom', $head)): foreach ($head['atom'] as &$_atom): ?>
        <link rel="alternate" type="application/atom+xml" title="Atom" href="<?php echo $_atom; ?>" />
    <?php endforeach; endif; ?>

    <?php if (ake('css', $head)): foreach ($head['css'] as $_media => &$_css): ?>
        <?php foreach ($_css as &$_cs): ?>
            <link rel="stylesheet" media="<?php echo $_media; ?>" href="<?php echo $_cs; ?>">
        <?php endforeach; ?>
    <?php endforeach; endif; ?>

    <script src="/js/libs/modernizr-2.5.3.min.js"></script>

    <?php if (ake('js', $head)): foreach ($head['js'] as &$_js): ?>
        <script src="<?php echo $_js; ?>"></script>
    <?php endforeach; endif; ?>
</head>
<body>
    <!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
    <header>

    </header>
    <div role="main">
