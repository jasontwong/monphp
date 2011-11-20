<?php

class SearchData extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE
            )
        );
        $this->hasColumn(
            'namespace', 'string', 100
        );
        $this->hasColumn(
            'identifier', 'string', 100,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'return_data', 'array'
        );
        $this->hasColumn(
            'search_data', 'clob', NULL,
            array(
                'notnull' => TRUE,
            )
        );

        $this->index(
            'namespace', 
            array(
                'fields' => array('namespace')
            )
        );
        $this->index(
            'identifier', 
            array(
                'fields' => array('identifier')
            )
        );
        $this->index(
            'namespace_identifier', 
            array(
                'fields' => array('namespace', 'identifier')
            )
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->actAs('Searchable', 
            array(
                'fields' => array('search_data'),
            )
        );
        
    }

    //}}}
}
