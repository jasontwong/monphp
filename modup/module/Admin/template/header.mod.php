<?php 
$title = Admin::get('title');
$header = Admin::get('header');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link type="image/x-icon" href="/favicon.ico" rel="icon"/>
    <title><?php echo is_null($title) ? '' : $title.' &mdash; ' ?>Admin Interface (<?php echo Data::query('_Site', 'title') ?>)</title>
    <?php echo Module::h('admin_js_header', URI_PART_2) ?>
    <?php echo Module::h('admin_css', URI_PART_2) ?>
</head>

<body>

<?php Module::h('body_start', URI_PART_2) ?>

<div id='container'>

<div id='body' class='clear'>
    <div id='content'>
