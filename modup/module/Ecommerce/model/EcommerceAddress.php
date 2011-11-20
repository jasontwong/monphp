<?php

class EcommerceAddress extends Doctrine_Record
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
            'name', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'company', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'address1', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'address2', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'city', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'state', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'country', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255'
            )
        );
        $this->hasColumn(
            'phone', 'string', 20, 
            array(
                'type' => 'string', 
                'length' => '20'
            )
        );
        $this->hasColumn(
            'zipcode', 'string', 20, 
            array(
                'type' => 'string', 
                'length' => '20'
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
