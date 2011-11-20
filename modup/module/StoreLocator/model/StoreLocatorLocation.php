<?php

class StoreLocatorLocation extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 4,
            array(
                'primary' => TRUE,
                'notnull' => TRUE,
                'autoincrement' => TRUE
            )
        );
        $this->hasColumn(
            'store_id', 'integer', 4,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'address1', 'string', 70
        );
        $this->hasColumn(
            'address2', 'string', 70
        );
        $this->hasColumn(
            'city', 'string', 30
        );
        $this->hasColumn(
            'state', 'string', 30
        );
        $this->hasColumn(
            'country', 'string', 30
        );
        $this->hasColumn(
            'zip_code', 'string', 10
        );
        $this->hasColumn(
            'phone', 'string', 30
        );
        $this->hasColumn(
            'latitude', 'float'
        );
        $this->hasColumn(
            'longitude', 'float'
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasOne(
            'StoreLocatorName as Name',
            array(
                'local' => 'store_id',
                'foreign' => 'id'
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        if (strlen($this->address1) > 70)
        {
            $errors->add('validate', 'Address 1 is too long');
        }
        if (strlen($this->address1) == 0)
        {
            $errors->add('validate', 'Address 1 is empty');
        }
        if (strlen($this->address2) > 70)
        {
            $errors->add('validate', 'Address 2 is too long');
        }
        if (strlen($this->city) == 0)
        {
            $errors->add('validate', 'City is empty');
        }
        if (strlen($this->city) > 30)
        {
            $errors->add('validate', 'City is too long');
        }
        if (strlen($this->state) > 30)
        {
            $errors->add('validate', 'State is too long');
        }
        if (strlen($this->country) > 30)
        {
            $errors->add('validate', 'Country is too long');
        }
        if (strlen($this->zip_code) > 10)
        {
            $errors->add('validate', 'Zip Code is too long');
        }
        if (strlen($this->phone) > 30)
        {
            $errors->add('validate', 'Phone Number is too long');
        }
        if (empty($this->latitude) || empty($this->longitude))
        {
            $errors->add('validate', 'There was an address verification error');
        }
    }

    //}}}
}
