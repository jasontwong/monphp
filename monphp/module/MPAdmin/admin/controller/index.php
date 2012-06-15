<?php

if (MPUser::perm('admin access'))
{
    MPAdmin::set('title', 'Admin Dashboard');
    $dashboard = MPModule::h('mpadmin_dashboard');

    include dirname(dirname(__FILE__)) . '/view/index.php';
}
else
{
    header('Location: /admin/login/');
    exit;
}
