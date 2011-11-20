<?php

class TaxonomyEntry extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE,
            )
        );
        $this->hasColumn(
            'term_id', 'integer', 4,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'entry_id', 'integer', 8,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE,
            )
        );
        $this->index(
            'term',
            array('fields' => array('term_id'))
        );
        $this->index(
            'entry',
            array('fields' => array('term_id', 'entry_id'))
        );
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
    }
    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasOne(
            'TaxonomyTerm',
            array(
                'local' => 'term_id',
                'foreign' => 'id'
            )
        );
    }
    //}}}
}

?>
