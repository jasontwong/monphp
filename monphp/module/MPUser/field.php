<?php

class MPUserField
{
    //{{{ public static function field_content_group($type, $data)
    public static function field_content_group($type, $data)
    {
        $ugc = MPDB::selectCollection('mpuser_group');
        $group = $ugc->findOne(array('name' => self::GROUP_ANONYMOUS));
        if (is_null($group))
        {
            $group = self::anonymous_info();
        }
        else
        {
            unset($group['permission']);
        }
        return $group;
    }

    //}}}
    //{{{ public static function field_content_group_multiple($type, $data)
    public static function field_content_group_multiple($type, $data)
    {
        return self::field_content_group($type, $data);
    }

    //}}}
    //{{{ public static function field_content_user($type, $data)
    public static function field_content_user($type, $data)
    {
        $uac = MPDB::selectCollection('mpuser_account');
        $user = $uac->findOne(array('name' => $data));
        if (is_null($user))
        {
            $user = self::anonymous_user();
        }
        else
        {
            unset($user['pass'], $user['salt'], $user['permission']);
        }
        return $user;
    }

    //}}}
    //{{{ public static function field_content_user_current($type, $data)
    public static function field_content_user_current($type, $data)
    {
        $uac = MPDB::selectCollection('mpuser_account');
        $user = $uac->findOne(array('name' => $data));
        if (is_null($user))
        {
            $user = self::anonymous_user();
        }
        else
        {
            unset($user['pass'], $user['salt'], $user['permission']);
        }
        return $user;
    }

    //}}}
    //{{{ public static function field_content_user_multiple($type, $data)
    //TODO uhh this! once multiple user accounts are made
    public static function field_content_user_multiple($type, $data)
    {
        return self::field_content_user($type, $data);
    }

    //}}}
    //{{{ public static function field_form_group($name, $value, $extra)
    public static function field_form_group($name, $value, $extra)
    {
        $ugc = MPDB::selectCollection('mpuser_group');
        $groups = $ugc->find();
        $extra['options'][''] = 'none';
        foreach ($groups as $group)
        {
            $extra['options'][$group['name']] = $group['nice_name'];
        }
        return MPField::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_form_group_multiple($name, $value, $extra)
    public static function field_form_group_multiple($name, $value, $extra)
    {
        $ugc = MPDB::selectCollection('mpuser_group');
        $groups = $ugc->find();
        foreach ($groups as $group)
        {
            $extra['options'][$group['name']] = $group['nice_name'];
        }
        unset($extra['options']['']);
        $extra['attr']['multiple'] = 'multiple';
        $extra['attr']['size'] = 3;
        return MPField::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_form_user($name, $value, $extra)
    public static function field_form_user($name, $value, $extra)
    {
        $uac = MPDB::selectCollection('mpuser_account');
        $users = $uac->find();
        $extra['options'][''] = 'none';
        foreach ($users as $user)
        {
            $extra['options'][$user['name']] = $user['nice_name'];
        }
        return MPField::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_form_user_current($name, $value, $extra)
    /** 
     * In the content module, when going back into a revision this will cause
     * problems. If editing an old revision with no data set for this field
     * then saving will save the newest revision with that noted. And the 
     * previously newest revision might have had this value set, but it is now
     * over written. The best way to use this field is to assign it to the
     * content type as early as possible.
     */
    public static function field_form_user_current($name, $value, $extra)
    {
        $action = deka('add', $extra, 'form_action');
        $value = is_array($value) ? $value[0] : $value;
        if ($action === 'edit')
        {
            switch ($value)
            {
                case '':
                case self::ID_NONE:
                    $field_name = 'None';
                    $field_value = self::ID_NONE;
                break;
                case self::ID_ANONYMOUS:
                    $field_name = 'Anonymous';
                    $field_value = self::ID_ANONYMOUS;
                break;
                default:
                    $uac = MPDB::selectCollection('mpuser_account');
                    $user = $uac->findOne(array('name' => $value));
                    $field_name = $user ? $user['nice_name'] : 'user: '.$value.' (account no longer exists)';
                    $field_value = $value;
                break;
            }
        }
        else
        {
            $field_name = self::info('nice_name');
            $field_value = self::info('name');
        }
        $extra['html'] = '<p>'.$field_name.'</p>';
        return MPField::act('form', 'hidden', $name, $field_value, $extra);
    }

    //}}}
    //{{{ public static function field_form_user_multiple($name, $value, $extra)
    public static function field_form_user_multiple($name, $value, $extra)
    {
        $uac = MPDB::selectCollection('mpuser_account');
        $users = $uac->find();
        foreach ($users as $user)
        {
            $extra['options'][$user['name']] = $user['nice_name'];
        }
        unset($extra['options']['']);
        $extra['attr']['multiple'] = 'multiple';
        $extra['attr']['size'] = 3;
        return MPField::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_public_group()
    public static function field_public_group()
    {
        return array(
            'description' => 'A user group',
            'meta' => FALSE,
            'name' => 'MPUser Group',
        );
    }

    //}}}
    //{{{ public static function field_public_group_multiple()
    public static function field_public_group_multiple()
    {
        return array(
            'description' => 'Multiple user groups',
            'meta' => FALSE,
            'name' => 'MPUser Group (multiple select menu)',
        );
    }

    //}}}
    //{{{ public static function field_public_user()
    public static function field_public_user()
    {
        return array(
            'description' => 'A user account',
            'meta' => FALSE,
            'name' => 'MPUser Account',
        );
    }

    //}}}
    //{{{ public static function field_public_user_current()
    public static function field_public_user_current()
    {
        return array(
            'description' => 'Records the current user',
            'meta' => FALSE,
            'name' => 'MPUser Account (the current one)',
        );
    }

    //}}}
    //{{{ public static function field_public_user_multiple()
    public static function field_public_user_multiple()
    {
        return array(
            'description' => 'Multiple user accounts',
            'meta' => FALSE,
            'name' => 'MPUser Account (multiple select menu)',
        );
    }

    //}}}
}
