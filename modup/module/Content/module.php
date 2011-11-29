<?php

/**
 * Content module 
 * This module handles most of a CMS' needs. It allows for customizing the
 * fields a certain content type should have, which field group it belongs in,
 * categories, and much more.
 * @package Content
 */
class Content
{
    //{{{ constants
    const MODULE_DESCRIPTION = 'The workhorse for the CMS';
    const MODULE_AUTHOR = 'Glenn';
    const MODULE_DEPENDENCY = 'User';
    const ACCESS_DENY = 0;
    const ACCESS_ALLOW = 1;
    const ACCESS_VIEW = 2;
    const ACCESS_EDIT = 3;

    //}}}
    //{{{ properties
    /**
     * @staticvar mixed Content Types
     */
    public static $types = NULL;

    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        $db = new MonDB;
        $db->content_entry->ensureIndex(array('entry_type_id' => 1, 'weight' => 1, 'updated' => -1));
        $db->content_entry->ensureIndex(array('slug' => 1, 'entry_type_id' => 1));
        $db->content_entry_revision->ensureIndex(array('entry_id' => 1, 'revision' => -1));
        $db->content_entry_type->ensureIndex(array('name' => 1));
        $db->content_entry_type->ensureIndex(array('_id' => 1, 'field_groups.weight' => 1, 'field_groups.fields.weight' => 1));
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
        $types = self::get_entry_types();
        $uri = '/admin/module/Content';
        $links = array(
            'Add' => array(),
            'Edit' => array(),
            'Tools' => array()
        );

        if ($types)
        {
            foreach ($types as $type)
            {
                $name = &$type['name'];
                $id = &$type['id'];
                if (User::has_perm('add content entries type', 'add content entries type-'.$id))
                {
                    $links['Add'][] = "<a href='$uri/new_entry/$id/'>$name</a>";
                }
                if (User::has_perm('view content entries type', 'view content entries type-'.$id))
                {
                    $links['Edit'][] = "<a href='$uri/edit_entries/?filter[limit][data]=25&filter[type][data]=$id'>$name</a>";
                }
            }
        }

        if (User::perm('add content type'))
        {
            $links['Tools'][] = "<a href='$uri/new_type/'>New Content Type</a>";
        }
        if (User::perm('edit content type') && $types)
        {
            $links['Tools'][] = "<a href='$uri/edit_types/'>Edit Content Types</a>";
        }
        return $links;
    }

    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        if (strpos(URI_PATH, '/admin/module/Content/') !== FALSE)
        {
            //$js[] = '/file/module/Admin/tiny_mce/jquery.tinymce.js';
            $js[] = '/admin/static/Content/content.js/';
            if (URI_PARTS > 3)
            {
                if (URI_PART_3 === 'new_entry' || URI_PART_3 === 'edit_entry')
                {
                    $js[] = '/admin/static/Content/field.js/';
                }
                if (URI_PART_3 === 'fields')
                {
                    $js[] = '/admin/static/Content/field.type.js/';
                }
            }
        }
        if (strpos(URI_PATH, '/admin/module/Content/edit_entry/') !== FALSE)
        {
            $js[] = '/file/module/Admin/js/jquery/ui/jquery.ui.sortable.js';
        }
        if (strpos(URI_PATH, '/admin/module/Content/edit_entries/') !== FALSE)
        {
            $js[] = '/file/module/Admin/js/jquery/ui/jquery.ui.sortable.js';
            $js[] = '/admin/static/Content/entries.js/';
        }
        return $js;
    }

    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        $css = array();
        if (strpos(URI_PATH, '/admin/module/Content/') !== FALSE)
        {
            $css['screen'][] = '/admin/static/Content/content.css/';
        }
        return $css;
    }

    //}}}
    //{{{ public function hook_admin_dashboard()
    public function hook_admin_dashboard()
    {
        $dashboard_items = array();

        $can_view = User::has_perm('view content', 'view content entries type');
        $can_add = User::has_perm('add content', 'add content entries type');
        $can_edit = User::has_perm('edit content', 'edit content entries type');

        if ($can_edit || $can_view)
        {
            $entries = self::get_latest_entries_created();
            $latest['title'] = 'Latest Content Entries';
            $latest['content'] = '<ul>';
            if (count($entries))
            {
                foreach ($entries as $entry)
                {
                    $href = '/admin/module/Content/edit_entry/'.$entry['id'].'/';
                    $latest['content'] .= '<li><a href="'.$href.'">'.$entry['title'].'</a> <small>added on '.gmdate('m-d-Y', $entry['created']).'</small></li>';
                }
            }
            else
            {
                $latest['content'] .= '<li>None</li>';
            }
            $latest['content'] .= '</ul>';
            $dashboard_items[] = $latest;

            $entries = self::get_most_revised_entries();
            $revised['title'] = 'Most Revised Content Entries';
            $revised['content'] = '<ul>';
            if (count($entries))
            {
                foreach ($entries as $entry)
                {
                    $href = '/admin/module/Content/edit_entry/'.$entry['id'].'/';
                    $revised['content'] .= '<li><a href="'.$href.'">'.$entry['title'].'</a> <small>'.$entry['revisions'].' revisions</small></li>';
                }
            }
            else
            {
                $revised['content'] .= '<li>None</li>';
            }
            $revised['content'] .= '</ul>';
            $dashboard_items[] = $revised;
        }

        if ($can_add || $can_edit)
        {
            $types = self::get_entry_types(
                array(), 
                array('select' => array('id', 'name'))
            );

            if ($can_add)
            {
                $add['title'] = 'Quick Add';
                $add['content'] = '<ul>';
                $add_entries = array();
            }
            if ($can_edit)
            {
                $edit['title'] = 'Filter Entries';
                $edit['content'] = '<ul>';
                $edit_entries = array();
            }

            if (count($types))
            {
                foreach ($types as $type)
                {
                    $id = &$type['id'];
                    $name = &$type['name'];

                    if ($can_add)
                    {
                        $title = 'Add New '.$name;
                        $href = '/admin/module/Content/new_entry/'.$id.'/';
                        $add_entries[] = '<li><a href="'.$href.'">'.$title.'</a></li>';
                    }

                    if ($can_edit)
                    {
                        $title = 'Filter by '.$name;
                        $href = '/admin/module/Content/edit_entries/?filter[type][data]='.$id;
                        $edit_entries[] = '<li><a href="'.$href.'">'.$title.'</a></li>';
                    }
                }
            }

            if ($can_add)
            {
                $add['content'] .= $add_entries ? implode('', $add_entries) : '<li>None</li>';
                $add['content'] .= '</ul>';
                $dashboard_items[] = $add;
            }

            if ($can_edit)
            {
                $edit['content'] .= $edit_entries ? implode('', $edit_entries) : '<li>None</li>';
                $edit['content'] .= '</ul>';
                $dashboard_items[] = $edit;
            }
        }

        return $dashboard_items;
    }

    //}}}
    //{{{ public function hook_rpc($action, $params = NULL)
    /**
     * Implementation of hook_rpc
     *
     * This looks at the action and checks for the method _rpc_<action> and
     * passes the parameters to that. There is no limit on parameters.
     *
     * @param string $action action name
     * @return string
     */
    public function hook_rpc($action)
    {
        $method = '_rpc_'.$action;
        $caller = array($this, $method);
        $args = array_slice(func_get_args(), 1);
        return method_exists($this, $method) 
            ? call_user_func_array($caller, $args)
            : '';
    }

    //}}}
    //{{{ public function hook_settings_fields()
    public function hook_settings_fields()
    {
        $autoslug = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Auto-slug replacement character'
                    )
                )
            ),
            'name' => 'autoslug',
            'type' => 'text',
            'value' => array(
                'data' => Data::query('Content', 'autoslug')
            )
        );

        return array($autoslug);
    }

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        $types = self::get_entry_types();
        $perms_array = array_fill_keys(
            array('type', 'entry_add', 'entry_edit', 'entry_view'),
            array()
        );
        foreach ($types as $type)
        {
            $id = &$type['id'];
            $name = &$type['name'];
            $perms_array['type']['edit content type-'.$id] = 'Edit content type &ldquo;'.$name.'&rdquo;';
            $perms_array['entry_add']['add content entries type-'.$id] = 'Add new content entries for &ldquo;'.$name.'&rdquo; content types';
            $perms_array['entry_edit']['edit content entries type-'.$id] = 'Edit content entries for &ldquo;'.$name.'&rdquo; content types';
            $perms_array['entry_view']['view content entries type-'.$id] = 'View content entries for &ldquo;'.$name.'&rdquo; content types in the admin back end';
        }
        asort($perms_array['type']);
        asort($perms_array['entry_add']);
        asort($perms_array['entry_edit']);
        asort($perms_array['entry_view']);

        $perms['General']['add content type'] = 'Add content types';
        $perms['General']['edit content type'] = 'Edit all content types';
        $perms['General'] = array_merge($perms['General'], $perms_array['type']);
        $perms['General']['add content entries type'] = 'Add new content entries for all content types';
        $perms['General'] = array_merge($perms['General'], $perms_array['entry_add']);
        $perms['General']['edit content entries type'] = 'Edit content entries for all content types';
        $perms['General'] = array_merge($perms['General'], $perms_array['entry_edit']);
        $perms['General']['view content entries type'] = 'View content entries for all content types in the admin back end';
        $perms['General'] = array_merge($perms['General'], $perms_array['entry_view']);

        $module_perms = Module::h('content_perms', Module::TARGET_ALL, $types);
        foreach ($module_perms as $module => $mod_perms)
        {
            $perms = array_merge($perms, $mod_perms);
        }
        foreach ($types as $type)
        {
            $id = &$type['id'];
            $name = '&ldquo;'.$type['name'].'&rdquo;';
            if ($type['ordering'])
            {
                $perms['Ordering']['edit order-'.$id] = 'Manually order entries of content type '.$name;
            }
        }
        return $perms;
    }

    //}}}
    //{{{ public function prep_content_entry_sidebar_edit_process($mod, &$layout, &$entry, &$post)
    public function prep_content_entry_sidebar_edit_process($mod, &$layout, &$entry, &$post)
    {
        $mpost = array();
        foreach ($post as $k => $row)
        {
            $key = strtolower($mod).'__';
            if (strpos($k, $key) === 0)
            {
                $mpost[$k] = $row;
            }
        }
        if ($mpost)
        {
            $layout->merge($mpost);
            $mdata = $layout->acts('post', $mpost);
        }
        return array(
            'data' => array(&$layout, &$entry, $mdata),
            'use_method' => TRUE
        );
    }
    //}}}
    //{{{ public function prep_content_entry_sidebar_new_process($mod, &$layout, &$entry, &$post)
    public function prep_content_entry_sidebar_new_process($mod, &$layout, &$entry, &$post)
    {
        $mpost = array();
        foreach ($post as $k => $row)
        {
            $key = strtolower($mod).'__';
            if (strpos($k, $key) === 0)
            {
                $mpost[$k] = $row;
            }
        }
        if ($mpost)
        {
            $layout->merge($mpost);
            $mdata = $layout->acts('post', $mpost);
        }
        $layout->merge($mdata);
        return array(
            'data' => array(&$layout, &$entry, $mdata),
            'use_method' => TRUE
        );
    }
    //}}}
    //{{{ public function prep_content_edit_type_process($mod, &$layout, &$type, $post)
    public function prep_content_edit_type_process($mod, &$layout, &$type, $post)
    {
        $mpost = array();
        foreach ($post as $k => $row)
        {
            $key = strtolower($mod).'__';
            if (strpos($k, $key) === 0)
            {
                $mpost[$k] = $row;
            }
        }
        if ($mpost)
        {
            $mdata = $layout->acts('post', $mpost);
        }

        return array(
            'data' => array(&$layout, &$type, $mdata),
            'use_method' => TRUE
        );
    }
    //}}}
    //{{{ public function prep_content_new_type_process($mod, &$layout, $type, $post)
    public function prep_content_new_type_process($mod, &$layout, &$type, $post)
    {
        $mpost = array();
        foreach ($post as $k => $row)
        {
            $key = strtolower($mod).'__';
            if (strpos($k, $key) === 0)
            {
                $mpost[$k] = $row;
            }
        }
        if ($mpost)
        {
            $mdata = $layout->acts('post', $mpost);
        }

        return array(
            'data' => array(&$layout, &$type, $mdata),
            'use_method' => TRUE
        );
    }
    //}}}
    //{{{ public function cb_content_edit_type_other_links($links)
    public function cb_content_edit_type_other_links($links)
    {
        $result = array();
        foreach ($links as $link)
        {
            foreach ($link as $l)
            {
                $result[] = $l;
            }
        }
        return $result;
    }
    //}}}
    //{{{ public function cb_content_edit_type_process()
    public function cb_content_edit_type_process()
    {
    }
    //}}}
    //{{{ public function cb_content_new_type_process()
    public function cb_content_new_type_process()
    {
    }
    //}}}
    //{{{ public function cb_content_entry_sidebar_new_process()
    public function cb_content_entry_sidebar_new_process()
    {
    }
    //}}}
    //{{{ public function cb_content_entry_sidebar_edit_process()
    public function cb_content_entry_sidebar_edit_process()
    {
    }
    //}}}
    //{{{ public function cb_content_entry_add_access($access)
    public function cb_content_entry_add_access($access)
    {
        return $access ? max($access) : Content::ACCESS_DENY;
    }
    //}}}
    //{{{ public function cb_content_entry_edit_access($access)
    public function cb_content_entry_edit_access($access)
    {
        return $access ? max($access) : Content::ACCESS_DENY;
    }
    //}}}
    // {{{ public function cb_content_entry_add_finish($meta)
    public function cb_content_entry_add_finish($meta)
    {
        Admin::notify(Admin::TYPE_SUCCESS, 'Successfully created');
    }
    //}}}
    //{{{ public function cb_content_entry_edit_finish($meta)
    public function cb_content_entry_edit_finish($meta)
    {
        Admin::notify(Admin::TYPE_SUCCESS, 'Successfully saved');
    }
    //}}}
    //{{{ public function cb_content_entry_delete_finish($meta)
    public function cb_content_entry_delete_finish($meta)
    {
        Admin::notify(Admin::TYPE_SUCCESS, 'Successfully deleted');
    }
    //}}}
    //{{{ protected function _rpc_order_entries($json)
    protected function _rpc_order_entries($json)
    {
        $data = (array)json_decode($json['data']);
        $ids = array_values($data);
        $type = $json['type'];
        $result['success'] = FALSE;
        try
        {
            $query = array(
                '_id' => array(
                    '$in' => $ids,
                ),
            );
            $cec = MonDB::selectCollection('content_entry');
            $entries = iterator_to_array($cec->find($query));
            foreach ($entries as &$entry)
            {
                $weight = array_search($entry['_id'], $data);
                if (is_numeric($weight))
                {
                    $entry['weight'] = $weight;
                    $cec->save($entry);
                }
            }
            $result['success'] = TRUE;
            $meta['content_entry_type_id'] = $type;
            Module::h('content_order_entries_success', Module::TARGET_ALL, $meta);
        }
        catch (Exception $e)
        {
            $result['success'] = FALSE;
        }

        return json_encode($result);
    }

    //}}}

    // API
    /**
     * Every API get_ method has a $spec parameter which provides additional 
     * info for the method. Essentially it's even more parameters into one 
     * array. Each method should start with an array_merge($defaults, $spec) to
     * have all the spec parameters prepared. More complex API methods might not
     * have a $spec array due to forming multiple DQL objects
     * (ex. entry type details).
     *
     * The column values should always have the shortcut table initials:
     *      ex. ety.id, ety.name
     *
     * The letters match the first letter of each word in the table name 
     * (sans "content") and if any word has matching letters, it is required to
     * add consecutive letters until it is enough to distinguish it. In the 
     * above example, ety is for content_entry_type. It needs the "ty" since the
     * additional letter "y" helps distinguish it from content_entry_title.
     *
     * So tables without the need will be two letters.
     *      ex. content_field_meta is "fm"
     *
     * They make use of the dql_exec() function which has parameters ordered
     * $spec, $param. But these methods will have the order $param, $spec. The
     * reason is the specs will not likely change much, and the parameters are
     * more likely used.
     *
     * If the method has a _by_ in the name, then the $param parameter will be
     * replaced with a more directly defined parameter such as an id integer
     * instead of a generic array for the DQL where clauses.
     *
     * Content module now uses the caching mechanism. But only caches:
     *      - all entries of a type in an array
     *      - all entries of a type, one cache entry each
     *      - all entries' id, slug, and title in an array
     * Caching is enabled with the $use_cache parameter, but this parameter 
     * only exists for methods that return results like those listed above.
     */

    //{{{ public function get_entry_slug_id($type, $slug, $use_cache = TRUE, $expire = 0)
    /**
     * Gets the row from the id/slug provided from self::get_entries_slugs()
     */
    public function get_entry_slug_id($type, $slug, $use_cache = TRUE, $expire = 0)
    {
        $ids_slugs = Content::get_entries_slugs($type);
        foreach ($ids_slugs as $id_slug)
        {
            if ($id_slug['slug'] === $slug)
            {
                return $id_slug;
            }
        }
        return NULL;
    }
    //}}}
    //{{{ public function get_entries_slugs($type = NULL, $use_cache = TRUE, $expire = 0)
    public function get_entries_slugs($type = NULL, $use_cache = TRUE, $expire = 0)
    {
        if (!is_null($type) && $use_cache)
        {
            $mapping = Cache::get($type.' - ids slugs', 'Content');
            $has_cache = !is_null($mapping);
        }
        if (!$has_cache || is_null($type))
        {
            $db = new MonDB;
            $query = array();
            if (!is_null($type))
            {
                $cet = $db->content_entry_type->findOne(array('name' => $type), array('_id'));
                $query['entry_type_id'] = $cet['_id'];
            }
            else
            {
                $cet = $db->content_entry_type->find(array(), array('_id'));
                $ids = array();
                foreach ($cet as $et)
                {
                    $ids[] = $et['_id'];
                }
                $query['entry_type_id'] = array('$in' => $ids);
            }
            $entries = $db->content_entry->find($query, array('_id', 'title', 'slug'));
            $mapping = iterator_to_array($entries);
        }
        if ($use_cache && !$has_cache)
        {
            Cache::set($type.' - ids slugs', $mapping, $expire, 'Content');
        }
        return $mapping;
    }
    //}}}
    //{{{ public function get_entries_by_type_name($name)
    public function get_entries_by_type_name($name)
    {
        $entries = array();

        /*
        $type = Doctrine_Query::create()
                ->select('et.id, et.ordering')
                ->from('ContentEntryType et')
                ->where('et.name = ?')
                ->fetchOne(array($name));
        $dt_dql = Doctrine_Query::create()
                    ->select('
                        em.id, em.created, em.revision, em.weight,
                        eti.title as title, eti.slug as slug, 
                        eti.modified as modified
                    ')
                    ->from('ContentEntryMeta em')
                    ->leftJoin('em.ContentEntryTitle eti')
                    ->where('em.content_entry_type_id = ?')
                    ->andWhere('eti.revision = em.revision')
                    ->orderBy($type->ordering
                                ? 'em.weight ASC'
                                : 'eti.modified DESC');
        $rows = $dt_dql->execute(array($type['id']), Doctrine::HYDRATE_ARRAY);
        $ei = 0;
        foreach ($rows as $row)
        {
            $entries[$ei]['entry'] = $row;
            ++$ei;
        }
        */

        return $entries;
    }
    //}}}
    //{{{ public function get_entries_details_by_type_id($id, $spec = array(), $use_cache = TRUE, $expire = 0)
    /**
     * Returns multiple entries. Data set format is like get_entry_details
     */
    public function get_entries_details_by_type_id($id, $spec = array(), $use_cache = TRUE, $expire = 0)
    {
        $dt_dspec = array(
            'select' => array(
                'ety.id', 'ety.name'
            ),
            'from' => 'ContentEntryType ety',
            'where' => 'ety.id = ?'
        );
        $dt_spec = array_merge($dt_dspec, $spec);
        $types = dql_exec($dt_spec, array($id));
        $type = $types[0]['name'];
        return self::get_entries_details_by_type_name($type, $use_cache, $expire);
    }
    //}}}
    //{{{ public function get_entries_details_by_type_name($name, $use_cache = TRUE, $expire = 0)
    public function get_entries_details_by_type_name($name, $use_cache = TRUE, $expire = 0)
    {
        if ($use_cache)
        {
            $entries = Cache::get($name.' - entries', 'Content');
            $has_cache = !is_null($entries);
        }
        if (!$use_cache || !$has_cache)
        {
            $entries = array();

            /*
            $type = Doctrine_Query::create()
                    ->from('ContentEntryType et')
                    ->where('et.name = ?')
                    ->fetchOne(array($name));
                    //->fetchOne(array($name), Doctrine::HYDRATE_ARRAY);
            $dt_dql = Doctrine_Query::create()
                        ->select('
                            em.id, em.created, em.revision, em.weight,
                            eti.title as title, eti.slug as slug, 
                            eti.modified as modified, em.content_entry_type_id as type_id
                        ')
                        ->from('ContentEntryMeta em')
                        ->leftJoin('em.ContentEntryTitle eti')
                        ->where('em.content_entry_type_id = ?')
                        ->andWhere('eti.revision = em.revision')
                        ->orderBy($type->ordering
                                    ? 'em.weight ASC'
                                    : 'eti.modified DESC');
            $rows = $dt_dql->execute(array($type['id']), Doctrine::HYDRATE_ARRAY);
            //$rows = $dt_dql->execute(array($type->id));
            $entries_map = array();
            $ei = 0;
            foreach ($rows as $row)
            {
                $entries[$ei]['entry'] = $row;
                $entries_map[$row['id']] = $ei;
                //$entries_map[$row->id] = $ei;
                ++$ei;
            }

            $data = array();
            $df_dql = Doctrine_Query::create()
                        ->select('
                            fd.cdata, fd.bdata, fd.akey, fd.meta, 
                            ft.name as name, ft.multiple as multiple,
                            ft.id as type_id, ft.type as type_type,
                            fm.id as meta_id, fm.name as meta_name,
                            em.id as entry_meta_id
                        ')
                        ->from('ContentFieldData fd')
                        ->leftJoin('fd.ContentEntryMeta em')
                        ->leftJoin('em.ContentEntryType ety')
                        ->leftJoin('fd.ContentFieldMeta fm')
                        ->leftJoin('fm.ContentFieldType ft')
                        ->where('ety.name = ?')
                        ->andWhere('em.revision = fd.revision');
            $field_rows = $df_dql->execute(array($name), Doctrine::HYDRATE_ARRAY);
            //$field_rows = $df_dql->execute(array($name));

            $field_data_raw = array();
            $field_types = array();
            foreach ($field_rows as $row)
            {
                // $field_data_raw[$row->entry_meta_id][$row->type_id][$row->meta_name][$row->akey][] = $row;
                // $field_types[$row->entry_meta_id][$row->type_id] = array(
                    // 'type' => $row->type_type, 
                    // 'multiple' => (bool)$row->multiple,
                    // 'name' => $row->name,
                // );
                if (!is_null($row['type_id']))
                {
                    if (!is_null($row['type_id']))
                    {
                        $field_data_raw[$row['entry_meta_id']][$row['type_id']][$row['meta_name']][$row['akey']][] = $row;
                        $field_types[$row['entry_meta_id']][$row['type_id']] = array(
                            'type' => $row['type_type'], 
                            'multiple' => (bool)$row['multiple'],
                            'name' => $row['name'],
                        );
                    }
                }
            }

            foreach ($field_types as $entry_meta_id => $field_type)
            {
                $data = array();
                foreach ($field_type as $type_id => $type_info)
                {
                    $field_data = Field::quick_act('read', $type_info['type'], $field_data_raw[$entry_meta_id][$type_id]);
                    $temp = array();
                    if ($type_info['multiple'])
                    {
                        foreach ($field_data as $mkey => $fd)
                        {
                            foreach ($fd as $akey => $cdata)
                            {
                                $temp[$akey][$mkey] = $cdata;
                            }
                        }
                    }
                    else
                    {
                        foreach ($field_data as $mkey => $fd)
                        {
                            $temp[$mkey] = array_pop($fd);
                        }
                    }
                    $data[$type_info['name']] = $temp;
                }
                $entries[$entries_map[$entry_meta_id]]['data'] = $data;
            }
            */
        }
        if ($use_cache && !$has_cache)
        {
            Cache::set($name.' - entries', $entries, $expire, 'Content');
        }

        return $entries;
    }
    //}}}
    //{{{ public function get_entries_details
    /**
     * This tries to be very minimal, getting as little info as needed.
     */
    public function get_entries_details($params = array(), $spec = array())
    {
        $entry = array();

        $dt_dspec = array(
            'select' => array(
                'em.id', 'em.created', 'em.revision',
                'em.weight', 
                'eti.title as title', 'eti.slug as slug', 
                'eti.modified as modified'
            ),
            'from' => 'ContentEntryMeta em',
            'leftJoin' => 'em.ContentEntryTitle eti',
            'where' => 'em.id IN ?',
            'andWhere' => 'eti.revision = em.revision'
        );
        $dt_params =& $params;
        $dt_spec = array_merge($dt_dspec, $spec);
        $entries = dql_exec($dt_spec, $dt_params);

        $results = array();
        foreach ($entries as &$entry)
        {
            $result = array(
                'entry' => array(
                    'id' => $entry['id'],
                    'created' => $entry['created'],
                    'revision' => $entry['revision'],
                    'slug' => $entry['slug'],
                    'title' => $entry['title'],
                    'modified' => $entry['modified'],
                    'weight' => $entry['weight']
                ),
                'data' => self::get_field_data_by_entry_id_and_revision(
                    $entry['id'],
                    $entry['revision']
                )
            );
            $results[] = $result;
        }
        return $results;
    }
    //}}}
    //{{{ public function get_entries_details_by_ids($ids, $use_cache = TRUE, $expire = 0)
    public function get_entries_details_by_ids($ids, $use_cache = TRUE, $expire = 0)
    {
        if ($use_cache)
        {
            $entries = array();
            foreach ($ids as $id)
            {
                $entries[] = self::get_entry_details_by_id($id, TRUE, $expire);
            }
        }
        else
        {
            $entries = self::get_entries_details(array($ids), array('where' => 'em.id IN ?'));
        }
        return $entries;
    }
    //}}}
    //{{{ public function get_entry_details
    /**
     * This tries to be very minimal, getting as little info as needed.
     */
    public function get_entry_details($params = array(), $spec = array())
    {
        $entry = array();

        $dt_dspec = array(
            'select' => array(
                'em.id', 'em.created', 'em.revision',
                'eti.title as title', 'eti.slug as slug', 
                'eti.modified as modified'
            ),
            'from' => 'ContentEntryMeta em',
            'leftJoin' => 'em.ContentEntryTitle eti',
            'where' => 'em.id = ?',
            'andWhere' => 'eti.revision = em.revision'
        );
        $dt_params =& $params;
        $dt_spec = array_merge($dt_dspec, $spec);
        $entry['entry'] = array_pop(dql_exec($dt_spec, $dt_params));

        $entry['data'] = self::get_field_data_by_entry_id_and_revision(
            $entry['entry']['id'],
            $entry['entry']['revision']
        );
        return $entry;
    }
    //}}}
    //{{{ public function get_entry_details_by_id($id, $use_cache = TRUE, $expire = 0)
    public function get_entry_details_by_id($id, $use_cache = TRUE, $expire = 0)
    {
        if ($use_cache)
        {
            $entry = Cache::get('entry:'.$id, 'Content');
            if (is_null($entry))
            {
                $entry = self::get_entry_details(array($id), array('where' => 'em.id = ?'));
                Cache::set('entry:'.$id, $entry, $expire, 'Content');
            }
        }
        else
        {
            $entry = self::get_entry_details(array($id), array('where' => 'em.id = ?'));
        }
        return $entry;
    }
    //}}}
    //{{{ public function get_entry_details_by_slug_and_type_id($slug, $type_id, $use_cache = TRUE, $expire = 0)
    public function get_entry_details_by_slug_and_type_id($slug, $type_id, $use_cache = TRUE, $expire = 0)
    {
        $dt_dspec = array(
            'select' => array(
                'ety.id', 'ety.name'
            ),
            'from' => 'ContentEntryType ety',
            'where' => 'ety.id = ?'
        );
        $types = dql_exec($dt_spec, array($id));
        $type = $types[0]['name'];
        return self::get_entry_details_by_slug_and_type_name($slug, $type, $use_cache, $expire);
    }
    //}}}
    //{{{ public function get_entry_details_by_slug_and_type_name($slug, $type, $use_cache = TRUE, $expire = 0)
    public function get_entry_details_by_slug_and_type_name($slug, $type, $use_cache = TRUE, $expire = 0)
    {
        if ($use_cache)
        {
            $entry_slug = self::get_entry_slug_id($type, $slug, TRUE);
            $entry = Cache::get('entry:'.$entry_slug['id'], 'Content');
            $has_cache = !is_null($entry['entry']);
        }
        if (!$use_cache || !$has_cache)
        {
            /*
            $dql = Doctrine_Query::create()
                    ->select('
                        em.id as id, em.created as created, em.revision as revision,
                        eti.title, eti.slug, eti.modified
                    ')
                    ->from('ContentEntryTitle eti')
                    ->leftJoin('eti.ContentEntryMeta em')
                    ->leftJoin('em.ContentEntryType ety')
                    ->where('eti.slug = ?', $slug)
                    ->andWhere('ety.name = ?', $type)
                    ->andWhere('eti.revision = em.revision');

            $entry['entry'] = array_pop($dql->execute(array(), Doctrine::HYDRATE_ARRAY));
            $entry['data'] = self::get_field_data_by_entry_id_and_revision(
                $entry['entry']['id'],
                $entry['entry']['revision']
            );
            */
        }
        if ($use_cache && !$has_cache)
        {
            Cache::set('entry:'.$entry_slug['id'], $entry, $expire, 'Content');
        }

        return $entry;
    }
    //}}}
    //{{{ public function get_entry_type($params = array(), $spec = array())
    public function get_entry_type($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array(
                'ety.id', 'ety.name', 'ety.description', 
                'ety.ordering', 'ety.stack'
            ),
            'from' => 'ContentEntryType ety',
            'where' => 'ety.id = ?'
        );
        $s = array_merge($dspec, $spec);
        return array_pop(dql_exec($s, $params));
    }
    //}}}
    //{{{ public function get_entry_type_by_entry_id($id, $spec = array())
    public function get_entry_type_by_entry_id($id, $spec = array())
    {
        $dspec = array(
            'select' => array(
                'em.id',
                'ety.id as entry_type_id', 
                'ety.name as name', 'ety.description as description',
                'ety.ordering as ordering', 'ety.stack as stack'
            ),
            'from' => 'ContentEntryMeta em',
            'leftJoin' => 'em.ContentEntryType ety',
            'where' => 'em.id = ?'
        );
        $s = array_merge($dspec, $spec);
        return array_pop(dql_exec($s, array($id)));
    }
    //}}}
    //{{{ public function get_entry_type_by_id($id, $spec = array())
    public function get_entry_type_by_id($id, $spec = array())
    {
        $dspec = array('where' => 'ety.id = ?');
        return self::get_entry_type(array($id), array_merge($dspec, $spec));
    }
    //}}}
    //{{{ public function get_entry_type_by_name($name, $spec = array())
    public function get_entry_type_by_name($name, $spec = array())
    {
        $dspec = array('where' => 'ety.name = ?');
        return self::get_entry_type(array($name), array_merge($dspec, $spec));
    }
    //}}}
    //{{{ public function get_entry_type_details_by_id($id)
    public function get_entry_type_details_by_id($id)
    {
        $details = array();
        $details['type'] = self::get_entry_type_by_id($id);
        $details['fields'] = self::get_entry_type_fields_by_id($id);
        return $details;
    }
    //}}}
    //{{{ public function get_entry_type_details_by_name($name)
    public function get_entry_type_details_by_name($name)
    {
        $details = array();
        $details['type'] = self::get_entry_type_by_name($name);
        $details['fields'] = self::get_entry_type_fields_by_name($name);
        return $details;
    }
    //}}}
    //{{{ public function get_entry_type_fields($params = array(), $spec = array())
    public function get_entry_type_fields($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array(
                'fg.id', 'fg.weight', 'fg.name', 
            ),
            'from' => 'ContentFieldGroup fg',
            'where' => 'fg.id = ?',
            'orderBy' => 'fg.weight asc'
        );
        $s = array_merge($dspec, $spec);
        $groups = dql_exec($s, $params);
        $type['groups'] = $groups;
        $group_ids = array();
        $group_map = array();
        foreach ($type['groups'] as &$group)
        {
            $gid = $group['id'];
            $group_ids[] = $gid;
            $group_map[$gid] = &$group;
            $group['fields'] = array();
        }
        $fields = self::get_field_group_details(array($group_ids));
        foreach ($fields as $field)
        {
            $gid = $field['content_field_group_id'];
            $group_map[$gid]['fields'][] = $field;
        }
        return $type;
    }
    //}}}
    //{{{ public function get_entry_type_fields_by_id($id, $spec = array())
    public function get_entry_type_fields_by_id($id, $spec = array())
    {
        $dspec = array('where' => 'fg.content_entry_type_id = ?');
        return self::get_entry_type_fields(array($id), array_merge($dspec, $spec));
    }
    //}}}
    //{{{ public function get_entry_type_fields_by_name($name, $spec = array())
    public function get_entry_type_fields_by_name($name, $spec = array())
    {
        $dspec = array('where' => 'fg.name = ?');
        return self::get_entry_type_fields(array($name), array_merge($dspec, $spec));
    }
    //}}}
    //{{{ public function get_entry_types($params = array(), $spec = array())
    /**
     * Gets entry types
     */
    public function get_entry_types($params = array(), $spec = array())
    {
        /*
        $dspec = array(
            'select' => array(
                'ety.id', 'ety.name', 'ety.description', 
                'ety.ordering', 'ety.stack'
            ),
            'from' => 'ContentEntryType ety',
            'orderBy' => 'ety.name asc',
            'limit' => 0,
            'offset' => 0
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, $params);
        */
        return array();
    }
    //}}}
    //{{{ public function get_field_data_by_entry_id_and_revision($id, $revision)
    public function get_field_data_by_entry_id_and_revision($id, $revision)
    {
        $data = array();
        /*
        $df_dql = Doctrine_Query::create()
                    ->select('
                        fd.cdata, fd.bdata, fd.akey, fd.meta, 
                        ft.name as name, ft.multiple as multiple,
                        ft.id as type_id, ft.type as type_type,
                        fm.id as meta_id, fm.name as meta_name
                    ')
                    ->from('ContentFieldData fd')
                    ->leftJoin('fd.ContentFieldMeta fm')
                    ->leftJoin('fm.ContentFieldType ft')
                    ->where('fd.content_entry_meta_id = ?', $id)
                    ->andWhere('fd.revision = ?', $revision);
        $field_rows = $df_dql->execute()->toArray();

        $field_data_raw = array();
        $field_types = array();
        foreach ($field_rows as $row)
        {
            if (!is_null($row['type_id']))
            {
                $field_data_raw[$row['type_id']][$row['meta_name']][$row['akey']][] = $row;
                $field_types[$row['type_id']] = array(
                    'type' => $row['type_type'], 
                    'multiple' => (bool)$row['multiple'],
                    'name' => $row['name']
                );
            }
        }

        foreach ($field_types as $type_id => $type_info)
        {
            $field_data = Field::quick_act('read', $type_info['type'], $field_data_raw[$type_id]);
            $temp = array();
            if ($type_info['multiple'])
            {
                foreach ($field_data as $mkey => $fd)
                {
                    foreach ($fd as $akey => $cdata)
                    {
                        $temp[$akey][$mkey] = $cdata;
                    }
                }
            }
            else
            {
                foreach ($field_data as $mkey => $fd)
                {
                    $temp[$mkey] = array_pop($fd);
                }
            }
            $data[$type_info['name']] = $temp;
        }
        */

        return $data;
    }
    //}}}
    //{{{ public function get_field_details_by_id($id)
    public function get_field_details_by_id($id)
    {
        $field = array();
        $field['type'] = self::get_field_type_by_id(
            $id,
            array(
                'select' => array(
                    'ft.id', 'ft.name', 'ft.type', 'ft.weight',
                    'ft.multiple', 'ft.description',
                    'fg.id as content_field_group_id', 
                    'fg.content_entry_type_id as content_entry_type_id'
                ),
                'leftJoin' => 'ft.ContentFieldGroup fg'
            )
        );
        $field['meta'] = self::get_field_meta_by_type_id($id);
        return $field;
    }
    //}}}
    //{{{ public function get_field_details_by_entry_type_name($name)
    public function get_field_details_by_entry_type_name($name)
    {
        $tree = array();
        /*
        $dql = Doctrine_Query::create()
               ->from('ContentFieldMeta fm')
               ->leftJoin('fm.ContentFieldType ft')
               ->leftJoin('ft.ContentFieldGroup fg')
               ->leftJoin('fg.ContentEntryType et')
               ->where('et.name = ?');
        $rows = $dql->execute(array($name), Doctrine::HYDRATE_ARRAY);
        $details = array();
        $groups = array();
        $types = array();
        foreach ($rows as $row)
        {
            $field_type = $row['ContentFieldType'];
            $field_group = $row['ContentFieldType']['ContentFieldGroup'];
            $group_name = $field_group['name'];
            if (!eka($tree, $group_name))
            {
                unset($field_group['ContentEntryType']);
                $tree[$group_name] = $field_group;
                $tree[$group_name]['fields'] = array();
            }
            if (!eka($tree, $group_name, 'fields', $field_type['id']))
            {
                unset($field_type['ContentFieldGroup']);
                $tree[$group_name]['fields'][$field_type['id']] = $field_type;
                $tree[$group_name]['fields'][$field_type['id']]['meta'] = array();
            }
            $tree[$group_name]['fields'][$field_type['id']]['meta'][$row['name']] = $row;
            unset($tree[$group_name]['fields'][$field_type['id']]['meta'][$row['name']]['ContentFieldType']);
        }
        */
        return $tree;
    }
    //}}}
    //{{{ public function get_field_group($params = array(), $spec = array())
    public function get_field_group($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array('fg.id', 'fg.name', 'fg.weight'),
            'from' => 'ContentFieldGroup fg',
            'where' => 'fg.id = ?'
        );
        $s = array_merge($dspec, $spec);
        return array_pop(dql_exec($s, $params));
    }
    //}}}
    //{{{ public function get_field_group_by_id($id, $spec = array())
    public function get_field_group_by_id($id, $spec = array())
    {
        $dspec = array('where' => 'fg.id = ?');
        return self::get_field_group(array($id), array_merge($dspec, $spec));
    }
    //}}}
    //{{{ public function get_field_group_by_type_id($id, $spec = array())
    public function get_field_group_by_type_id($id, $spec = array())
    {
        $dspec = array(
            'select' => array('fg.id', 'fg.name', 'fg.weight'),
            'from' => 'ContentFieldGroup fg',
            'where' => 'fg.content_entry_type_id = ?'
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, array($id));
    }
    //}}}
    //{{{ public function get_field_group_details($params = array(), $spec = array())
    public function get_field_group_details($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array(
                'ft.id', 'ft.name', 'ft.type', 'ft.content_field_group_id',
                'ft.weight', 'ft.multiple', 'ft.description'
            ),
            'from' => 'ContentFieldType ft',
            'where' => 'ft.content_field_group_id IN ?',
            'orderBy' => array(
                'ft.content_field_group_id asc', 'ft.weight asc', 'ft.name asc'
            )
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, $params);
    }
    //}}}
    //{{{ public function get_field_meta($params = array(), $spec = array())
    public function get_field_meta($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array(
                'fm.id', 'fm.name', 'fm.label', 'fm.required',
                'fm.meta', 'fm.default_data'
            ),
            'from' => 'ContentFieldMeta fm'
        );
        $nspec = array_merge($dspec, $spec);
        $metas = dql_exec($nspec, $params);
        return $metas;
    }
    //}}}
    //{{{ public function get_field_meta_by_type_id($id, $spec = array())
    public function get_field_meta_by_type_id($id, $spec = array())
    {
        $dspec = array('where' => 'fm.content_field_type_id = ?');
        $nspec = array_merge($dspec, $spec);
        $param = array($id);
        $metas = array();
        $rows = self::get_field_meta($param, $nspec);
        foreach ($rows as $row)
        {
            $metas[$row['name']] = $row;
        }
        return $metas;
    }
    //}}}
    //{{{ public function get_field_type($params = array(), $spec = array())
    public function get_field_type($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array(
                'ft.id', 'ft.name', 'ft.type', 'ft.weight',
                'ft.multiple', 'ft.description'
            ),
            'from' => 'ContentFieldType ft'
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, $params);
    }
    //}}}
    //{{{ public function get_field_type_by_id($id, $spec = array())
    public function get_field_type_by_id($id, $spec = array())
    {
        $dspec = array('where' => 'ft.id = ?');
        $nspec = array_merge($dspec, $spec);
        $param = array($id);
        $type = array_pop(self::get_field_type($param, $nspec));
        return $type;
    }
    //}}}
    //{{{ public function get_latest_entries_created($params = array(), $spec = array())
    public function get_latest_entries_created($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array(
                'em.id as id', 'em.created as created', 'eti.modified',
                'eti.title', 'eti.slug'
            ),
            'from' => 'ContentEntryTitle eti',
            'leftJoin' => 'eti.ContentEntryMeta em',
            'where' => 'eti.revision = em.revision',
            'orderBy' => array('em.created asc', 'eti.title asc'),
            'limit' => 10,
            'offset' => 0
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, $params);
    }
    //}}}
    //{{{ public function get_latest_entries_modified($params = array(), $spec = array())
    public function get_latest_entries_modified($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array(
                'em.id as id', 'em.created as created', 'eti.modified',
                'eti.title', 'eti.slug'
            ),
            'from' => 'ContentEntryTitle eti',
            'leftJoin' => 'eti.ContentEntryMeta em',
            'where' => 'eti.revision = em.revision',
            'orderBy' => array('eti.modified desc', 'eti.title asc'),
            'limit' => 10,
            'offset' => 0
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, $params);
    }
    //}}}
    //{{{ public function get_most_revised_entries($params = array(), $spec = array())
    public function get_most_revised_entries($params = array(), $spec = array())
    {
        $dspec = array(
            'select' => array('em.id as id', 'em.revisions as revisions', 'eti.title'),
            'from' => 'ContentEntryTitle eti',
            'leftJoin' => 'eti.ContentEntryMeta em',
            'where' => 'eti.revision = em.revision',
            'orderBy' => array('em.revisions desc', 'eti.title asc'),
            'limit' => 10,
            'offset' => 0
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, $params);
    }
    //}}}
    //{{{ public function get_entries_titles_by_type_and_field_name($type, $field_name, $field_search)
    public function get_entries_titles_by_type_and_field_name($type, $field_name, $field_search)
    {
        /*
        $dql = Doctrine_Query::create()
               ->select('
                    fd.id, em.id, et.id, fm.id, ft.id,
                    fd.cdata,
                    ft.name as field_name,
                    et.title as title,
                    et.slug as slug,
                    et.modified as modified,
                    em.created as created
                ')
               ->from('ContentFieldData fd')
               ->leftjoin('fd.ContentFieldMeta fm')
               ->leftjoin('fm.ContentFieldType ft')
               ->leftjoin('fd.ContentEntryMeta em')
               ->leftjoin('em.ContentEntryTitle et')
               ->where('fd.revision = em.revision')
               ->andWhere('et.revision = em.revision')
               ->andWhere('ft.name = ?', $field_name);
        if (is_array($field_search))
        {
            $dql->andWhereIn('fd.cdata', $field_search);
        }
        else
        {
            $dql->andWhere('fd.cdata = ?', $field_search);
        }
        return $dql->execute(array(), Doctrine::HYDRATE_ARRAY);
        */
        return array();
    }

    //}}}

    /**
     * search_ API methods
     *
     * Similar to get_ API methods, except the where clauses use the LIKE 
     * operator instead of =.
     */
    //{{{ public function search_entry_title_by_title($title, $spec = array())
    public function search_entry_title_by_title($title, $spec = array())
    {
        $dspec = array(
            'select' => array(
                'eti.id', 'eti.modified', 'eti.title', 'eti.slug'
            ),
            'from' => 'ContentEntryTitle eti',
            'leftJoin' => 'eti.ContentEntryMeta em',
            'where' => 'eti.title LIKE ?',
            'andWhere' => 'eti.revision = em.revision'
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, array($title));
    }

    //}}}

    /**
     * save_ API methods
     *
     * These methods accept different number of parameters. Unlike the get_ API
     * methods, these use the model classes due to potential pre and post hooks
     * as well as data formatting specified in the model itself. If an id array
     * key exists then it is treated as an update.
     */
    //{{{ public function save_entry($entry)
    public function save_entry($entry)
    {
        /*
        $cem = new ContentEntryMeta;
        $cem->content_entry_type_id = $entry['meta']['content_entry_type_id'];
        $cem->save();

        $ceti = new ContentEntryTitle;
        $ceti->merge($entry['entry']);
        $ceti->content_entry_meta_id = $cem['id'];
        $ceti->save();

        if (!empty($entry['data']))
        {
            $type_ids = array_keys($entry['data']);
            $params = array($type_ids);
            $specs = array(
                'select' => array(
                    'fm.id', 'fm.name', 'fm.required', 
                    'fm.content_field_type_id'
                ),
                'where' => 'fm.content_field_type_id IN ?'
            );
            $fields = array();
            $fields_rows = self::get_field_meta($params, $specs);
            foreach ($fields_rows as $field_row)
            {
                $field_type_id = $field_row['content_field_type_id'];
                $field_meta_name = $field_row['name'];
                $fields[$field_type_id][$field_meta_name] = $field_row;
            }

            $data_rows = new Doctrine_Collection('ContentFieldData');
            $di = 0;
            foreach ($entry['data'] as $type_id => $field_metas)
            {
                foreach ($field_metas as $meta_name => $field_datas)
                {
                    if ($field_datas)
                    {
                        foreach ($field_datas as $field_data)
                        {
                            if (eka($fields, $type_id, $meta_name))
                            {
                                $data_rows[$di]->akey = $field_data['akey'];
                                $data_rows[$di]->cdata = $field_data['cdata'];
                                $data_rows[$di]->meta = array();
                                $data_rows[$di]->content_entry_meta_id = $cem['id'];
                                $data_rows[$di]->content_field_meta_id = $fields[$type_id][$meta_name]['id'];
                                ++$di;
                            }
                        }
                    }
                }
            }
            $data_rows->save();
        }
        return $cem['id'];
        /*
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        foreach ($data as $type_id => $fm)
        {
            foreach ($fm as $key => $fd)
            {
                $meta = $cfmt->findByNameAndType($key, $type_id);
                if (!eka($meta, 0, 'id'))
                {
                    continue;
                }
                $field_id = $meta[0]['id'];
                foreach ($fd as $row)
                {
                    $field_data = new ContentFieldData;
                    $field_data->merge($row);
                    $field_data->content_entry_meta_id = $entry_id;
                    $field_data->content_field_meta_id = $field_id;
                    $field_data->revision = $revision;
                    $field_data->meta = (array)$field_data->meta;
                    $field_data->save();
                }
            }
        }
        */
        return array();
    }
    //}}}
    //{{{ public function save_entry_type($entry_type)
    public function save_entry_type($entry_type)
    {
        $type = new ContentEntryType;
        if (eka($entry_type, 'id'))
        {
            $type->assignIdentifier($entry_type['id']);
        }
        $type->merge($entry_type);
        if (empty($entry_type['description']))
        {
            $type->description = ' ';
        }
        $type->save();
        return $type;
    }
    //}}}
    //{{{ public function save_field_group($field_group)
    public function save_field_group($field_group)
    {
        $group = new ContentFieldGroup;
        if (eka($field_group, 'id'))
        {
            $group->assignIdentifier($field_group['id']);
        }
        $group->merge($field_group);
        $group->save();
        return $group;
    }
    //}}}
    //{{{ public function save_field($field)
    public function save_field($field, $layout)
    {
        // TODO DOES NOT WORK YET
        /*
        $type = array(
            'name' => $field['name'],
            'type' => $field['type']['type'],
            'weight' => $field['weight'],
            'content_field_group_id' => $field['content_field_group_id'],
            'multiple' => $field['multiple'],
            'description' => $field['description']
        );
        */
        $cft = new ContentFieldType;
        $cft->merge($field);
        $cft->save();

        $field_type = &$cft;
        $fmeta = $layout->meta($field_type->type);
        foreach (Field::layout($field_type->type, $field_type->id) as $name => $field)
        {
            $field_meta = new ContentFieldMeta;
            if (is_array($fmeta))
            {
                foreach ($fmeta as $key => $meta)
                {
                    if ($key === $name)
                    {
                        // TODO make another loop if the meta type has multiple keys
                        $mdata = $layout->acts('post', $meta['type'], array('meta', array('data' => $_POST['field']['meta'][$name])));
                        if ($mdata !== FALSE)
                        {
                            $field_meta->meta = $mdata;
                        }
                        break;
                    }
                }
            }
            $field_meta->name = $name;
            $field_meta->content_field_type_id = $field_type->id;
            if ($field_meta->isValid())
            {
                $field_meta->save();
            }
            else
            {
                $field_type->delete();
            }
            $field_meta->free();
        }
        /*
        $metas = $field['type'];
        foreach ($metas['type'] as $meta)
        {
            $meta_row = Field::layout($meta, $cft->id);
            $meta_row['content_field_type_id'] = $cft->id;
            $cfm = new ContentFieldMeta;
            $cfm->merge($meta_row);
            $cfm->save();
        }
        */
    }
    //}}}

    /**
     * delete_ API methods
     *
     * General delete_ methods that don't specify  by_id, by_name, etc. will
     * accept DQL building arrays like the get_ methods. These however are
     * only used in where clauses. Some delete_ methods won't follow this
     * convention due to table relations and ondelete rules.
     */
    //{{{ public function delete_entry_by_id($id)
    public function delete_entry_by_id($id)
    {
        $dqls = array(
            array(
                'delete' => 'ContentFieldData fd',
                'where' => 'fd.content_entry_meta_id = ?'
            ),
            array(
                'delete' => 'ContentEntryTitle eti',
                'where' => 'eti.content_entry_meta_id = ?'
            ),
            array(
                'delete' => 'ContentEntryMeta em',
                'where' => 'em.id = ?'
            )
        );
        foreach ($dqls as $dql)
        {
            $do = dql_build($dql);
            $do->execute(array($id));
        }
    }
    //}}}
    //{{{ public function delete_entry_type_by_id($id)
    public function delete_entry_type_by_id($id)
    {
        /*
        $dql = dql_build(array('delete' => 'ContentFieldData fd'));
        $sq = ' SELECT  em.id 
                FROM    ContentEntryMeta em
                WHERE   em.content_entry_type_id = ?';
        $dql->where('fd.content_entry_meta_id IN ('.$sq.')', $id);
        $dql->execute();

        $dql = dql_build(array('delete' => 'ContentFieldMeta fm'));
        $ssq = 'SELECT  fg.id
                FROM    ContentFieldGroup fg
                WHERE   fg.content_entry_type_id = ?';
        $sq = ' SELECT  ft.id 
                FROM    ContentFieldType ft
                WHERE   ft.id in ('.$ssq.')';
        $dql->where('fm.content_field_type_id IN ('.$sq.')', $id);
        $dql->execute();

        // incomplete, more deletes follow. but at this point the speed is the
        // same or sometimes better

        // tests show this is as fast or faster than tailored DQLs
        $cett = Doctrine_Core::getTable('ContentEntryType');
        $type = $cett->findById($id);
        $type->delete();
        //*/
    }
    //}}}
    //{{{ public function delete_field_by_ids($ids)
    public function delete_field_by_ids($ids)
    {
        $dql = dql_build(array('delete' => 'ContentFieldMeta fm'));
        $dql->where('fm.content_field_type_id IN ?', array($ids));
        $dql->execute();

        $dql = dql_build(array('delete' => 'ContentFieldType ft'));
        $dql->where('ft.id IN ?', array($ids));
        $dql->execute();
    }
    //}}}
    //{{{ public function delete_field_group_by_id($id)
    public function delete_field_group_by_id($id)
    {
        /*
        $cfgt = Doctrine_Core::getTable('ContentFieldGroup');
        $group = $cfgt->findById($id);
        $group->delete();
        */
    }
    //}}}

    /**
     * modify_ API methods
     *
     * Mostly for a content entries. If the rows are just being updated, the
     * functionality should fall under the save_ API methods. These are for
     * more specialized changes (ex. changing live revision # of an entry)
     */
    //{{{ public function modify_entry_revision($entry_meta_id, $revision)
    public function modify_entry_revision($entry_meta_id, $revision)
    {
        $dql = dql_build(array('update' => 'ContentEntryMeta em'));
        $dql->set('em.revision', $revision)
            ->where('em.id = ?', $entry_meta_id)
            ->execute();
    }
    //}}}

}

?>
