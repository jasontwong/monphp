<?php

/**
 * Extension for fields
 *
 * @package ExtensionFieldBasic
 */
class ExtensionFieldBasic
{
    //{{{ properties
    public $type = 'field';

    //}}}
    //{{{ public static function field_delete_file($data)
    public static function field_delete_file($data)
    {

        $file = DIR_FILE.'/'.$data['cdata'];
        if (is_writable($file))
        {
            unlink($file);
        }
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
                'element' => Field::ELEMENT_INPUT,
                'options' => deka(array(), $meta, 'data', 'meta', 'options'),
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                    'value' => 0
                ),
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_INPUT,
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                    'value' => 0
                ),
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_BUTTON,
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
                'element' => Field::ELEMENT_SELECT,
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
                'element' => Field::ELEMENT_SELECT,
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
                'element' => Field::ELEMENT_INPUT,
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                    'value' => 0
                ),
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE
            ),
            'group_key' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden'
                ),
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE
            ),
            'delete' => array(
                'attr' => array(
                    'class' => 'checkbox',
                    'type' => 'checkbox',
                    'value' => 'delete'
                ),
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_INPUT,
                'text' => 'Link text'
            ),
            'uri' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text'
                ),
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_SELECT,
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
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_INPUT,
            ),
            'hashed' => array(
                'attr' => array(
                    'type' => 'hidden',
                    'value' => '0'
                ),
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE
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
                    'type' => 'password'
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'password_confirm' => array(
                'label' => 'Confirm password',
                'attr' => array(
                    'class' => 'password password_confirm',
                    'type' => 'password'
                ),
                'element' => Field::ELEMENT_INPUT,
            )
        );
    }

    //}}}
    //{{{ public static function field_layout_password_confirm_sha1()
    public static function field_layout_password_confirm_sha1()
    {
        return array(
            'password' => array(
                'attr' => array(
                    'class' => 'password',
                    'type' => 'password'
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'password_confirm' => array(
                'label' => 'Confirm password',
                'attr' => array(
                    'class' => 'password password_confirm',
                    'type' => 'password'
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'password_hashed' => array(
                'attr' => array(
                    'type' => 'hidden',
                    'value' => '0'
                ),
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_BUTTON,
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
                'element' => Field::ELEMENT_TEXTAREA,
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
                'element' => Field::ELEMENT_SELECT,
            ),
            'hidden' => array(
                'attr' => array(
                    'class' => 'hidden',
                    'type' => 'hidden',
                ),
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_BUTTON,
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
                'element' => Field::ELEMENT_BUTTON,
                'text' => 'Yes'
            ),
            'cancel' => array(
                'attr' => array(
                    'class' => 'submit',
                    'type' => 'submit',
                    'value' => 0
                ),
                'element' => Field::ELEMENT_BUTTON,
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
                'element' => Field::ELEMENT_BUTTON,
                'text' => 'Submit'
            ),
            'reset' => array(
                'attr' => array(
                    'class' => 'reset',
                    'type' => 'reset'
                ),
                'element' => Field::ELEMENT_BUTTON,
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
                'element' => Field::ELEMENT_INPUT,
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
                'element' => Field::ELEMENT_TEXTAREA,
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
                'element' => Field::ELEMENT_TEXTAREA,
            )
        );
    }

    //}}}
    //{{{ public static function field_validate_password_confirm($key, $data)
    public static function field_validate_password_confirm($key, $data)
    {
        if ($data['password'] !== $data['password_confirm'])
        {
            throw new Exception('Passwords do not match');
        }
    }

    //}}}
    //{{{ public static function field_post_checkbox($key, $data)
    public static function field_post_checkbox($key, $data)
    {
        return deka(array(), $data, 'data');
    }

    //}}}
    //{{{ public static function field_post_checkbox_boolean($key, $data)
    public static function field_post_checkbox_boolean($key, $data)
    {
        return ake('data', $data) ? (boolean)$data['data'] : FALSE;
    }

    //}}}
    //{{{ public static function field_post_submit_confirm($key, $data)
    public static function field_post_submit_confirm($key, $data)
    {
        return ake('submit',$data);
    }

    //}}}
    //{{{ public static function field_post_file($key, $data)
    /**
     * Gets the info of the uploaded file if there are no errors
     * Since the form and field classes use data and type array keys, this will
     * always look into the data key for $_FILES. Unlike the save method, this
     * does not move the file.
     * @param string $key array key for the $_FILES array
     * @param array $data POST data
     * @return array|boolean FALSE if there is an upload error
     */
    public static function field_post_file($key, $data)
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
    //{{{ public static function field_post_link($key, $data)
    /**
     * @param string $key array key for the $_FILES array
     * @param array $data POST data
     * @return array
     */
    public static function field_post_link($key, $data)
    {
        return $data;
    }

    //}}}
    //{{{ public static function field_post_password_sha1($key, $data)
    public static function field_post_password_sha1($key, $data)
    {
        return $data['hashed'] ? $data['data'] : sha1($data['data']);
    }

    //}}}
    //{{{ public static function field_post_password_confirm($key, $data)
    public static function field_post_password_confirm($key, $data)
    {
        return $data['password'];
    }

    //}}}
    //{{{ public static function field_post_password_confirm_sha1($key, $data)
    public static function field_post_password_confirm_sha1($key, $data)
    {
        if (!$data['password_hashed'] && !(empty($data['password']) || empty($data['password_confirm'])))
        {
            $data['password'] = sha1($data['password']);
            $data['password_confirm'] = sha1($data['password_confirm']);
        }
        return $data['password'] === $data['password_confirm'] ? $data['password'] : FALSE;
    }

    //}}}
    //{{{ public static function field_post_select_multiple($key, $data)
    public static function field_post_select_multiple($key, $data)
    {
        return ake('data', $data) 
            ? $data['data'] 
            : array();
    }

    //}}}
    //{{{ public static function field_post_text($key, $data)
    public static function field_post_text($key, $data)
    {
        return deka('', $data, 'data');
    }

    //}}}
    //{{{ public static function field_post_textarea_array($key, $data)
    public static function field_post_textarea_array($key, $data)
    {
        return eka($data, 'data') 
            ? preg_split("/[\n\r]+/", $data['data']) 
            : array();
    }

    //}}}
    //{{{ public static function field_prepare_delete($type, $data)
    public static function field_prepare_delete($type, $data)
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
    //{{{ public static function field_fallback_meta()
    public static function field_fallback_meta()
    {
        return NULL;
    }

    //}}}
    //{{{ public static function field_fallback_post($key, $data)
    public static function field_fallback_post($key, $data)
    {
        return deka('', $data, 'data');
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
}

?>
