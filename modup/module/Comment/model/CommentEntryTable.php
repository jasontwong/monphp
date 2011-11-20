<?php

class CommentEntryTable extends Doctrine_Table
{
    // {{{ public function construct()
    public function construct()
    {
        $this->addNamedQuery(
            'get.all', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->orderBy('e.create_date DESC')
        );
        $this->addNamedQuery(
            'get.id', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->where('e.id = ?')
                ->orderBy('e.create_date DESC')
        );
        $this->addNamedQuery(
            'get.id.and.status', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->addWhere('e.id = ?')
                ->addWhere('e.status = ?')
                ->orderBy('e.create_date DESC')
        );
        $this->addNamedQuery(
            'get.status', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->where('e.status = ?')
                ->orderBy('e.create_date DESC')
        );
        $this->addNamedQuery(
            'get.module_name', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->addWhere('e.module_name = ?')
                ->orderBy('e.create_date DESC')
        );
        $this->addNamedQuery(
            'get.module_name.and.status', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->addWhere('e.module_name = ?')
                ->addWhere('e.status = ?')
                ->orderBy('e.create_date DESC')
        );
        $this->addNamedQuery(
            'get.module_name.and.module_entry_id', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->addWhere('e.module_name = ?')
                ->addWhere('e.module_entry_id = ?')
                ->orderBy('e.create_date DESC')
        );
        $this->addNamedQuery(
            'get.module_name.and.module_entry_id.and.status', Doctrine_Query::create()
                ->from('CommentEntry e')
                ->addWhere('e.module_name = ?')
                ->addWhere('e.module_entry_id = ?')
                ->addWhere('e.status = ?')
                ->orderBy('e.create_date DESC')
        );
    }

    // }}}
    //{{{ public function findPaginated($approved, $count, $page, $doc_type, $module, $ids, $sort)
    public function findPaginated($approved = FALSE, $count = NULL, $page = 1, $doc_type = Doctrine::HYDRATE_RECORD, $module = NULL, $ids = NULL, $sort = NULL)
    {
    	$query = Doctrine_Query::create()
        	->from( 'CommentEntry' )
            ->addWhere( 'status = ?', $approved ? Comment::APPROVED : Comment::UNAPPROVED);

        if (!is_null($module))
        {
            $query->addWhere( 'module_name = ?', $module );
        }
        if (is_array($ids))
        {
            $query->whereIn( 'module_entry_id', $ids);
        }
        if (!is_null($sort))
        {
            switch ($sort)
            {
                case 'create_date':
        	        $query->orderby( 'create_date ASC' );
                break;
            }
        }
        else
        {
        	$query->orderby( 'create_date DESC' );
        }
		$pager = new Doctrine_Pager(
            $query,
    		$page, // Current page of request
    		$count // (Optional) Number of results per page. Default is 25
		);

        $comments['entries'] = $pager->execute();
        $comments['total_pages'] = $pager->getLastPage();
        $comments['current_page'] = $page;

        return $comments;
    }

    //}}}
    //{{{ public function findEntries($name, $id, $doc_type)
    public function findEntries($name = NULL, $id = NULL, $doc_type = Doctrine::HYDRATE_ARRAY)
    {
        if (Comment::is_approval_required())
        {
            if (is_null($name))
            {
                $comments = $this->find('get.status', array(Comment::APPROVED), $doc_type);
            }
            else if (is_null($id))
            {
                $comments = $this->find('get.module_name.and.status', array($name, Comment::APPROVED), $doc_type);
            }
            else
            {
                $comments = $this->find('get.module_name.and.module_entry_id.and.status', array($name, $id, Comment::APPROVED), $doc_type);
            }
        }
        else
        {
            if (is_null($name))
            {
                $comments = $this->find('get.all', array(), $doc_type);
            }
            else if (is_null($id))
            {
                $comments = $this->find('get.module_name', array($name), $doc_type);
            }
            else
            {
                $comments = $this->find('get.module_name.and.module_entry_id', array($name, $id), $doc_type);
            }
        }

        return $comments;
    }

    //}}}
}

?>
