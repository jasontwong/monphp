<?php

$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
if (isset($_POST['profile']))
{
    $keys = Profile::save_profile_form($_SESSION['user']['name'], $_POST['profile']);
    if (!empty($keys))
    {
        var_dump('Error');
    }
    $post = $_POST['profile'];
}
else
{
    $pdata = Profile::get_profile_data($_SESSION['user']['name'], array(), TRUE);
    $post = !empty($pdata)
        ? $pdata
        : NULL;
}
$form = Profile::get_profile_form('profile', NULL, $post);
$form->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'do'
);

echo $form->build();

?>
