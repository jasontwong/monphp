<?php

class InventoryProduct extends Doctrine_Record
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
            'inventory_product_group_id', 'integer', 4,
            array(
                'default' => '',
            )
        );
        $this->hasColumn(
            'name', 'string', 200,
            array(
                'notnull' => TRUE,
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
            'InventoryProductGroup',
            array(
                'local' => 'inventory_product_group_id',
                'foreign' => 'id'
            )
        );
    }
    //}}}
}

?>
