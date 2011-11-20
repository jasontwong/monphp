<?php

class Krate
{
    //{{{ properties
    //}}}
    //{{{ constants
    const MODULE_AUTHOR = 'Jason';
    const MODULE_DESCRIPTION = 'Krate Specific Module';
    const MODULE_WEBSITE = 'http://www.jasontwong.com/';
    const NUM_HOMEPAGE_SLIDES = 10;

    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        if (is_mobile())
        {
            $_SESSION['force_mobile'] = deka(TRUE, $_SESSION, 'force_mobile');
            if ($_SESSION['force_mobile'] && URI_PART_0 !== 'mobile')
            {
                header('Location: /mobile/');
                exit;
            }
        }
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_admin_nav()
    /*
    public function hook_admin_nav()
    {
        $links = array('Workflow' => array());
        if (User::has_perm('create workflow'))
        {
            $links['Tools']['Create Workflow'] = '/admin/module/Workflow/create/';
        }
        if (User::has_perm('edit workflow'))
        {
            $links['Tools']['Edit Workflow'] = '/admin/module/Workflow/list/';
        }
        return $links;
    }
    */

    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        if (strpos(URI_PATH, '/admin/module/Content/') !== FALSE)
        {
            if (URI_PARTS > 3)
            {
                if (URI_PART_3 === 'new_entry' || URI_PART_3 === 'edit_entry')
                {
                    $js[] = '/file/module/Krate/js/colorpicker.js';
                    $js[] = '/file/module/Krate/js/eye.js';
                    $js[] = '/file/module/Krate/js/utils.js';
                    $js[] = '/admin/static/Krate/field.js/';
                    // $js[] = '/admin/static/Krate/js/layout.js/';
                }
            }
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
            if (URI_PARTS > 3)
            {
                if (URI_PART_3 === 'new_entry' || URI_PART_3 === 'edit_entry')
                {
                    $css['screen'][] = '/file/module/Krate/css/colorpicker.css';
                    // $css['screen'][] = '/admin/static/Krate/css/layout.css/';
                }
            }
        }
        return $css;
    }

    //}}}
    //{{{ public function hook_data_info()
    public function hook_data_info()
    {
        // {{{ homepage fields
        $fields = array();
        for ($i = 1; $i <= self::NUM_HOMEPAGE_SLIDES; $i++)
        {
            $feature_file = Data::query('Krate', 'feature_'.$i.'_file', 'name');
            $has_featured = is_file(DIR_FILE.'/upload/'.$feature_file);
            $hidden['delete'] = !$has_featured;
            $file_field = array(
                'field' => Field::layout(
                    'file',
                    array(
                        'data' => array(
                            'label' => 'Feature Image/SWF '.$i
                        )
                    )
                ),
                'name' => 'feature_'.$i.'_file',
                'type' => 'file',
                'hidden' => $hidden,
                'value' => array(
                    'group_key' => 'Krate',
                )
            );
            if ($has_featured)
            {
                $file_field['html_before'] = array(
                    'data' => '<a target="_blank" href="/file/upload/'.$feature_file.'">click to see the file</a><br />'
                );
            }
            $url_field = array(
                'field' => Field::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Feature Image/SWF '.$i.' URL'
                        )
                    )
                ),
                'name' => 'feature_'.$i.'_url',
                'type' => 'text',
                'value' => array(
                    'data' => Data::query('Krate', 'feature_'.$i.'_url'),
                )
            );
            $fields[] = $file_field;
            $fields[] = $url_field;
        }
        $fields[] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'About Krate Header'
                    )
                )
            ),
            'name' => 'about_head',
            'type' => 'text',
            'value' => array(
                'data' => Data::query('Krate', 'about_head')
            )
        );
        $fields[] = array(
            'field' => Field::layout(
                'richtext',
                array(
                    'data' => array(
                        'label' => 'About Krate Body'
                    )
                )
            ),
            'name' => 'about_body',
            'type' => 'richtext',
            'value' => array(
                'data' => Data::query('Krate', 'about_body')
            )
        );
        $cemt = Doctrine::getTable('ContentEntryMeta');
        $entries = $cemt->filterByType('Work', NULL);
        $titles = array();
        $work_opts = array('' => 'None');
        foreach ($entries['data'] as $k => $entry)
        {
            $titles[$entry['id']] = $entry['title'];
        }
        asort($titles);
        $work_opts += $titles;
        for ($i = 1; $i <= 3; $i++)
        {
            $fields[] = array(
                'field' => Field::layout(
                    'dropdown',
                    array(
                        'data' => array(
                            'options' => $work_opts
                        )
                    )
                ),
                'name' => 'work_'.$i,
                'type' => 'dropdown',
                'value' => array(
                    'data' => Data::query('Krate', 'work_'.$i)
                )
            );
        }
        // }}}
        // {{{ about page fields
        $about_fields = array();
        $rte_about_fields = array(
            'body_about' => 'Body',
            'services_left_about' => 'Services Left',
            'services_right_about' => 'Services Right',
            'client_left_about' => 'Client Left',
            'client_right_about' => 'Client Right',
            'agencies_left_about' => 'Agencies Left',
            'agencies_right_about' => 'Agencies Right',
        );
        foreach ($rte_about_fields as $name => $label)
        {
            $about_fields[] = array(
                'field' => Field::layout(
                    'richtext',
                    array(
                        'data' => array(
                            'label' => $label
                        )
                    )
                ),
                'name' => $name,
                'type' => 'richtext',
                'value' => array(
                    'data' => Data::query('Krate', $name)
                )
            );
        }
        // }}}
        return array(
            'Homepage' => $fields,
            'About' => $about_fields,
        );
    }

    //}}}
    //{{{ public function hook_data_validate($name, $data)
    public function hook_data_validate($name, $data)
    {
        $names = array();
        for ($i = 1; $i <= self::NUM_HOMEPAGE_SLIDES; $i++)
        {
            $names[] = 'feature_'.$i.'_file';
        }
        $success = TRUE;
        if (in_array($name, $names))
        {
            if ($data['delete'])
            {
                $data = array();
            }
            elseif (ake('tmp_name', $data))
            {
                if (!is_dir(DIR_FILE.'/upload'))
                {
                    $can_move = mkdir(DIR_FILE.'/upload', 0777, TRUE);
                }
                if (move_uploaded_file($data['tmp_name'], DIR_FILE.'/upload/'.$data['name']))
                {
                    $data['tmp_name'] = DIR_FILE.'/upload/'.$data['name'];
                    $success = TRUE;
                }
            }
            else
            {
                $data = Data::query('Krate', $name);
            }
        }
        return array(
            'success' => $success,
            'data' => $data
        );
    }
    //}}}
    //{{{ public function hook_data_validate($name, $data)
    /*
    public function hook_data_validate($name, $data)
    {
        $success = FALSE;
        switch ($name)
        {
            case 'feature_1_file':
            case 'feature_2_file':
            case 'feature_3_file':
            case 'feature_4_file':
            case 'feature_5_file':
            case 'feature_6_file':
            case 'feature_7_file':
            case 'feature_8_file':
            case 'feature_9_file':
            case 'feature_10_file':
                if (!ake('tmp_name', $data))
                {
                    $data = array();
                    break;
                }
                if (!is_dir(DIR_FILE.'/upload'))
                {
                    $can_move = mkdir(DIR_FILE.'/upload', 0777, TRUE);
                }
                if (move_uploaded_file($data['tmp_name'], DIR_FILE.'/upload/'.$data['name']))
                {
                    $data['tmp_name'] = DIR_FILE.'/upload/'.$data['name'];
                    $success = TRUE;
                }
            break;

            default:
                $success = TRUE;
            break;
        }
        return array(
            'success' => $success,
            'data' => $data
        );
    }
    */
    //}}}
    //{{{ public function hook_user_perm()
    /*
    public function hook_user_perm()
    {
        return array(
            'Workflow' => array(
                'create workflow' => 'Create automated workflow',
                'edit workflow' => 'Edit automated workflow',
                'delete workflow' => 'Delete automated workflow'
            )
        );
    }
    */

    //}}}
    //{{{ public function hook_content_entry_new_finish($meta)
    public function hook_content_entry_new_finish($meta)
    {
        $cemt = Doctrine::getTable('ContentEntryMeta');
        $entry = $cemt->findCurrentEntry($meta['content_entry_meta_id']);
        $cett = Doctrine::getTable('ContentEntryType');
        $types = place_ids($cett->getTypes());
        $type = $types[$entry['content_entry_type_id']]['name'];
        if ($type === 'Work')
        {
            Data::update('web', 'work_time', 0);
            Data::save();
        }
    }

    // }}}
    //{{{ public function hook_content_entry_edit_finish($meta)
    public function hook_content_entry_edit_finish($meta)
    {
        $cemt = Doctrine::getTable('ContentEntryMeta');
        $entry = $cemt->findCurrentEntry($meta['content_entry_meta_id']);
        $cett = Doctrine::getTable('ContentEntryType');
        $types = place_ids($cett->getTypes());
        $type = $types[$entry['content_entry_type_id']]['name'];
        if ($type === 'Work')
        {
            Data::update('web', 'work_time', 0);
            Data::save();
        }
    }

    // }}}
    //{{{ public function hook_content_entry_delete_finish($meta)
    public function hook_content_entry_delete_finish($meta)
    {
        $cett = Doctrine::getTable('ContentEntryType');
        $type = $cett->find($meta['content_entry_type_id']);
        if ($type !== FALSE)
        {
            $type = $type['name'];
            if ($type === 'Work')
            {
                Data::update('web', 'work_time', 0);
                Data::save();
            }
        }
    }

    // }}}
    //{{{ public function hook_content_order_entries_success($meta)
    public function hook_content_order_entries_success($meta)
    {
        $cett = Doctrine::getTable('ContentEntryType');
        $type = $cett->find($meta['content_entry_type_id']);
        if ($type->name === 'Work')
        {
            Data::update('web', 'work_time', 0);
            Data::save();
        }
    }

    // }}}
}

?>
