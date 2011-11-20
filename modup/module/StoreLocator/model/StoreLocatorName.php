<?php

class StoreLocatorName extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 2,
            array(
                'primary' => TRUE,
                'notnull' => TRUE,
                'autoincrement' => TRUE
            )
        );
        $this->hasColumn(
            'name', 'string', 100, 
            array(
                'unique' => TRUE, 
            )
        );
        $this->hasColumn(
            'slug', 'string', 100, 
            array(
                'unique' => TRUE, 
            )
        );
        $this->hasColumn(
            'website', 'string', 100
        );
        $this->hasColumn(
            'online', 'integer', 1
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasMany(
            'StoreLocatorLocation as Locations',
            array(
                'local' => 'id',
                'foreign' => 'store_id'
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        $slnt = Doctrine::getTable('StoreLocatorName');
        // $sln = $slnt->findOneByName($this->name)->toArray();
        $sln = Doctrine_Query::create()
            ->from('StoreLocatorName n')
            ->where('n.name = ?', array($this->name))
            ->fetchArray();
        if (is_array($sln))
        {
            if ($sln[0]['id'] !== $this->id)
            {
                $errors->add('validate', 'Store Name already exists');
            }
        }
        if (strlen($this->name) > 100 || strlen($this->slug) > 100)
        {
            $errors->add('validate', 'Store Name is too long');
        }
        if (strlen($this->name) == 0 || strlen($this->slug) == 0)
        {
            $errors->add('validate', 'Store Name is empty');
        }
        if (strlen($this->website) > 100)
        {
            $errors->add('validate', 'The website name is too long');
        }
    }

    //}}}
}
