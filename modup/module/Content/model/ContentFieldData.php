<?php

class ContentFieldData extends Doctrine_Record
{
    //{{{ public function delete()
    /**
     * Looks up the field meta type to call the proper field_delete_ method
     */
    public function delete()
    {
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        $cftt = Doctrine::getTable('ContentFieldType');
        $field_meta = $cfmt->find($this->content_field_meta_id);
        if ($field_meta !== FALSE)
        {
            $field_type = $cftt->find($field_meta->content_field_type_id);
            if ($field_type !== FALSE)
            {
                Field::quick_act('delete', $field_type->type, $this->toArray());
            }
        }
        parent::delete();
    }

    //}}}
    //{{{ public function setTableDefinition()
    /**
     * akey: check ContentFieldMeta's multiple column to see if these
     * rows need to be returned as an array
     */
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'autoincrement' => TRUE,
                'notnull' => TRUE,
                'primary' => TRUE,
            )
        );
        $this->hasColumn(
            'content_entry_meta_id', 'integer', 4,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'content_field_meta_id', 'integer', 2,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'revision', 'integer', 2,
            array(
                'default' => 0,
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'cdata', 'clob', NULL,
            array(
                'default' => NULL,
                'notnull' => FALSE,
            )
        );
        $this->hasColumn(
            'bdata', 'blob', NULL,
            array(
                'default' => NULL,
                'notnull' => FALSE,
            )
        );
        $this->hasColumn(
            'akey', 'integer', 2,
            array(
                'default' => 0,
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'meta', 'array', NULL,
            array(
                'notnull' => TRUE,
            )
        );
        $this->index(
            'cdata',
            array(
                'fields' => array('cdata'),
                'type' => 'fulltext'
            )
        );
        $this->index(
            'entry_meta',
            array(
                'fields' => array('content_entry_meta_id')
            )
        );
        $this->index(
            'meta_revision',
            array(
                'fields' => array('content_entry_meta_id', 'revision')
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
            'ContentEntryMeta', 
            array(
                'foreign' => 'id',
                'local' => 'content_entry_meta_id',
            )
        );
        $this->hasOne(
            'ContentFieldMeta', 
            array(
                'foreign' => 'id',
                'local' => 'content_field_meta_id',
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        $field_meta = $cfmt->findOneById($this->content_field_meta_id);
        if (!$field_meta)
        {
            $errors->add('field meta', 'The field does not exist. Please specify an existing field.');
        }
    }

    //}}}
}

?>
