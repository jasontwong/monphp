<?php

class SearchTermTable extends Doctrine_Table
{
    // {{{ public function construct()
    public function construct()
    {
        $this->addNamedQuery(
            'get.term', Doctrine_Query::create()
                ->from('SearchTerm t')
                ->where('t.term = ?')
        );
    }

    // }}}
}

?>
