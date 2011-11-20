<?php

Admin::set('title', 'Map Test');
Admin::set('header', 'Map Test');

//{{{ build form
$form = new form(new form_builder_rows);
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'name' => 'map_test'
);
//{{{ fields
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Address'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'address', '', array(
                        'attr'=>array(
                            'id' => 'address'
                        )
                    ))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Radius'
                ),
                'fields' => array(
                    Field::act('form', 'select', 'radius', '25', array(
                        'attr' => array(
                            'id' => 'radius'
                        ),
                        'options' => array_combine(StoreLocator::get_radii(), StoreLocator::get_radii())
                    ))
                )
            ),
        )
    ),
    'store_locator'
);

//}}}
// {{{ submit
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => array(
                    Field::act('form', 'button', '', 'Submit', array(
                        'text' => 'Submit',
                        'attr' => array(
                            'onclick' => 'return false',
                            'id' => 'get_markers'
                        )
                    ))
                ),
            ),
        )
    )
);

// }}} 
$fh = $form->build();

//}}}

?>
