<?php

if (!User::perm('admin settings') && (defined('URI_PART_1') && !User::perm(URI_PART_1.' settings')))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Settings');
Admin::set('header', 'Settings');

$default_key = '_Site';
$settings_key = URI_PART_2;
$layouts = Module::h('settings_fields', $settings_key);

// {{{ default site settings
if (empty($layouts))
{
    $settings_key = $default_key;
    $layouts[$settings_key] = Data::settings_form();
    $settings = Data::query();
    $modules = Data::query('_System', 'modules');
    $mods = Module::available();
    $mod_values = array();
    foreach ($mods as $name => $mod)
    {
        $mod_values[$name] = Admin::row_module($name, $mod);
    }
    if (User::check_group('admin'))
    {
        $layouts[$settings_key][] = array(
            'field' => Field::layout(
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
$layout = new Field();
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
        'field' => Field::layout('submit_reset'),
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
        $mdepends = Module::check_dependency($mpost);
        if ($mdepends)
        {
            $deactivated = array_diff($modules, $mpost);
            $activated = array_diff($mpost, $modules);
            Data::update('_System', 'modules', $mpost);
            foreach ($deactivated as $mod)
            {
                Module::h('deactivate', $mod);
                Module::uninstall($mod);
            }
            foreach ($activated as $mod)
            {
                Module::h('activate', $mod);
                Module::load_active(TRUE);
                Module::install($mod);
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
            $result = Module::h('data_validate', $settings_key, $name, $data);
            if (empty($result) || deka(FALSE, $result, $settings_key, 'success'))
            {
                Data::update($settings_key, $name, $data);
            }
        }
    }
    Data::save();
}

//}}}
//{{{ build form
$form = new FormBuilderRows;
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

include DIR_MODULE.'/Admin/view/settings.php';

?>
