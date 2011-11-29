<?php

class ContentField
{
    //{{{ public static function field_conclude_fieldtype($data, $args)
    public static function field_conclude_fieldtype($data, $args)
    {
        if ($data)
        {
            $post = $args[1];
            if (!eka($post, '_fieldtype'))
            {
                return;
            }
            $type = $post['_fieldtype'];
            if ($meta_caller = Field::action_array('meta', $type))
            {
                $metas = call_user_func($meta_caller, $type, $post);
                foreach ($data as &$row)
                {
                    if (eka($row, 'name'))
                    {
                        $name = $row['name'];
                        if (eka($metas, $name))
                        {
                            if (deka(FALSE, $metas[$name], 'label_field'))
                            {
                                $row['label'] = deka('', $post, '_label_'.$name);
                            }
                            if (deka(FALSE, $metas[$name], 'required_option'))
                            {
                                $row['required'] = deka('0', $post, '_required_'.$name);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }
    //}}}
    //{{{ public static function field_fallback_content($type, $data)
    /**
     * Generic field action method for content
     * 
     * Content is used if the content type needs further data extraction, such
     * as turning a user id into the user account's information.
     *
     * @param string $type
     * @param string|binary $data the content of the cdata or bdata column
     */
    public static function field_fallback_content($type, $data)
    {
        return $data;
    }

    //}}}
    //{{{ public static function field_fallback_fieldtype($data)
    public static function field_fallback_fieldtype($data)
    {
        if (is_array($data['data']))
        {
            $meta = array_combine($data['data'], $data['data']);
            $default = deka(array(), $data, 'default_data');
        }
        elseif (is_string($data['data']))
        {
            $meta = deka('', $data, 'data');
            $default = deka('', $data, 'default_data');
        }
        else
        {
            $meta = deka('', $data, 'data');
            $default = deka('', $data, 'default_data');
        }
        return array(
            array(
                'name' => 'data',
                'meta' => $meta,
                'default_data' => $default
            ),
        );
    }
    //}}}
    //{{{ public static function field_fallback_read($key, $data)
    public static function field_fallback_read($key, $data)
    {
        $results = array();
        if ($data)
        {
            foreach ($data as $k => $v)
            {
                foreach ($v as $nk => $nv)
                {
                    foreach ($nv as $nnk => $nnv)
                    {
                        $results[$k][$nnv['akey']][] = !ake('bdata', $nnv) || is_null($nnv['bdata'])
                            ? $nnv['cdata']
                            : $nnv['bdata'];
                    }
                }
            }
        }
        return $results;
    }

    //}}}
    //{{{ public static function field_fallback_save($key, $data)
    /**
     * Generic field action method for save
     *
     * The save action is preparation for merging with the ContentFieldData
     * model. So the data should just be placed into an array with the key
     * either cdata or bdata.
     *
     * @param array $data
     * @return array
     */
    public static function field_fallback_save($key, $data)
    {
        $d = array();
        // TODO Make more efficient / recursive?
        foreach ($data as $name => $val)
        {
            if (is_array($val))
            {
                foreach ($val as $k => $r)
                {
                    if (is_array($r))
                    {
                        foreach ($r as $nr)
                        {
                            if (is_array($nr))
                            {
                                foreach ($nr as $nnr)
                                {
                                    $d[$name][] = array(
                                        'akey' => $k, 
                                        'cdata' => $nnr
                                    );
                                }
                            }
                            else
                            {
                                $d[$name][] = array(
                                    'akey' => $k, 
                                    'cdata' => $nr
                                );
                            }
                        }
                    }
                    else
                    {
                        $d[$name][] = array(
                            'akey' => 0, 
                            'cdata' => $r
                        );
                    }
                }
            }
            else
            {
                $d[$name][] = array(
                    'akey' => 0,
                    'cdata' => $val,
                );
            }
        }
        return $d;
    }

    //}}}
    //{{{ public static function field_fieldtype_checkbox($data)
    public static function field_fieldtype_checkbox($data)
    {
        return array(
            array(
                'name' => 'data',
                'meta' => array(
                    'options' => array_combine($data['data'], $data['data'])
                ),
                'default_data' => deka(array(), $data, 'default_data')
            ),
        );
    }
    //}}}
    //{{{ public static function field_fieldtype_fieldtype($key, $data)
    public static function field_fieldtype_fieldtype($key, $data)
    {
        $type = $data['_fieldtype'];
        unset($data['_fieldtype']);
        $info = array();
        if ($meta_caller = Field::action_array('meta', $type))
        {
            $metas = call_user_func($meta_caller, $key, $data);
            foreach ($metas as $mkey => $meta)
            {
                $caller = ($post_caller = Field::action_array('post', $meta['type']))
                    ? $post_caller
                    : Field::action_array('fallback', 'post');
                if ($caller && ake($mkey, $data))
                {
                    $layout = Field::layout($meta['type']);
                    $lkeys = array_keys($layout);
                    $dkey = eka($lkeys, 'data') ? 'data' : array_shift($lkeys);
                    $data_array = array($dkey => $data[$mkey]);
                    $info[$mkey] = call_user_func($caller, $mkey, $data_array);
                }
            }
            if ($type_caller = Field::action_array('fieldtype', $type))
            {
                $info = call_user_func($type_caller, $info);
            }
        }
        $info['type'] = deka($type, $info, 'type');
        return $info;
    }
    //}}}
    //{{{ public static function field_fieldtype_dropdown($data)
    public static function field_fieldtype_dropdown($data)
    {
        if (is_array($data))
        {
            return array(
                array(
                    'name' => 'data',
                    'meta' => array(
                        'options' => array_combine($data['data'], $data['data'])
                    ),
                    'default_data' => deka('', $data, 'default_data')
                ),
            );
        }
    }
    //}}}
    //{{{ public static function field_fieldtype_radio($data)
    public static function field_fieldtype_radio($data)
    {
        return array(
            array(
                'name' => 'data',
                'meta' => array(
                    'options' => array_combine($data['data'], $data['data'])
                ),
                'default_data' => deka('', $data, 'default_data')
            ),
        );
    }
    //}}}
    //{{{ public static function field_fieldtype_relationship($data)
    public static function field_fieldtype_relationship($data)
    {
        return array(
            array(
                'name' => 'data',
                'meta' => array(
                    'content_type_id' => deka('', $data, 'data'),
                    'ordering' => deka(FALSE, $data, 'ordering')
                ),
                'default_data' => deka('', $data, 'default_data')
            ),
        );
    }
    //}}}
    //{{{ public static function field_fieldtype_relationship_multiple($data)
    public static function field_fieldtype_relationship_multiple($data)
    {
        return array(
            array(
                'name' => 'data',
                'meta' => array(
                    'content_type_id' => deka('', $data, 'data'),
                    'ordering' => deka(FALSE, $data, 'ordering')
                ),
                'default_data' => deka(array(), $data, 'default_data')
            ),
        );
    }
    //}}}
    //{{{ public static function field_fieldtype_text($data)
    public static function field_fieldtype_text($data)
    {
        return array(
            array(
                'name' => 'data',
                'meta' => deka('', $data, 'data'),
                'default_data' => deka('', $data, 'default_data')
            )
        );
    }
    //}}}
    //{{{ public static function field_layout_fieldtype()
    public static function field_layout_fieldtype()
    {
        $types = Field::types();
        $options = array();
        foreach ($types as $type => $info)
        {
            $options[$type] = $info['name'];
        }
        return array(
            '_fieldtype' => array(
                'element' => Field::ELEMENT_SELECT,
                'options' => $options
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_relationship($meta = array())
    public static function field_layout_relationship($meta = array())
    {
        $id = deka('', $meta, 'data', 'meta', 'content_type_id');
        $titles = $options = array();
        if (is_numeric($id))
        {
            $cemt = Doctrine::getTable('ContentEntryMeta');
            $entries = $cemt->queryTypeEntries($id)->fetchArray();
            foreach ($entries as $entry)
            {
                $titles[$entry['id']] = $entry['title'];
            }
            asort($titles);
            $options += $titles;
        }
        if (deka(FALSE, $meta, 'data', 'meta', 'ordering'))
        {
            $field = Field::layout('list_double_ordered');
            $field['data']['options'] = $options;
            return $field;
        }
        else
        {
            $options += array('' => 'None');
            return array(
                'data' => array(
                    'element' => Field::ELEMENT_SELECT,
                    'options' => $options
                )
            );
        }
    }

    //}}}
    //{{{ public static function field_layout_relationship_multiple($meta = array())
    public static function field_layout_relationship_multiple($meta = array())
    {
        $ids = deka(array(), $meta, 'data', 'meta', 'content_type_id');
        $titles = $options = array();
        foreach ($ids as $id)
        {
            $cemt = Doctrine::getTable('ContentEntryMeta');
            $entries = $cemt->queryTypeEntries($id)->fetchArray();
            foreach ($entries as $entry)
            {
                $titles[$entry['id']] = $entry['title'];
            }
        }
        asort($titles);
        $options += $titles;
        if (deka(FALSE, $meta, 'data', 'meta', 'ordering'))
        {
            $field = Field::layout('list_double_ordered');
            $field['data']['options'] = $options;
            return $field;
        }
        else
        {
            $options += array('' => 'None');
            return array(
                'data' => array(
                    'element' => Field::ELEMENT_SELECT,
                    'options' => $options
                )
            );
        }
    }

    //}}}
    //{{{ public static function field_meta_checkbox()
    public static function field_meta_checkbox()
    {
        return array(
            'data' => array(
                'description' => 'One option per line',
                'field' => '<textarea></textarea>',
                'label_field' => TRUE,
                'label_value' => '',
                'required_option' => TRUE,
                'required_value' => FALSE,
                'type' => 'textarea_array'
            ),
            'default_data' => array(
                'description' => 'default values',
                'field' => '<textarea></textarea>',
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'textarea_array'
            )
        );
    }

    //}}}
    //{{{ public static function field_meta_dropdown()
    public static function field_meta_dropdown()
    {
        return array(
            'data' => array(
                'description' => 'One option per line',
                'extra' => array(),
                'field' => '<textarea></textarea>',
                'type' => 'textarea_array'
            )
        );
    }

    //}}}
    //{{{ public static function field_meta_link()
    public static function field_meta_link()
    {
        return array(
            'data' => array(
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'link'
            ),
            'uri' => array(
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'link'
            )
        );
    }

    //}}}
    //{{{ public static function field_meta_radio()
    public static function field_meta_radio()
    {
        return array(
            'data' => array(
                'description' => 'One option per line',
                'field' => '<textarea></textarea>',
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'textarea_array'
            ),
            'default_data' => array(
                'description' => 'default value',
                'field' => "<input type='text' class='text'>",
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'text'
            )
        );
    }

    //}}}
    //{{{ public static function field_meta_relationship()
    public static function field_meta_relationship()
    {
        $types = MonDB::selectCollection('content_entry_type')->find();
        $field = '<select>';
        $options = array();
        foreach ($types as $type)
        {
            $name = $type['name'];
            $nice_name = $type['nice_name'];
            $field .= "<option value='{$name}'>{$nice_name}</option>";
            $options[$name] = $nice_name;
        }
        $field .= '</select>';

        return array(
            'data' => array(
                'description' => 'Choose the content type',
                'field' => $field,
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'dropdown'
            ),
            'ordering' => array(
                'description' => '',
                'field' => "<label><input type='checkbox' value='1' /> Allow Ordering?</label>",
                'type' => 'checkbox_boolean'
            ),
        );
    }

    //}}}
    //{{{ public static function field_meta_relationship_multiple()
    public static function field_meta_relationship_multiple()
    {
        $types = MonDB::selectCollection('content_entry_type')->find();
        $field = '';
        foreach ($types as $type)
        {
            $name = $type['name'];
            $nice_name = $type['nice_name'];
            $field .= "<label><input type='checkbox' value='{$name}' />{$nice_name}</label>";
        }

        return array(
            'data' => array(
                'description' => 'Choose the content type',
                'field' => $field,
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'checkbox'
            ),
            'ordering' => array(
                'description' => '',
                'field' => "<label><input type='checkbox' value='1' /> Allow Ordering?</label>",
                'type' => 'checkbox_boolean'
            ),
        );
    }

    //}}}
    //{{{ public static function field_meta_text()
    public static function field_meta_text()
    {
        return array(
            'default_data' => array(
                'description' => 'default value',
                'field' => "<input type='text' class='text'>",
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'text'
            )
        );
    }

    //}}}
    //{{{ public static function field_prepare_save()
    public static function field_prepare_save()
    {
        return func_get_args();
    }

    //}}}
    //{{{ public static function field_public_relationship()
    public static function field_public_relationship()
    {
        return array(
            'description' => 'Related entry',
            'meta' => TRUE,
            'name' => 'Relationship',
        );
    }

    //}}}
    //{{{ public static function field_public_relationship_multiple()
    public static function field_public_relationship_multiple()
    {
        return array(
            'description' => 'Related entry',
            'meta' => TRUE,
            'name' => 'Relationship Multiple',
        );
    }

    //}}}
    //{{{ public static function field_read_date($key, $data)
    /**
     * @param array $key
     * @param array $data
     * @return array
     */
    public static function field_read_date($key, $data)
    {
        $results = array();
        foreach ($data as $k => $v)
        {
            foreach ($v as $nk => $nv)
            {
                foreach ($nv as $nnk => $nnv)
                {
                    $results[$k][$nnv['akey']][] = is_numeric($nnv['cdata'])
                        ? date('Y-m-d H:i', $nnv['cdata'])
                        : $nnv['cdata'];
                }
            }
        }
        return $results;
    }

    //}}}
    //{{{ public static function field_read_file($type, $data)
    /*
    public static function field_read_file($type, $data)
    {
    }
    */

    //}}}
    //{{{ public static function field_read_relationship_ordered($key, $data)
    public static function field_read_relationship_ordered($key, $data)
    {
        $result = $pad = array();
        if (empty($data))
        {
            return $data;
        }
        foreach ($data['data'] as $akey => $rows)
        {
            foreach ($rows as $row)
            {
                if (eka($row, 'meta', 'weight'))
                {
                    $result[$row['meta']['weight']] = $row['cdata'];
                }
                else
                {
                    $pad[] = $row['cdata'];
                }
            }
            $results['data'][$akey] = array_merge(array_merge($result), $pad);
        }
        return $results;
    }

    //}}}
    //{{{ public static function field_save_date($key, $data)
    /**
     * @param array $key
     * @param array $data
     * @return array
     */
    public static function field_save_date($key, $data)
    {
        $d = self::field_fallback_save($key, $data);
        foreach ($d['data'] as &$val)
        {
            $val['cdata'] = strtotime($val['cdata'])
                ? strtotime($val['cdata'])
                : '';
        }
        return $d;
    }

    //}}}
    //{{{ public static function field_save_file($key, $data)
    /**
     * Prepare data for storage in database
     *
     * About looking into $_FILES: If the field is a multiple field, then the
     * array will look like $_FILES['data']['name'][$key][0]['data'][0]. If not
     * it will look like $_FILES['data']['name'][$key]['data']
     *
     * @param array $key
     * @param array $data
     * @return array
     */
    public static function field_save_file($key, $data)
    {
        $d = array();
        if (eka($_FILES,'data','name',$key))
        {
            $filenames = &$_FILES['data']['name'][$key];
            $errors = $names = $sizes = $temps = $types = array();
            if (is_array($filenames['data']))
            {
                foreach ($filenames['data'] as $k => $v)
                {
                    $errors[$k] = $_FILES['data']['error'][$key]['data'][$k][0];
                    $names[$k] = $_FILES['data']['name'][$key]['data'][$k][0];
                    $sizes[$k] = $_FILES['data']['size'][$key]['data'][$k][0];
                    $temps[$k] = $_FILES['data']['tmp_name'][$key]['data'][$k][0];
                    $types[$k] = $_FILES['data']['type'][$key]['data'][$k][0];
                }
            }
            else
            {
                $errors[] = $_FILES['data']['error'][$key]['data'];
                $names[] = $_FILES['data']['name'][$key]['data'];
                $sizes[] = $_FILES['data']['size'][$key]['data'];
                $temps[] = $_FILES['data']['tmp_name'][$key]['data'];
                $types[] = $_FILES['data']['type'][$key]['data'];
            }
            $akey = 0;
            // GridFS?
            /*
            foreach ($names as $k => $name)
            {
                $src = $temps[$k];
                $upload_dir = DIR_FILE.'/upload/';
                if (!is_dir($upload_dir))
                {
                    mkdir($upload_dir);
                }
                $dest = available_filename($upload_dir.$name);
                if ($errors[$k] === 0 && move_uploaded_file($src, $dest))
                {
                    chmod($dest, 0666);
                    $d[] = array(
                        'cdata' => basename($dest),
                        'akey' => $akey++,
                        'meta' => array(
                            'size' => $sizes[$k],
                            'type' => $types[$k]
                        )
                    );
                }
                else
                {
                    $delete = ake('delete', $data) && !is_array($data['delete']);
                    if (!$delete && ake('_content_entry_meta_id', $data))
                    {
                        $cfdt = Doctrine::getTable('ContentFieldData');
                        $field = $cfdt->findCurrentEntryFieldData($data['_content_entry_meta_id'], $key, 'data')->fetchArray();
                        if (ake($k, $field) && !eka($data, 'delete', $k))
                        {
                            $d[$k] = array(
                                'cdata' => $field[$k]['cdata'],
                                'akey' => $akey++,
                                'meta' => $field[$k]['meta']
                            );
                        }
                    }
                }
            }
            */
        }
        return array('data' => $d);
    }

    //}}}
    //{{{ public static function field_save_relationship_ordered($key, $data)
    public static function field_save_relationship_ordered($key, $data)
    {
        if (ake('data', $data))
        {
            foreach ($data['data'] as $k => &$v)
            {
                $v = array(
                    'cdata' => $v,
                    'meta' => array('weight' => $k)
                );
            }
            return $data;
        }
        else
        {
            return array();
        }
    }

    //}}}
}

?>
