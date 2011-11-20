<?php

/**
 * Taxonomy module 
 * This module helps organize and build relationships between entries of other
 * modules. Handles both categories and tagging.
 * @package Taxonomy
 */
class Taxonomy
{
    //{{{ constants
    const MODULE_DESCRIPTION = 'Organize and relate data';
    const MODULE_AUTHOR = 'Glenn';
    const TYPE_NONE = 0;
    const TYPE_FREE = 1; // tagging
    const TYPE_FLAT = 2; // categories
    const TYPE_TREE = 3; // categories in a heirarchy
    //}}}
    //{{{ properties
    private static $keys;
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
    //{{{ public function hook_content_dashboard_most_revised()
    public function hook_content_dashboard_most_revised()
    {
        $conds = array();
        /* TODO make it work or delete it
        if (!User::has_perm('edit content entries type'))
        {
            $perms['edit'] = User::search_perms('edit content entries type');
            $perms['view'] = User::search_perms('view content entries type');
            $perms['edit_term'] = User::search_perms('edit content with taxonomy term');
            $perms['view_term'] = User::search_perms('view content with taxonomy term');
            $type_ids = array();
            $term_ids = array();
            foreach ($perms as $level => $tokens)
            {
                foreach ($tokens as &$token)
                {
                    $token = explode('-', $token);
                    switch ($level)
                    {
                        case 'edit':
                        case 'view':
                            $type_ids[] = $token[1];
                        break;
                        case 'edit_term':
                        case 'view_term':
                            $term_ids[] = $token[2];
                        break;
                    }
                }
            }
            if ($type_ids)
            {
                $conds[] = 'SELECT      te.term_id
                            FROM        TaxonomyEntry te
                            LEFT JOIN   te.TaxonomyTerm tt
                            LEFT JOIN   tt.TaxonomyScheme ts
                            WHERE       ts.mkey IN ('.implode(',', $type_ids).')
                                AND     ts.module = "Content"';
            }
            if ($term_ids)
            {
                $conds[] = 'SELECT      tte.term_id
                            FROM        TaxonomyEntry tte
                            LEFT JOIN   tte.TaxonomyTerm ttt
                            WHERE       ttt.id IN ('.implode(',', $term_ids).')';
            }
        }
        */
        return $conds;
    }
    //}}}
    //{{{ public function hook_content_edit_type_form(&$layout)
    public function hook_content_edit_type_form(&$layout)
    {
        /*
        $groups['rows'][] = array(
            'fields' => $layout->get_layout('taxonomy__default'),
            'label' => array(
                'text' => 'Enable taxonomy'
            )
        );
        */
        $groups['rows'][] = array(
            'fields' => $layout->get_layout('taxonomy__status'),
            'label' => array(
                'text' => 'Enable statuses'
            )
        );
        $groups['rows'][] = array(
            'fields' => $layout->get_layout('taxonomy__statuses'),
            'label' => array(
                'text' => 'Status options (one per line)'
            )
        );
        $groups['rows'][] = array(
            'fields' => $layout->get_layout('taxonomy__flag'),
            'label' => array(
                'text' => 'Enable flags'
            )
        );
        $groups['rows'][] = array(
            'fields' => $layout->get_layout('taxonomy__flags'),
            'label' => array(
                'text' => 'Flag options (one per line)'
            )
        );
        return $groups;
    }
    //}}}
    //{{{ public function hook_content_edit_type_layout(&$layout, &$type)
    public function hook_content_edit_type_layout(&$layout, &$type)
    {
        $tax = new TaxonomyManager('Content', $type['id']);

        $tst = Doctrine::getTable('TaxonomyScheme');
        $sdid = $tax->get_scheme_id('default');
        $ssid = $tax->get_scheme_id('status');
        $sfid = $tax->get_scheme_id('flag');
        $taxonomy['default'] = is_null($sdid) ? NULL : $tst->findOneById($sdid);
        $taxonomy['status'] = is_null($ssid) ? NULL : $tst->findOneById($ssid);
        $taxonomy['flag'] = is_null($sfid) ? NULL : $tst->findOneById($sfid);

        $terms['default'] = is_null($sdid) ? array() : $tax->get_terms('default');
        $terms['status'] = is_null($ssid) ? array() : $tax->get_terms('status');
        $terms['flag'] = is_null($sfid) ? array() : $tax->get_terms('flag');

        $value['default'] = array();
        foreach ($terms['default'] as $term)
        {
            $value['default'][] = $term['term'];
        }
        $value['status'] = array();
        foreach ($terms['status'] as $term)
        {
            $value['status'][] = $term['term'];
        }
        $value['flag'] = array();
        foreach ($terms['flag'] as $term)
        {
            $value['flag'][] = $term['term'];
        }

        $field['default'] = array(
            'field' => Field::layout('radio'),
            'name' => 'taxonomy__default',
            'type' => 'radio',
            'options' => array(
                'data' => array(
                    Taxonomy::TYPE_NONE => 'None',
                    Taxonomy::TYPE_FREE => 'Free tagging',
                    Taxonomy::TYPE_FLAT => 'Categories (flat)',
                    Taxonomy::TYPE_TREE => 'Categories (tree)'
                )
            ),
            'value' => array(
                'data' => $taxonomy['default']['type']
            )
        );
        $field['default_parent'] = array(
            'field' => Field::layout('textarea'),
            'name' => 'taxonomy__default_parent',
            'type' => 'textarea_array',
            'options' => array($value['default'])
        );
        $field['defaults'] = array(
            'field' => Field::layout('textarea'),
            'name' => 'taxonomy__defaults',
            'type' => 'textarea_array',
            'value' => array(
                'data' => implode("\n", $value['default'])
            )
        );
        $field['status'] = array(
            'field' => Field::layout('checkbox_boolean'),
            'name' => 'taxonomy__status',
            'type' => 'checkbox_boolean',
            'value' => array(
                'data' => $taxonomy['status']['type'] ? 1 : NULL
            )
        );
        $field['statuses'] = array(
            'field' => Field::layout('textarea'),
            'name' => 'taxonomy__statuses',
            'type' => 'textarea_array',
            'value' => array(
                'data' => implode("\n", $value['status'])
            )
        );
        $field['flag'] = array(
            'field' => Field::layout('checkbox_boolean'),
            'name' => 'taxonomy__flag',
            'type' => 'checkbox_boolean',
            'value' => array(
                'data' => $taxonomy['flag']['type'] ? 1 : NULL
            )
        );
        $field['flags'] = array(
            'field' => Field::layout('textarea'),
            'name' => 'taxonomy__flags',
            'type' => 'textarea_array',
            'value' => array(
                'data' => implode("\n", $value['flag'])
            )
        );

        return $field;
    }
    //}}}
    //{{{ public function hook_content_edit_type_process(&$layout, $type, $post)
    public function hook_content_edit_type_process(&$layout, $type, $post)
    {
        $pstatus = $post['taxonomy__status'] ? Taxonomy::TYPE_FREE : Taxonomy::TYPE_NONE;
        $pflag = $post['taxonomy__flag'] ? Taxonomy::TYPE_FLAT : Taxonomy::TYPE_NONE;
        $mstatus = deka(FALSE, $post, 'taxonomy__status') ? 1 : NULL;
        $mflag = deka(FALSE, $post, 'taxonomy__flag') ? 1 : NULL;
        $mstatuses = implode("\n", deka(array(), $post, 'taxonomy__statuses'));
        $mflags = implode("\n", deka(array(), $post, 'taxonomy__flags'));
        $layout->merge(array('taxonomy__status' => array('data' => $mstatus)));
        $layout->merge(array('taxonomy__flag' => array('data' => $mflag)));
        $layout->merge(array('taxonomy__statuses' => array('data' => $mstatuses)));
        $layout->merge(array('taxonomy__flags' => array('data' => $mflags)));

        $taxm = new TaxonomyManager('Content', $type['id']);
        $taxm->set_scheme('status', $pstatus);
        $taxm->set_scheme('flag', $pflag);
        if ($mstatus)
        {
            $taxm->set_scheme_terms('status', $post['taxonomy__statuses']);
        }
        if ($mflag)
        {
            $taxm->set_scheme_terms('flag', $post['taxonomy__flags']);
        }
    }
    //}}}
    //{{{ public function hook_content_edit_type_other_links($type_id)
    public function hook_content_edit_type_other_links($type_id)
    {
        $taxm = new TaxonomyManager('Content', $type_id);
        $id = $taxm->get_scheme_id();
        if (is_null($id))
        {
            $uri = '/admin/module/Taxonomy/enable/Content/'.$type_id.'/default/';
            $text = 'enable taxonomy';
        }
        else
        {
            $uri = '/admin/module/Taxonomy/edit_scheme/'.$id.'/';
            $text = 'taxonomy';
        }
        return array(
            array('uri' => $uri, 'text' => $text)
        );
    }
    //}}}
    //{{{ public function hook_content_entry_edit_access($type, $entry)
    /**
     * Controls access to entry edit page
     * 
     * @param int $type content type id
     * @param int $entry entry id
     * @return int
     */
    public function hook_content_entry_edit_access($type, $entry)
    {
        $level = Content::ACCESS_DENY;

        if (User::perm('edit content with taxonomy term-'.$type))
        {
            $level = Content::ACCESS_EDIT;
        }
        else
        {
            $taxm = new TaxonomyManager('Content', $type);
            $terms = $taxm->get_entry_terms($entry);
            foreach ($terms as $term)
            {
                if (User::perm('edit content with taxonomy term-'.$type.'-'.$term['term_id']))
                {
                    $level = Content::ACCESS_EDIT;
                }
            }
        }

        if ($level === Content::ACCESS_DENY)
        {
            if (User::perm('view content with taxonomy term-'.$type))
            {
                $level = Content::ACCESS_VIEW;
            }
            else
            {
                foreach ($terms as $term)
                {
                    if (User::perm('view content with taxonomy term-'.$type.'-'.$term['term_id']))
                    {
                        $level = Content::ACCESS_VIEW;
                    }
                }
            }
        }
        return $level;
    }
    //}}}
    //{{{ public function hook_content_entry_sidebar_edit($entry)
    /**
     * Builds the sidebar for the content entry form
     * 
     * @param array $entry content entry data
     * @return array
     */
    public function hook_content_entry_sidebar_edit($entry)
    {
        $ctid = &$entry['content_entry_type_id'];
        $taxm = new TaxonomyManager('Content', $ctid);
        $schemes = $taxm->get_schemes();
        $entry_term_rows = $taxm->get_entry_terms($entry['id']);
        $entry_terms = array();
        foreach ($entry_term_rows as $row)
        {
            if (User::has_perm('assign any '.$row['scheme_name'].'-'.$ctid, 'assign taxonomy term-'.$ctid.'-'.$row['term_id']))
            {
                $entry_terms[$row['scheme_name']][] = $row['term_id'];
            }
        }
        $form = array();

        foreach ($schemes as $scheme)
        {
            if ($scheme['type'] != Taxonomy::TYPE_NONE)
            {
                $name = &$scheme['name'];
                $terms = $taxm->arrange_terms($taxm->get_terms($name), $scheme['type']);
                if ($terms)
                {
                    $options = array();
                    foreach ($terms as $term)
                    {
                        if (User::has_perm('assign any '.$term['scheme_name'].'-'.$ctid, 'assign taxonomy term-'.$ctid.'-'.$term['id']))
                        {
                            $options[$term['id']] = $term['term'];
                        }
                    }
                    $form[$name] = array();
                    switch ($name)
                    {
                        case 'default':
                            $form[$name]['fields'] = array(
                                'field' => Field::layout('checkbox'),
                                'name' => 'taxonomy__default',
                                'type' => 'checkbox',
                                'options' => array('data' => $options),
                                'value' => array('data' => deka(array(), $entry_terms, $name))
                            );
                            $form[$name]['label'] = array(
                                'text' => 'Taxonomy'
                            );
                        break;
                        case 'flag':
                            $form[$name]['fields'] = array(
                                'field' => Field::layout('checkbox'),
                                'name' => 'taxonomy__flags',
                                'type' => 'checkbox',
                                'options' => array('data' => $options),
                                'value' => array('data' => deka(array(), $entry_terms, $name))
                            );
                            $form[$name]['label'] = array(
                                'text' => 'Flags'
                            );
                        break;
                        case 'status':
                            $form[$name]['fields'] = array(
                                'field' => Field::layout('dropdown'),
                                'name' => 'taxonomy__status',
                                'type' => 'dropdown',
                                'options' => array('data' => $options),
                                'value' => array('data' => deka(array(), $entry_terms, $name))
                            );
                            $form[$name]['label'] = array(
                                'text' => 'Status'
                            );
                        break;
                    }
                }
            }
        }
        return $form;
    }
    //}}}
    //{{{ public function hook_content_entry_sidebar_edit_process(&$layout, &$entry, $data)
    /**
     * Content module's hook when new entries and edits are made
     *
     * @param array $data
     * @return array
     */
    public function hook_content_entry_sidebar_edit_process(&$layout, &$entry, $data)
    {
        $ctid = $entry['content_entry_type_id'];
        $taxm = new TaxonomyManager('Content', $ctid);
        $terms = $taxm->get_terms();
        foreach ($data as $scheme => $ids)
        {
            if (!is_array($ids) && !is_null($ids))
            {
                $ids = array($ids);
            }
            foreach ($ids as &$id)
            {
                if (eka($terms, $id, 'scheme_name'))
                {
                    $scheme_name = $terms[$id]['scheme_name'];
                    if (!User::has_perm('assign any '.$scheme_name.'-'.$ctid, 'assign taxonomy term-'.$ctid.'-'.$id))
                    {
                        unset($id);
                    }
                }
                else
                {
                    if (!User::has_perm('assign taxonomy term-'.$ctid.'-'.$id))
                    {
                        unset($id);
                    }
                }
            }
            $scheme = substr($scheme, 10);
            $taxm->set_entry_term_ids($entry['content_entry_meta_id'], $ids, $scheme);
        }
    }
    //}}}
    //{{{ public function hook_content_entry_sidebar_new($ctid)
    /**
     * Builds the sidebar for the content entry form
     * 
     * @param int $ctid content type id
     * @return array
     */
    public function hook_content_entry_sidebar_new($ctid)
    {
        $taxm = new TaxonomyManager('Content', $ctid);
        $schemes = $taxm->get_schemes();
        $form = array();
        foreach ($schemes as $scheme)
        {
            if ($scheme['type'] != Taxonomy::TYPE_NONE)
            {
                $name = &$scheme['name'];
                $terms = $taxm->arrange_terms($taxm->get_terms($name), $scheme['type']);
                if ($terms)
                {
                    $options = array();
                    foreach ($terms as $term)
                    {
                        if (User::has_perm('assign any '.$term['scheme_name'].'-'.$ctid, 'assign taxonomy term-'.$ctid.'-'.$term['id']))
                        {
                            $options[$term['id']] = $term['term'];
                        }
                    }
                    $form[$name] = array();
                    switch ($name)
                    {
                        case 'default':
                            $form[$name]['fields'] = array(
                                'field' => Field::layout('checkbox'),
                                'name' => 'taxonomy__default',
                                'type' => 'checkbox',
                                'options' => array('data' => $options)
                            );
                            $form[$name]['label'] = array(
                                'text' => 'Taxonomy'
                            );
                        break;
                        case 'flag':
                            $form[$name]['fields'] = array(
                                'field' => Field::layout('checkbox'),
                                'name' => 'taxonomy__flags',
                                'type' => 'checkbox',
                                'options' => array('data' => $options)
                            );
                            $form[$name]['label'] = array(
                                'text' => 'Flags'
                            );
                        break;
                        case 'status':
                            $form[$name]['fields'] = array(
                                'field' => Field::layout('dropdown'),
                                'name' => 'taxonomy__status',
                                'type' => 'dropdown',
                                'options' => array('data' => $options)
                            );
                            $form[$name]['label'] = array(
                                'text' => 'Status'
                            );
                        break;
                    }
                }
            }
        }
        return $form;
    }
    //}}}
    //{{{ public function hook_content_entry_sidebar_new_process(&$layout, &$entry, $data)
    /**
     * Content module's hook when new entries and edits are made
     *
     * @param array $data
     * @return array
     */
    public function hook_content_entry_sidebar_new_process(&$layout, &$entry, $data)
    {
        $ctid = $entry['content_entry_type_id'];
        $taxm = new TaxonomyManager('Content', $ctid);
        $terms = $taxm->get_terms();
        foreach ($data as $scheme => $ids)
        {
            if (!is_array($ids) && !is_null($ids))
            {
                $ids = array($ids);
            }
            foreach ($ids as &$id)
            {
                if (eka($terms, $id, 'scheme_name'))
                {
                    $scheme_name = $terms[$id]['scheme_name'];
                    if (!User::has_perm('assign any '.$scheme_name.'-'.$ctid, 'assign taxonomy term-'.$ctid.'-'.$id))
                    {
                        unset($id);
                    }
                }
                else
                {
                    if (!User::has_perm('assign taxonomy term-'.$ctid.'-'.$id))
                    {
                        unset($id);
                    }
                }
            }
            $scheme = substr($scheme, 10);
            $taxm->set_entry_term_ids($entry['content_entry_meta_id'], $ids, $scheme);
        }
    }
    //}}}
    //{{{ public function hook_content_new_type_form(&$layout)
    public function hook_content_new_type_form(&$layout)
    {
        $groups = array(
            'rows' => array(
                array(
                    'fields' => $layout->get_layout('taxonomy__default'),
                    'label' => array(
                        'text' => 'Enable taxonomy'
                    )
                ),
                array(
                    'fields' => $layout->get_layout('taxonomy__status'),
                    'label' => array(
                        'text' => 'Enable statuses'
                    )
                ),
                array(
                    'fields' => $layout->get_layout('taxonomy__statuses'),
                    'label' => array(
                        'text' => 'Status options (one per line)'
                    )
                ),
                array(
                    'fields' => $layout->get_layout('taxonomy__flag'),
                    'label' => array(
                        'text' => 'Enable flags'
                    )
                ),
                array(
                    'fields' => $layout->get_layout('taxonomy__flags'),
                    'label' => array(
                        'text' => 'Flag options (one per line)'
                    )
                )
            )
        );
        return $groups;
    }
    //}}}
    //{{{ public function hook_content_new_type_layout()
    public function hook_content_new_type_layout()
    {
        $fields['default'] = array(
            'field' => Field::layout('radio'),
            'name' => 'taxonomy__default',
            'type' => 'radio',
            'options' => array(
                'data' => array(
                    Taxonomy::TYPE_NONE => 'None',
                    Taxonomy::TYPE_FREE => 'Free tagging',
                    Taxonomy::TYPE_FLAT => 'Categories (flat)',
                    Taxonomy::TYPE_TREE => 'Categories (tree)'
                )
            ),
        );
        $fields['status'] = array(
            'field' => Field::layout('checkbox_boolean'),
            'name' => 'taxonomy__status',
            'type' => 'checkbox_boolean',
        );
        $fields['statuses'] = array(
            'field' => Field::layout('textarea'),
            'name' => 'taxonomy__statuses',
            'type' => 'textarea_array',
        );
        $fields['flag'] = array(
            'field' => Field::layout('checkbox_boolean'),
            'name' => 'taxonomy__flag',
            'type' => 'checkbox_boolean',
        );
        $fields['flags'] = array(
            'field' => Field::layout('textarea'),
            'name' => 'taxonomy__flags',
            'type' => 'textarea_array',
        );
        return $fields;
    }
    //}}}
    //{{{ public function hook_content_new_type_process(&$layout, $type, $post)
    public function hook_content_new_type_process(&$layout, $type, $post)
    {
        $pstatus = $post['taxonomy__status'] ? Taxonomy::TYPE_FREE : Taxonomy::TYPE_NONE;
        $pflag = $post['taxonomy__flag'] ? Taxonomy::TYPE_FLAT : Taxonomy::TYPE_NONE;
        $mstatus = deka(FALSE, $post, 'taxonomy__status') ? 1 : NULL;
        $mflag = deka(FALSE, $post, 'taxonomy__flag') ? 1 : NULL;
        $mstatuses = implode("\n", deka(array(), $post, 'taxonomy__statuses'));
        $mflags = implode("\n", deka(array(), $post, 'taxonomy__flags'));
        $layout->merge(array('taxonomy__status' => array('data' => $mstatus)));
        $layout->merge(array('taxonomy__flag' => array('data' => $mflag)));
        $layout->merge(array('taxonomy__statuses' => array('data' => $mstatuses)));
        $layout->merge(array('taxonomy__flags' => array('data' => $mflags)));

        $taxm = new TaxonomyManager('Content', $type['id']);
        $taxm->set_scheme('status', $pstatus);
        $taxm->set_scheme('flag', $pflag);
        if ($mstatus)
        {
            $taxm->set_scheme_terms('status', $post['taxonomy__statuses']);
        }
        if ($mflag)
        {
            $taxm->set_scheme_terms('flag', $post['taxonomy__flags']);
        }
    }
    //}}}
    //{{{ public function hook_content_perms($types)
    public function hook_content_perms($types)
    {
        $perms = array();
        $taxm = new TaxonomyManager('Content');
        $terms = $taxm->get_all_terms();
        $schemes = array();
        $term_ids = array();
        $types = place_ids($types);
        foreach ($terms as $term)
        {
            $term_ids[$term['mkey']][$term['name']] = $term['id'];
            if (eka($term, 'terms'))
            {
                if (eka($types, $term['mkey'], 'name'))
                {
                    $type_name = $types[$term['mkey']]['name'];
                    $type_id = $types[$term['mkey']]['id'];
                    switch ($term['name'])
                    {
                        case 'default':
                            $pkey = 'Taxonomy Terms';
                            $paval = 'Can assign &ldquo;'.$type_name.'&rdquo; entries term ';
                            $perms[$pkey]['assign any default-'.$type_id] = 'Allow assign all terms to &ldquo;'.$type_name.'&rdquo;';
                        break;
                        case 'flag':
                            $pkey = 'Flags';
                            $paval = 'Can assign &ldquo;'.$type_name.'&rdquo; entries flag ';
                            $peval = 'Can edit &ldquo;'.$type_name.'&rdquo; entries flag ';
                            $pvval = 'Can view &ldquo;'.$type_name.'&rdquo; entries flag ';
                            $perms[$pkey]['assign any flag-'.$type_id] = 'Can assign &ldquo;'.$type_name.'&rdquo; entries any flag';
                            $perms[$pkey]['edit content with any flag-'.$type_id] = 'Can edit &ldquo;'.$type_name.'&rdquo; entries with any flag';
                            $perms[$pkey]['view content with any flag-'.$type_id] = 'Can view &ldquo;'.$type_name.'&rdquo; entries with any flag in admin backend';
                        break;
                        case 'status':
                            $pkey = 'Statuses';
                            $paval = 'Can assign &ldquo;'.$type_name.'&rdquo; entries status ';
                            $peval = 'Can edit &ldquo;'.$type_name.'&rdquo; entries with status ';
                            $pvval = 'Can view &ldquo;'.$type_name.'&rdquo; entries with status ';
                            $perms[$pkey]['assign any status-'.$type_id] = 'Can assign &ldquo;'.$type_name.'&rdquo; entries any status';
                            $perms[$pkey]['edit content with any status-'.$type_id] = 'Can edit &ldquo;'.$type_name.'&rdquo; entries with any status';
                            $perms[$pkey]['view content with any status-'.$type_id] = 'Can view &ldquo;'.$type_name.'&rdquo; entries with any status in admin backend';
                        break;
                    }
                    foreach ($term['terms'] as $t)
                    {
                        $perms[$pkey]['assign taxonomy term-'.$type_id.'-'.$t['id']] = $paval.'&ldquo;'.$t['term'].'&rdquo;';
                        if ($term['name'] == 'status' || $term['name'] == 'flag')
                        {
                            $perms[$pkey]['edit content with taxonomy term-'.$type_id.'-'.$t['id']] = $peval.'&ldquo;'.$t['term'].'&rdquo;';
                            $perms[$pkey]['view content with taxonomy term-'.$type_id.'-'.$t['id']] = $pvval.'&ldquo;'.$t['term'].'&rdquo; in admin back end';
                        }
                    }
                }
            }
        }
        foreach ($types as $type)
        {
            if (eka($term_ids, $type['id']))
            {
                $perms['Taxonomy']['edit taxonomy'] = 'Edit taxonomy for all types';
                $perms['Taxonomy']['edit taxonomy-'.$term_ids[$type['id']]] = 'Edit taxonomy for content type '.$type['name'];
            }
        }
        foreach ($perms as &$perm)
        {
            asort($perm);
        }
        return $perms;
    }
    //}}}
    // {{{ public function hook_content_get_entry_taxonomy($type_id, $entry_id, $scheme = NULL)
    public function hook_content_get_entry_taxonomy($type_id, $entry_id, $scheme = NULL)
    {
        $taxm = new TaxonomyManager('Content', $type_id);
        $schemes = is_null($scheme)
            ? $taxm->get_schemes()
            : array(array('name' => $scheme));
        $results = array();
        foreach ($schemes as $scheme)
        {
            $results['taxonomy'][$scheme['name']] = $taxm->get_entry_terms($entry_id, $scheme['name']);
            $results['taxonomy_terms'][$scheme['name']] = array();
            $terms = array();
            foreach ($results['taxonomy'][$scheme['name']] as &$v)
            {
                unset($v['TaxonomyTerm']);
                $terms[] = $v['term_name'];
            }
            if (!empty($terms))
            {
                $all_terms = $taxm->get_parent_terms($scheme['name'], $terms, TRUE);
                foreach ($all_terms as $term)
                {
                    if (!in_array($term['term'], $terms))
                    {
                        $terms[] = $term['term'];
                    }
                }
            }
            $results['taxonomy_terms'][$scheme['name']] = $terms;
        }

        return $results;
    }

    // }}}
    //{{{ public function hook_content_entry_delete_finish($meta)
    public function hook_content_entry_delete_finish($meta)
    {
        $taxm = new TaxonomyManager('Content', $meta['content_entry_type_id']);
        $taxm->remove_entry_ids($meta['content_entry_meta_id']);
    }

    //}}}
}

class TaxonomyManager
{
    //{{{ properties
    /**
     * @protected $module module name
     */
    protected $module;
    /**
     * @protected $key taxonomy set key
     */
    protected $key;

    //}}}
    //{{{ public function __construct($module, $key = NULL)
    /**
     * Constructor
     *
     * @param string $module module name
     * @return void
     */
    public function __construct($module, $key = NULL)
    {
        $this->module = $module;
        $this->key = $key;
    }
    //}}}
    //{{{ public function set_entry_terms($entry, $terms, $scheme = 'default')
    /**
     * Sets an entry and automatically inserts tag rows if needed
     *
     * @param int $entry entry id
     * @param array $terms terms
     * @param string $scheme scheme name
     * @return void
     */
    public function set_entry_terms($entry, $terms, $scheme = 'default')
    {
        $ttq = Doctrine_Query::create()
               ->select('k.id, k.name, k.scheme_id')
               ->from('TaxonomyKeyword k')
               ->leftJoin('k.TaxonomyScheme s')
               ->whereIn('k.name', $terms)
               ->andWhere('s.module = ?', $this->module)
               ->andWhere('s.key = ?', $this->key);
        $kws = $ttq->execute();

        $keywords = array();
        foreach ($kws as $kw)
        {
            $keywords[$kw['id']] = $kw['name'];
        }

        Doctrine_Query::create()
            ->delete()
            ->from('TaxonomyEntry e')
            ->whereNotIn('e.keyword_id', array_keys($keywords))
            ->andWhere('e.entry_id = ?', $entry)
            ->execute();

        $ksq = Doctrine_Query::create()
               ->select('k.name')
               ->from('TaxonomyEntry e')
               ->leftJoin('e.TaxonomyKeyword k')
               ->whereIn('k.id', array_keys($keys))
               ->andWhere('e.entry_id = ?', $entry);
    }
    //}}}
    //{{{ public function set_entry_term_ids($entry, $ids, $scheme = 'default')
    /**
     * Sets entry term rows based on term id
     *
     * @param int $entry entry id
     * @param array $ids term ids
     * @param string $scheme scheme name
     * @return void
     */
    public function set_entry_term_ids($entry, $ids, $scheme = 'default')
    {
        $ttq = Doctrine_Query::create()
               ->select('t.id, t.term, t.scheme_id')
               ->from('TaxonomyTerm t')
               ->leftJoin('t.TaxonomyScheme s')
               ->whereIn('t.id', $ids)
               ->andWhere('s.module = ?', $this->module)
               ->andWhere('s.name = ?', $scheme)
               ->andWhere('s.mkey = ?', $this->key);
        $tts = $ttq->fetchArray();

        $terms = array();
        if (!empty($ids))
        {
            foreach ($tts as $tt)
            {
                $terms[$tt['id']] = $tt['term'];
            }
        }

        $eids = Doctrine_Query::create()
            ->select('e.id')
            ->from('TaxonomyEntry e')
            ->leftJoin('e.TaxonomyTerm t')
            ->whereNotIn('e.term_id', array_keys($terms))
            ->andWhere('e.entry_id = ?', $entry)
            ->andWhere('t.scheme_id = ?', $tts[0]['scheme_id'])
            ->fetchArray();

        $teids = array();
        foreach ($eids as $eid)
        {
            $teids[] = $eid['id'];
        }

        if (!empty($teids))
        {
            Doctrine_Query::create()
                ->delete()
                ->from('TaxonomyEntry e')
                ->whereIn('e.id', $teids)
                ->execute();
        }

        if (empty($ids))
        {
            return FALSE;
        }

        $etq = Doctrine_Query::create()
               ->select('e.term_id')
               ->from('TaxonomyEntry e')
               ->whereIn('e.term_id', array_keys($terms))
               ->andWhere('e.entry_id = ?', $entry);
        $ets = $etq->fetchArray();
        $etids = array();
        foreach ($ets as $et)
        {
            $tid = $et['term_id'];
            if (!in_array($tid, $etids))
            {
                $etids[] = $et['term_id'];
            }
        }

        foreach ($ids as $id)
        {
            if (!in_array($id, $etids))
            {
                $tem = new TaxonomyEntry;
                $tem->term_id = $id;
                $tem->entry_id = $entry;
                $tem->save();
                $tem->free();
            }
        }
    }
    //}}}
    //{{{ public function set_key($key)
    /**
     * Sets the key property
     *
     * @param mixed $key key property
     * @return void
     */
    public function set_key($key)
    {
        $this->key = $key;
    }
    //}}}
    //{{{ public function set_module($module)
    /**
     * Sets the module property
     *
     * @param string $module module name
     * @return void
     */
    public function set_module($module)
    {
        $this->module = $module;
    }
    //}}}
    //{{{ public function set_scheme($name, $type = Taxonomy::TYPE_FREE)
    /**
     * Updates a taxonomy scheme
     *
     * @param string $name scheme name
     * @param int $type set type constant
     * @return void
     */
    public function set_scheme($name, $type = Taxonomy::TYPE_FREE)
    {
        $tid = $this->get_scheme_id($name);
        if (is_null($tid))
        {
            $this->add_scheme($name, $type);
        }
        else
        {
            $tst = Doctrine::getTable('TaxonomyScheme');
            $scheme = $tst->findOneById($tid);
            if ($scheme->type != $type)
            {
                $scheme->type = $type;
                $scheme->save();
            }
        }
    }
    //}}}
    //{{{ public function set_scheme_terms($name, $terms)
    /**
     * Updates a taxonomy scheme's terms
     *
     * @param string $name scheme name
     * @param array $terms term names
     * @return void
     */
    public function set_scheme_terms($name, $terms)
    {
        $sid = $this->get_scheme_id($name);
        if (is_null($sid))
        {
            return;
            $sid = $this->add_scheme($name, $type);
        }
        $sterms = $this->get_terms($name);
        $rterms = array();
        $oterms = array();
        foreach ($sterms as $id => $sterm)
        {
            if (in_array($sterm['term'], $terms))
            {
                $oterms[] = $sterm['term'];
            }
            else
            {
                $rterms[] = $id;
            }
        }
        $this->remove_terms($rterms);
        $nterms = array_diff($terms, $oterms);
        $this->add_terms($nterms, $name);
    }
    //}}}
    //{{{ public function add_entry_categories($entry, $cats)
    /**
     * Adds an entry and category ids
     *
     * @param int $entry entry id
     * @param array $cats category ids
     * @return void
     */
    public function add_entry_categories($entry, $cats)
    {
    }
    //}}}
    //{{{ public function add_keyword_categories($cats, $set = 'default', $parent = NULL)
    /**
     * Add categories to the keyword table
     *
     * @param array $keywords array of keyword entries
     * @param string $set set name
     * @return void
     */
    public function add_keyword_categories($cats, $set = 'default', $parent = NULL)
    {
        $sid = is_int($set) ? $set : $this->get_set_id($set);
        if (!is_null($sid))
        {
            foreach ($cats as $cat)
            {
                $row = new TaxonomyKeyword;
                $row->set_id = $sid;
                $row->name = $cat['name'];
                $row->parent_id = $parent;
                $row->weight = deka(0, $cat, 'weight');
                $row->save();
                if (eka($cat['children']))
                {
                    $this->add_keyword_categories($cat['children'], (int)$sid, $row->id);
                }
            }
        }
    }
    //}}}
    //{{{ public function add_keyword_tags($tags, $set = 'default')
    /**
     * Add tags to the keyword table
     *
     * @param array $keywords array of keyword entries
     * @param string $set set name
     * @return void
     */
    public function add_keyword_tags($keywords, $set = 'default')
    {
        $sid = $this->get_set_id($set);
        if (!is_null($sid))
        {
            $tkc = new Doctrine_Collection('TaxonomyKeyword');
            foreach ($keywords as $k => $keyword)
            {
                $tag = $keyword;
                $tkc[$k]->set_id = $sid;
                $tkc[$k]->name = $tag['name'];
                $tkc[$k]->parent_id = deka(NULL, $tag, 'parent_id');
            }
            $tkc->save();
            return $tkc;
        }
        return;
    }
    //}}}
    //{{{ public function add_terms($terms, $scheme = 'default')
    /**
     * Add terms to the term table
     *
     * @param array $terms array of terms
     * @param string $scheme scheme name
     * @return void
     */
    public function add_terms($terms, $scheme = 'default')
    {
        $sid = $this->get_scheme_id($scheme);
        if (!is_null($sid))
        {
            $tkc = new Doctrine_Collection('TaxonomyTerm');
            foreach ($terms as $k => $term)
            {
                if (!is_array($term) && !empty($term))
                {
                    $term = array('term' => $term);
                }
                if (!empty($term) && eka($term, 'term'))
                {
                    $tkc[$k]->scheme_id = $sid;
                    $tkc[$k]->term = $term['term'];
                    $tkc[$k]->slug = slugify($term['term']);
                    $tkc[$k]->parent_id = deka(NULL, $term, 'parent_id');
                }
            }
            $tkc->save();
            return $tkc;
        }
        return;
    }
    //}}}
    //{{{ public function add_scheme($name, $type = Taxonomy::TYPE_FREE)
    /**
     * Creates a taxonomy scheme
     *
     * @param string $name scheme name
     * @param int $type set type constant
     * @return scheme id
     */
    public function add_scheme($name, $type = Taxonomy::TYPE_FREE)
    {
        $scheme = new TaxonomyScheme();
        $scheme->name = $name;
        $scheme->module = $this->module;
        $scheme->mkey = $this->key;
        $scheme->type = $type;
        $scheme->save();
        return $scheme->id;
    }
    //}}}
    //{{{ public function get_all_terms($mkey = NULL)
    /**
     * Gets all terms for a module for all mkeys unless specified
     *
     * @param int $mkey
     * @return array
     */
    public function get_all_terms($mkey = NULL)
    {
        $schemes = Doctrine_Query::create()
                   ->select('s.*')
                   ->from('TaxonomyScheme s')
                   ->where('s.module = ?', $this->module);
        if (!is_null($mkey))
        {
            $schemes->andWhere('s.mkey = ?', $mkey);
        }
        $schemes = place_ids($schemes->fetchArray());
        $sids = array_keys($schemes);
        $terms = Doctrine_Query::create()
                 ->select('t.*')
                 ->from('TaxonomyTerm t')
                 ->whereIn('t.scheme_id', $sids)
                 ->fetchArray();
        foreach ($terms as $term)
        {
            $sid = &$term['scheme_id'];
            $schemes[$sid]['terms'][] = $term;
        }
        return $schemes;
    }
    //}}}
    //{{{ public function get_entries($terms, $set = 'default')
    /**
     * Returns array of ids that mactch $terms
     *
     * @param array $terms array of term ids or names
     * @param mixed $set keyword set name or id
     * @return array
     */
    public function get_entries($terms, $set = 'default', $negate = FALSE)
    {
        $eq = Doctrine_Query::create()
              ->select('e.entry_id')
              ->from('TaxonomyEntry e')
              ->leftJoin('e.TaxonomyTerm t')
              ->leftJoin('t.TaxonomyScheme s')
              ->where('s.module = ?', $this->module)
              ->andWhere('s.mkey = ?', $this->key)
              ->andWhere('s.name = ?', $set);
        if (is_string($terms[0]))
        {
            if ($negate)
            {
                $eq->andWhereNotIn('t.term', $terms);
            }
            else
            {
                $eq->andWhereIn('t.term', $terms);
            }
        }
        elseif (is_numeric($terms[0]))
        {
            if ($negate)
            {
                $eq->andWhereNotIn('e.term_id', $terms);
            }
            else
            {
                $eq->andWhereIn('e.term_id', $terms);
            }
        }
        if (!empty($ids))
        {
            $eq->andWhereIn('e.entry_id', $ids);
        }
        return $eq->fetchArray();
    }
    //}}}
    //{{{ public function get_entry_terms($entry_id, $scheme = NULL)
    /**
     * Returns array of ids and associated keywords
     *
     * @param int $entry_id entry id
     * @param string $scheme scheme name. NULL looks up all schemes
     * @return array
     */
    public function get_entry_terms($entry_id, $scheme = NULL)
    {
        $eq = Doctrine_Query::create()
              ->select('e.entry_id, e.term_id, t.parent_id as parent_id, t.id, t.term as term_name, s.id, s.name as scheme_name')
              ->from('TaxonomyEntry e')
              ->leftJoin('e.TaxonomyTerm t')
              ->leftJoin('t.TaxonomyScheme s')
              ->where('e.entry_id = ?', $entry_id)
              ->andWhere('s.mkey = ?', $this->key)
              ->andWhere('s.module = ?', $this->module);
        if (!is_null($scheme))
        {
            $eq->andWhere('s.name = ?', $scheme);
        }
        return $eq->fetchArray();
    }
    //}}}
    // {{{ public function get_parent_terms($scheme = 'default', $terms = NULL, $deep = FALSE)
    /**
     * Get the parent terms of a scheme
     *
     * @param string $scheme scheme name
     * @return array
     */
    public function get_parent_terms($scheme = 'default', $terms = NULL, $deep = FALSE)
    {
        $sq = Doctrine_Query::create()
              ->select('t.*, s.name as scheme_name')
              ->from('TaxonomyTerm t')
              ->leftJoin('t.TaxonomyScheme s')
              ->where('s.mkey = ?', $this->key)
              ->andWhere('s.module = ?', $this->module)
              ->andWhere('s.name = ?', $scheme)
              ->andWhere('t.parent_id IS NULL');
        if (is_array($terms) && !empty($terms))
        {
            $sq->andWhereIn('t.term', $terms);
        }
        $rows = $sq->fetchArray();
        $result = array();

        if (count($rows))
        {
            $result = $this->get_terms($scheme, array_keys(place_ids($rows)), $deep);
        }

        return $result;
    }
    //}}}
    //{{{ public function get_terms($scheme = NULL, $ids = NULL, $deep = FALSE)
    /**
     * Get the terms of all schemes in the mkey unless specified
     *
     * @param string $scheme scheme name
     * @param array $ids optional array of ids. if NULL, gets all
     * @param bool $deep with use of $ids. if TRUE, gets children
     * @return array
     */
    public function get_terms($scheme = NULL, $ids = NULL, $deep = FALSE)
    {
        $sq = Doctrine_Query::create()
              ->select('t.*, s.name as scheme_name')
              ->from('TaxonomyTerm t')
              ->leftJoin('t.TaxonomyScheme s')
              ->where('s.mkey = ?', $this->key)
              ->andWhere('s.module = ?', $this->module);
        if (!is_null($scheme))
        {
            $sq->andWhere('s.name = ?', $scheme);
        }
        if (!is_null($ids))
        {
            $sq->andWhereIn('t.id', $ids);
            if ($deep)
            {
                $sq->orWhereIn('t.parent_id', $ids);
            }
        }
        $rows = $sq->fetchArray();
        $terms = place_ids($rows);
        return $terms;
    }
    //}}}
    //{{{ public function get_term_scheme($id)
    /**
     * Get the scheme of a term
     *
     * @param int $id term id
     * @return array
     */
    public function get_term_scheme($id)
    {
        $sq = Doctrine_Query::create()
              ->select('s.*, t.*')
              ->from('TaxonomyTerm t')
              ->leftJoin('t.TaxonomyScheme s')
              ->where('t.id = ?')
              ->fetchOne(array($id), Doctrine::HYDRATE_ARRAY);
        $tax['term'] = $sq;
        $tax['scheme'] = $sq['TaxonomyScheme'];
        unset($tax['term']['TaxonomyScheme']);
        return $tax;
    }
    //}}}
    //{{{ public function get_empty_keywords($set = 'default')
    /**
     * Get the keywords where no entries are linked
     *
     * @param string $set set name
     * @return array
     */
    public function get_empty_keywords($set = 'default')
    {
        $kq = Doctrine_Query::create()
              ->select('k.*')
              ->from('TaxonomyKeyword k')
              ->leftJoin('k.TaxonomySet s')
              ->where('s.key = ?', $this->key)
              ->andWhere('s.name = ?', $set)
              ->andWhere('s.module = ?', $this->module)
              ->andWhere('k.id NOT IN 
                            (
                                SELECT      se.id 
                                FROM        TaxonomyEntry se 
                                LEFT JOIN   se.TaxonomyKeyword sk 
                                WHERE       sk.set_id = s.id
                            )');
        $krows = $kq->execute();
        /*
        $keys = array();
        foreach ($krows as $row)
        {
            $keys[$row['id']] = $row['name'];
        }

        $eq = Doctrine_Query::create()
              ->select('e.id, e.keyword_id')
              ->from('TaxonomyEntry e')
              ->whereIn('e.keyword_id = ?');
        $entries = $eq->execute(array(array_keys($keys)), Doctrine::HYDRATE_ARRAY);
        foreach ($entries as $entry)
        {
            if (eka($keys, $entry['keyword_id']))
            {
                unset($keys[$entry['keyword_id']]);
            }
        }
        */
    }
    //}}}
    //{{{ public function get_schemes($scheme = NULL)
    /**
     * Get the schemes' names for the module
     *
     * @param string $scheme optional scheme name
     * @return array
     */
    public function get_schemes($scheme = NULL)
    {
        $tsq = Doctrine_Query::create()
               ->select('s.*')
               ->from('TaxonomyScheme s')
               ->where('s.module = ?', $this->module)
               ->andWhere('s.mkey = ?', $this->key);
        if (!is_null($scheme))
        {
            $tsq->andWhere('s.name = ?', $scheme);
        }
        $sets = $tsq->execute(array(), Doctrine::HYDRATE_ARRAY);
        return $sets;
    }
    //}}}
    //{{{ public function get_scheme_id($scheme = 'default')
    /**
     * Get the set id and type from the name
     *
     * @return int
     */
    public function get_scheme_id($scheme = 'default')
    {
        $row = Doctrine_Query::create()
               ->select('s.id')
               ->from('TaxonomyScheme s')
               ->where('s.mkey = ?')
               ->andWhere('s.name = ?')
               ->andWhere('s.module = ?')
               ->fetchOne(array($this->key, $scheme, $this->module), Doctrine::HYDRATE_ARRAY);
        return $row !== FALSE ? $row['id'] : NULL;
    }
    //}}}
    //{{{ public function has_scheme($scheme)
    /**
     * Check if $scheme exists
     *
     * @param string $scheme name to check
     * @return bool 
     */
    public function has_scheme($scheme)
    {
        $row = Doctrine_Query::create()
               ->from('TaxonomyScheme s')
               ->where('s.mkey = ?')
               ->andWhere('s.name = ?')
               ->andWhere('s.module = ?')
               ->fetchArray(array($this->key, $scheme, $this->module));
        return (boolean)count($row);
    }
    //}}}
    //{{{ public function has_terms($terms = array(), $scheme = 'default')
    /**
     * Checks to see if the terms exist in the scheme
     *
     * @param array $terms terms to check
     * @param string $scheme scheme name
     * @param bool $deep if TRUE, gets children
     * @return bool 
     */
    public function has_terms($terms, $scheme = 'default')
    {
        if (!is_array($terms) || empty($terms))
        {
            return FALSE;
        }
        $rows = Doctrine_Query::create()
                ->from('TaxonomyTerm t')
                ->leftJoin('t.TaxonomyScheme s')
                ->where('s.mkey = ?', $this->key)
                ->andWhere('s.module = ?', $this->module)
                ->andWhere('s.name = ?', $scheme)
                ->andWhereIn('t.term', $terms)
                ->fetchArray();
        return (boolean)count($rows);
    }
    //}}}
    //{{{ public function has_entry_terms($entry_id, $terms, $scheme = 'default', $deep = FALSE)
    /**
     * Returns array of ids and associated keywords
     *
     * @param int $entry_id entry id
     * @param array $terms terms to find
     * @param string $scheme scheme name
     * @param bool $deep find all children
     * @param bool $exact check if entry id as all terms
     * @return array
     */
    public function has_entry_terms($entry_id, $terms, $scheme = 'default', $deep = FALSE, $exact = FALSE)
    {
        if ($deep)
        {
            $all_terms = $this->get_parent_terms($scheme, $terms, TRUE);
            foreach ($all_terms as $term)
            {
                if (!in_array($term['term'], $terms))
                {
                    $terms[] = $term['term'];
                }
            }
        }
        $rows = $this->get_entry_terms($entry_id, $scheme);
        foreach ($rows as $term)
        {
            if (in_array($term['term_name'], $terms))
            {
                if ($exact)
                {
                    continue;
                }
                return TRUE;
            }
            elseif ($exact)
            {
                return FALSE;
            }
        }
        if ($exact)
        {
            return count($rows) === count($terms);
        }
        return FALSE;
    }
    //}}}
    //{{{ public function remove_keywords($keywords, $set = 'default')
    public function remove_keywords($keywords, $set = 'default')
    {
        $tst = Doctrine::getTable('TaxonomySet');
        $tset = $tst->findOneByName($set);

        $tsq = Doctrine_Query::create()
               ->select('s.type')
               ->from('TaxonomySet s')
               ->where('s.key = ?', $this->key)
               ->andWhere('s.name = ?', $set)
               ->andWhere('s.module = ?', $this->module);
        $tset = $tsq->execute();

        $tkq = Doctrine_Query::create()
               ->delete()
               ->from('TaxonomyKeyword k');
        switch ($tset[0]['type'])
        {
            case Taxonomy::TYPE_FREE:
                $tkq->whereIn('k.name', $keywords);
            break;
            case Taxonomy::TYPE_FLAT:
            case Taxonomy::TYPE_TREE:
                $tkq->whereIn('k.id', $keywords);
            break;
        }
        $tkq->execute();
    }
    //}}}
    //{{{ public function remove_terms($ids = array())
    public function remove_terms($ids = array())
    {
        if ($ids)
        {
            Doctrine_Query::create()
                ->delete('TaxonomyTerm t')
                ->whereIn('t.id', $ids)
                ->execute();
        }
    }
    //}}}
    //{{{ public function remove_entry_ids($id)
    public function remove_entry_ids($ids = array())
    {
        if ($ids)
        {
            Doctrine_Query::create()
                ->delete('TaxonomyEntry e')
                ->whereIn('e.entry_id', $ids)
                ->execute();
        }
    }
    //}}}
    //{{{ public function remove_set($set = 'default')
    public function remove_set($set = 'default')
    {
        $sq = Doctrine_Query::create()
              ->delete('TaxonomySet s')
              ->where('s.key = ?', $this->key)
              ->andWhere('s.name = ?', $set)
              ->andWhere('s.module = ?', $this->module)
              ->execute();
    }
    //}}}
    //{{{ public function arrange_terms($terms, $type, $prepend = '&raquo;')
    /**
     * Properly arranges terms based on name, type, parent_id, and weight
     *
     * @param array $terms array of terms
     * @param int $type Taxonomy Type constant
     * @param string $prepend used to display heirarchy for tree types
     * @return array
     */
    public function arrange_terms($terms, $type, $prepend = ' &raquo; ')
    {
        $ordered = array();
        switch ($type)
        {
            case Taxonomy::TYPE_FREE:
            case Taxonomy::TYPE_FLAT:
                $alpha = array();
                foreach ($terms as $term)
                {
                    $alpha[] = $term['term'];
                }
                array_multisort($alpha, $terms, SORT_REGULAR);
                $ordered = $terms;
            break;
            /*
            case Taxonomy::TYPE_FLAT:
                $weight = array();
                foreach ($terms as $term)
                {
                    $weight[$term['weight']][] = $term;
                }
                foreach ($weight as $w => &$v)
                {
                    $alpha = array();
                    foreach ($v as $term)
                    {
                        $alpha[] = $term['term'];
                    }
                    array_multisort($alpha, $v, SORT_REGULAR);
                }
                array_multisort($weight, $terms, SORT_NUMERIC);
                $ordered = $terms;
            break;
            */
            case Taxonomy::TYPE_TREE:
                $names = array();
                $alpha = array();
                foreach ($terms as $k => $term)
                {
                    $names[$k] = $term['term'];
                }
                foreach ($terms as &$term)
                {
                    if (!is_null($term['parent_id']))
                    {
                        $pid = (int)$term['parent_id'];
                        while (!is_null($pid) && eka($names, $pid))
                        {
                            $term['term'] = $names[$pid] . $prepend . $term['term'];
                            $pid = $terms[$pid]['parent_id'];
                        }
                    }
                    $alpha[$term['id']] = strtolower($term['term']);
                }
                $ordered = array();
                asort($alpha);
                foreach ($alpha as $k => $v)
                {
                    $ordered[$k] = $terms[$k];
                }
            break;
        }
        return $ordered;
    }
    //}}}
}

?>
