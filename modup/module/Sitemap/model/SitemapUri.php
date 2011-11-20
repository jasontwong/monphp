<?php

class SitemapUri extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', NULL,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'sitemap_entry_id', 'integer', NULL,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'entry_id', 'integer'
        );
        $this->hasColumn(
            'rel_uri', 'string', 255,
            array(
                'notnull' => TRUE,
                'unique' => TRUE
            )
        );
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
        $this->index('sitemap_entry_id_index', array(
                'fields' => array('sitemap_entry_id')
            )
        );
        $this->index('entry_id_index', array(
                'fields' => array('entry_id')
            )
        );
        $this->index('rel_uri_index', array(
                'fields' => array('rel_uri')
            )
        );
        
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasOne(
            'SitemapEntry',
            array(
                'foreign' => 'id',
                'local' => 'sitemap_entry_id',
            )
        );
    }

    //}}}
}

?>
