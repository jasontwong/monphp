<?php

if (MPUser::perm('admin access'))
{
    MPAdmin::set('title', 'MPAdmin Dashboard');
    $dashboard = MPModule::h('mpadmin_dashboard');

    include DIR_MODULE.'/MPAdmin/view/index.php';
}
else
{
    header('Location: /admin/login/');
}

?>
