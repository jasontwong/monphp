<?php

/*
 * Made for Content module. Will be made more generic later on
 *
 */
class Sitemap
{
    //{{{ constants
    const MODULE_DESCRIPTION = 'Create sitemaps';
    const MODULE_AUTHOR = 'Jason Wong';
    const MODULE_DEPENDENCY = 'Content';
    //}}}
    //{{{ public function __construct()
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
    }
    //}}}
    //{{{ public function hook_admin_module_page()
    public function hook_admin_module_page()
    {
    }
    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links['Tools'] = array(
            '<a href="/admin/module/Sitemap/generator/">Sitemap Generator</a>'
        );
        return $links;
    }

    //}}}
}

?>
