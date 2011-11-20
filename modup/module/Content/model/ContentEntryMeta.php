<?php

/**
 * This and ContentEntryTitle are separated because there are possible
 * revisions of the title column
 */
class ContentEntryMeta extends Doctrine_Record
{
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 4,
            array(
                'primary' => TRUE,
                'autoincrement' => TRUE,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'content_entry_type_id', 'integer', 1,
            array(
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'created', 'integer', 8,
            array(
                'default' => 0,
            )
        );
        $this->hasColumn(
            'revision', 'integer', 2, 
            array(
                'default' => 0,
            )
        );
        $this->hasColumn(
            'revisions', 'integer', 2, 
            array(
                'default' => 0,
            )
        );
        $this->hasColumn(
            'weight', 'integer', 4, 
            array(
                'default' => 0,
            )
        );
        $this->hasColumn(
            'status', 'string', 50,
            array(
                'default' => '',
            )
        );
        $this->hasColumn(
            'flags', 'array', NULL,
            array(
                'default' => '',
            )
        );
        $this->hasColumn(
            'date_control', 'string', 50
        );
        $this->hasColumn(
            'start_date', 'integer', 8,
            array(
                'default' => 0,
            )
        );
        $this->hasColumn(
            'end_date', 'integer', 8
        );
        $this->index(
            'entry_type', 
            array(
                'fields' => array('content_entry_type_id')
            )
        );
        $this->index(
            'revisions', 
            array(
                'fields' => array('revisions')
            )
        );
        $this->index(
            'type_revision', 
            array(
                'fields' => array('content_entry_type_id', 'revision')
            )
        );
        $this->index(
            'created', 
            array(
                'fields' => array('created')
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
        $this->addListener(new TimestampListener);
        $this->addListener(new RevisionListener);
        $this->hasOne(
            'ContentEntryType',
            array(
                'foreign' => 'id',
                'local' => 'content_entry_type_id',
            )
        );
        $this->hasMany(
            'ContentEntryTitle', 
            array(
                'cascade' => array('delete'),
                'foreign' => 'content_entry_meta_id',
                'local' => 'id',
                'onDelete' => 'CASCADE',
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        $cett = Doctrine::getTable('ContentEntryType');
        $entry_type = $cett->findOneById($this->content_entry_type_id);
        if (!$entry_type)
        {
            $errors->add('entry type', 'The entry type does not exist. Please specify an existing entry type.');
        }
    }

    //}}}
}

?>
