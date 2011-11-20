<?php

class EcommerceOption extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE,
            )
        );
        $this->hasColumn('order_id', 'integer', 8);
        $this->hasColumn('product_id', 'integer', 8);
        $this->hasColumn(
            'name', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'data', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
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
        $this->hasOne(
            'EcommerceOrder as Order',
            array(
                'foreign' => 'id',
                'local' => 'order_id',
                'onDelete' => 'CASCADE',
                'onUpdate' => 'CASCADE',
            )
        );
        $this->hasOne(
            'EcommerceProduct as Product',
            array(
                'foreign' => 'id',
                'local' => 'product_id',
                'onDelete' => 'CASCADE',
                'onUpdate' => 'CASCADE',
            )
        );
    }

    //}}}
}

?>
