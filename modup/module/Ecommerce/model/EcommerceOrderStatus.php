<?php

class EcommerceOrderStatus extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 2,
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
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'name', 'string', 100, 
            array(
                'type' => 'string', 
                'length' => '100',
                'notnull' => TRUE
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
                'foreign' => 'order_status_id',
                'local' => 'id',
            )
        );
    }

    //}}}
}

?>
