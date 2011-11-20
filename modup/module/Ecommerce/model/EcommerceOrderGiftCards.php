<?php

class EcommerceOrderGiftCards extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE,
            )
        );
        $this->hasColumn('order_id', 'integer', 8, array(
                'primary' => true
            )
        );
        $this->hasColumn('gift_card_id', 'integer', 4, array(
                'primary' => true
            )
        );

        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
    }
}

?>
