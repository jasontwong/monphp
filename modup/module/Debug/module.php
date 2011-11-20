<?php

class Debug
{
    //{{{ constants
    const MODULE_AUTHOR = '';
    const MODULE_DESCRIPTION = 'Output debugging information';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = '';

    //}}}
    //{{{ properties
    private $controller_start;
    private $controller_end;

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'debug access' => 'Can access debug output',
        );
    }

    //}}}
    //{{{ public function hook_start()
    public function hook_start()
    {
        $this->controller_start = microtime(TRUE);
    }

    //}}}
    //{{{ public function hook_end()
    public function hook_body_end()
    {
        $this->controller_end = microtime(TRUE);

        if (User::perm('admin'))
        {
            include DIR_MODULE.'/Debug/template/debug.php';
        }
    }

    //}}}
}
