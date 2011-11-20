<?php

if (User::perm('admin access'))
{
    Admin::set('title', 'Admin Dashboard');
    $dashboard = Module::h('admin_dashboard');

    include DIR_MODULE.'/Admin/view/index.php';
}
else
{
    header('Location: /admin/login/');
}

?>
