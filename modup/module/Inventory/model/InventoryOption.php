<?php

class InventoryOption extends Doctrine_Record
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
            'inventory_option_group_id', 'integer', 4,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'weight', 'integer', 2,
            array(
                'default' => 0,
                'notnull' => TRUE,
                'range' => array(-10000, 10000)
            )
        );
        $this->hasColumn(
            'name', 'string', 200,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'display_name', 'string', 200,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'image', 'string', 200,
            array(
                'default' => '',
            )
        );
        $this->index(
            'group_weight',
            array(
                'fields' => array('inventory_option_group_id', 'weight')
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
        $this->hasOne(
            'InventoryOptionGroup',
            array(
                'foreign' => 'id',
                'local' => 'inventory_option_group_id',
            )
        );
    }
    //}}}
}

?>
