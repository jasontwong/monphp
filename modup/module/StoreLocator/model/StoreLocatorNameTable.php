<?php

class StoreLocatorNameTable extends Doctrine_Table
{
    // {{{ public function construct()
    public function construct()
    {
        $this->addNamedQuery(
            'get.all', Doctrine_Query::create()
                ->from('StoreLocatorName n')
                ->leftJoin('n.Locations l')
        );
        $this->addNamedQuery(
            'get.all.by.country', Doctrine_Query::create()
                ->from('StoreLocatorName n')
                ->leftJoin('n.Locations l')
                ->orderBy('l.country ASC')
        );
    }

    // }}}
    // {{{ public function getNames()
    public function getNames()
    {
        $names = $this->findAll(Doctrine::HYDRATE_ARRAY);

        if (empty($names))
        {
            return $names;
        }

        foreach ($names as $name)
        {
            $list[] = $name['name'];
        }

        return $list;
    }

    // }}}
    // {{{ public function getOnlineStores()
    public function getOnlineStores()
    {
        $stores = $this->findAll(Doctrine::HYDRATE_ARRAY);

        foreach ($stores as $k => $store)
        {
            if ($store['online'])
            {
                $slugs[$k] = $store['slug'];
            }
            else
            {
                unset($stores[$k]);
            }
        }

        if (isset($slugs))
        {
            array_multisort($slugs, SORT_ASC, $stores);
        }

        return $stores;
    }

    // }}}
}

?>
