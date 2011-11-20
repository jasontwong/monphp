<?php

Admin::set('title', 'Search Test');
Admin::set('header', 'Search Test');

/*
SearchAPI::set_data(
    'id', 
    array(),
    // "testing stuff again",
    "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas et enim mi, feugiat ullamcorper ipsum. Proin ut mi justo. Praesent erat enim, pretium id accumsan id, fringilla et augue. Donec commodo ultricies massa. Vivamus eu quam urna, nec sodales est. Sed eget turpis molestie ante pharetra elementum. Nam id ante mi, sed feugiat magna. Morbi nec enim leo, eget varius augue. Nulla fringilla tempor lacinia. Maecenas tincidunt, augue non eleifend interdum, lorem lorem consequat justo, et pellentesque magna massa eu leo. Praesent porta lectus orci, semper ornare tellus.

Duis diam tortor, varius quis lacinia nec, vestibulum id felis. Duis vitae vehicula magna. Nunc venenatis placerat turpis sed tincidunt. Ut porttitor, erat vel fermentum tempus, nunc elit pulvinar lectus, ut tempor urna orci vehicula lectus. Etiam nunc lorem, viverra ac mollis at, ullamcorper eget neque. Etiam nec vehicula tellus. Nunc ac hendrerit dui. In quis odio sapien. Praesent porta leo purus. Aliquam ac tincidunt arcu. Etiam eget nibh nec augue lobortis semper. Maecenas vel massa nunc, et commodo risus. Nunc et diam nec orci volutpat convallis. Maecenas mattis laoreet auctor. Donec volutpat euismod turpis in bibendum. Nam lobortis, nibh sed suscipit tempus, elit lacus pulvinar turpis, a iaculis nibh est sit amet ante. Aenean blandit porta dolor sit amet tempus. Nunc eget ultricies tellus. Suspendisse eu lorem nec est hendrerit adipiscing.",
    'test'
);
*/

    /*
var_dump(
    SearchAPI::find('hendrerit'),
    SearchAPI::find('hendrerit lorem'),
    SearchAPI::find('hendrerit lorem ipsum'),
    SearchAPI::find('hendrerit lorem testing'),
    SearchAPI::find('hendrerit dui')
    SearchAPI::find('Lorem'),
    SearchAPI::find('ipsum'),
    SearchAPI::find('nothing in here'),
    SearchAPI::find('Lorem ipsum')
);
    */

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'query',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset',
    )
);

// }}}
// {{{ post submission
if (isset($_POST['search']))
{
    $search = $layout->acts('post', $_POST['search']);
    $layout->merge($_POST['search']);
    // $results = Module::h('search_results', Module::TARGET_ALL, Search::tokenize($search['query']));
    $results = Search::get_search_results($search['query']);
    var_dump($results);
}

// }}}
//{{{ build form
$form = new FormBuilderRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'name' => 'search_test'
);
//{{{ fields
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Search'
                ),
                'fields' => $layout->get_layout('query')
            ),
        )
    ),
    'search'
);

$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            ),
        )
    )
);

// }}} 
$fh = $form->build();

//}}}

?>
