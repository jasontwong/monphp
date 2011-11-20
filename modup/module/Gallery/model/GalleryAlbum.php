<?php

class GalleryAlbum extends Doctrine_Record
{
    // {{{ public function preUpdate($event)
    public function preUpdate($event)
    {
        $invoker = $event->getInvoker();
        $file = $invoker->cover_image;
        if (ake('type', $file))
        {
            $type = explode('/', $file['type']);
            if ($type[0] !== 'image')
            {
                unlink($file['tmp_name']);
                $invoker->cover_image = array();
            }
            else if (strpos($file['tmp_name'], '/tmp/') !== FALSE)
            {
                $invoker->cover_image = Gallery::store_album_cover_image($invoker->id, $file);
            }
        }
    }

    // }}}
    // {{{ public function postInsert($event)
    public function postInsert($event)
    {
        $invoker = $event->getInvoker();
        $file = $invoker->cover_image;
        if (ake('type', $file))
        {
            $type = explode('/', $file['type']);
            if ($type[0] !== 'image')
            {
                unlink($file['tmp_name']);
                $invoker->cover_image = array();
            }
            else if (strpos($file['tmp_name'], '/tmp/') !== FALSE)
            {
                $invoker->cover_image = Gallery::store_album_cover_image($invoker->id, $file);
            }
            if ($invoker->isModified())
            {
                $invoker->save();
            }
        }
    }

    // }}}
    // {{{ public function postDelete($event)
    public function postDelete($event)
    {
        $invoker = $event->getInvoker();
        unlink($invoker->cover_image['tmp_name']);
        rm_resource_dir(Gallery::get_album_dir($invoker->id));
    }

    // }}}
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
            'name', 'string', 100,
            array(
                'notblank' => TRUE
            )
        );
        $this->hasColumn(
            'slug', 'string', 100,
            array(
                'notblank' => TRUE,
                'unique' => TRUE
            )
        );
        $this->hasColumn(
            'cover_image', 'array'
        );
        $this->hasColumn(
            'description', 'string', 255
        );
        $this->hasColumn(
            'rank', 'integer', 4,
            array(
                'default' => 0
            )
        );
        $this->hasColumn(
            'status', 'integer', 1,
            array(
                'default' => Gallery::LISTED
            )
        );
        $this->hasColumn(
            'misc_data', 'array' // for customization
        );
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    //}}}
    //{{{ public function setUp()
    public function setUp()
    {
        $this->hasMany(
            'GalleryItem as Items',
            array(
                'local' => 'id',
                'foreign' => 'album_id'
            )
        );
    }

    //}}}
    //{{{ protected function validate()
    protected function validate()
    {
        $errors = $this->getErrorStack();
        if (strlen($this->name) < 1 || strlen($this->name) > 100)
        {
            $errors->add('validate', 'name must be 1-100 characters long');
        }
        if (strlen($this->slug) > 100)
        {
            $this->slug = substr($this->slug, 0, 100);
        }
        $gt = Doctrine_Query::create()
            ->from('GalleryAlbum')
            ->where('slug = ?', $this->slug)
            ->fetchArray();
        if (count($gt) > 0 && $gt[0]['id'] !== $this->id)
        {
            $errors->add('validate', 'slug is already in use');
        }
        unset($gt);

        if (strlen($this->description) > 255)
        {
            $errors->add('validate', 'description must be less than 255 characters long');
        }
        if (!is_numeric($this->rank))
        {
            $errors->add('validate', 'rank must be a number');
        }
        if (!is_numeric($this->status))
        {
            $errors->add('validate', 'status must be a number');
        }
        if (!is_array($this->misc_data))
        {
            $errors->add('validate', 'misc_data must be an array');
        }
        if (!is_array($this->cover_image))
        {
            $errors->add('validate', 'cover_image must be an array');
        }
    }

    //}}}
}
