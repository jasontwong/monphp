<?php

Admin::set('title', 'Create a new inventory option group');
Admin::set('header', 'Create a new inventory option group');

if ($_POST)
{
    $og_id = Inventory::save_option_group(
        $_POST['group_name'],
        $_POST['group_description']
    );

    $option_weight = 0;
    foreach ($_POST['option'] as $option)
    {
        if ($option['name'] !== '' && $option['display_name'] !== '')
        {
            $oid = Inventory::save_option(
                $og_id,
                $option['name'],
                $option['display_name'],
                $option['image'],
                $option_weight
            );
            $option_weight++;
        }
    }
    header('Location: /admin/module/Inventory/option_group_edit/'.$og_id.'/');
    exit;
}

?>
