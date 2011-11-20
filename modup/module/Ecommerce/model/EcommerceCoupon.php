<?php

class EcommerceCoupon extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 4,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE,
            )
        );
        $this->hasColumn(
            'type', 'string', 100, 
            array(
                'type' => 'string', 
                'length' => '100',
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'code', 'string', 100, 
            array(
                'type' => 'string', 
                'length' => '100',
                'notnull' => TRUE,
                'unique' => TRUE,
            )
        );
        $this->hasColumn(
            'amount', 'decimal', 18,
            array(
                'notnull' => TRUE,
                'scale' => 2,
            )
        );
        $this->hasColumn(
            'qualifier', 'decimal', 18,
            array(
                'notnull' => TRUE,
                'default' => '0.00',
                'scale' => 2,
            )
        );
        $this->hasColumn(
            'free_shipping', 'boolean'
        );
        $this->hasColumn(
            'uses', 'integer', 8,
            array(
                'unsigned' => FALSE,
                'default' => -1,
            )
        );
        $this->hasColumn(
            'end_date', 'integer', 8,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'start_date', 'integer', 8,
            array(
                'notnull' => TRUE,
            )
        );

        $this->index(
            'coupon_code', 
            array(
                'fields' => array('code')
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
        $this->hasMany(
            'EcommerceOrder as Orders',
            array(
                'foreign' => 'order_id',
                'local' => 'coupon_id',
                'refClass' => 'EcommerceOrderCoupons',
            )
        );
    }

    //}}}
}

?>
