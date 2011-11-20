<?php

class ContentEntryTitle extends Doctrine_Record
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
            'modified', 'integer', 8,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'content_entry_meta_id', 'integer', 4,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'title', 'string', 200,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'slug', 'string', 200,
            array(
                'default' => '',
                'minlength' => 1,
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'revision', 'integer', 2, 
            array(
                'notnull' => TRUE,
                'default' => 0
            )
        );
        $this->index(
            'slug', 
            array(
                'fields' => array('slug')
            )
        );
        $this->index(
            'entry_meta', 
            array(
                'fields' => array('content_entry_meta_id')
            )
        );
        $this->index(
            'entry_meta_revision', 
            array(
                'fields' => array('content_entry_meta_id', 'revision')
            )
        );
        $this->index(
            'revision', 
            array(
                'fields' => array('revision')
            )
        );
        $this->index(
            'modified_title', 
            array(
                'fields' => array('modified', 'title')
            )
        );
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
        $this->setListener(new TimestampListener);
    }

    //}}}
    //{{{ public function setup()
    public function setup()
    {
        $this->hasOne(
            'ContentEntryMeta', 
            array(
                'local' => 'content_entry_meta_id',
                'foreign' => 'id',
            )
        );
    }
    
    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        $cemt = Doctrine::getTable('ContentEntryMeta');
        $entry_meta = $cemt->findOneById($this->content_entry_meta_id);
        $slug_char = Data::query('Content', 'autoslug');
        $slug_char = is_string($slug_char) && strlen($slug_char) ? $slug_char : '-';
        if (!$entry_meta)
        {
            $errors->add('validate', 'The entry does not exist. Please specify an existing entry for the title.');
        }
        if (strlen($this->title) === 0)
        {
            $errors->add('validate', 'The title is empty. Please provide a title with at least one character.');
        }
        if (strlen($this->slug) === 0)
        {
            $errors->add('validate', 'The slug is empty. Please provide a slug with at least one character.');
        }
        elseif (!is_slug($this->slug, $slug_char))
        {
            $errors->add('validate', 'The slug is invalid. Please use only letters, numbers, and ('.$slug_char.').');
        }
    }

    //}}}
}

?>
