<?php

class InventoryProductGroup extends Doctrine_Record
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
            'inventory_option_group_x', 'integer', 4,
            array(
                'notnull' => FALSE,
            )
        );
        $this->hasColumn(
            'inventory_option_group_y', 'integer', 4,
            array(
                'notnull' => FALSE,
            )
        );
        $this->hasColumn(
            'name', 'string', 200,
            array(
                'default' => '',
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
            'InventoryOptionGroup as InventoryOptionGroupX',
            array(
                'foreign' => 'id',
                'local' => 'inventory_option_group_x',
            )
        );
        $this->hasOne(
            'InventoryOptionGroup as InventoryOptionGroupY',
            array(
                'foreign' => 'id',
                'local' => 'inventory_option_group_y',
            )
        );
        $this->hasMany(
            'InventoryProduct',
            array(
                'cascade' => array('delete'),
                'foreign' => 'inventory_product_group_id',
                'local' => 'id',
                'onDelete' => 'CASCADE',
            )
        );
    }
    //}}}
}

?>
