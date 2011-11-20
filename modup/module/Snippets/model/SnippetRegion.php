<?php

class SnippetRegion extends Doctrine_Record
{
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
                'notnull' => TRUE,
                'unique' => TRUE
            )
        );
        $this->hasColumn(
            'description', 'string', null,
            array(
                'default' => '',
                'notnull' => TRUE,
            )
        );
        $this->hasColumn(
            'title', 'string', 200,
            array(
                'default' => ''
            )
        );
        $this->hasColumn(
            'content', 'clob', null,
            array(
                'default' => ''
            )
        );
        $this->hasColumn(
            'attachment', 'clob', null,
            array(
                'default' => ''
            )
        );
        $this->hasColumn(
            'active', 'integer', 1,
            array(
                'default' => 1,
                'notnull' => TRUE
            )
        );
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
        $this->option('type', 'MyISAM');
    }
    //}}}
}

?>
