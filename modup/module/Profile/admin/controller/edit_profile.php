<?php

if (!User::perm('edit profile') && !User::perm('edit own profile'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Profile');
Admin::set('header', 'Edit Profile');

$pet = Doctrine::getTable('ProfileEntry');
$pe = $pet->find(URI_PART_4);
/*
$pe->data = array('default_name' => 'test', 'user_required' => 1, 'approval_required' => 0, 'akismet_in_use' => 1, 'akismet_api_key' => 'test2');
if ($pe->isModified() && $pe->isValid())
{
    $pe->save();
}
*/
if ($pe === FALSE)
{
    header('Location: /admin/module/profile/manage/');
    exit;
}
$post_values = NULL;
// {{{ post submission
if (isset($_POST['profile']))
{
    $profile = Field::acts('post', $_POST['profile']);
    $post_values = deka(array(), $profile, $pe->module_name);
    $success = Module::h('profile_validate', $pe->module_name, $pe->module_entry_id, $post_values);
    if (!$success)
    {
        echo 'ERROR!';
    }
}

// }}}
// {{{ build form
$form = new form(new form_builder_rows);
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'name' => 'edit_profile'
);
$groups = Module::h('profile_fields', $pe->module_name, $pe->module_entry_id, is_array($post_values) ? $post_values : $pe->data);
foreach ($groups as $mod => $group)
{
    $form->add_group($group, 'profile['.$mod.']');
}

// {{{ submit
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => Field::act('form', 'submit_reset', '', '')
            )
        )
    )
);

// }}}
// }}}
$fh = $form->build();

?>
