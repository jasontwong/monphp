<?php

$file = '<?php

$_db_conn["default"] = array(
    "server" => "mongodb://' . $db['host'] . ':' . $db['port'] . '/",
    "options" => array(';

foreach ($db['options'] as $k => $v)
{
    if (is_string($v))
    {
        if (strlen($v))
        {
            $file .= '"' . $k . '" => "' . $v . '",';
        }
    }
    else
    {
        $file .= '"' . $k . '" => ' . $v . ',';
    }
}

$file .= '
    )
);

?>';

?>
