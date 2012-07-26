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
    // {{{ constants
    const MODULE_DESCRIPTION = 'The workhorse for the CMS';
    const MODULE_AUTHOR = 'Jason Wong';
    const MODULE_DEPENDENCY = 'MPUser';
    const ACCESS_DENY = 0;
    const ACCESS_ALLOW = 1;
    const ACCESS_VIEW = 2;
    const ACCESS_EDIT = 3;
    // }}}

    // {{{ public function cb_mpcontent_edit_type_other_links($links)
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
    // }}}
    // {{{ public function cb_mpcontent_edit_type_process()
    public function cb_mpcontent_edit_type_process()
    {
    }
    // }}}
    // {{{ public function cb_mpcontent_entry_add_access($access)
    public function cb_mpcontent_entry_add_access($access)
    {
        return $access ? max($access) : MPContent::ACCESS_DENY;
    }
    // }}}
    // {{{ public function cb_mpcontent_entry_add_finish($meta)
    public function cb_mpcontent_entry_add_finish($meta)
    {
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Successfully created');
    }
    // }}}
    // {{{ public function cb_mpcontent_entry_delete_finish($meta)
    public function cb_mpcontent_entry_delete_finish($meta)
    {
        MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'Successfully deleted');
    }
    // }}}
    // {{{ public function cb_mpcontent_entry_edit_access($access)
    public function cb_mpcontent_entry_edit_access($access)
    {
        return $access ? max($access) : MPContent::ACCESS_DENY;
    }
    // }}}
    // {{{ public function cb_mpcontent_entry_sidebar_new_process()
    public function cb_mpcontent_entry_sidebar_new_process()
    {
    }
    // }}}
    // {{{ public function cb_mpcontent_entry_sidebar_edit_process()
    public function cb_mpcontent_entry_sidebar_edit_process()
    {
    }
    // }}}
    // {{{ public function cb_mpcontent_new_type_process()
    public function cb_mpcontent_new_type_process()
    {
    }
    // }}}

    // {{{ public function hook_mpadmin_dashboard()
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

    // }}}
    // {{{ public function hook_mpadmin_enqueue_css()
    public function hook_mpadmin_enqueue_css()
    {
        if (strpos(URI_PATH, '/admin/module/MPContent/') !== FALSE)
        {
            mp_enqueue_style('mpcontent_content', '/admin/static/MPContent/content.css');
        }
    }

    // }}}
    // {{{ public function hook_mpadmin_enqueue_js()
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
        }
        /*
        if (strpos(URI_PATH, '/admin/module/MPContent/edit_entries/') !== FALSE)
        {
        }
        */
    }

    // }}}
    // {{{ public function hook_mpadmin_module_page($page)
    public function hook_mpadmin_module_page($page)
    {
    }
    
    // }}}
    // {{{ public function hook_mpadmin_nav()
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

    // }}}
    // {{{ public function hook_mpadmin_rpc($function, $data)
    public function hook_mpadmin_rpc($function, $data)
    {
        $result = array();
        switch ($function)
        {
            // {{{ case 'order_entries':
            case 'order_entries':
                $ids = $data['ids'];
                $type = $data['type'];
                try
                {
                    $mpentry = MPDB::selectCollection('mpcontent_entry');
                    foreach ($ids as $weight => &$id)
                    {
                        $query = array('_id' => new MongoID($id));
                        $mpentry->update(
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
            break;
            // }}}
            default:
                $result['success'] = FALSE;
        }
        return json_encode($result);
    }
    // }}}
    // {{{ public function hook_mpadmin_settings_fields()
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

    // }}}
    // {{{ public function hook_mpsystem_active()
    public function hook_mpsystem_active()
    {
    }

    // }}}
    // {{{ public function hook_mpsystem_install()
    public function hook_mpsystem_install()
    {
        $db = new MPDB;
        $db->mpcontent_entry->ensureIndex(
            array(
                'entry_type_name' => 1, 
                'weight' => 1, 
                'modified' => -1,
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

    // }}}
    // {{{ public function hook_mpuser_perm()
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

    // }}}

    // {{{ public function prep_mpcontent_entry_sidebar_edit_process($mod, &$layout, &$entry, &$post)
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
    // }}}
    // {{{ public function prep_mpcontent_entry_sidebar_new_process($mod, &$layout, &$entry, &$post)
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
    // }}}
    // {{{ public function prep_mpcontent_edit_type_process($mod, &$layout, &$type, $post)
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
    // }}}
    // {{{ public function prep_mpcontent_new_type_process($mod, &$layout, $type, $post)
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
    // }}}

    // {{{ API methods
    // {{{ public function delete_entries($query = array())
    public function delete_entries($query = array())
    {
        $options = array(
            'safe' => TRUE,
        );
        $mpentry = MPDB::selectCollection('mpcontent_entry');
        $entries = $mpentry->find($query, array('_id' => 1));
        if ($entries->hasNext())
        {
            $ids = array();
            foreach ($entries as $entry)
            {
                $ids[] = $entry['_id'];
            }
            $rquery = array(
                'entry._id' => array(
                    '$in' => $ids,
                ),
            );
        }
        $success = $mpentry->remove($query, $options);
        if (isset($rquery))
        {
            MPDB::selectCollection('mpcontent_entry_revision')->remove($rquery);
        }
        return $success;
    }
    // }}}
    // {{{ public function delete_entry($query = array())
    public function delete_entry($query = array())
    {
        $options = array(
            'safe' => TRUE,
            'justOne' => TRUE,
        );
        $mpentry = MPDB::selectCollection('mpcontent_entry');
        $entry = $mpentry->findOne($query, array('_id' => 1));
        if (!is_null($entry))
        {
            $rquery = array(
                'entry._id' => $entry['_id'],
            );
        }
        $success = $mpentry->remove($query, $options);
        if (isset($rquery))
        {
            MPDB::selectCollection('mpcontent_entry_revision')->remove($rquery);
        }
        return $success;
    }
    // }}}
    // {{{ public function delete_entry_by_id($id)
    public function delete_entry_by_id($id)
    {
        $query = is_object($id) && get_class($id) === 'MongoId'
            ? array('_id' => $id)
            : array('_id' => new MongoId($id));
        return self::delete_entry($query);
    }
    // }}}
    // {{{ public function delete_entry_type($query = array())
    public function delete_entry_type($query = array())
    {
        $options = array(
            'safe' => TRUE,
            'justOne' => TRUE,
        );
        $mpentry_type = MPDB::selectCollection('mpcontent_entry_type');
        $entry_type = $mpentry_type->findOne($query, array('field_groups' => 1));
        $success = $mpentry_type->remove($query, $options);
        if (!is_null($entry_type))
        {
            foreach ($entry_type['field_groups'] as &$field_groups)
            {
                foreach ($field_groups['fields'] as &$field)
                {
                    MPField::deregister_field($field['_id']);
                }
            }
            $equery = array(
                'entry_type._id' => $entry_type['_id'],
            );
            self::delete_entries($equery);
        }
        return $success;
    }
    // }}}
    // {{{ public function delete_entry_type_by_name($name)
    public function delete_entry_type_by_name($name)
    {
        $query['name'] = $name;
        return self::delete_entry_type($query);
    }
    // }}}
    // {{{ public function delete_fields_by_type_name_and_ids($name, $ids)
    public function delete_fields_by_type_name_and_ids($name, $ids)
    {
        $entry_type = self::get_entry_type_by_name($name);
        foreach ($entry_type['field_groups'] as &$entry_field_group)
        {
            foreach ($entry_field_group['fields'] as $k => &$entry_field)
            {
                $id = $entry_field['_id']->{'$id'};
                if (in_array($id, $ids))
                {
                    MPField::deregister_field($entry_field['_id']);
                    unset($entry_field_group['fields'][$k]);
                }
            }
        }
        self::save_entry_type($entry_type);
    }
    // }}}

    // {{{ public function get_entries($query = array(), $fields = array())
    /**
     * Gets the entries
     *
     * @param array $query
     * @param array $fields fields to return
     * @return MongoCursor
     */
    public function get_entries($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry')->find($query, $fields);
    }
    // }}}
    // {{{ public function get_entries_slugs($type = NULL, $use_cache = TRUE, $expire = 0)
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
    // }}}
    // {{{ public function get_entries_by_type_name($name)
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
    // }}}
    // {{{ public function get_entry($query = array(), $fields = array())
    /**
     * Get an entry
     *
     * @param array $query
     * @param array $fields fields to return
     * @return array|NULL
     */
    public function get_entry($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry')->findOne($query, $fields);
    }
    // }}}
    // {{{ public function get_entry_by_id($id, $fields = array())
    /**
     * Gets the row from the id/slug provided from self::get_entries_slugs()
     */
    public function get_entry_by_id($id, $fields = array())
    {
        $query = is_object($id) && get_class($id) === 'MongoId'
            ? array('_id' => $id)
            : array('_id' => new MongoId($id));
        return self::get_entry($query, $fields);
    }
    // }}}
    // {{{ public function get_entry_slug_id($type, $slug, $use_cache = TRUE, $expire = 0)
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
    // }}}
    // {{{ public function get_entry_type($query = array(), $fields = array())
    /**
     * Get an entry type
     *
     * @param array $query
     * @param array $fields fields to return
     * @return array|NULL
     */
    public function get_entry_type($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry_type')->findOne($query, $fields);
    }
    // }}}
    // {{{ public function get_entry_type_by_name($name, $fields = array())
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
    // }}}
    // {{{ public function get_entry_types($query = array(), $fields = array())
    /**
     * Gets entry types
     *
     * @param array $query
     * @param array $fields fields to return
     * @return MongoCursor
     */
    public function get_entry_types($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry_type')->find($query, $fields);
    }
    // }}}
    // {{{ public function get_revision($query = array(), $fields = array())
    /**
     * Get a revision
     *
     * @param array $query
     * @param array $fields fields to return
     * @return array|NULL
     */
    public function get_revision($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry_revision')->findOne($query, $fields);
    }
    // }}}
    // {{{ public function get_revision_by_entry_id_and_revision($id, $revision, $fields = array())
    /**
     * Gets entry types
     */
    public function get_revision_by_entry_id_and_revision($id, $revision, $fields = array())
    {
        $query = is_object($id) && get_class($id) === 'MongoId'
            ? array('entry._id' => $id)
            : array('entry._id' => new MongoId($id));
        $query['revision'] = $revision;
        return self::get_revision($query, $fields);
    }
    // }}}
    // {{{ public function get_revisions($query = array(), $fields = array())
    /**
     * Gets the revisions
     *
     * @param array $query
     * @param array $fields fields to return
     * @return MongoCursor
     */
    public function get_revisions($query = array(), $fields = array())
    {
        return MPDB::selectCollection('mpcontent_entry_revision')->find($query, $fields);
    }
    // }}}
    // {{{ public function get_revisions_by_entry_id($id, $fields = array())
    /**
     * Gets entry types
     */
    public function get_revisions_by_entry_id($id, $fields = array())
    {
        $query = is_object($id) && get_class($id) === 'MongoId'
            ? array('entry._id' => $id)
            : array('entry._id' => new MongoId($id));
        return self::get_revisions($query, $fields);
    }
    // }}}

    // {{{ public function save_entry($entry, $entry_type)
    public function save_entry($entry, $entry_type)
    {
        $entry_type_data_format = array_fill_keys(
            array('_id', 'name', 'nice_name'),
            ''
        );
        $entry_data_format = array_fill_keys(
            array('_id', 'title', 'slug', 'weight', 'revision', 'entry_type', 'modified', 'data', 'status'),
            ''
        );
        $entry_type_data = array_join($entry_type_data_format, $entry_type);
        $entry_data = array_join($entry_data_format, $entry['entry']);
        if (is_string($entry_data['_id']))
        {
            unset($entry_data['_id']);
        }
        if (!is_numeric($entry_data['weight']))
        {
            $entry_data['weight'] = 0;
        }
        $entry_data['revision'] = 0;
        $entry_data['entry_type'] = $entry_type_data;
        $entry_data['modified'] = new MongoDate();
        $entry_data['data'] = $entry['data'];

        $mpentry = MPDB::selectCollection('mpcontent_entry');
        $mpentry->save($entry_data, array('safe' => TRUE));

        $revisions = self::get_revisions_by_entry_id($entry_data['_id'], array('_id' => 1));
        $num_revisions = $revisions->hasNext() ? $revisions->count() : 0;
        $revision = array(
            'entry' => $entry_data,
            'revision' => ++$num_revisions,
        );
        MPDB::selectCollection('mpcontent_entry_revision')->save($revision, array('safe' => TRUE));
        return $entry_data;
    }
    // }}}
    // {{{ public function save_entry_type($entry_type)
    public function save_entry_type($entry_type)
    {
        $etc = MPDB::selectCollection('mpcontent_entry_type');
        if (!ake('_id', $entry_type))
        {
            $entry_type['name'] = slugify($entry_type['nice_name']);
            $entry_type['ordering'] = FALSE;
            $entry_type['statuses'] = array();
            $entry_type['field_groups'] = array(
                array(
                    'name' => $entry_type['name'],
                    'nice_name' => $entry_type['nice_name'],
                    'weight' => 0,
                    'fields' => array(),
                ),
            );
        }
        else
        {
            $fgns = $fgs = array();
            foreach ($entry_type['field_groups'] as &$fg)
            {
                $fgs[] = $fg['weight'];
                $fgns[] = $fg['name'];
                $fns = $fs = array();
                foreach ($fg['fields'] as &$f)
                {
                    $fs[] = $f['weight'];
                    $fns[] = $f['name'];
                }
                array_multisort($fs, SORT_NUMERIC, SORT_ASC, $fns, SORT_ASC, $fg['fields']);
            }
            array_multisort($fgs, SORT_NUMERIC, SORT_ASC, $fgns, SORT_ASC, $entry_type['field_groups']);
        }
        $etc->save($entry_type, array('safe' => TRUE));
        return $entry_type;
    }
    // }}}
    // {{{ public function save_entry_field(&$fgs, $data)
    /**
     * This function will register a field with the MPField class and record the
     * field into the proper entry type group
     *
     * @param array &$fgs A pointer to the field groups array of the entry type
     *                    to save to
     * @param array $data The field to be saved
     * @return void
     */
    public function save_entry_field(&$fgs, $data)
    {
        foreach ($fgs as &$group)
        {
            if ($group['name'] === $data['field_group_name'])
            {
                foreach ($group['fields'] as &$cfield)
                {
                    if ($cfield['name'] === $data['name'])
                    {
                        throw new Exception('Field name already exists');
                    }
                }
                $field = MPField::register_field($data);
                $group['fields'][] = array(
                    '_id' => $field['_id'],
                    'name' => $field['name'],
                    'weight' => (int)$data['weight'],
                );
                break;
            }
        }
    }
    // }}}

    // {{{ not yet used or converted
    // {{{ public function get_latest_entries_created($query = array(), $fields = array())
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
    // }}}
    // {{{ public function get_latest_entries_modified($query = array(), $fields = array())
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
    // }}}
    // {{{ public function get_most_revised_entries($query = array(), $fields = array())
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
    // }}}
    // {{{ public function get_entries_titles_by_type_and_field_name($type, $field_name, $field_search)
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

    // }}}
    // {{{ public function search_entry_title_by_title($title, $spec = array())
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

    // }}}
    // }}}
    // }}}
}
