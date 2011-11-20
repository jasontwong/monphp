<?php 

if (!User::has_perm('edit workflow'))
{
    header('Location: /admin/');
    exit;
}

$tasks = Workflow::get_list();

?>
