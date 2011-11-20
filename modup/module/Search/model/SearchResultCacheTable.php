<?php

class SearchResultCacheTable extends Doctrine_Table
{
    // {{{ public function construct()
    public function construct()
    {
        $this->addNamedQuery(
            'get.term.and.module', Doctrine_Query::create()
                ->from('SearchResultCache c')
                ->addWhere('c.term = ?')
                ->addWhere('c.module = ?')
        );
    }

    // }}}
}

?>
