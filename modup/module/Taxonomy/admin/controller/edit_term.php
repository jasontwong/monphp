<?php

$taxm = new TaxonomyManager('');
$term = $taxm->get_term_scheme(URI_PART_4);
$taxm->set_key($term['scheme']['mkey']);
$taxm->set_module($term['scheme']['module']);
$terms = $taxm->get_terms($term['scheme']['name']);
$pterm = $term['term'];

if (!User::has_perm('edit taxonomy', 'edit taxonomy-'.$term['scheme']['id']))
{
    $sfh = '';
    $tfh = '';
    exit;
}
Admin::set('title', 'Edit Taxonomy Term');
Admin::set('header', 'Edit Taxonomy Term');

//{{{ layout
$tlayout = new Field();
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
        'field' => Field::layout('text'),
        'name' => 'term',
        'type' => 'text',
    )
);
$tlayout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'slug',
        'type' => 'text',
    )
);
$tlayout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset',
    )
);
if ($term['scheme']['type'] == Taxonomy::TYPE_TREE)
{
    $options = array();
    foreach ($terms as $t)
    {
        $options[$t['id']] = $t;
    }
    $options = $taxm->arrange_terms($options, $term['scheme']['type']);
    $dd = array(-1 => '&mdash; none &mdash;');
    foreach ($options as $k => &$option)
    {
        $dd[$k] = $option['term'];
    }
    $tlayout->add_layout(
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
//}}}
//{{{ form submission
if (eka($_POST, 'term'))
{
    try
    {
        $pterm = $tlayout->acts('post', $_POST['term']);
        $ttt = Doctrine::getTable('TaxonomyTerm');
        $term_row = $ttt->findOneById($pterm['id']);
        $term_row->merge($pterm);
        if ($term_row->parent_id == -1)
        {
            $term_row->parent_id = NULL;
        }
        $term_row->save();
        header('Location: /admin/module/Taxonomy/edit_scheme/'.$term['scheme']['id'].'/');
        exit;
    }
    catch (Doctrine_Validator_Exception $e)
    {
        echo 'exception caught';
    }
}
//}}}
//{{{ form build
$tlayout->merge(
    array(
        'term' => array(
            'data' => $pterm['term']
        ),
        'slug' => array(
            'data' => $pterm['slug']
        ),
        'parent_id' => array(
            'data' => $pterm['parent_id']
        )
    )
);
$tform = new FormBuilderRows;
$tform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$tform->label = array(
    'text' => 'Taxonomy Term'
);
$trows['rows'][] = array(
    'fields' => $tlayout->get_layout('id'),
    'hidden' => TRUE
);
$trows['rows'][] = array(
    'fields' => $tlayout->get_layout('term'),
    'label' => array(
        'text' => 'Name'
    )
);
$trows['rows'][] = array(
    'fields' => $tlayout->get_layout('slug'),
    'label' => array(
        'text' => 'Slug'
    )
);
if ($term['scheme']['type'] == Taxonomy::TYPE_TREE)
{
    $trows['rows'][] = array(
        'fields' => $tlayout->get_layout('parent_id'),
        'label' => array(
            'text' => 'Parent'
        )
    );
}
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
