<?php

class StoreLocator
{
    //{{{ properties
    protected static $radii = array();
    protected static $google_api_key = NULL;
    //}}}
    //{{{ constants
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Store Locator (Google maps based)';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = '';

    //}}}
    //{{{ constructor
    /**
     * @param int $state current state of module manager
     */
    public function __construct()
    {
    }

    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        self::$radii = !is_null(Data::query('StoreLocator', 'radii')) 
            ? Data::query('StoreLocator', 'radii') 
            : array('25' => '25');
        self::$google_api_key = Data::query('StoreLocator', 'google_api_key');
    }

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'add store' => 'Add New Store',
            'edit store' => 'Edit Store'
        );
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        if (URI_PARTS > 3)
        {
            if (URI_PART_2 === 'StoreLocator')
            {
                switch (URI_PART_3)
                {
                    case 'add_store':
                    case 'edit_store':
                        $js[] = '/admin/static/StoreLocator/geocoding.js';
                        $js[] = 'http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key='.self::$google_api_key;
                    break;
                    case 'map_test':
                        $js = self::get_google_map_js();
                    break;
                }
            }
        }

        return $js;
    }

    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links = array();
        if (User::perm('add store'))
        {
            $links['Add'] = array(
                '<a href="/admin/module/StoreLocator/add_store/">Store</a>',
            );
        }
        if (User::perm('edit store'))
        {
            $links['Edit'] = array(
                '<a href="/admin/module/StoreLocator/stores/">Stores</a>',
            );
        }
        if (CMS_DEVELOPER)
        {
            $links['Tools'] = array(
                '<a href="/admin/module/StoreLocator/map_test/">Map Test</a>',
            );
        }
        return $links;
    }

    //}}}
    //{{{ public function hook_routes()
    public function hook_routes()
    {
        $ctrl = dirname(__FILE__).'/controller';
        $routes = array(
            array('#^/store_locator/(results)/$#', $ctrl.'/${1}.php', Router::ROUTE_PCRE),
        );
        return $routes;
    }

    //}}}
    //{{{ public function hook_data_info()
    public function hook_data_info()
    {
        $items = array();
        $items[] = array(
            'field' => Field::layout(
                'textarea_array',
                array(
                    'data' => array(
                        'label' => 'Radii'
                    )
                )
            ),
            'name' => 'radii',
            'type' => 'textarea_array',
            'value' => array(
                'data' => array(25, 50, 100)
            )
        );
        $items[] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Google API Key'
                    )
                )
            ),
            'name' => 'google_api_key',
            'type' => 'text',
            'value' => array(
                'data' => self::$google_api_key
            )
        );
        return $items;
    }

    //}}}
    //{{{ public function hook_search_bad_terms()
    public function hook_search_bad_terms()
    {
        return array(
            'street',
            'city',
            'state'
        );
    }

    //}}}
    //{{{ public function hook_search_results($terms)
    /*
    public function hook_search_results($terms)
    {
        foreach ($terms as $term)
        {
            $results[] = array(
                array(
                    'title' => "TITLE",
                    'snippet' => "Here is the snippet for $term",
                    'link' => "/admin/store_locator/$term"
                ),
                array(
                    'title' => "TITLE",
                    'snippet' => "Here is another snippet for $term",
                    'link' => "/admin/store_locator/another/$term"
                ),
            );
        }

        return $results;
    }
    */

    //}}}
    //{{{ public static function get_radii()
    public static function get_radii()
    {
        return self::$radii;
    }

    //}}}
    // {{{ public static function get_store_locations($lat, $lng, $distance)
    public static function get_store_locations($lat, $lng, $distance)
    {
        $sllt = Doctrine::getTable('StoreLocatorLocation');
        $sll = $sllt->findClosestStores($lat, $lng, $distance);
        
        $stores = array();
        foreach ($sll as $sl)
        {
            $address = $sl['address1'];
            if (!empty($sl['address2']))
            {
                $address .= ' '.$sl['address2'];
            }
            $address .= '<br />'.$sl['city'];
            if (!empty($sl['state']))
            {
                $address .= ', '.$sl['state'];
            }
            if (!empty($sl['country']) && $sl['country'] !== 'United States')
            {
                $address .= ', '.$sl['country'];
            }
            if (!empty($sl['zip_code']))
            {
                $address .= ' '.$sl['zip_code'];
            }
            $sl['address'] = $address;
            $stores[] = $sl;
        }

        return $stores;
    }

    //}}}
    //{{{ public static function get_google_map_js()
    public static function get_google_map_js()
    {
        $js[] = '/file/module/StoreLocator/geocoding.js';
        $js[] = 'http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key='.self::$google_api_key;
        return $js;
    }

    //}}}
    //{{{ public static function get_country_list()
    public static function get_country_list()
    {
        return array(
    		"Afghanistan",
    		"Albania",
    		"Algeria",
    		"Andorra",
    		"Angola",
    		"Antigua and Barbuda",
    		"Argentina",
    		"Armenia",
    		"Australia",
    		"Austria",
    		"Azerbaijan",
    		"Bahamas",
    		"Bahrain",
    		"Bangladesh",
    		"Barbados",
    		"Belarus",
    		"Belgium",
    		"Belize",
    		"Benin",
    		"Bhutan",
    		"Bolivia",
    		"Bosnia and Herzegovina",
    		"Botswana",
    		"Brazil",
    		"Brunei",
    		"Bulgaria",
    		"Burkina Faso",
    		"Burundi",
    		"Cambodia",
    		"Cameroon",
    		"Canada",
    		"Cape Verde",
    		"Central African Republic",
    		"Chad",
    		"Chile",
    		"China",
    		"Colombi",
    		"Comoros",
    		"Congo (Brazzaville)",
    		"Congo",
    		"Costa Rica",
    		"Cote d'Ivoire",
    		"Croatia",
    		"Cuba",
    		"Cyprus",
    		"Czech Republic",
    		"Denmark",
    		"Djibouti",
    		"Dominica",
    		"Dominican Republic",
    		"East Timor (Timor Timur)",
    		"Ecuador",
    		"Egypt",
    		"El Salvador",
    		"Equatorial Guinea",
    		"Eritrea",
    		"Estonia",
    		"Ethiopia",
    		"Fiji",
    		"Finland",
    		"France",
    		"Gabon",
    		"Gambia, The",
    		"Georgia",
    		"Germany",
    		"Ghana",
    		"Greece",
    		"Grenada",
    		"Guatemala",
    		"Guinea",
    		"Guinea-Bissau",
    		"Guyana",
    		"Haiti",
    		"Honduras",
    		"Hungary",
    		"Iceland",
    		"India",
    		"Indonesia",
    		"Iran",
    		"Iraq",
    		"Ireland",
    		"Israel",
    		"Italy",
    		"Jamaica",
    		"Japan",
    		"Jordan",
    		"Kazakhstan",
    		"Kenya",
    		"Kiribati",
    		"Korea, North",
    		"Korea, South",
    		"Kuwait",
    		"Kyrgyzstan",
    		"Laos",
    		"Latvia",
    		"Lebanon",
    		"Lesotho",
    		"Liberia",
    		"Libya",
    		"Liechtenstein",
    		"Lithuania",
    		"Luxembourg",
    		"Macedonia",
    		"Madagascar",
    		"Malawi",
    		"Malaysia",
    		"Maldives",
    		"Mali",
    		"Malta",
    		"Marshall Islands",
    		"Mauritania",
    		"Mauritius",
    		"Mexico",
    		"Micronesia",
    		"Moldova",
    		"Monaco",
    		"Mongolia",
    		"Morocco",
    		"Mozambique",
    		"Myanmar",
    		"Namibia",
    		"Nauru",
    		"Nepa",
    		"Netherlands",
    		"New Zealand",
    		"Nicaragua",
    		"Niger",
    		"Nigeria",
    		"Norway",
    		"Oman",
    		"Pakistan",
    		"Palau",
    		"Panama",
    		"Papua New Guinea",
    		"Paraguay",
    		"Peru",
    		"Philippines",
    		"Poland",
    		"Portugal",
    		"Qatar",
    		"Romania",
    		"Russia",
    		"Rwanda",
    		"Saint Kitts and Nevis",
    		"Saint Lucia",
    		"Saint Vincent",
    		"Samoa",
    		"San Marino",
    		"Sao Tome and Principe",
    		"Saudi Arabia",
    		"Senegal",
    		"Serbia and Montenegro",
    		"Seychelles",
    		"Sierra Leone",
    		"Singapore",
    		"Slovakia",
    		"Slovenia",
    		"Solomon Islands",
    		"Somalia",
    		"South Africa",
    		"Spain",
    		"Sri Lanka",
    		"Sudan",
    		"Suriname",
    		"Swaziland",
    		"Sweden",
    		"Switzerland",
    		"Syria",
    		"Taiwan",
    		"Tajikistan",
    		"Tanzania",
    		"Thailand",
    		"Togo",
    		"Tonga",
    		"Trinidad and Tobago",
    		"Tunisia",
    		"Turkey",
    		"Turkmenistan",
    		"Tuvalu",
    		"Uganda",
    		"Ukraine",
    		"United Arab Emirates",
    		"United Kingdom",
    		"United States",
    		"Uruguay",
    		"Uzbekistan",
    		"Vanuatu",
    		"Vatican City",
    		"Venezuela",
    		"Vietnam",
    		"Yemen",
    		"Zambia",
    		"Zimbabwe"
    	);
    }

    //}}}
    //{{{ public function get_names()
    public function get_names()
    {
        $dql = Doctrine_Query::create()
               ->select('name')
               ->from('StoreLocatorName')
               ->orderBy('name ASC');
        $names = $dql->execute(array(), Doctrine::HYDRATE_ARRAY);
        $result = array();
        foreach ($names as $name)
        {
            $result[] = $name['name'];
        }
        return $result;
    }
    //}}}
}

?>
