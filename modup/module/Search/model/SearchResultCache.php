<?php

class SearchResultCache extends Doctrine_Record
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
            'term', 'string', 40,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'module', 'string', 255,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'data', 'array'
        );
        $this->hasColumn(
            'date', 'integer', 8,
            array(
                'notnull' => TRUE,
                'default' => time()
            )
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        if (strlen($this->term) > 40)
        {
            $errors->add('validate', 'Search term is too long');
        }
        if (strlen($this->term) === 0)
        {
            $errors->add('validate', 'Search term is empty');
        }
        if (strlen($this->module) > 255)
        {
            $errors->add('validate', 'Module name is too long');
        }
        if (strlen($this->module) === 0)
        {
            $errors->add('validate', 'Module name is empty');
        }
        if (!is_array($this->data))
        {
            $errors->add('validate', 'Data is not in an array format');
        }
    }

    //}}}
}
