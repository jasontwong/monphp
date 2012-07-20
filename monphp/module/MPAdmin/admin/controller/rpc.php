<?php 

/**
 * Calls the RPC hook method for the module
 * Each additional URI_PART after URI_PART_2 (module name, required for this
 * RPC controller) will be thrown in as a parameter with the final parameter
 * being array_merge($_POST, $_GET)
 */
$module = URI_PART_2;
$caller = array('MPModule', 'h');
$params = array('mpadmin_rpc', $module, URI_PART_3, array_merge($_POST, $_GET));
$result = call_user_func_array($caller, $params);
echo ake($module, $result) 
    ? $result[$module] 
    : $result;
