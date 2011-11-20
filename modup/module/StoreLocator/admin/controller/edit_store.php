<?php

if (!User::perm('edit store'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Store');
Admin::set('header', 'Edit Store');

$countries = StoreLocator::get_country_list();
$countries = array_combine($countries, $countries);
$names = StoreLocator::get_names();
array_unshift($names, '');
$names = $names ? array_combine($names, $names) : array();

$sllt = Doctrine::getTable('StoreLocatorLocation');
$sll = $sllt->find('get.id', array(URI_PART_4), Doctrine::HYDRATE_RECORD)->getLast();

// {{{ field layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('dropdown'),
        'name' => 'db_name',
        'type' => 'dropdown',
        'value' => array(
            'data' => $sll['Name']['name']
        ),
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
        'value' => array(
            'data' => $sll['address1']
        ),
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'address2',
        'value' => array(
            'data' => $sll['address2']
        ),
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'city',
        'value' => array(
            'data' => $sll['city']
        ),
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'state',
        'value' => array(
            'data' => $sll['state']
        ),
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
            'data' => $sll['country']
        ),
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'zip_code',
        'value' => array(
            'data' => $sll['zip_code']
        ),
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'phone',
        'value' => array(
            'data' => $sll['phone']
        ),
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'website',
        'value' => array(
            'data' => $sll['Name']['website']
        ),
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'online',
        'value' => array(
            'data' => $sll['Name']['online']
        ),
        'type' => 'checkbox_boolean'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'delete',
        'type' => 'checkbox_boolean'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'latitude',
        'value' => array(
            'data' => $sll['latitude']
        ),
        'type' => 'hidden'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'longitude',
        'value' => array(
            'data' => $sll['longitude']
        ),
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
    $layout->merge($_POST['location']);
    if (deka(FALSE, $store, 'delete'))
    {
        $sll->delete();
        header('Location: /admin/module/StoreLocator/stores/');
        exit;
    }
    if (strlen($store['db_name']))
    {
        $store['name'] = $store['db_name'];
    }
    $slnt = Doctrine::getTable('StoreLocatorName');
    $store['slug'] = slugify($store['name']);
    $record = $slnt->findOneBySlug($store['slug']);
    $valid_name = TRUE;
    if ($record !== FALSE)
    {
        $record->merge($store);
        if ($record->isValid())
        {
            $record->save();
        }
        else
        {
            $valid_name = FALSE;
        }
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

    $sll->Name = $sln;
    $sll->merge($store);
    if($sll->isValid() && $valid_name)
    {
        $sll->save();
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
    'name' => 'store_location_edit'
);
$form->label = array(
    'text' => 'Edit a store location'
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
            'fields' => $layout->get_layout('delete'),
            'label' => array(
                'text' => 'Delete this entry'
            )
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
