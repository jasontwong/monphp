<?php

if (!MPUser::perm('admin settings') && (defined('URI_PART_1') && !MPUser::perm(URI_PART_1.' settings')))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Settings');
MPAdmin::set('header', 'Settings');

$default_key = '_Site';
$settings_key = URI_PART_2;
$layouts = MPModule::h('settings_fields', $settings_key);

// {{{ default site settings
if (empty($layouts))
{
    $settings_key = $default_key;
    $layouts[$settings_key] = MPData::settings_form();
    $settings = MPData::query();
    $modules = MPData::query('_System', 'modules');
    $mods = MPModule::available();
    $mod_values = array();
    foreach ($mods as $name => $mod)
    {
        $mod_values[$name] = MPAdmin::row_module($name, $mod);
    }
    if (MPUser::check_group('admin'))
    {
        $layouts[$settings_key][] = array(
            'field' => MPField::layout(
                'checkbox',
                array(
                    'data' => array(
                        'options' => $mod_values
                    )
                )
            ),
            'name' => 'modules',
            'type' => 'checkbox',
            'value' => array(
                'data' => $modules
            )
        );
    }
}

// }}}
// {{{ layout
$layout = new MPField();
$field_names = array();
foreach ($layouts[$settings_key] as $fg => $fields)
{
    if (ake('field', $fields))
    {
        $tmp[] = $fields;
        $fields = $tmp;
        unset($tmp);
    }
    foreach ($fields as $field)
    {
        $field_names[$fg][] = $field['name'];
        $layout->add_layout($field);
    }
}
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
//{{{ form submitted
if (isset($_POST['form']))
{
    $settings = $layout->acts('post', $_POST[$settings_key]);
    // TODO this is for file upload. there should be a cleaner way to do this
    $layout->acts('save', $_POST[$settings_key]);
    $layout->merge($_POST[$settings_key]);
    $mdepends = TRUE;
    if (ake('modules', $settings) && $settings_key === $default_key)
    {
        $mpost = $settings['modules'];
        $mdepends = MPModule::check_dependency($mpost);
        if ($mdepends)
        {
            $deactivated = array_diff($modules, $mpost);
            $activated = array_diff($mpost, $modules);
            MPData::update('_System', 'modules', $mpost);
            foreach ($deactivated as $mod)
            {
                MPModule::h('deactivate', $mod);
                MPModule::uninstall($mod);
            }
            foreach ($activated as $mod)
            {
                MPModule::h('activate', $mod);
                MPModule::load_active(TRUE);
                MPModule::install($mod);
            }
            unset($settings['modules']);
        }
        else
        {
            // TODO dependency check didn't pass
        }
    }
    // if ($mdepends && $settings_key !== $default_key)
    if ($mdepends)
    {
        foreach ($settings as $name => $data)
        {
            $result = MPModule::h('data_validate', $settings_key, $name, $data);
            if (empty($result) || deka(FALSE, $result, $settings_key, 'success'))
            {
                MPData::update($settings_key, $name, $data);
            }
        }
    }
    MPData::save();
}

//}}}
//{{{ build form
$form = new MPFormRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'enctype' => 'multipart/form-data',
    'name' => 'modify_settings',
    'class' => $settings_key.'_settings'
);

$rows = array();
foreach ($field_names as $fg => $names)
{
    $tmp = is_numeric($fg) 
        ? $settings_key  === $default_key
            ? 'Site'
            : $settings_key
        : $fg;
    foreach ($names as $name)
    {
        $rows[$tmp][] = array('fields' => $layout->get_layout($name));
    }
}
foreach ($rows as $fg => $frows)
{
    $form->add_group(
        array(
            'attr' => array(
                'class' => 'clear tabbed'
            ),
            'label' => array(
                'text' => $fg
            ),
            'rows' => $frows, 
        ),
        $settings_key
    );
}

$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'form'
);

$fh = $form->build();

//}}}

include DIR_MODULE.'/MPAdmin/view/settings.php';

?>
