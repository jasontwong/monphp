<?php

if (!User::has_perm('edit taxonomy', 'edit taxonomy-'.URI_PART_4))
{
    $sfh = '';
    $tfh = '';
    exit;
}

Admin::set('title', 'Edit Taxonomy Scheme');
Admin::set('header', 'Edit Taxonomy Scheme');

$tt = Doctrine::getTable('TaxonomyScheme');
$scheme = $tt->findOneById(URI_PART_4);
$taxm = new TaxonomyManager($scheme['module'], $scheme['mkey']);
$terms = $taxm->get_terms($scheme['name']);

// scheme
//{{{ layout
$slayout = new Field();
if ($scheme['type'] == Taxonomy::TYPE_TREE)
{
    $options = array();
    foreach ($terms as $term)
    {
        $options[$term['id']] = $term;
    }
    $options = $taxm->arrange_terms($options, $scheme['type']);
    $dd = array(-1 => '&mdash; none &mdash;');
    foreach ($options as $k => &$option)
    {
        $dd[$k] = $option['term'];
    }
    $slayout->add_layout(
        array(
            'field' => Field::layout(
                'dropdown',
                array(
                    'data' => array(
                        'options' => $dd
                    )
                )
            ),
            'name' => 'parent_id',
            'type' => 'dropdown',
        )
    );
}
$slayout->add_layout(
    array(
        'field' => Field::layout('textarea_array'),
        'name' => 'terms',
        'type' => 'textarea_array',
    )
);
$slayout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$slayout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'attr' => array('value' => 'edit')
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
//}}}
//{{{ form submission
if (isset($_POST['scheme']))
{
    try
    {
        $pterms = $slayout->acts('post', $_POST['scheme']);
        if (eka($pterms, 'parent_id'))
        {
            foreach ($pterms['terms'] as $k => &$pterm)
            {
                $pterm = array(
                    'term' => $pterm,
                    'parent_id' => $pterms['parent_id'] == -1 ? NULL : $pterms['parent_id']
                );
            }
        }
        switch ($scheme['type'])
        {
            case Taxonomy::TYPE_FLAT:
            case Taxonomy::TYPE_FREE:
                $taxm->set_scheme_terms($scheme['name'], $pterms['terms']);
            break;
            case Taxonomy::TYPE_TREE:
                $taxm->add_terms($pterms['terms'], $scheme['name']);
            break;
        }
        /*
        $pmerge = $pterms;
        $pmerge['terms'] = implode("\n", $pmerge['terms']);
        $slayout->merge(array('terms' => array('data' => $pmerge['terms'])));
        */
    }
    catch (Doctrine_Validator_Exception $e)
    {
        echo 'exception caught';
    }
}
//}}}
//{{{ scheme form build
$sform = new FormBuilderRows;
$sform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$sform->label = array(
    'text' => 'Taxonomy Scheme'
);
$srows['rows'][] = array(
    'fields' => $slayout->get_layout('id'),
    'hidden' => TRUE
);
$terms_box = array(
    'fields' => $slayout->get_layout('terms'),
    'label' => array(
        'text' => 'Taxonomy terms'
    ),
    'description' => array(
        'text' => 'One term per line.'
    )
);
if ($scheme['type'] == Taxonomy::TYPE_TREE)
{
    $srows['rows'][] = array(
        'fields' => $slayout->get_layout('parent_id'),
        'label' => array(
            'text' => 'Parent'
        )
    );
    $terms_box['description']['text'] .= ' Each term will be a child of the parent selected.';
}
$srows['rows'][] = $terms_box;
$sform->add_group($srows, 'scheme');
$sform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $slayout->get_layout('submit')
            )
        )
    ),
    'form'
);
$sfh = $sform->build();
//}}}

// terms table
//{{{ layout
$tlayout = new Field();
if ($scheme['type'] == Taxonomy::TYPE_TREE)
{
    $options = array();
    foreach ($terms as $term)
    {
        $options[$term['id']] = $term;
    }
    $options = $taxm->arrange_terms($options, $scheme['type']);
    foreach ($options as &$option)
    {
        $option = '<a href="/admin/module/Taxonomy/edit_term/'.$option['id'].'">'.$option['term'].'</a>';
    }
    $tlayout->add_layout(
        array(
            'field' => Field::layout(
                'checkbox',
                array(
                    'data' => array(
                        'options' => $options
                    )
                )
            ),
            'name' => 'terms',
            'type' => 'checkbox',
        )
    );
}
$tlayout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$tlayout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'attr' => array(
                        'value' => 'delete'
                    ),
                    'text' => 'Delete'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset',
    )
);
//}}}
//{{{ scheme form build
$tform = new FormBuilderRows;
$tform->attr = array(
    'action' => '/admin/module/Taxonomy/delete_terms/',
    'method' => 'post'
);
$tform->label = array(
    'text' => 'Taxonomy Scheme'
);
$trows['rows'][] = array(
    'fields' => $tlayout->get_layout('id'),
    'hidden' => TRUE
);
$trows['rows'][] = array(
    'fields' => $tlayout->get_layout('terms'),
    'label' => array(
        'text' => 'Current taxonomy terms'
    ),
    'description' => array(
        'text' => 'Click the term names to edit them.'
    )
);
$tform->add_group($trows, 'term');
$tform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $tlayout->get_layout(
                    'submit'
                )
            )
        )
    ),
    'form'
);
$tfh = $tform->build();
//}}}

?>
