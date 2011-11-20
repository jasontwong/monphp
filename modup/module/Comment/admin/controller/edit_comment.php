<?php

if (!User::perm('moderate comments') && !User::perm('edit comments'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Comment');
Admin::set('header', 'Edit Comment');

$cet = Doctrine::getTable('CommentEntry');
$ce = $cet->find('get.id', array(URI_PART_4))->getLast();
if ($ce === FALSE)
{
    header('Location: /admin/module/Comment/manage/');
    exit();
}
$user_data = $ce->user_data;
$comment = array();
// {{{ post submission
if (isset($_POST['comment']))
{
    $comment = Field::acts('post', $_POST['comment']);
    $comment['create_date'] = strtotime($comment['create_date']);
    $comment['user_data'] = array_merge($user_data, $comment['user_data']);
    $comment['status'] = $comment['status'] ? Comment::APPROVED : Comment::UNAPPROVED;
    $comment['spam'] = $comment['spam'] ? Comment::SPAM : Comment::NOT_SPAM;
    if ($comment['delete'])
    {
        $ce->delete();
        header('Location: /admin/module/Comment/manage/');
        exit;
    }
    $ce->merge($comment);
    if ($ce->isValid())
    {
        $ce->save();
    }
}

// }}}
// {{{ build form
$form = new form(new form_builder_rows);
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'name' => 'edit_comment'
);
// {{{ field prep
$approved_row = array(
    'label' => array(
        'text' => 'Approved'
    ),
    'fields' => array(
        Field::act('form', 'checkbox_boolean', 'status', deka($ce->status, $comment, 'status'))
    )
);
$spam_row = array(
    'label' => array(
        'text' => 'Spam'
    ),
    'fields' => array(
        Field::act('form', 'checkbox_boolean', 'spam', deka($ce->spam, $comment, 'spam'))
    )
);
$delete_row = array(
    'label' => array(
        'text' => 'Delete'
    ),
    'fields' => array(
        Field::act('form', 'checkbox_boolean', 'delete', '')
    )
);
if (ake('id', $user_data))
{
    $user_row_disabled = array(
        'label' => array(
            'text' => 'User'
        ),
        'fields' => array(
            Field::act('form', 'user', 'user_data[id]', deka($user_data['id'], $comment, 'user_data', 'id'),
                array(
                    'attr' => array(
                        'disabled' => 1
                    )
                )
            )
        )
    );

    $user_row = array(
        'label' => array(
            'text' => 'User'
        ),
        'fields' => array(
            Field::act('form', 'user', 'user_data[id]', deka($user_data['id'], $comment, 'user_data', 'id'))
        )
    );
}
else
{
    $user_row_disabled = array(
        'label' => array(
            'text' => 'User'
        ),
        'fields' => array(
            Field::act('form', 'text', 'user_data[name]', deka($user_data['name'], $comment, 'user_data', 'name'),
                array(
                    'attr' => array(
                        'disabled' => 1
                    )
                )
            )
        )
    );
    $user_row = array(
        'label' => array(
            'text' => 'User'
        ),
        'fields' => array(
            Field::act('form', 'text', 'user_data[name]', deka($user_data['name'], $comment, 'user_data', 'name'))
        )
    );
}

// }}}
// {{{ fields - moderate 
$moderate_rows = array(
            array(
                'label' => array(
                    'text' => 'Module Name'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'module_name', deka($ce->module_name, $comment, 'module_name'),
                        array(
                            'attr' => array(
                                'disabled' => 1
                            )
                        )
                    )
                ),
            ),
            array(
                'label' => array(
                    'text' => 'Module Entry ID'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'module_entry_id', deka($ce->module_entry_id, $comment, 'module_module_entry_id_id'),
                        array(
                            'attr' => array(
                                'disabled' => 1
                            )
                        )
                    )
                ),
            ),
            array(
                'label' => array(
                    'text' => 'Create Date'
                ),
                'fields' => array(
                    Field::act('form', 'date', 'create_date', gmdate('Y-m-d H:i:s', deka($ce->create_date, $comment, 'create_date')),
                        array(
                            'attr' => array(
                                'disabled' => 1
                            )
                        )
                    )
                ),
            ),
            array(
                'label' => array(
                    'text' => 'Comment Approved by'
                ),
                'fields' => array(
                    Field::act('form', 'user', 'approved_by', deka($ce->approved_by, $comment, 'approved_by'),
                        array(
                            'attr' => array(
                                'disabled' => 1
                            )
                        )
                    )
                )
            ),
            array(
                'label' => array(
                    'text' => 'Permalink'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'permalink', deka($ce->permalink, $comment, 'permalink'),
                        array(
                            'attr' => array(
                                'disabled' => 1
                            )
                        )
                    )
                )
            ),
            array(
                'label' => array(
                    'text' => 'Entry'
                ),
                'fields' => array(
                    Field::act('form', 'textarea', 'entry', deka($ce->entry, $comment, 'entry'),
                        array(
                            'attr' => array(
                                'disabled' => 1
                            )
                        )
                    )
                ),
            ),
            $user_row_disabled,
            $approved_row,
);

// }}}
// {{{ fields - edit
$edit_rows = array(
            array(
                'label' => array(
                    'text' => 'Module Name'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'module_name', deka($ce->module_name, $comment, 'module_name'))
                ),
            ),
            array(
                'label' => array(
                    'text' => 'Module Entry ID'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'module_entry_id', deka($ce->module_entry_id, $comment, 'module_module_entry_id_id'))
                ),
            ),
            array(
                'label' => array(
                    'text' => 'Create Date'
                ),
                'fields' => array(
                    Field::act('form', 'date', 'create_date', gmdate('c', deka($ce->create_date, $comment, 'create_date')))
                ),
            ),
            array(
                'label' => array(
                    'text' => 'Comment Approved by'
                ),
                'fields' => array(
                    Field::act('form', 'user', 'approved_by', deka($ce->approved_by, $comment, 'approved_by'))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Permalink'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'permalink', deka($ce->permalink, $comment, 'permalink'))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Entry'
                ),
                'fields' => array(
                    Field::act('form', 'textarea', 'entry', deka($ce->entry, $comment, 'entry'))
                )
            ),
            $user_row,
            $approved_row,
);

// }}}
if (Comment::is_using_akismet())
{
    array_push($edit_rows, $spam_row);
    array_push($moderate_rows, $spam_row);
}
array_push($edit_rows, $delete_row);
if (User::perm('edit comments'))
{
    $group = array(
        'rows' => $edit_rows
    );
}
else
{
    $group = array(
        'rows' => $moderate_rows
    );
}
$form->add_group($group, 'comment');
// {{{ submit
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => Field::act('form', 'submit_reset', '', '')
            )
        )
    )
);

// }}}
// }}}
$fh = $form->build();

?>
