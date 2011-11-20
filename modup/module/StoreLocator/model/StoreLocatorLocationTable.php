<?php

class StoreLocatorLocationTable extends Doctrine_Table
{
    // {{{ public function construct()
    public function construct()
    {
        $this->addNamedQuery(
            'get.all', Doctrine_Query::create()
                ->from('StoreLocatorLocation l')
                ->leftJoin('l.Name n')
        );
        $this->addNamedQuery(
            'get.all.by.country.state.city', Doctrine_Query::create()
                ->from('StoreLocatorLocation l')
                ->leftJoin('l.Name n')
                ->orderBy('l.country ASC, l.state ASC, l.city ASC, n.name ASC')
        );
        $this->addNamedQuery(
            'get.id', Doctrine_Query::create()
                ->from('StoreLocatorLocation l')
                ->leftJoin('l.Name n')
                ->where('l.id = ?')
        );
    }

    // }}}
    // {{{ public function findClosestStores($lat, $lng, $distance, $doc_type)
    public function findClosestStores($lat, $lng, $distance)
    {
        $stores = $this->find('get.all', array(), Doctrine::HYDRATE_ARRAY);

        foreach ($stores as $k => &$store)
        {
            // $unit = 6371; // kilometers
            $unit = 3959; // miles
            $dist[$k] = $unit * acos(
                cos( deg2rad($store['latitude']) ) *
                cos( deg2rad($lat) ) *
                cos( deg2rad($lng) - deg2rad($store['longitude']) ) +
                sin( deg2rad($store['latitude']) ) *
                sin( deg2rad($lat) )
            );

            if ($dist[$k] > $distance)
            {
                unset($stores[$k]);
                unset($dist[$k]);
            }
        }

        if (is_array($stores) && count($stores) > 0)
            array_multisort($dist, $stores);

        return $stores;
    }

    // }}}
}

?>
