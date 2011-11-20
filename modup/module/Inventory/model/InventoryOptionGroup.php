<?php

class InventoryOptionGroup extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 4,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'name', 'string', 200,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'description', 'clob', NULL,
            array(
                'notnull' => TRUE,
            )
        );
        $this->index(
            'name',
            array(
                'fields' => array('name')
            )
        );
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'InnoDB');
    }
    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasMany(
            'InventoryOption',
            array(
                'foreign' => 'inventory_option_group_id',
                'local' => 'id',
            )
        );
    }
    //}}}
}

?>
