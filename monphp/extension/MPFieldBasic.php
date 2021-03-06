<?php

/**
 * Extension for fields
 *
 * @package MPFieldBasic 
 */
class MPFieldBasic
{
    //{{{ properties
    public $type = 'field';

    //}}}

    //{{{ public static function field_delete_file($key, $data = array())
    public static function field_delete_file($key, $data = array())
    {

        $file = DIR_FILE.'/'.$data['cdata'];
        if (is_writable($file))
        {
            unlink($file);
        }
    }

    //}}}

    //{{{ public static function field_fallback_fieldtype($key, $data = array())
    public static function field_fallback_fieldtype($key, $data)
    {
        if (ake('data', $data) && is_array($data['data']))
        {
            $meta = array_combine($data['data'], $data['data']);
            $default = deka(array(), $data, 'default_data');
        }
        else
        {
            $meta = deka('', $data, 'data');
            $default = deka('', $data, 'default_data');
        }
        return array(
            'data' => array(
                'meta' => $meta,
                'default_data' => $default
            ),
        );
    }
    //}}}
    //{{{ public static function field_fallback_post($key, $data = array())
    public static function field_fallback_post($key, $data = array())
    {
        return deka('', $data, 'data');
    }

    //}}}

    //{{{ public static function field_fieldtype_checkbox($key, $data = array())
    public static function field_fieldtype_checkbox($key, $data = array())
    {
        return array(
            'data' => array(
                'meta' => array(
                    'options' => array_combine($data['data'], $data['data'])
                ),
                'default_data' => deka(array(), $data, 'default_data')
            ),
        );
    }
    //}}}
    //{{{ public static function field_fieldtype_dropdown($key, $data = array())
    public static function field_fieldtype_dropdown($key, $data = array())
    {
        $options = deka(array(), $data, 'data');
        return array(
            'data' => array(
                'meta' => array(
                    'options' => array_combine($options, $options),
                ),
                'default_data' => deka('', $data, 'default_data'),
            ),
        );
    }
    //}}}
    //{{{ public static function field_fieldtype_radio($key, $data = array())
    public static function field_fieldtype_radio($key, $data = array())
    {
        return array(
            'data' => array(
                'meta' => array(
                    'options' => array_combine($data['data'], $data['data'])
                ),
                'default_data' => deka('', $data, 'default_data')
            ),
        );
    }
    //}}}

    //{{{ public static function field_layout_checkbox($meta = array())
    public static function field_layout_checkbox($meta = array())
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'checkbox',
                    'type' => 'checkbox'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'options' => deka(array(), $meta, 'data', 'meta', 'options'),
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                    'value' => 0
                ),
                'element' => MPField::ELEMENT_INPUT,
                'hidden' => TRUE
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_checkbox_boolean()
    public static function field_layout_checkbox_boolean()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'checkbox_boolean',
                    'type' => 'checkbox',
                    'value' => 1
                ),
                'element' => MPField::ELEMENT_INPUT,
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                    'value' => 0
                ),
                'element' => MPField::ELEMENT_INPUT,
                'hidden' => TRUE
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_date()
    public static function field_layout_date()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'text date',
                    'type' => 'text'
                ),
                'element' => MPField::ELEMENT_INPUT,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_delete()
    public static function field_layout_delete()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'delete',
                    'type' => 'button'
                ),
                'element' => MPField::ELEMENT_BUTTON,
                'text' => 'Delete'
            ),
        );
    }

    //}}}
    //{{{ public static function field_layout_dropdown($meta = array())
    public static function field_layout_dropdown($meta = array())
    {
        return array(
            'data' => array(
                'element' => MPField::ELEMENT_SELECT,
                'options' => deka(array(), $meta, 'data', 'meta', 'options'),
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_dropdown_timezones()
    public static function field_layout_dropdown_timezones()
    {
        return array(
            'data' => array(
                'element' => MPField::ELEMENT_SELECT,
                'options' => time_zones()
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_file()
    public static function field_layout_file()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'file',
                    'type' => 'file'
                ),
                'element' => MPField::ELEMENT_INPUT,
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                    'value' => 0
                ),
                'element' => MPField::ELEMENT_INPUT,
                'hidden' => TRUE
            ),
            'group_key' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'hidden' => TRUE
            ),
            'delete' => array(
                'attr' => array(
                    'class' => 'checkbox',
                    'type' => 'checkbox',
                    'value' => 'delete'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'text' => 'Delete file'
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_hidden()
    public static function field_layout_hidden()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden'
                ),
                'element' => MPField::ELEMENT_INPUT,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_link()
    public static function field_layout_link()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'text' => 'Link text'
            ),
            'uri' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'text' => 'Link address'
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_list_double_ordered()
    public static function field_layout_list_double_ordered()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'list_double_ordered',
                    'multiple' => 'multiple'
                ),
                'element' => MPField::ELEMENT_SELECT,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_password()
    public static function field_layout_password()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'password',
                    'type' => 'password'
                ),
                'element' => MPField::ELEMENT_INPUT,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_password_confirm()
    public static function field_layout_password_confirm()
    {
        return array(
            'password' => array(
                'attr' => array(
                    'class' => 'password',
                    'type' => 'password',
                    'autocomplete' => 'off',
                ),
                'element' => MPField::ELEMENT_INPUT,
            ),
            'password_confirm' => array(
                'label' => 'Confirm password',
                'attr' => array(
                    'class' => 'password password_confirm',
                    'type' => 'password',
                    'autocomplete' => 'off',
                ),
                'element' => MPField::ELEMENT_INPUT,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_password_sha1()
    public static function field_layout_password_sha1()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'password_sha1',
                    'type' => 'password'
                ),
                'element' => MPField::ELEMENT_INPUT,
            ),
            'hashed' => array(
                'attr' => array(
                    'type' => 'hidden',
                    'value' => '0'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'hidden' => TRUE
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_password_sha1_confirm()
    public static function field_layout_password_sha1_confirm()
    {
        return array(
            'password' => array(
                'attr' => array(
                    'class' => 'password',
                    'type' => 'password'
                ),
                'element' => MPField::ELEMENT_INPUT,
            ),
            'password_confirm' => array(
                'label' => 'Confirm password',
                'attr' => array(
                    'class' => 'password password_confirm',
                    'type' => 'password'
                ),
                'element' => MPField::ELEMENT_INPUT,
            ),
            'password_hashed' => array(
                'attr' => array(
                    'type' => 'hidden',
                    'value' => '0'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'hidden' => TRUE
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_radio($meta = array())
    public static function field_layout_radio($meta = array())
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'radio',
                    'type' => 'radio'
                ),
                'element' => MPField::ELEMENT_INPUT,
                'options' => deka(array(), $meta, 'data', 'meta', 'options'),
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_reset()
    public static function field_layout_reset()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'reset',
                    'type' => 'reset'
                ),
                'element' => MPField::ELEMENT_BUTTON,
                'text' => 'Reset'
            ),
        );
    }

    //}}}
    //{{{ public static function field_layout_richtext()
    public static function field_layout_richtext()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'rte'
                ),
                'element' => MPField::ELEMENT_TEXTAREA,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_select_multiple()
    public static function field_layout_select_multiple()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'select_multiple',
                    'multiple' => 'multiple'
                ),
                'element' => MPField::ELEMENT_SELECT,
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                ),
                'element' => MPField::ELEMENT_INPUT,
                'hidden' => TRUE
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_submit()
    public static function field_layout_submit()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'submit',
                    'type' => 'submit'
                ),
                'element' => MPField::ELEMENT_BUTTON,
                'text' => 'Submit'
            ),
        );
    }

    //}}}
    //{{{ public static function field_layout_submit_confirm()
    public static function field_layout_submit_confirm()
    {
        return array(
            'submit' => array(
                'attr' => array(
                    'class' => 'submit',
                    'type' => 'submit',
                    'value' => 1
                ),
                'element' => MPField::ELEMENT_BUTTON,
                'text' => 'Yes'
            ),
            'cancel' => array(
                'attr' => array(
                    'class' => 'submit',
                    'type' => 'submit',
                    'value' => 0
                ),
                'element' => MPField::ELEMENT_BUTTON,
                'text' => 'No'
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_submit_reset()
    public static function field_layout_submit_reset()
    {
        return array(
            'submit' => array(
                'attr' => array(
                    'class' => 'submit',
                    'type' => 'submit'
                ),
                'element' => MPField::ELEMENT_BUTTON,
                'text' => 'Submit'
            ),
            'reset' => array(
                'attr' => array(
                    'class' => 'reset',
                    'type' => 'reset'
                ),
                'element' => MPField::ELEMENT_BUTTON,
                'text' => 'Reset'
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_text()
    public static function field_layout_text()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text'
                ),
                'element' => MPField::ELEMENT_INPUT,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_textarea()
    public static function field_layout_textarea()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'textarea'
                ),
                'element' => MPField::ELEMENT_TEXTAREA,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_textarea_array()
    public static function field_layout_textarea_array()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'textarea_array'
                ),
                'element' => MPField::ELEMENT_TEXTAREA,
            )
        );
    }

    //}}}

    //{{{ public static function field_meta_checkbox($key, $data = array())
    public static function field_meta_checkbox($key, $data = array())
    {
        return array(
            'data' => array(
                'field' => MPField::layout('textarea_array'),
                'label' => 'Enter one option per line',
                'type' => 'textarea_array',
                'value' => array(
                    'data' => deka(array(), $data, 'data', 'meta', 'options'),
                ),
            ),
            'default_data' => array(
                'field' => MPField::layout('textarea_array'),
                'label' => 'Enter in the default values',
                'type' => 'textarea_array',
                'value' => array(
                    'data' => deka(array(), $data, 'data', 'default_data'),
                ),
            ),
        );
    }

    //}}}
    //{{{ public static function field_meta_dropdown($key, $data = array())
    public static function field_meta_dropdown($key, $data = array())
    {
        return array(
            'data' => array(
                'field' => MPField::layout('textarea_array'),
                'label' => 'Enter one option per line',
                'type' => 'textarea_array',
                'value' => array(
                    'data' => deka(array(), $data, 'data', 'meta', 'options'),
                ),
            ),
        );
    }

    //}}}
    //{{{ public static function field_meta_radio($key, $data = array())
    public static function field_meta_radio($key, $data = array())
    {
        return array(
            'data' => array(
                'field' => MPField::layout('textarea_array'),
                'label' => 'Enter one option per line',
                'type' => 'textarea_array',
                'value' => array(
                    'data' => deka(array(), $data, 'data', 'meta', 'options'),
                ),
            ),
            'default_data' => array(
                'field' => MPField::layout('text'),
                'label' => 'Enter in the default value',
                'type' => 'text',
                'value' => array(
                    'data' => deka(array(), $data, 'data', 'default_data'),
                ),
            ),
        );
    }

    //}}}
    //{{{ public static function field_meta_text($key, $data = array())
    public static function field_meta_text($key, $data = array())
    {
        return array(
            'default_data' => array(
                'field' => MPField::layout('text'),
                'label' => 'Enter in the default value',
                'type' => 'text',
                'value' => array(
                    'data' => deka(array(), $data, 'data', 'default_data'),
                ),
            ),
        );
    }

    //}}}

    //{{{ public static function field_post_checkbox($key, $data = array())
    public static function field_post_checkbox($key, $data = array())
    {
        return deka(array(), $data, 'data');
    }

    //}}}
    //{{{ public static function field_post_checkbox_boolean($key, $data = array())
    public static function field_post_checkbox_boolean($key, $data = array())
    {
        return ake('data', $data) ? (bool)$data['data'] : FALSE;
    }

    //}}}
    //{{{ public static function field_post_file($key, $data = array())
    /**
     * Gets the info of the uploaded file if there are no errors
     * Since the form and field classes use data and type array keys, this will
     * always look into the data key for $_FILES. Unlike the save method, this
     * does not move the file.
     * @param string $key array key for the $_FILES array
     * @param array $data POST data
     * @return array|boolean FALSE if there is an upload error
     */
    public static function field_post_file($key, $data = array())
    {
        $result = array();
        $v = $data['group_key'];
        if (isset($_FILES[$v]['name'][$key]['data']))
        {
            $files = $_FILES[$v];
            if ($files['error'][$key]['data'] === 0)
            {
                $result = array(
                    'name' => $files['name'][$key]['data'],
                    'size' => $files['size'][$key]['data'],
                    'tmp_name' => $files['tmp_name'][$key]['data'],
                    'type' => $files['type'][$key]['data']
                );
            }
        }
        $result['delete'] = ake('delete', $data);
        return $result;
    }

    //}}}
    //{{{ public static function field_post_link($key, $data = array())
    /**
     * @param string $key array key for the $_FILES array
     * @param array $data POST data
     * @return array
     */
    public static function field_post_link($key, $data = array())
    {
        return $data;
    }

    //}}}
    //{{{ public static function field_post_password_sha1($key, $data = array())
    public static function field_post_password_sha1($key, $data = array())
    {
        return $data['hashed'] ? $data['data'] : sha1($data['data']);
    }

    //}}}
    //{{{ public static function field_post_password_confirm($key, $data = array())
    public static function field_post_password_confirm($key, $data = array())
    {
        return deka(NULL, $data, 'password');
    }

    //}}}
    //{{{ public static function field_post_password_confirm_sha1($key, $data = array())
    public static function field_post_password_confirm_sha1($key, $data = array())
    {
        if (!ake('password', $data) || !ake('password_hashed', $data) || !ake('password_confirm', $data))
        {
            return NULL;
        }
        if (!$data['password_hashed'] && !(empty($data['password']) || empty($data['password_confirm'])))
        {
            $data['password'] = sha1($data['password']);
            $data['password_confirm'] = sha1($data['password_confirm']);
        }
        return $data['password'] === $data['password_confirm'] ? $data['password'] : NULL;
    }

    //}}}
    //{{{ public static function field_post_select_multiple($key, $data = array())
    public static function field_post_select_multiple($key, $data = array())
    {
        return deka(array(), $data, 'data');
    }

    //}}}
    //{{{ public static function field_post_submit_confirm($key, $data = array())
    public static function field_post_submit_confirm($key, $data = array())
    {
        return ake('submit',$data);
    }

    //}}}
    //{{{ public static function field_post_textarea_array($key, $data = array())
    public static function field_post_textarea_array($key, $data = array())
    {
        return ake('data', $data) && strlen($data['data'])
            ? preg_split("/[\n\r]+/", $data['data']) 
            : array();
    }

    //}}}

    //{{{ public static function field_prepare_delete($key, $data = array())
    public static function field_prepare_delete($key, $data = array())
    {
        return array($data);
    }

    //}}}
    //{{{ public static function field_prepare_post()
    public static function field_prepare_post()
    {
        return func_get_args();
    }

    //}}}

    //{{{ public static function field_public_checkbox()
    public static function field_public_checkbox()
    {
        return array(
            'description' => 'List of checkboxes to choose',
            'meta' => TRUE,
            'name' => 'Checkboxes',
        );
    }

    //}}}
    //{{{ public static function field_public_dropdown()
    public static function field_public_dropdown()
    {
        return array(
            'description' => 'List of dropdown options to choose',
            'meta' => TRUE,
            'name' => 'Dropdown',
        );
    }

    //}}}
    //{{{ public static function field_public_date()
    public static function field_public_date()
    {
        return array(
            'description' => 'Popup calendar to help select the date',
            'meta' => FALSE,
            'name' => 'Date',
        );
    }

    //}}}
    //{{{ public static function field_public_file()
    public static function field_public_file()
    {
        return array(
            'description' => 'Upload a file',
            'meta' => FALSE,
            'name' => 'File Upload',
        );
    }

    //}}}
    //{{{ public static function field_public_link()
    public static function field_public_link()
    {
        return array(
            'description' => 'Link',
            'meta' => FALSE,
            'name' => 'Link'
        );
    }

    //}}}
    //{{{ public static function field_public_radio()
    public static function field_public_radio()
    {
        return array(
            'description' => 'List of radio options to choose',
            'meta' => TRUE,
            'name' => 'Radio Options',
        );
    }

    //}}}
    //{{{ public static function field_public_richtext()
    public static function field_public_richtext()
    {
        return array(
            'description' => 'Richtext editor using TinyMCE',
            'meta' => FALSE,
            'name' => 'Richtext',
        );
    }

    //}}}
    //{{{ public static function field_public_text()
    public static function field_public_text()
    {
        return array(
            'description' => 'Simple text box',
            'meta' => TRUE,
            'name' => 'Text',
        );
    }

    //}}}
    //{{{ public static function field_public_textarea()
    public static function field_public_textarea()
    {
        return array(
            'description' => 'Large textarea box',
            'meta' => FALSE,
            'name' => 'Textarea',
        );
    }

    //}}}

    //{{{ public static function field_validate_password_confirm($key, $data = array())
    public static function field_validate_password_confirm($key, $data = array())
    {
        if ($data['password'] !== $data['password_confirm'])
        {
            throw new Exception('Passwords do not match');
        }
    }

    //}}}
}
