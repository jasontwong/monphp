<?php

class GalleryItem extends Doctrine_Record
{
    // {{{ public function preUpdate($event)
    public function preUpdate($event)
    {
        $invoker = $event->getInvoker();
        if (strpos($invoker->file_tmp_name, '/tmp/') !== FALSE)
        {
            $data = Gallery::store_album_item($invoker->album_id, $invoker->toArray());
            $invoker->merge($data);
        }
        if ($invoker->isModified())
        {
            $invoker->save();
        }
    }

    // }}}
    // {{{ public function postInsert($event)
    public function postInsert($event)
    {
        $invoker = $event->getInvoker();
        if (strpos($invoker->file_tmp_name, '/tmp/') !== FALSE)
        {
            $data = Gallery::store_album_item($invoker->album_id, $invoker->toArray());
            $invoker->merge($data);
        }
        if ($invoker->isModified())
        {
            $invoker->save();
        }
    }

    // }}}
    // {{{ public function postDelete($event)
    public function postDelete($event)
    {
        $invoker = $event->getInvoker();
        unlink($invoker->file_tmp_name);
        switch($invoker->type)
        {
            case 'image':
                unlink($invoker->file_meta['large']['file_tmp_name']);
                unlink($invoker->file_meta['medium']['file_tmp_name']);
                unlink($invoker->file_meta['thumbnail']['file_tmp_name']);
            break;
        }
    }

    // }}}
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
            'album_id', 'integer', 4,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'title', 'string', 100,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'file_name', 'string', 150, // how they uploaded it
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'file_tmp_name', 'string', 150, // what's stored on the server
            array(
                'unique' => TRUE,
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'file_type', 'string', 50,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'file_size', 'integer', 6,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'file_meta', 'array'
        );
        $this->hasColumn(
            'type', 'string', 20,
            array(
                'notnull' => TRUE
            )
        );
        $this->hasColumn(
            'caption', 'string', 255
        );
        $this->hasColumn(
            'rank', 'integer', 4,
            array(
                'default' => 0
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
        $this->hasOne(
            'GalleryAlbum as Album',
            array(
                'local' => 'album_id',
                'foreign' => 'id'
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
    }

    //}}}
}
