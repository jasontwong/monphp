<?php

if (URI_PARTS < 7)
{
    header('Location: /admin/');
    exit;
}

$tax['module'] = URI_PART_4;
$tax['mkey'] = URI_PART_5;
$tax['name'] = URI_PART_6;
$tax['type'] = Taxonomy::TYPE_NONE;

try
{
    $taxm = new TaxonomyScheme;
    $taxm->merge($tax);
    $taxm->save();
}
catch (Exception $e)
{
}

header('Location: '.($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '/admin/'));

?>
