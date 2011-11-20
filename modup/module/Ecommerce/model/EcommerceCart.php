<?php

class EcommerceCart extends Doctrine_Record
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
            'identifier', 'string', 255, 
            array(
                'type' => 'string', 
                'length' => '255',
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'created_date', 'integer', 8,
            array(
                'default' => 0,
            )
        );
        $this->hasColumn('data', 'array');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
    }

    //}}}
    // {{{ public function preInsert($event)
    public function preInsert($event)
    {
        $this->created_date = time();
    }
    // }}}
}

?>
