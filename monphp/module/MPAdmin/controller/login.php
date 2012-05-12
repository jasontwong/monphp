<?php

list($layout, $form) = MPModule::h('mpadmin_login_build');

if (isset($_POST['login']))
{
    $post = array();
    foreach ($_POST['login'] as $mod => $data)
    {
        $post[$mod] = $layout->acts('post', $data);
    }
    $result = MPModule::h('mpadmin_login_submit', MPModule::TARGET_ALL, $post);
    if ($result['login'])
    {
        header('Location: /admin/');
        exit;
    }
}

$head['title'] = 'Log in';

include DIR_TMPL.'/header.php';
include DIR_MODULE.'/MPAdmin/view/login.php';
include DIR_TMPL.'/footer.php';

?>
