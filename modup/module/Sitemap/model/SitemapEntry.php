<?php

class SitemapEntry extends Doctrine_Record
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
            'parent_id', 'integer', NULL,
            array(
                'default' => 0
            )
        );
        $this->hasColumn(
            'assoc_id', 'integer'
        );
        $this->hasColumn(
            'assoc_name', 'string', NULL,
            array(
                'default' => 'custom'
            )
        );
        $this->hasColumn(
            'level', 'integer', NULL,
            array(
                'default' => 0
            )
        );
        $this->hasColumn(
            'slug', 'string', NULL,
            array(
                'default' => ''
            )
        );
        $this->hasColumn(
            'filter', 'array', NULL,
            array(
                'default' => array()
            )
        );
        $this->hasColumn(
            'build_field', 'array', NULL,
            array(
                'default' => array()
            )
        );
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasMany(
            'SitemapUri', 
            array(
                'cascade' => array('delete'),
                'foreign' => 'sitemap_entry_id',
                'local' => 'id',
                'onDelete' => 'CASCADE',
            )
        );
    }

    //}}}
}

?>
