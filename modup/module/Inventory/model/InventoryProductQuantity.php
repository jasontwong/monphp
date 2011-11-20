<?php

class InventoryProductQuantity extends Doctrine_Record
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
            'weight_x', 'integer', 2
        );
        $this->hasColumn(
            'weight_y', 'integer', 2
        );
        $this->hasColumn(
            'quantity', 'integer', 4
        );

        $this->index(
            'product_xy',
            array(
                'fields' => array('inventory_product_id', 'weight_x', 'weight_y')
            )
        );
        $this->index(
            'product_x',
            array(
                'fields' => array('inventory_product_id', 'weight_x')
            )
        );
        $this->index(
            'product_y',
            array(
                'fields' => array('inventory_product_id', 'weight_y')
            )
        );
        $this->index(
            'product_quantity',
            array(
                'fields' => array('inventory_product_id', 'quantity')
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
    }
    //}}}
}

?>
