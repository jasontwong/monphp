<?php 
$title = MPAdmin::get('title');
$header = MPAdmin::get('header');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link type="image/x-icon" href="/favicon.ico" rel="icon"/>
    <title><?php echo is_null($title) ? '' : $title.' &mdash; ' ?>MPAdmin Interface (<?php echo MPData::query('_Site', 'title') ?>)</title>
    <?php MPModule::h('mpadmin_header') ?>
</head>
<body>
    <!--[if lt IE 7]><p class="chromeframe">Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->

    <?php MPModule::h('body_start', URI_PART_2) ?>

    <div id='container'>

    <section id='body' class='clear' role="main">
        <div id='content'>
