<?php

class SearchTerm extends Doctrine_Record
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
            'term', 'string', NULL,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'count', 'integer', NULL,
            array(
                'notnull' => TRUE
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
        if (strlen($this->term) == 0)
        {
            $errors->add('validate', 'Search term is empty');
        }
        if (!is_numeric($this->count))
        {
            $errors->add('validate', 'Count is not a number');
        }
    }

    //}}}
}
