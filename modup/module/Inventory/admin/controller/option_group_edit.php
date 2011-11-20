<?php

Admin::set('title', 'Create a new inventory option group');
Admin::set('header', 'Create a new inventory option group');

if ($_POST)
{
    Inventory::update_option_group(
        $_POST['group_id'],
        $_POST['group_name'],
        $_POST['group_description']
    );
    $option_group = array(
        'id' => $_POST['group_id'],
        'name' => $_POST['group_name'],
        'description' => $_POST['group_description']
    );

    $options = array();
    $option_weight = 0;
    foreach ($_POST['option'] as $option)
    {
        if (deka(0, $option, 'delete') && eka($option, 'id'))
        {
            Inventory::delete_option($option['id']);
        }
        else
        {
            if (eka($option, 'id'))
            {
                Inventory::update_option(
                    $option['id'],
                    $_POST['group_id'],
                    $option['name'],
                    $option['display_name'],
                    $option['image'],
                    $option_weight
                );
                $options[] = array(
                    'id' => $option['id'],
                    'weight' => $option_weight,
                    'name' => $option['name'],
                    'display_name' => $option['display_name'],
                    'image' => deka('', $option, 'image')
                );
            }
            elseif ($option['name'] !== '' && $option['display_name'] !== '')
            {
                $oid = Inventory::save_option(
                    $_POST['group_id'],
                    $option['name'],
                    $option['display_name'],
                    $option['image'],
                    $option_weight
                );
                $options[] = array(
                    'id' => $oid,
                    'weight' => $option_weight,
                    'name' => $option['name'],
                    'display_name' => $option['display_name'],
                    'image' => deka('', $option, 'image')
                );
            }
            $option_weight++;
        }
    }
}
else
{
    $option_group = Inventory::get_option_group(URI_PART_4);
    $options = Inventory::get_options(URI_PART_4);
}

?>
