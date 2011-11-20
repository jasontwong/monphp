<?php

class ContentFieldType extends Doctrine_Record
{
    //{{{ public function save()
    public function save()
    {
        $this->multiple = (int)$this->multiple;
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
            'name', 'string', 200,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'type', 'string', 50,
            array(
                'default' => 'text',
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'weight', 'integer', 2,
            array(
                'default' => 0,
                'notnull' => TRUE,
                'range' => array(-10000, 10000)
            )
        );
        $this->hasColumn(
            'content_field_group_id', 'integer', 2,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'multiple', 'integer', 1,
            array(
                'default' => 0,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'description', 'string', NULL,
            array(
                'default' => '',
                'notnull' => TRUE
            )
        );
        $this->index(
            'group_weight_name',
            array(
                'fields' => array('content_field_group_id', 'weight', 'name')
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
            'ContentFieldGroup', 
            array(
                'foreign' => 'id',
                'local' => 'content_field_group_id',
            )
        );
        $this->hasMany(
            'ContentFieldMeta',
            array(
                'cascade' => array('delete'),
                'foreign' => 'content_field_type_id',
                'local' => 'id',
                'onDelete' => 'CASCADE'
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        $cfgt = Doctrine::getTable('ContentFieldGroup');
        $field_group = $cfgt->findOneById($this->content_field_group_id);
        if (!$field_group)
        {
            $errors->add('field group', 'The field group does not exist. Please specify an existing field group.');
        }
        else
        {
            $names = Doctrine_Query::create()
                ->from('ContentFieldType')
                ->where('name = ?', $this->name)
                ->fetchArray();
            if (is_array($names) && count($names))
            {
                foreach ($names as $name)
                {
                    if ($name['id'] !== $this->id)
                    {
                        $fg = $cfgt->find($name['content_field_group_id']);
                        if ($fg && $fg->content_entry_type_id === $field_group->content_entry_type_id)
                        {
                            $errors->add('duplicate entry', 'Two items have the same name for the same content type');
                        }
                    }
                }
            }
        }

        $types = array_keys(Field::types());
        if (!in_array($this->type, $types))
        {
            $errors->add('type', 'The field type does not exist. Please specify an existing field type.');
        }
    }

    //}}}
}

?>
