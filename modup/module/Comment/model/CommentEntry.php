<?php

class CommentEntry extends Doctrine_Record
{
    //{{{ public function save()
    public function save()
    {
        $user_data = $this->user_data;
        $name = !is_null(Data::query('Comment', 'name')) 
            ? Data::query('Comment', 'name') 
            : Comment::$default_user);
        if (is_array($user_data))
        {
            if (!ake('name', $user_data) || !strlen($user_data['name']))
            {
                $user_data['name'] = $name;
            }
            $this->user_data = $user_data;
        }
        else
        {
            $this->user_data = array('name' => $name);
        }
        parent::save();
    }

    //}}}
    //{{{ public function setTableDefinition()
    public function setTableDefinition()
    {
        $this->hasColumn(
            'id', 'integer', 8,
            array(
                'primary' => TRUE,
                'notnull' => TRUE,
                'autoincrement' => TRUE
            )
        );
        $this->hasColumn(
            'module_name', 'string', 150,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'module_entry_id', 'integer', 8,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'entry', 'string', 1000,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'user_data', 'array', NULL,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'create_date', 'integer', 8,
            array(
                'default' => time()
            )
        );
        $this->hasColumn(
            'approved_by', 'string', 150,
            array(
                'default' => ''
            )
        );
        $this->hasColumn(
            'status', 'integer', 1,
            array(
                'default' => Comment::UNAPPROVED
            )
        );
        $this->hasColumn(
            'spam', 'integer', 1,
            array(
                'default' => Comment::NOT_SPAM
            )
        );
        $this->hasColumn(
            'permalink', 'string', 150
        );
        $this->index('name_id_status_spam_index', array(
            'fields' => array(
                'module_name', 'module_entry_id', 'status', 'spam'
            )
        ));
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
        $user_data = $this->user_data;
        if ($this->module_name === '')
        {
            $errors->add('validate', 'Module name is empty');
        }
        if (strlen($this->module_name) > 150)
        {
            $errors->add('validate', 'Module name is too long');
        }
        if (!is_numeric($this->module_entry_id))
        {
            $errors->add('validate', 'Module entry id is not a number');
        }
        if ($this->entry === '')
        {
            $errors->add('validate', 'Entry is empty');
        }
        if (strlen($this->entry) > 1000)
        {
            $errors->add('validate', 'Entry is too long');
        }
        if (!is_array($user_data))
        {
            $errors->add('validate', 'User data is not an array');
        }
    }

    //}}}
}
