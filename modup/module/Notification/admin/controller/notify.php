<?php

echo 'test';

$notifications = array();

if (isset($_SESSION['notifications']))
{
    foreach ($_SESSION['notifications'] as $type => $messages)
    {
        $notifications['type'] = $type;
        $notifications['messages'] = $messages;
    }
}

echo json_encode($notifications);

?>
