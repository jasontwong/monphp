<?php

/**
 * Holds bits of text to be displayed at various areas of the site.
 */
class Snippets
{
    //{{{ constants
    const MODULE_DESCRIPTION = 'Create snippets of text for areas of the site';
    const MODULE_AUTHOR = 'Glenn Yonemitsu';
    const MODULE_DEPENDENCY = '';
    //}}}
    //{{{ constructor
    public function __construct()
    {
    }
    //}}}
    //{{{ public function hook_admin_dashboard()
    public function hook_admin_dashboard()
    {
        $srt = Doctrine::getTable('SnippetRegion');
        $rows = $srt->findAll()->toArray();
        $active = $inactive = array();
        foreach ($rows as $row)
        {
            if ($row['active'])
            {
                $active[$row['name']] = $row;
            }
            else
            {
                $inactive[$row['name']] = $row;
            }
        }

        $snippets['title'] = 'Current Text Snippets';
        $snippets['content'] = '';
        if ($active)
        {
            ksort($active, SORT_REGULAR);
            $snippets['content'] .= '<h3>Active Regions</h3><ul>';
            foreach ($active as $region)
            {
                $title = $region['description'];
                $href = '/admin/module/Snippets/edit/'.$region['id'].'/';
                $name = htmlentities($region['name']);
                //$copy = htmlentities($region['content']);
                $snippets['content'] .= '<li>
                                                <a title="'.$title.'" href="'.$href.'">
                                                    '.$name.'
                                                </a>
                                        </li>';
            }
            $snippets['content'] .= '</ul>';
        }
        if ($inactive)
        {
            ksort($inactive, SORT_REGULAR);
            $snippets['content'] .= '<h3>Inactive Regions</h3><ul>';
            foreach ($inactive as $region)
            {
                $title = $region['description'];
                $href = '/admin/module/Snippets/edit/'.$region['id'].'/';
                $name = htmlentities($region['name']);
                //$copy = $region['copy'];
                $snippets['content'] .= '<li>
                                                <a title="'.$title.'" href="'.$href.'">
                                                    '.$name.'
                                                </a>
                                        </li>';
            }
            $snippets['content'] .= '</ul>';
        }

        return array($snippets);
    }
    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $uri = '/admin/module/Snippets';
        $links = array();
        $links['Snippets'] = array(
            '<a href="'.$uri.'/new/">Add Region</a>',
            '<a href="'.$uri.'/">Edit Region</a>'
        );
        return $links;
    }
    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'Snippets' => array(
                'add region' => 'Add New Snippet Regions',
                'edit region' => 'Edit Snippet Regions',
                'add snippet' => 'Add Snippets',
                'edit snippet' => 'Edit Snippets'
            )
        );
    }
    //}}}
    //{{{ static public function get($name)
    static public function get($name)
    {
        $snippet = Doctrine_Query::create()
                    ->select('*')
                    ->from('SnippetRegion')
                    ->where('name = ?')
                    ->andWhere('active = 1')
                    ->fetchOne(array($name), Doctrine::HYDRATE_ARRAY);
        return $snippet ? $snippet : NULL;
    }
    //}}}
}

?>
