<?php

ob_start('ob_gzhandler');
// {{{ defining constants
define('MP_SESSION', 'monphp');
define('MP_GRIDFS', 'monphp');
define('MP_DEBUG', TRUE);
define('DIR_WEB', dirname(__FILE__));
define('DIR_FILE', DIR_WEB . '/file');
define('DIR_MP', dirname(__FILE__) . '/monphp');
define('DIR_SYS', DIR_MP . '/system');
define('DIR_EXT', DIR_MP . '/extension');
define('DIR_CTRL', DIR_MP . '/controller');
define('DIR_LIB', DIR_MP . '/library');
define('DIR_MODULE', DIR_MP . '/module');
define('DIR_TMPL', DIR_MP . '/template');
define('DIR_VIEW', DIR_MP . '/view');
define('MP_VERSION', '0.0.1');
define('MP_SITE_VERSION', '0.0.1');
// }}}
// {{{ disecting the URI
$ru = &$_SERVER['REQUEST_URI'];
$qmp = strpos($ru, '?');
list($path, $params) = $qmp === FALSE
    ? array($ru, NULL)
    : array(substr($ru, 0, $qmp), substr($ru, $qmp + 1));
$parts = explode('/', $path);
$i = 0;
foreach ($parts as $part)
{
    if (strlen($part) && $part !== '..' && $part !== '.')
    {
        define('URI_PART_'.$i++, $part);
    }
}
define('URI_PARAM', isset($params) ? '' : $params);
define('URI_PARTS', $i);
define('URI_PATH', $path);
define('URI_REQUEST', $_SERVER['REQUEST_URI']);
// }}}
// {{{ init
if (MP_DEBUG)
{
    ini_set('display_errors', 1);
    ini_set('html_errors', 1);
    ini_set('xdebug.profiler_output_name', 'trace.$H.' . URI_PATH . '%R');
    ini_set('xdebug.profiler_enable_trigger', 1);
    error_reporting(E_ALL);
}
else
{
    ini_set('display_errors', 0);
    error_reporting(0);
}
include DIR_SYS . '/helper.php';
include DIR_SYS . '/function.php';
spl_autoload_register('mp_autoload');
$tz = MPData::query('_Site', 'time_zone');
date_default_timezone_set(is_null($tz) ? 'America/New_York' : $tz);
// }}}
// {{{ routing 
$installed = !is_null(MPData::query('_System', 'version'));
if (MP_DEBUG || !$installed)
{
    define('WEB_DIR_INSTALL', '/install');
    MPRouter::add(
        '#^' . WEB_DIR_INSTALL . '/([^/]+/)?$#', 
        DIR_SYS . '/install/index.php', 
        MPRouter::ROUTE_PCRE
    );
    MPRouter::add(
        '#^' . WEB_DIR_INSTALL . '/static/([^/]+)/$#', 
        DIR_SYS . '/install/controller/static/${1}.php', 
        MPRouter::ROUTE_PCRE
    );
    if ((URI_PARTS > 1 && '/' . URI_PART_1 !== WEB_DIR_INSTALL) && !MP_DEBUG)
    {
        header('Location: ' . WEB_DIR_INSTALL . '/start/');
        exit;
    }
}
if ($installed)
{
    session_name(MP_SESSION);
    session_start();
    MPModule::h('mpsystem_active');
    MPModule::h('mpsystem_routes');
}

include DIR_SYS.'/config.routes.php';

MPModule::h('mpsystem_start');
if (defined('MP_CTRL'))
{
    include MP_CTRL;
}
elseif ($ctrl = MPRouter::controller()) 
{
    include $ctrl;
}
else
{
    header('HTTP/1.1 404 Not Found');
    if (is_file(DIR_CTRL . '/404.php'))
    {
        include DIR_CTRL . '/404.php';
    }
}
MPModule::h('mpsystem_end');
MPData::save();
// }}}
