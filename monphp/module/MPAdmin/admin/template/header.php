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
    <?php echo MPModule::h('mpadmin_header') ?>
</head>
<body>
    <!--[if lt IE 7]><p class="chromeframe">Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
    <?php MPModule::h('body_start') ?>
    <div id='container'>

    <header id='header' class='clear'>
        <?php if (strlen(MPData::query('_Site', 'title'))): ?>
            <div class='site'>
            <a href='/admin/'>
                <?php if (is_file(MPData::query('MPAdmin', 'logo', 'tmp_name'))): ?>
                    <img src='/file/upload/admin_logo.jpg' />
                <?php else: ?>
                    <p class='title'><?php echo MPData::query('_Site', 'title'); ?></p>
                    <?php if (strlen(MPData::query('_Site', 'description'))): ?>
                        <p class='description'><?php echo MPData::query('_Site', 'description') ?></p>
                    <?php endif ?>
                <?php endif; ?>
            </a>
            </div>
        <?php endif ?>
        <div class='user_ctrl'>
            Currently logged in as <strong><?php echo MPUser::i('nice_name'); ?></strong>.<br />
            <a href='/admin/logout/'>&larr; logout</a>
        </div>
        <div class='admin'>
            <ul>
                <li id='quicklinks'>
                    &darr; Quicklinks
                    <ul>
                    <?php if (!is_null($quicklinks = MPUser::setting('admin', 'quicklinks'))): ?>
                        <?php foreach ($quicklinks as $href => $label): ?>
                        <li><a href='<?php echo $href; ?>'><?php echo $label; ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </ul>
                </li>
                <li><a href='/admin/'>&larr; MPAdmin Dashboard</a></li>
            </ul>
        </div>
    </header>

    <section id='body' class='clear' role="main">

        <?php include DIR_MODULE.'/MPAdmin/admin/template/notifications.php'; ?>
        <div class='header'><?php echo is_null($title) ? '' : $title; ?></div>
        <?php include DIR_MODULE.'/MPAdmin/admin/template/nav.php'; ?>

        <div id='content'>

        <?php if (!is_null($header)): ?><h1><?php echo $header ?></h1><?php endif ?>
