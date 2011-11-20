<?php

class ContentFieldMeta extends Doctrine_Record
{
    //{{{ public function save()
    public function save()
    {
        $this->default_data = (array)$this->default_data;
        $this->meta = (array)$this->meta;
        $this->required= (int)$this->required;
        parent::save();
    }

    //}}}
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 2,
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE,
            )
        );
        $this->hasColumn(
            'name', 'string', 50,
            array(
                'default' => 'data',
                'minlength' => 1,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'content_field_type_id', 'integer', 2,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'label', 'string', 200,
            array(
                'default' => '',
            )
        );
        $this->hasColumn(
            'required', 'integer', 1,
            array(
                'default' => 0,
            )
        );
        $this->hasColumn(
            'meta', 'array', 1000,
            array(
                'default' => ''
            )
        );
        $this->hasColumn(
            'default_data', 'array', 1000,
            array(
                'default' => ''
            )
        );
        $this->index(
            'field_type',
            array(
                'fields' => array('content_field_type_id')
            )
        );
        $this->index(
            'name',
            array(
                'fields' => array('name')
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
        $this->hasOne(
            'ContentFieldType', 
            array(
                'foreign' => 'id',
                'local' => 'content_field_type_id',
            )
        );
        $this->hasMany(
            'ContentFieldData',
            array(
                'cascade' => array('delete'),
                'foreign' => 'content_field_meta_id',
                'local' => 'id',
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        /*
        $errors = $this->getErrorStack();
        $names = Doctrine_Query::create()
            ->from('ContentFieldMeta')
            ->where('name = ?', $this->name)
            ->fetchArray();
        $labels = Doctrine_Query::create()
            ->from('ContentFieldMeta')
            ->where('label = ?', $this->label)
            ->fetchArray();
        if (is_array($names) && count($names))
        {
            if ($name['content_field_type_id'] === $this->content_field_type_id)
            {
                $errors->add('duplicate entry', 'Two items have the same name for the same field type');
            }
        }
        if (is_array($labels) && count($labels))
        {
            if ($name['content_field_type_id'] === $this->content_field_type_id)
            {
                $errors->add('duplicate entry', 'Two items have the same label for the same field type');
            }
        }
        */
    }

    //}}}
}

?>
