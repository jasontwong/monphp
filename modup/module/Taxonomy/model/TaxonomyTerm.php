<?php

class TaxonomyTerm extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE
            )
        );
        $this->hasColumn(
            'scheme_id', 'integer', 4,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'term', 'string', 200,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'slug', 'string', 200,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'parent_id', 'integer', 8,
            array(
                'default' => NULL,
                'notnull' => FALSE
            )
        );

        $this->index(
            'names',
            array(
                'fields' => array('term')
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
            'TaxonomyEntry',
            array(
                'local' => 'id',
                'foreign' => 'term_id'
            )
        );
        $this->hasOne(
            'TaxonomyScheme',
            array(
                'local' => 'scheme_id',
                'foreign' => 'id'
            )
        );
    }
    //}}}
}

?>
