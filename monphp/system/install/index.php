<?php 

if (URI_PATH === WEB_DIR_INSTALL.'/')
{
    header('Location: '.WEB_DIR_INSTALL.'/start/');
    exit;
}
else
{
    $parts = count(explode('/', trim(WEB_DIR_INSTALL, '/')));
    $script = constant('URI_PART_'.$parts);

    $ctrl = DIR_SYS.'/install/controller/'.$script.'.php';
    $view = DIR_SYS.'/install/view/'.$script.'.php';

    $fc = is_file($ctrl);
    $fv = is_file($view);

    if ($fc || $fv)
    {
        include DIR_SYS.'/install/template/header.php';
        if ($fc)
        {
            include $ctrl;
        }
        if ($fv)
        {
            include $view;
        }
        include DIR_SYS.'/install/template/footer.php';
    }
}


?>
