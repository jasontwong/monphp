<?php

if ($_REQUEST) {
    $output['items'] = StoreLocator::get_store_locations($_REQUEST['point'][0], $_REQUEST['point'][1], $_REQUEST['distance']);
    if (count($output['items']) > 0)
    {
        $output['success'] = 1;
    }
    else
    {
        $output['success'] = 0;
    }

    print json_encode($output);
}

?>
