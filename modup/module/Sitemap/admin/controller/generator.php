<?php

$cett = Doctrine::getTable('ContentEntryType');
$cemt = Doctrine::getTable('ContentEntryMeta');
$types = $cett->getTypes();
$options['type'] = array('' => 'None');
$options['entry']['None'] = $options['type'];
$mpost = array();
foreach ($types as $type)
{
    $options['type'][$type['id']] = $type['name'];
    try
    {
        $metas = $cemt->filterByType($type['name']);
        $mpost[$type['id']] = $metas['data'];
        foreach ($metas['data'] as $entry)
        {
            $options['entry'][$type['name']][$entry['id']] = $entry['title'];
        }
    }
    catch (Exception $e)
    {
    }
}

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $options['type']
                )
            )
        ),
        'name' => 'ctypes',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $options['entry']
                )
            )
        ),
        'name' => 'centries',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'custom',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'text' => 'Add to root'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
// {{{ submission
if (isset($_POST['do']))
{
    $sitemap = $layout->acts('post', $_POST['sitemap']);
    if (is_numeric($sitemap['ctypes']))
    {
        $entry = new SitemapEntry();
        $entry->assoc_id = $sitemap['ctypes'];
        $entry->assoc_name = 'ContentEntryType';
        $entry->build_field = array('slug');
        if ($entry->isValid())
        {
            $entry->save();
        }
        $entry->free();
    }
    elseif (is_numeric($sitemap['centries']))
    {
        $entry = new SitemapEntry();
        $entry->assoc_id = $sitemap['centries'];
        $entry->assoc_name = 'ContentEntryMeta';
        $entry->build_field = array('slug');
        if ($entry->isValid())
        {
            $entry->save();
        }
        $entry->free();
    }
    elseif (strlen($sitemap['custom']))
    {
        $entry = new SitemapEntry();
        $entry->assoc_id = 0000;
        $entry->slug = slugify($sitemap['custom']);
        if ($entry->isValid())
        {
            $entry->save();
        }
        $entry->free();
    }
}

// }}}
// {{{ form build
$form = new FormBuilderRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);

$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Entry Type'
                ),
                'description' => array(
                    'text' => 'All Entries of type'
                ),
                'fields' => $layout->get_layout('ctypes')
            ),
            array(
                'label' => array(
                    'text' => 'Entry Title'
                ),
                'description' => array(
                    'text' => 'Specific Entry Slug'
                ),
                'fields' => $layout->get_layout('centries')
            ),
            array(
                'label' => array(
                    'text' => 'Custom Slug'
                ),
                'fields' => $layout->get_layout('custom')
            )
        )
    ),
    'sitemap'
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

// }}}

$set = Doctrine::getTable('SitemapEntry');
$sentries = $set->findAll();
if ($sentries !== FALSE)
{
    $sentries = $sentries->toArray();
    foreach ($sentries as $sentry)
    {
        if ($sentry['level'] == 0)
        {
            if ($sentry['assoc_name'] === 'ContentEntryType')
            {
                if (!ake($sentry['assoc_id'], $mpost))
                {
                    continue;
                }
                foreach ($mpost[$sentry['assoc_id']] as $mdata)
                {
                    $uri = new SitemapUri();
                    $uri->entry_id = $mdata['id'];
                    $temp = $mdata;
                    foreach ($sentry['build_field'] as $key)
                    {
                        if (ake($key, $temp))
                        {
                            $temp = $temp[$key];
                        }
                        else
                        {
                            $temp = NULL;
                        }
                    }
                    $uri->rel_uri = '/'.slugify($temp);
                    $uri->sitemap_entry_id = $sentry['id'];
                    if ($uri->isValid())
                    {
                        $uri->save();
                    }
                    $uri->free();
                }
            }
            elseif ($sentry['assoc_name'] === 'ContentEntryMeta')
            {
                try
                {
                    $cem = $cemt->findCurrentEntry($sentry['assoc_id']);
                    $uri = new SitemapUri();
                    $uri->entry_id = $sentry['assoc_id'];
                    $temp = $cem;
                    foreach ($sentry['build_field'] as $key)
                    {
                        if (ake($key, $temp))
                        {
                            $temp = $temp[$key];
                        }
                        else
                        {
                            $temp = NULL;
                        }
                    }
                    $uri->rel_uri = '/'.slugify($temp);
                    $uri->sitemap_entry_id = $sentry['id'];
                    if ($uri->isValid())
                    {
                        $uri->save();
                    }
                    $uri->free();
                }
                catch (Exception $e)
                {
                }
            }
            else
            {
                if (strlen($sentry['slug']))
                {
                    $uri = new SitemapUri();
                    $uri->rel_uri = '/'.$sentry['slug'];
                    $uri->sitemap_entry_id = $sentry['id'];
                    if ($uri->isValid())
                    {
                        $uri->save();
                    }
                    $uri->free();
                }
            }
        }
    }
}

$sut = Doctrine::getTable('SitemapUri');
$site_uris = $sut->findAll();
if ($site_uris !== FALSE)
{
    $uris = $site_uris->toArray();
}
else
{
    $uris = array();
}

?>
