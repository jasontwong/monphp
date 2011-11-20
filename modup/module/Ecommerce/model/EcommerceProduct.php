<?php

class EcommerceProduct extends Doctrine_Record
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
        $this->hasColumn(
            'name', 'string', 100, 
            array(
                'type' => 'string', 
                'length' => '100',
            )
        );
        $this->hasColumn(
            'sku', 'string', 100, 
            array(
                'type' => 'string', 
                'length' => '100',
            )
        );
        $this->hasColumn(
            'price', 'decimal', 18,
            array(
                'default' => 0,
                'scale' => 2,
            )
        );
        $this->hasColumn(
            'quantity', 'integer', 4,
            array(
                'default' => 1,
            )
        );
        $this->hasColumn(
            'discount', 'decimal', 18,
            array(
                'default' => 0,
                'scale' => 2,
            )
        );
        $this->hasColumn(
            'tax', 'decimal', 18,
            array(
                'default' => 0,
                'scale' => 2,
            )
        );
        $this->hasColumn(
            'weight', 'decimal', 18,
            array(
                'default' => 0,
                'scale' => 2,
            )
        );
        $this->hasColumn(
            'shipping', 'decimal', 18,
            array(
                'default' => 0,
                'scale' => 2,
            )
        );
        $this->hasColumn(
            'total', 'decimal', 18,
            array(
                'default' => 0,
                'scale' => 2,
            )
        );
        $this->hasColumn('order_id', 'integer', 8);

        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasMany(
            'EcommerceOption as Options',
            array(
                'local' => 'id',
                'foreign' => 'product_id',
            )
        );
        $this->hasOne(
            'EcommerceOrder as Order',
            array(
                'local' => 'order_id',
                'foreign' => 'id',
                'onDelete' => 'CASCADE',
                'onUpdate' => 'CASCADE',
            )
        );
    }

    //}}}
}

?>
