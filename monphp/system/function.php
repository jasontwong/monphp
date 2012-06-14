<?php

// Autoloader
//{{{ function mp_autoload($class)
function mp_autoload($class)
{
    $file = DIR_SYS.'/classes/' . $class . '.php';
    if (!class_exists($class) && is_file($file))
    {
        include_once $file;
    }
}
//}}}

// Scripts handler
// {{{ function mp_register_script($handle, $src, $deps = array(), $ver = FALSE, $in_footer = FALSE)
function mp_register_script($handle, $src, $deps = array(), $ver = FALSE, $in_footer = FALSE)
{
    global $_mp;
    if (!eka($_mp, 'scripts', 'registered'))
    {
        $_mp['scripts']['registered'] = array();
    }
    $scripts = &$_mp['scripts']['registered'];
    if (!eka($scripts, $handle))
    {
        $scripts[$handle] = array(
            'src' => $src,
            'deps' => (array)$deps,
            'ver' => $ver,
            'in_footer' => (bool)$in_footer,
        );
    }
}
// }}}
// {{{ function mp_deregister_script($handle)
function mp_deregister_script($handle)
{
    global $_mp;
    if (eka($_mp, 'scripts', 'registered', $handle))
    {
        unset($_mp['scripts']['registered'][$handle]);
    }
    if (eka($_mp, 'scripts', 'enqueued', $handle))
    {
        unset($_mp['scripts']['enqueued'][$handle]);
    }
}
// }}}
// {{{ function mp_enqueue_script($handle, $src, $deps = array(), $ver = FALSE, $in_footer = FALSE)
function mp_enqueue_script($handle, $src, $deps = array(), $ver = FALSE, $in_footer = FALSE)
{
    global $_mp;
    if (!eka($_mp, 'scripts', 'enqueued'))
    {
        $_mp['scripts']['enqueued'] = array();
    }
    $scripts = &$_mp['scripts']['enqueued'];
    if (!eka($scripts, $handle))
    {
        $scripts[$handle] = deka(
            array(
                'src' => $src,
                'deps' => (array)$deps,
                'ver' => $ver,
                'in_footer' => (bool)$in_footer,
            ),
            $_mp, 
            'scripts', 
            'registered', 
            $handle
        );
    }
}
// }}}
// {{{ function mp_dequeue_script($handle)
function mp_dequeue_script($handle)
{
    global $_mp;
    if (eka($_mp, 'scripts', 'enqueued', $handle))
    {
        unset($_mp['scripts']['enqueued'][$handle]);
    }
}
// }}}

// Styles handler
// {{{ function mp_register_style($handle, $src, $deps = array(), $ver = FALSE, $media = 'all')
function mp_register_style($handle, $src, $deps = array(), $ver = FALSE, $media = 'all')
{
    global $_mp;
    if (!eka($_mp, 'styles', 'registered'))
    {
        $_mp['styles']['registered'] = array();
    }
    $styles = &$_mp['styles']['registered'];
    if (!eka($styles, $handle))
    {
        $styles[$handle] = array(
            'src' => $src,
            'deps' => (array)$deps,
            'ver' => $ver,
            'media' => $media,
        );
    }
}
// }}}
// {{{ function mp_deregister_style($handle)
function mp_deregister_style($handle)
{
    global $_mp;
    if (eka($_mp, 'styles', 'registered', $handle))
    {
        unset($_mp['styles']['registered'][$handle]);
    }
    if (eka($_mp, 'styles', 'enqueued', $handle))
    {
        unset($_mp['styles']['enqueued'][$handle]);
    }
}
// }}}
// {{{ function mp_enqueue_style($handle, $src, $deps = array(), $ver = FALSE, $media = 'all')
function mp_enqueue_style($handle, $src, $deps = array(), $ver = FALSE, $media = 'all')
{
    global $_mp;
    if (!eka($_mp, 'styles', 'enqueued'))
    {
        $_mp['styles']['enqueued'] = array();
    }
    $styles = &$_mp['styles']['enqueued'];
    if (!eka($styles, $handle))
    {
        $styles[$handle] = deka(
            array(
                'src' => $src,
                'deps' => (array)$deps,
                'ver' => $ver,
                'media' => $media,
            ),
            $_mp, 
            'styles', 
            'registered', 
            $handle
        );
    }
}
// }}}
// {{{ function mp_dequeue_style($handle)
function mp_dequeue_style($handle)
{
    global $_mp;
    if (eka($_mp, 'styles', 'enqueued', $handle))
    {
        unset($_mp['styles']['enqueued'][$handle]);
    }
}
// }}}
