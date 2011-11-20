<?php

class Comment
{
    //{{{ properties
    public static $default_user = '';
    private static $base_uri = '';

    // Store Akismet setting information
    private static $akismet_in_use = FALSE;
    private static $akismet_api_key = '';
    // Static include path for the Akismet this module uses
    private static $akismet_path = 'includes/Akismet.class.php';

    //}}}
    //{{{ constants 
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Comment Module';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = '';

    const UNAPPROVED = 0;
    const APPROVED = 1;
    const NOT_SPAM = 0;
    const SPAM = 1;
    
    //}}}
    //{{{ constructor
    /**
     * @param int $state current state of module manager
     */
    public function __construct()
    {
    }

    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        self::$defaul_user = !is_null(Data::query('Comment', 'default_name'))
            ? Data::query('Comment', 'default_name')
            : 'Anonymous';
        self::$base_uri = empty($_SERVER['HTTPS']) 
            ? 'http://'.$_SERVER['HTTP_HOST'].'/' 
            : 'https://'.$_SERVER['HTTP_HOST'].'/';
        self::$base_uri .= !is_null(Data::query('Comment', 'blog_path'))
            ? Data::query('Comment', 'blog_path')
            : '';
        self::$akismet_api_key = !is_null(Data::query('Comment','akismet_api_key')) 
            ? Data::query('Comment','akismet_api_key') 
            : '';

        // Akismet will be disabled if the api key is not valid
        if (self::$akismet_api_key !== '')
        {
            self::$akismet_in_use = !is_null(Data::query('Comment','akismet_in_use')) 
                ? Data::query('Comment','akismet_in_use') 
                : FALSE;
        }
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        if (URI_PARTS > 3 && URI_PART_2 === 'Comment')
        {
            switch (URI_PART_3)
            {
                case 'manage':
                    return array(
                        'screen' => array(
                            '/admin/static/Comment/comment.css/'
                        )
                    );
                break;
            }
        }

        return array();
    }

    //}}}
    //{{{ public function hook_admin_dashboard()
    public function hook_admin_dashboard()
    {
        if (!User::perm('moderate comments') && !User::perm('edit comments'))
        {
            return array();
        }
        $cet = Doctrine::getTable('CommentEntry');
        $ce = $cet->findPaginated(FALSE, 5, 1, Doctrine::HYDRATE_ARRAY);
        if (count($ce['entries']) === 0)
        {
            return array();
        }
        $title = 'Recent Unapproved Comments';
        $html = '<ul>';
        foreach ($ce['entries'] as $e)
        {
            $snippet = NULL;
            $snippet_array = explode(' ', $e['entry']);
            for ($i = 0; $i < 10; $i++)
            {
                $snippet .= $snippet_array[$i].' ';
            }
            $snippet .=' &hellip;';
            $row[$e['module_name']][] = '<li><a href="/admin/module/Comment/edit_comment/'.$e['id'].'/">'.$snippet.'</a> -'.date('Y-m-d H:s:i', $e['create_date']).'</li>';
        }

        foreach ($row as $mod => $v)
        {
            $html .= '<li><ul>'.$mod;
            foreach ($v as $item)
            {
                $html .= $item;
            }
            $html .= '</li></ul>';
        }

        return array(
            array(
                'title' => $title,
                'content' => $html,
            ),
        );
    }

    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links = array();
        if (User::perm('moderate comments') || User::perm('edit comments'))
        {
            $links['Tools'] = array(
                '<a href="/admin/module/Comment/manage/">Manage Comments</a>'
            );
        }
        if (User::perm('admin') && CMS_DEVELOPER)
        {
            $links['Tools'] = array(
                '<a href="/admin/module/Comment/random/">Add a random comment</a>'
            );
        }
        return $links;
    }

    //}}}
    //{{{ public function hook_data_info()
    public function hook_data_info()
    {
        $items = array(
            array(
                'field' => Field::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Default name for users who comment'
                        )
                    )
                ),
                'type' => 'text',
                'name' => 'default_name',
                'value' => array(
                    'data' => !is_null(Data::query('Comment', 'default_name'))
                        ? Data::query('Comment', 'default_name')
                        : self::$default_user
                )
            ),
            array(
                'field' => Field::layout(
                    'checkbox_boolean',
                    array(
                        'data' => array(
                            'label' => 'Require logged in user?',
                        )
                    )
                ),
                'type' => 'checkbox_boolean',
                'name' => 'user_required',
                'value' => array(
                    'data' => Data::query('Comment', 'user_required')
                )
            ),
            array(
                'field' => Field::layout(
                    'checkbox_boolean',
                    array(
                        'data' => array(
                            'label' => 'Require approved comments?',
                        )
                    )
                ),
                'type' => 'checkbox_boolean',
                'name' => 'approval_required',
                'value' => array(
                    'data' => Data::query('Comment', 'approval_required')
                )
            ),
            array(
                'field' => Field::layout(
                    'checkbox_boolean',
                    array(
                        'data' => array(
                            'label' => 'Use Akismet spam protection?',
                            'description' => 'Setting only works if API key is valid'
                        )
                    )
                ),
                'type' => 'checkbox_boolean',
                'name' => 'akismet_in_use',
                'value' => array(
                    'data' => Data::query('Comment', 'akismet_in_use')
                )
            ),
            array(
                'field' => Field::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Akismet API Key',
                            'description' => 'Get API key at: http://akismet.com/'
                        )
                    )
                ),
                'type' => 'text',
                'name' => 'akismet_api_key',
                'value' => array(
                    'data' => !is_null(Data::query('Comment', 'akismet_api_key'))
                        ? Data::query('Comment', 'akismet_api_key')
                        : ''
                )
            )
        );

        return $items;
    }

    //}}}
    //{{{ public function hook_data_validate($name, $data)
    public function hook_data_validate($name, $data)
    {
        switch ($name)
        {
            case 'akismet_api_key':
                include self::$akismet_path;
                $akismet = new Akismet(self::$base_uri, $data);
                if (!$akismet->isKeyValid())
                {
                    $data = '';
                }
        }
        return array(
            'success' => TRUE,
            'data' => $data
        );
    }

    //}}}
    //{{{ public function hook_search_results($terms)
    public function hook_search_results($terms)
    {
        $query = Doctrine_Query::create()
            ->from('CommentEntry e');
        foreach ($terms as $term)
        {
            $query->orWhere('e.entry LIKE ?', '%'.$term.'%');
        }
        $ces = $query->addWhere('e.status = ?', self::APPROVED)
            ->addWhere('e.spam = ?', self::NOT_SPAM)
            ->orderBy('e.create_date DESC')
            ->fetchArray();
        $data = array();
        foreach ($ces as $ce)
        {
            $title = NULL;
            $snippet = NULL;
            $snippet_array = explode(' ', $ce['entry']);
            for ($i = 0; $i < 30; $i++)
            {
                if ($i < 2)
                {
                    $title .= $snippet_array[$i].' ';
                }
                $snippet .= $snippet_array[$i].' ';
            }
            if ((is_null($title) || strlen($title) === 0) || (is_null($snippet) || strlen($snippet) === 0))
            {
                continue;
            }
            $data[] = array(
                'title' => trim($title),
                'snippet' => trim($snippet),
                'keys' => array(
                    'id' => $ce['id']
                )
            );
        }

        return $data;
    }

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'add comments' => 'Add Comments',
            'edit own comment' => 'Edit Own Comment',
            'edit comments' => 'Edit Comments',
            'moderate comments' => 'Moderate Comments'
        );
    }

    //}}}
    //{{{ public static function add_comment($info)
    /**
     * Ability to add comment via array of data.
     * Key / value pairs must match model.
     *
     * Required keys: module_name, module_entry_id, entry
     *
     * @param   array   $info   an array of comment data to be merged with CommentEntry object
     * @return  mixed id of comment if successful, else boolean FALSE
     */
    public static function add_comment($info)
    {
        $is_spam = FALSE;
        // {{{ akismet validation
        if (self::is_using_akismet())
        {
            include self::$akismet_path;
            $akismet = new Akismet(self::$base_uri, self::$akismet_api_key);
            $akismet->setCommentType('comment');
            $akismet->setCommentAuthorURL($info['user_data']['ip']);
            if (isset($info['user_data']['name']))
            {
                $akismet->setCommentAuthor($info['user_data']['name']);
            }
            if (isset($info['user_data']['email']))
            {
                $akismet->setCommentAuthorEmail($info['user_data']['email']);
            }
            if (isset($info['user_data']['url']))
            {
                $akismet->setCommentAuthorURL($info['user_data']['url']);
            }
            if (isset($info['entry']))
            {
                $akismet->setCommentContent($info['entry']);
            }
            if (isset($info['permalink']))
            {
                $akismet->setPermalink($info['permalink']);
            }
            if($akismet->isCommentSpam())
            {
                // store the comment but mark it as spam (in case of a mis-diagnosis)
                $info['spam'] = self::SPAM;
                $is_spam = TRUE;
            }
        }

        // }}}
        $ce = new CommentEntry();
        $ce->merge($info);
        if ($ce->isValid())
        {
            $ce->save();
            return $ce->id;
        }
        return FALSE;
    }

    //}}}
    //{{{ public static function update_comment($id, $info)
    /**
     * Update a comment
     *
     * @param   int $id id of the comment to be updated
     * @param   array   $info key / value pair to update comment with
     * @return  bool
     */
    public static function update_comment($id, $info)
    {
        $cet = Doctrine::getTable('CommentEntry');
        $ce = $cet->find($id);
        if ($ce !== FALSE)
        {
            $ce->merge($info);
            if ($ce->isValid())
            {
                $ce->save();
                return TRUE;
            }
        }

        return FALSE;
    }

    //}}}
    //{{{ public static function get_comment($id)
    /**
     * Retrieve a specific comment by its id
     *
     * @param   int $id
     * @return  array
     *
     */
    public static function get_comment($id)
    {
        $cet = Doctrine::getTable('CommentEntry');
        $ce = $cet->find($id);
        if ($ce !== FALSE)
        {
            return $ce->toArray();
        }
        return array();
    }

    //}}}
    //{{{ public static function get_comments($module, $entry_id, $spam = self::NOT_SPAM, $status = NULL)
    /**
     * Retrieve a set of comments
     *
     * @param   string  $module optional    name of module associated with the comment
     * @param   int $entry_id   optional    entry id of a specific module
     * @param   int $spam optional    spam constant that you wish to filter by
     * @param   int $status optional status constant that you wish to filter by
     * @return  array
     */
    public static function get_comments($module = NULL, $entry_id = NULL, $spam = NULL, $status = NULL)
    {
        $eq = Doctrine_Query::create()
            ->from('CommentEntry e');
        if (!is_null($module))
        {
            $eq->addWhere('e.module_name = ?', $module);
        }
        if (!is_null($entry_id))
        {
            $eq->addWhere('e.module_entry_id = ?', $entry_id);
        }
        if (!is_null($spam))
        {
            $eq->addWhere('e.spam = ?', $spam);
        }
        if (!is_null($status))
        {
            $eq->addWhere('e.status = ?', $status);
        }
        $ce = $eq->toArray();

        return $ce;
    }

    //}}}
    //{{{ public static function is_login_required()
    /**
     * Checks setting to see if users must be logged in to comment.
     *
     * Default to FALSE.
     *
     * @return bool
     */
    public static function is_login_required()
    {
        return !is_null(Data::query('comment','user_required')) 
            ? Data::query('comment','user_required') 
            : FALSE;
    }

    //}}}
    //{{{ public static function is_approval_required()
    /**
     * Checks setting to see if comments must be approved.
     *
     * Default to FALSE.
     *
     * @return bool
     */
    public static function is_approval_required()
    {
        return !is_null(Data::query('comment','approval_required')) 
            ? Data::query('comment','approval_required') 
            : FALSE;
    }

    //}}}
    //{{{ public static function is_using_akismet()
    /**
     * Returns property $akismet_in_use
     *
     * Default to FALSE.
     *
     * @return bool
     */
    public static function is_using_akismet()
    {
        return self::$akismet_in_use;
    }

    //}}}
}

?>
