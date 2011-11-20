<?php

if (!User::perm('add store'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Add Store');
// Admin::set('header', 'Add Store');

$countries = StoreLocator::get_country_list();
$countries = array_combine($countries, $countries);
$names = StoreLocator::get_names();
array_unshift($names, '');
$names = $names ? array_combine($names, $names) : array();

// {{{ field layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('dropdown'),
        'name' => 'db_name',
        'type' => 'dropdown',
        'options' => array(
            'data' => $names
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'address1',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'address2',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'city',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'state',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('dropdown'),
        'name' => 'country',
        'type' => 'dropdown',
        'options' => array(
            'data' => $countries
        ),
        'value' => array(
            'data' => 'United States'
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'zip_code',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'phone',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'website',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'online',
        'type' => 'checkbox_boolean'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'latitude',
        'type' => 'hidden'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'longitude',
        'type' => 'hidden'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'text' => 'Save'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
// }}}
//{{{ form submission
if (isset($_POST['location']))
{
    $store = $layout->acts('post', $_POST['location']);
    if ($store['db_name'] !== '')
    {
        $store['name'] = $store['db_name'];
    }
    $slnt = Doctrine::getTable('StoreLocatorName');
    $store['slug'] = slugify($store['name']);
    $record = $slnt->findOneBySlug($store['slug']);
    $valid_name = TRUE;
    if ($record !== FALSE)
    {
        $sln = $record;
    }
    else
    {
        $sln = new StoreLocatorName();
        $sln->merge($store);
        if ($sln->isValid())
        {
            $sln->save();
        }
        else
        {
            $valid_name = FALSE;
        }
    }

    $sll = new StoreLocatorLocation();
    $sll->Name = $sln;
    $sll->merge($store);
    if($sll->isValid() && $valid_name)
    {
        $sll->save();
        header('Location: /admin/module/StoreLocator/stores/');
        exit;
    }

    $sln->free();
    $sll->free();
    unset($sln, $sll);
}

//}}}
//{{{ form build
$form = new FormBuilderRows;
$form->attr = array(
    'action' => URI_PATH,
    'method' => 'post',
    'name' => 'store_location_add'
);
$form->label = array(
    'text' => 'Add a store location'
);
$form->add_group(array(
    'rows' => array(
        array(
            'fields' => $layout->get_layout('db_name'),
            'label' => array(
                'text' => 'Select store name'
            ),
        ),
        array(
            'fields' => $layout->get_layout('name'),
            'label' => array(
                'text' => '…or manually enter'
            ),
        ),
        array(
            'fields' => $layout->get_layout('address1'),
            'label' => array(
                'text' => 'Address Line 1'
            ),
        ),
        array(
            'fields' => $layout->get_layout('address2'),
            'label' => array(
                'text' => 'Address Line 2'
            ),
        ),
        array(
            'fields' => $layout->get_layout('city'),
            'label' => array(
                'text' => 'City'
            ),
        ),
        array(
            'fields' => $layout->get_layout('state'),
            'label' => array(
                'text' => 'State'
            ),
        ),
        array(
            'fields' => $layout->get_layout('country'),
            'label' => array(
                'text' => 'Country'
            ),
        ),
        array(
            'fields' => $layout->get_layout('zip_code'),
            'label' => array(
                'text' => 'Zip Code'
            ),
        ),
        array(
            'fields' => $layout->get_layout('phone'),
            'label' => array(
                'text' => 'Phone'
            ),
        ),
        array(
            'fields' => $layout->get_layout('website'),
            'label' => array(
                'text' => 'Website'
            ),
        ),
        array(
            'fields' => $layout->get_layout('online'),
            'label' => array(
                'text' => 'Online Reseller?'
            ),
        ),
        array(
            'fields' => $layout->get_layout('latitude'),
        ),
        array(
            'fields' => $layout->get_layout('longitude'),
        ),
    )
), 'location');
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            )
        )
    )
);
//}}}

?>
