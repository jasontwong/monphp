<?php

class TaxonomyScheme extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 4,
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE
            )
        );
        $this->hasColumn(
            'module', 'string', 100,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'mkey', 'integer', 8,
            array(
                'default' => NULL,
                'notnull' => FALSE 
            )
        );
        $this->hasColumn(
            'name', 'string', 50,
            array(
                'default' => 'default',
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'type', 'integer', 1,
            array(
                'default' => Taxonomy::TYPE_FREE,
                'notnull' => TRUE
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
            'TaxonomyTerm',
            array(
                'local' => 'id',
                'foreign' => 'scheme_id'
            )
        );
    }
    //}}}
}

?>
