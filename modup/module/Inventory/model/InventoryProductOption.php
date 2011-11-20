<?php

class InventoryProductOption extends Doctrine_Record
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
            'inventory_product_id', 'integer', 4
        );
        $this->hasColumn(
            'inventory_option_id', 'integer', 4
        );
        $this->hasColumn(
            'axis', 'string', 1,
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

        $this->index(
            'product_axis_weight',
            array(
                'fields' => array('inventory_product_id', 'axis', 'weight')
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
            'InventoryProduct',
            array(
                'local' => 'inventory_product_id',
                'foreign' => 'id'
            )
        );
        $this->hasOne(
            'InventoryOption',
            array(
                'local' => 'inventory_option_id',
                'foreign' => 'id'
            )
        );
    }
    //}}}
}

?>
