<?php

/**
 * MPContent module 
 * This module handles most of a CMS' needs. It allows for customizing the
 * fields a certain content type should have, which field group it belongs in,
 * categories, and much more.
 * @package MPContent
 */
class MPContent
{
    //{{{ constants
    const MODULE_DESCRIPTION = 'The workhorse for the CMS';
    const MODULE_AUTHOR = 'Glenn';
    const MODULE_DEPENDENCY = 'MPUser';
    const ACCESS_DENY = 0;
    const ACCESS_ALLOW = 1;
    const ACCESS_VIEW = 2;
    const ACCESS_EDIT = 3;

    //}}}
    //{{{ properties
    /**
     * @staticvar mixed MPContent Types
     */
    public static $types = NULL;

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
            $cec = MPDB::selectCollection('mpcontent_entry');
            foreach ($ids as $weight => &$id)
            {
                $query = array('_id' => new MongoID($id));
                $cec->update(
                    $query, 
                    array(
                        '$set' => array(
                            'weight' => $weight,
                        )
                    ),
                    array(
                        'safe' => TRUE,
                    )
                );
            }
            $result['success'] = TRUE;
            $meta['ids'] = $ids;
            $meta['content_entry_type_name'] = $type;
            MPModule::h('mpcontent_order_entries_success', MPModule::TARGET_ALL, $meta);
            MPModule::h('mpcontent_order_entries_success_'.$type, MPModule::TARGET_ALL, $meta);
        }
        catch (Exception $e)
        {
            $result['success'] = FALSE;
        }

        return json_encode($result);
    }

    //}}}

    //{{{ public function cb_mpcontent_edit_type_other_links($links)
    public function cb_mpcontent_edit_type_other_links($links)
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
    //{{{ public function cb_mpcontent_edit_type_process()
    public function cb_mpcontent_edit_type_process()
    {
    }
    //}}}
    //{{{ public function cb_mpcontent_entry_add_access($access)
    public function cb_mpcontent_entry_add_access($access)
    {
        return $access ? max($access) : MPContent::ACCESS_DENY;
    }
    //}}}
    // {{{ public function cb_mpcontent_entry_add_finish($meta)
    public function cb_mpcontent_entry_add_finish($meta)
    {
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Successfully created');
    }
    //}}}
    //{{{ public function cb_mpcontent_entry_delete_finish($meta)
    public function cb_mpcontent_entry_delete_finish($meta)
    {
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Successfully deleted');
    }
    //}}}
    //{{{ public function cb_mpcontent_entry_edit_access($access)
    public function cb_mpcontent_entry_edit_access($access)
    {
        return $access ? max($access) : MPContent::ACCESS_DENY;
    }
    //}}}
    //{{{ public function cb_mpcontent_entry_sidebar_new_process()
    public function cb_mpcontent_entry_sidebar_new_process()
    {
    }
    //}}}
    //{{{ public function cb_mpcontent_entry_sidebar_edit_process()
    public function cb_mpcontent_entry_sidebar_edit_process()
    {
    }
    //}}}
    //{{{ public function cb_mpcontent_new_type_process()
    public function cb_mpcontent_new_type_process()
    {
    }
    //}}}

    //{{{ public function hook_mpadmin_enqueu_css()
    public function hook_mpadmin_enqueu_css()
    {
        if (strpos(URI_PATH, '/admin/module/MPContent/') !== FALSE)
        {
            mp_enqueue_style('mpcontent_content', '/admin/static/MPContent/content.css');
        }
    }

    //}}}
    //{{{ public function hook_mpadmin_dashboard()
    public function hook_mpadmin_dashboard()
    {
        $dashboard_items = array();

        $can_view = MPUser::has_perm('view content', 'view content entries type');
        $can_add = MPUser::has_perm('add content', 'add content entries type');
        $can_edit = MPUser::has_perm('edit content', 'edit content entries type');

        if ($can_edit || $can_view)
        {
            $entries = self::get_latest_entries_created();
            $latest['title'] = 'Latest Content Entries';
            $latest['content'] = '<ul>';
            if (count($entries))
            {
                foreach ($entries as $entry)
                {
                    $href = '/admin/module/MPContent/edit_entry/' . $entry['_id']->{'$id'} . '/';
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
                    $href = '/admin/module/MPContent/edit_entry/' . $entry['id']->{'$id'} . '/';
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
                array('name', 'nice_name')
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
                    $name = &$type['name'];
                    $nice_name = &$type['nice_name'];

                    if ($can_add)
                    {
                        $title = 'Add New ' . $nice_name;
                        $href = '/admin/module/MPContent/new_entry/' . $name . '/';
                        $add_entries[] = '<li><a href="'.$href.'">'.$title.'</a></li>';
                    }

                    if ($can_edit)
                    {
                        $title = 'Filter by '.$nice_name;
                        $href = '/admin/module/MPContent/edit_entries/?filter[type][data]='.$name;
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
    //{{{ public function hook_mpadmin_enqueue_js()
    public function hook_mpadmin_enqueue_js()
    {
        if (strpos(URI_PATH, '/admin/module/MPContent/') !== FALSE)
        {
            mp_enqueue_script(
                'mpcontent_content',
                '/admin/static/MPContent/content.js',
                array(),
                FALSE,
                TRUE
            );
            if (URI_PARTS > 3)
            {
                if (URI_PART_3 === 'new_entry' || URI_PART_3 === 'edit_entry')
                {
                    mp_enqueue_script(
                        'mpcontent_field',
                        '/admin/static/MPContent/field.js',
                        array('jquery-ui-sortable'),
                        FALSE,
                        TRUE
                    );
                }
                if (URI_PART_3 === 'fields')
                {
                    mp_enqueue_script(
                        'mpcontent_field_type',
                        '/admin/static/MPContent/field.type.js',
                        array(),
                        FALSE,
                        TRUE
                    );
                }
            }
        }
        if (strpos(URI_PATH, '/admin/module/MPContent/edit_entries/') !== FALSE)
        {
            mp_enqueue_script(
                'mpcontent_entries',
                '/admin/static/MPContent/entries.js',
                array('jquery-ui-sortable'),
                FALSE,
                TRUE
            );
        }
    }

    //}}}
    //{{{ public function hook_mpadmin_module_page($page)
    public function hook_mpadmin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_mpadmin_nav()
    public function hook_mpadmin_nav()
    {
        $types = self::get_entry_types();
        $uri = '/admin/module/MPContent';
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
                $nice_name = &$type['nice_name'];
                if (MPUser::has_perm('add content entries type', 'add content entries type-'.$name))
                {
                    $links['Add'][] = "<a href='$uri/new_entry/$name/'>$nice_name</a>";
                }
                if (MPUser::has_perm('view content entries type', 'view content entries type-'.$name))
                {
                    $links['Edit'][] = "<a href='$uri/edit_entries/?filter[limit][data]=25&filter[type][data]=$name'>$nice_name</a>";
                }
            }
        }

        if (MPUser::perm('add content type'))
        {
            $links['Tools'][] = "<a href='$uri/new_type/'>New Content Type</a>";
        }
        if (MPUser::perm('edit content type') && $types)
        {
            $links['Tools'][] = "<a href='$uri/edit_types/'>Edit Content Types</a>";
        }
        return $links;
    }

    //}}}
    //{{{ public function hook_mpadmin_settings_fields()
    public function hook_mpadmin_settings_fields()
    {
        $autoslug = array(
            'field' => MPField::layout(
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
                'data' => MPData::query('MPContent', 'autoslug')
            )
        );

        return array($autoslug);
    }

    //}}}
    //{{{ public function hook_mpsystem_active()
    public function hook_mpsystem_active()
    {
    }

    //}}}
    //{{{ public function hook_mpsystem_install()
    public function hook_mpsystem_install()
    {
        $db = new MPDB;
        $db->mpcontent_entry->ensureIndex(
            array(
                'entry_type_name' => 1, 
                'weight' => 1, 
                'updated' => -1,
            )
        );
        $db->mpcontent_entry->ensureIndex(
            array(
                'slug' => 1, 
                'entry_type_name' => 1, 
            )
        );
        $db->mpcontent_entry->ensureIndex(
            array(
                'entry_type._id' => 1, 
                'slug' => 1, 
            )
        );
        $db->mpcontent_entry_revision->ensureIndex(
            array(
                'entry_id' => 1, 
                'revision' => -1,
            )
        );
        $db->mpcontent_entry_type->ensureIndex(
            array(
                'name' => 1,
            ), 
            array(
                'unique' => 1, 
                'dropDups' => 1,
            )
        );
        $db->mpcontent_entry_type->ensureIndex(
            array(
                'name' => 1, 
                'field_groups.weight' => 1, 
                'field_groups.fields.weight' => 1,
            )
        );
    }

    //}}}
    //{{{ public function hook_mpuser_perm()
    public function hook_mpuser_perm()
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

        $module_perms = MPModule::h('mpcontent_perms', MPModule::TARGET_ALL, $types);
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

    //{{{ public function prep_mpcontent_entry_sidebar_edit_process($mod, &$layout, &$entry, &$post)
    public function prep_mpcontent_entry_sidebar_edit_process($mod, &$layout, &$entry, &$post)
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
    //{{{ public function prep_mpcontent_entry_sidebar_new_process($mod, &$layout, &$entry, &$post)
    public function prep_mpcontent_entry_sidebar_new_process($mod, &$layout, &$entry, &$post)
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
    //{{{ public function prep_mpcontent_edit_type_process($mod, &$layout, &$type, $post)
    public function prep_mpcontent_edit_type_process($mod, &$layout, &$type, $post)
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
    //{{{ public function prep_mpcontent_new_type_process($mod, &$layout, $type, $post)
    public function prep_mpcontent_new_type_process($mod, &$layout, &$type, $post)
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
     * MPContent module now uses the caching mechanism. But only caches:
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
        $ids_slugs = MPContent::get_entries_slugs($type);
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
            $mapping = MPCache::get($type.' - ids slugs', 'MPContent');
            $has_cache = !is_null($mapping);
        }
        if (!$has_cache || is_null($type))
        {
            $db = new MPDB;
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
            MPCache::set($type.' - ids slugs', $mapping, $expire, 'MPContent');
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
                ->from('MPContentEntryType et')
                ->where('et.name = ?')
                ->fetchOne(array($name));
        $dt_dql = Doctrine_Query::create()
                    ->select('
                        em.id, em.created, em.revision, em.weight,
                        eti.title as title, eti.slug as slug, 
                        eti.modified as modified
                    ')
                    ->from('MPContentEntryMeta em')
                    ->leftJoin('em.MPContentEntryTitle eti')
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
    //{{{ public function get_entries_details_by_type_id($id, $fields = array(), $use_cache = TRUE, $expire = 0)
    /**
     * Returns multiple entries. MPData set format is like get_entry_details
     */
    public function get_entries_details_by_type_id($id, $fields = array(), $use_cache = TRUE, $expire = 0)
    {
        /*
        $dt_dfields = array(
            'select' => array(
                'ety.id', 'ety.name'
            ),
            'from' => 'MPContentEntryType ety',
            'where' => 'ety.id = ?'
        );
        $dt_fields = array_merge($dt_dfields, $fields);
        $types = dql_exec($dt_fields, array($id));
        $type = $types[0]['name'];
        return self::get_entries_details_by_type_name($type, $use_cache, $expire);
        */
        return array();
    }
    //}}}
    //{{{ public function get_entries_details_by_type_name($name, $use_cache = TRUE, $expire = 0)
    public function get_entries_details_by_type_name($name, $use_cache = TRUE, $expire = 0)
    {
        if ($use_cache)
        {
            $entries = MPCache::get($name.' - entries', 'MPContent');
            $has_cache = !is_null($entries);
        }
        if (!$use_cache || !$has_cache)
        {
            $entries = array();

            /*
            $type = Doctrine_Query::create()
                    ->from('MPContentEntryType et')
                    ->where('et.name = ?')
                    ->fetchOne(array($name));
                    //->fetchOne(array($name), Doctrine::HYDRATE_ARRAY);
            $dt_dql = Doctrine_Query::create()
                        ->select('
                            em.id, em.created, em.revision, em.weight,
                            eti.title as title, eti.slug as slug, 
                            eti.modified as modified, em.content_entry_type_id as type_id
                        ')
                        ->from('MPContentEntryMeta em')
                        ->leftJoin('em.MPContentEntryTitle eti')
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
                        ->from('MPContentMPFieldMPData fd')
                        ->leftJoin('fd.MPContentEntryMeta em')
                        ->leftJoin('em.MPContentEntryType ety')
                        ->leftJoin('fd.MPContentMPFieldMeta fm')
                        ->leftJoin('fm.MPContentMPFieldType ft')
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
                    $field_data = MPField::quick_act('read', $type_info['type'], $field_data_raw[$entry_meta_id][$type_id]);
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
            MPCache::set($name.' - entries', $entries, $expire, 'MPContent');
        }

        return $entries;
    }
    //}}}
    //{{{ public function get_entries_details
    /**
     * This tries to be very minimal, getting as little info as needed.
     */
    public function get_entries_details($query = array(), $fields = array())
    {
        $entry = array();

        /*
        $dt_dfields = array(
            'select' => array(
                'em.id', 'em.created', 'em.revision',
                'em.weight', 
                'eti.title as title', 'eti.slug as slug', 
                'eti.modified as modified'
            ),
            'from' => 'MPContentEntryMeta em',
            'leftJoin' => 'em.MPContentEntryTitle eti',
            'where' => 'em.id IN ?',
            'andWhere' => 'eti.revision = em.revision'
        );
        $dt_query =& $query;
        $dt_fields = array_merge($dt_dfields, $fields);
        $entries = dql_exec($dt_fields, $dt_query);

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
        */
        return array();
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
    // {{{ public function get_entry_details($query = array(), $fields = array())
    /**
     * This tries to be very minimal, getting as little info as needed.
     */
    public function get_entry_details($query = array(), $fields = array())
    {
        $entry = array();

        /*
        $dt_dfields = array(
            'select' => array(
                'em.id', 'em.created', 'em.revision',
                'eti.title as title', 'eti.slug as slug', 
                'eti.modified as modified'
            ),
            'from' => 'MPContentEntryMeta em',
            'leftJoin' => 'em.MPContentEntryTitle eti',
            'where' => 'em.id = ?',
            'andWhere' => 'eti.revision = em.revision'
        );
        $dt_query =& $query;
        $dt_fields = array_merge($dt_dfields, $fields);
        $entry['entry'] = array_pop(dql_exec($dt_fields, $dt_query));

        $entry['data'] = self::get_field_data_by_entry_id_and_revision(
            $entry['entry']['id'],
            $entry['entry']['revision']
        );
        */ 
        return $entry;
    }
    //}}}
    //{{{ public function get_entry_details_by_id($id, $use_cache = TRUE, $expire = 0)
    public function get_entry_details_by_id($id, $use_cache = TRUE, $expire = 0)
    {
        if ($use_cache)
        {
            $entry = MPCache::get('entry:'.$id, 'MPContent');
            if (is_null($entry))
            {
                $entry = self::get_entry_details(array($id), array('where' => 'em.id = ?'));
                MPCache::set('entry:'.$id, $entry, $expire, 'MPContent');
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
        /*
        $dt_dfields = array(
            'select' => array(
                'ety.id', 'ety.name'
            ),
            'from' => 'MPContentEntryType ety',
            'where' => 'ety.id = ?'
        );
        $types = dql_exec($dt_fields, array($id));
        $type = $types[0]['name'];
        return self::get_entry_details_by_slug_and_type_name($slug, $type, $use_cache, $expire);
        */
        return array();
    }
    //}}}
    //{{{ public function get_entry_details_by_slug_and_type_name($slug, $type, $use_cache = TRUE, $expire = 0)
    public function get_entry_details_by_slug_and_type_name($slug, $type, $use_cache = TRUE, $expire = 0)
    {
        if ($use_cache)
        {
            $entry_slug = self::get_entry_slug_id($type, $slug, TRUE);
            $entry = MPCache::get('entry:'.$entry_slug['id'], 'MPContent');
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
                    ->from('MPContentEntryTitle eti')
                    ->leftJoin('eti.MPContentEntryMeta em')
                    ->leftJoin('em.MPContentEntryType ety')
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
            MPCache::set('entry:'.$entry_slug['id'], $entry, $expire, 'MPContent');
        }

        return $entry;
    }
    //}}}
    //{{{ public function get_entry_type($query = array(), $fields = array())
    public function get_entry_type($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry_type')->findOne($query, $fields);
    }
    //}}}
    //{{{ public function get_entry_type_by_entry_id($id, $fields = array())
    public function get_entry_type_by_entry_id($id, $fields = array())
    {
        if (is_string($id))
        {
            $id = new MongoID($id);
        }
        $query = array('_id' => $id);
        return self::get_entry_type($query, $fields);
    }
    //}}}
    //{{{ public function get_entry_type_by_name($name, $fields = array())
    public function get_entry_type_by_name($name, $fields = array())
    {
        $query = array(
            '$or' => array(
                array('name' => $name),
                array('nice_name' => $name),
            ),
        );
        return self::get_entry_type($query, $fields);
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
    //{{{ public function get_entry_type_fields($query = array(), $fields = array())
    public function get_entry_type_fields($query = array(), $fields = array())
    {
        /*
        $dfields = array(
            'select' => array(
                'fg.id', 'fg.weight', 'fg.name', 
            ),
            'from' => 'MPContentMPFieldGroup fg',
            'where' => 'fg.id = ?',
            'orderBy' => 'fg.weight asc'
        );
        $s = array_merge($dfields, $fields);
        $groups = dql_exec($s, $query);
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
        */
        return array();
    }
    //}}}
    //{{{ public function get_entry_type_fields_by_id($id, $fields = array())
    public function get_entry_type_fields_by_id($id, $fields = array())
    {
        $dfields = array('where' => 'fg.content_entry_type_id = ?');
        return self::get_entry_type_fields(array($id), array_merge($dfields, $fields));
    }
    //}}}
    //{{{ public function get_entry_type_fields_by_name($name, $fields = array())
    public function get_entry_type_fields_by_name($name, $fields = array())
    {
        $dfields = array('where' => 'fg.name = ?');
        return self::get_entry_type_fields(array($name), array_merge($dfields, $fields));
    }
    //}}}
    //{{{ public function get_entry_types($query = array(), $fields = array())
    /**
     * Gets entry types
     */
    public function get_entry_types($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry_type')->find($query, $fields);
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
                    ->from('MPContentMPFieldMPData fd')
                    ->leftJoin('fd.MPContentMPFieldMeta fm')
                    ->leftJoin('fm.MPContentMPFieldType ft')
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
            $field_data = MPField::quick_act('read', $type_info['type'], $field_data_raw[$type_id]);
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
                'leftJoin' => 'ft.MPContentMPFieldGroup fg'
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
               ->from('MPContentMPFieldMeta fm')
               ->leftJoin('fm.MPContentMPFieldType ft')
               ->leftJoin('ft.MPContentMPFieldGroup fg')
               ->leftJoin('fg.MPContentEntryType et')
               ->where('et.name = ?');
        $rows = $dql->execute(array($name), Doctrine::HYDRATE_ARRAY);
        $details = array();
        $groups = array();
        $types = array();
        foreach ($rows as $row)
        {
            $field_type = $row['MPContentMPFieldType'];
            $field_group = $row['MPContentMPFieldType']['MPContentMPFieldGroup'];
            $group_name = $field_group['name'];
            if (!eka($tree, $group_name))
            {
                unset($field_group['MPContentEntryType']);
                $tree[$group_name] = $field_group;
                $tree[$group_name]['fields'] = array();
            }
            if (!eka($tree, $group_name, 'fields', $field_type['id']))
            {
                unset($field_type['MPContentMPFieldGroup']);
                $tree[$group_name]['fields'][$field_type['id']] = $field_type;
                $tree[$group_name]['fields'][$field_type['id']]['meta'] = array();
            }
            $tree[$group_name]['fields'][$field_type['id']]['meta'][$row['name']] = $row;
            unset($tree[$group_name]['fields'][$field_type['id']]['meta'][$row['name']]['MPContentMPFieldType']);
        }
        */
        return $tree;
    }
    //}}}
    //{{{ public function get_field_meta($query = array(), $fields = array())
    public function get_field_meta($query = array(), $fields = array())
    {
        /*
        $dfields = array(
            'select' => array(
                'fm.id', 'fm.name', 'fm.label', 'fm.required',
                'fm.meta', 'fm.default_data'
            ),
            'from' => 'MPContentMPFieldMeta fm'
        );
        $nfields = array_merge($dfields, $fields);
        $metas = dql_exec($nfields, $query);
        return $metas;
        */
        return array();
    }
    //}}}
    //{{{ public function get_field_meta_by_type_id($id, $fields = array())
    public function get_field_meta_by_type_id($id, $fields = array())
    {
        $dfields = array('where' => 'fm.content_field_type_id = ?');
        $nfields = array_merge($dfields, $fields);
        $param = array($id);
        $metas = array();
        $rows = self::get_field_meta($param, $nfields);
        foreach ($rows as $row)
        {
            $metas[$row['name']] = $row;
        }
        return $metas;
    }
    //}}}
    //{{{ public function get_field_type($query = array(), $fields = array())
    public function get_field_type($query = array(), $fields = array())
    {
        /*
        $dfields = array(
            'select' => array(
                'ft.id', 'ft.name', 'ft.type', 'ft.weight',
                'ft.multiple', 'ft.description'
            ),
            'from' => 'MPContentMPFieldType ft'
        );
        $s = array_merge($dfields, $fields);
        return dql_exec($s, $query);
        */
        return array();
    }
    //}}}
    //{{{ public function get_field_type_by_id($id, $fields = array())
    public function get_field_type_by_id($id, $fields = array())
    {
        $dfields = array('where' => 'ft.id = ?');
        $nfields = array_merge($dfields, $fields);
        $param = array($id);
        $type = array_pop(self::get_field_type($param, $nfields));
        return $type;
    }
    //}}}
    //{{{ public function get_latest_entries_created($query = array(), $fields = array())
    public function get_latest_entries_created($query = array(), $fields = array())
    {
        /*
        $dfields = array(
            'select' => array(
                'em.id as id', 'em.created as created', 'eti.modified',
                'eti.title', 'eti.slug'
            ),
            'from' => 'MPContentEntryTitle eti',
            'leftJoin' => 'eti.MPContentEntryMeta em',
            'where' => 'eti.revision = em.revision',
            'orderBy' => array('em.created asc', 'eti.title asc'),
            'limit' => 10,
            'offset' => 0
        );
        $s = array_merge($dfields, $fields);
        return dql_exec($s, $query);
        */
        return array();
    }
    //}}}
    //{{{ public function get_latest_entries_modified($query = array(), $fields = array())
    public function get_latest_entries_modified($query = array(), $fields = array())
    {
        /*
        $dfields = array(
            'select' => array(
                'em.id as id', 'em.created as created', 'eti.modified',
                'eti.title', 'eti.slug'
            ),
            'from' => 'MPContentEntryTitle eti',
            'leftJoin' => 'eti.MPContentEntryMeta em',
            'where' => 'eti.revision = em.revision',
            'orderBy' => array('eti.modified desc', 'eti.title asc'),
            'limit' => 10,
            'offset' => 0
        );
        $s = array_merge($dfields, $fields);
        return dql_exec($s, $query);
        */
        return array();
    }
    //}}}
    //{{{ public function get_most_revised_entries($query = array(), $fields = array())
    public function get_most_revised_entries($query = array(), $fields = array())
    {
        /*
        $dfields = array(
            'select' => array('em.id as id', 'em.revisions as revisions', 'eti.title'),
            'from' => 'MPContentEntryTitle eti',
            'leftJoin' => 'eti.MPContentEntryMeta em',
            'where' => 'eti.revision = em.revision',
            'orderBy' => array('em.revisions desc', 'eti.title asc'),
            'limit' => 10,
            'offset' => 0
        );
        $s = array_merge($dfields, $fields);
        return dql_exec($s, $query);
        */
        return array();
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
               ->from('MPContentMPFieldMPData fd')
               ->leftjoin('fd.MPContentMPFieldMeta fm')
               ->leftjoin('fm.MPContentMPFieldType ft')
               ->leftjoin('fd.MPContentEntryMeta em')
               ->leftjoin('em.MPContentEntryTitle et')
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
        /*
        $dspec = array(
            'select' => array(
                'eti.id', 'eti.modified', 'eti.title', 'eti.slug'
            ),
            'from' => 'MPContentEntryTitle eti',
            'leftJoin' => 'eti.MPContentEntryMeta em',
            'where' => 'eti.title LIKE ?',
            'andWhere' => 'eti.revision = em.revision'
        );
        $s = array_merge($dspec, $spec);
        return dql_exec($s, array($title));
        */
        return array();
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
        $cem = new MPContentEntryMeta;
        $cem->content_entry_type_id = $entry['meta']['content_entry_type_id'];
        $cem->save();

        $ceti = new MPContentEntryTitle;
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

            $data_rows = new Doctrine_Collection('MPContentMPFieldMPData');
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
        $cfmt = Doctrine::getTable('MPContentMPFieldMeta');
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
                    $field_data = new MPContentMPFieldMPData;
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
        $etc = MPDB::selectCollection('mpcontent_entry_type');
        if (!ake('_id', $entry_type))
        {
            $entry_type['name'] = slugify($entry_type['nice_name']);
            $entry_type['ordering'] = FALSE;
            $entry_type['field_groups'] = array(
                array(
                    'name' => $entry_type['name'],
                    'nice_name' => $entry_type['nice_name'],
                    'weight' => 0,
                    'fields' => array(),
                ),
            );
        }
        $etc->save($entry_type, array('safe' => TRUE));
        return $entry_type;
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
                'delete' => 'MPContentMPFieldMPData fd',
                'where' => 'fd.content_entry_meta_id = ?'
            ),
            array(
                'delete' => 'MPContentEntryTitle eti',
                'where' => 'eti.content_entry_meta_id = ?'
            ),
            array(
                'delete' => 'MPContentEntryMeta em',
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
        $dql = dql_build(array('delete' => 'MPContentMPFieldMPData fd'));
        $sq = ' SELECT  em.id 
                FROM    MPContentEntryMeta em
                WHERE   em.content_entry_type_id = ?';
        $dql->where('fd.content_entry_meta_id IN ('.$sq.')', $id);
        $dql->execute();

        $dql = dql_build(array('delete' => 'MPContentMPFieldMeta fm'));
        $ssq = 'SELECT  fg.id
                FROM    MPContentMPFieldGroup fg
                WHERE   fg.content_entry_type_id = ?';
        $sq = ' SELECT  ft.id 
                FROM    MPContentMPFieldType ft
                WHERE   ft.id in ('.$ssq.')';
        $dql->where('fm.content_field_type_id IN ('.$sq.')', $id);
        $dql->execute();

        // incomplete, more deletes follow. but at this point the speed is the
        // same or sometimes better

        // tests show this is as fast or faster than tailored DQLs
        $cett = Doctrine_Core::getTable('MPContentEntryType');
        $type = $cett->findById($id);
        $type->delete();
        //*/
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
        $dql = dql_build(array('update' => 'MPContentEntryMeta em'));
        $dql->set('em.revision', $revision)
            ->where('em.id = ?', $entry_meta_id)
            ->execute();
    }
    //}}}

}
