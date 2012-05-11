<?php

ob_start('ob_gzhandler');
//{{{ defining constants
define('MONPHP_SESSION', 'monphp');
define('CMS_DEVELOPER', TRUE);
define('WEB_DIR_INSTALL', '/install');
define('DIR_WEB', dirname(__FILE__));
define('DIR_FILE', DIR_WEB . '/file');

define('DIR_MONPHP', dirname(__FILE__) . '/monphp');
define('DIR_SYS', DIR_MONPHP . '/system');
define('DIR_EXT', DIR_MONPHP . '/extension');
define('DIR_CTRL', DIR_MONPHP . '/controller');
define('DIR_LIB', DIR_MONPHP . '/library');
define('DIR_MODEL', DIR_MONPHP . '/model');
define('DIR_MODULE', DIR_MONPHP . '/module');
define('DIR_TMPL', DIR_MONPHP . '/template');
define('DIR_VIEW', DIR_MONPHP . '/view');
define('MONPHP_VERSION', '0 . 5.0');

error_reporting(CMS_DEVELOPER ? E_ALL : 0);
//}}}
//{{{ disecting the URI
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
//}}}
// {{{ init
if (CMS_DEVELOPER)
{
    ini_set('display_errors', 1);
}
require_once DIR_SYS . '/function.php';
require_once DIR_SYS . '/config.database.php';
if (is_file(DIR_SYS . '/config.misc.php'))
{
    include DIR_SYS . '/config.misc.php';
}
spl_autoload_register('monphp_autoload');
$tz = MPData::query('_Site', 'time_zone');
date_default_timezone_set(is_null($tz) ? 'America/New_York' : $tz);
// }}}
//{{{ routing 
$installed = !is_null(MPData::query('_System', 'version'));
if (CMS_DEVELOPER || !$installed)
{
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
    if ((URI_PARTS > 1 && '/' . URI_PART_1 !== WEB_DIR_INSTALL) && !CMS_DEVELOPER)
    {
        header('Location: ' . WEB_DIR_INSTALL . '/start/');
        exit;
    }
}
if ($installed)
{
    session_name(MONPHP_SESSION);
    session_start();
    MPModule::h('active');
    $routes = MPModule::h('routes');
    foreach ($routes as $mod => $rs)
    {
        foreach ($rs as $r)
        {
            MPRouter::add(
                $r[0],
                $r[1],
                deka(MPRouter::ROUTE_STATIC, $r, 2),
                deka(MPRouter::PRIORITY_NORMAL, $r, 3),
                $mod
            );
        }
    }
}

require_once DIR_SYS.'/config.routes.php';

MPModule::h('start');
if ($ctrl = MPRouter::controller()) 
{
    include_once $ctrl;
}
else
{
    header('HTTP/1.1 404 Not Found');
    if (is_file(DIR_CTRL . '/404.php'))
    {
        include_once DIR_CTRL . '/404.php';
    }
}
MPModule::h('end');

MPData::save();
//}}}
