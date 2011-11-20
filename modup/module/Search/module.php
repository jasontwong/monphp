<?php

class Search
{
    //{{{ properties
    private static $cache_time = NULL;

    protected static $bad_terms = array();
    protected static $hashed_terms = array();
    protected static $cached_results = array();
    protected static $search_modules = array();

    //}}}
    //{{{ constants
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Find content based on term searches';
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
    // {{{ public function hook_active()
    public function hook_active()
    {
        Module::h('search_bad_terms');
        self::$search_modules = !is_null(Data::query('Search','modules')) 
            ? Data::query('Search','modules') 
            : array();
        self::$cache_time = !is_null(Data::query('Search','time')) 
            ? Data::query('Search','time') 
            : 360;
    }

    // }}}
    // {{{ public function hook_admin_module_page()
    public function hook_admin_module_page()
    {
    }

    // }}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links = array();
        if (CMS_DEVELOPER && User::has_perm('admin'))
        {
            $links['Tools'] = array(
                '<a href="/admin/module/Search/test_search/">Test Search</a>'
            );
        }
        return $links;
    }

    //}}}
    // {{{ public function hook_data_info()
    public function hook_data_info()
    {
        $modules = Module::active_names();
        $options = array_combine($modules, $modules);
        $selected = empty(self::$search_modules)
            ? $modules
            : self::$search_modules;
        $data = array(
            array(
                'field' => Field::layout(
                    'checkbox',
                    array(
                        'data' => array(
                            'label' => 'Modules to search',
                            'options' => $options
                        )
                    )
                ),
                'name' => 'modules',
                'type' => 'checkbox',
                'value' => array(
                    'data' => $selected
                )
            ),
            array(
                'field' => Field::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Amount of time to hold cache (in minutes)',
                        )
                    )
                ),
                'name' => 'time',
                'type' => 'text',
                'value' => array(
                    'data' => self::$cache_time
                )
            ),
            array(
                'field' => Field::layout(
                    'textarea_array',
                    array(
                        'data' => array(
                            'label' => 'Bad search terms',
                            'description' => 'separate terms by line; words less than 3 characters already ignored'
                        )
                    )
                ),
                'name' => 'terms',
                'type' => 'textarea_array',
                'value' => array(
                    'data' => self::$bad_terms['Search']
                )
            ),
            array(
                'field' => Field::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Google Search Ajax API Key'
                        )
                    )
                ),
                'name' => 'ajax_api_key',
                'type' => 'text',
                'value' => array(
                    'data' => !is_null(Data::query('Search', 'ajax_api_key'))
                        ? Data::query('Search', 'ajax_api_key')
                        : ''
                )
            ),
            array(
                'field' => Field::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Yahoo BOSS API Key'
                        )
                    )
                ),
                'name' => 'yahoo_boss_key',
                'type' => 'text',
                'value' => array(
                    'data' => !is_null(Data::query('Search', 'yahoo_boss_key'))
                        ? Data::query('Search', 'yahoo_boss_key')
                        : ''
                )
            ),
            array(
                'field' => Field::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Bing Search API Key'
                        )
                    )
                ),
                'name' => 'bing_api_key',
                'type' => 'text',
                'value' => array(
                    'data' => !is_null(Data::query('Search', 'bing_api_key'))
                        ? Data::query('Search', 'bing_api_key')
                        : ''
                )
            ),
        );

        return $data;
    }

    // }}}
    // {{{ public function hook_data_validate($name, $data)
    public function hook_data_validate($name, $data)
    {
        $success = TRUE;
        switch ($name)
        {
            case 'terms':
                foreach ($data as $k => &$v)
                {
                    if ($v === '' || strlen($v) < 3)
                    {
                        unset($data[$k]);
                    }
                    else
                    {
                        $v = strtolower($v);
                    }
                }
            break;
            case 'time':
                if (!is_numeric($data))
                    $success = FALSE;
            break;
        }
        return array(
            'success' => $success,
            'data' => $data
        );
    }

    // }}}
    //{{{ public function hook_search_bad_terms()
    public function hook_search_bad_terms()
    {
        return !is_null(Data::query('search','terms'))
            ? Data::query('search','terms')
            : array();
    }

    //}}}
    //{{{ public function cb_search_bad_terms($terms)
    /**
     * Collects all the bad terms for each module
     * Converts all terms to lowercase
     *
     * @param array $terms search results
     * @return array search results
     */
    public function cb_search_bad_terms($terms)
    {
        foreach ($terms as &$words)
        {
            foreach ($words as &$word)
            {
                $word = strtolower($word);
            }
        }
        self::$bad_terms = $terms;
    }

    //}}}
    // {{{ public static function tokenize($query)
    /**
     * Takes a search term string and turn it into an array of search terms
     * Records the search string and stores it. If search string exists, increases count.
     *
     * @param string $query words to be searched
     * @return array
     */
    public static function tokenize($query)
    {
        // record or update search terms
        $stt = Doctrine::getTable('SearchTerm');
        $st = $stt->find('get.term', array($query))->getLast();
        if ($st === FALSE)
        {
            $st = new SearchTerm();
            $st->term = $query;
            $st->count = 0;
        }
        else
        {
            $st->count++;
        }
        if ($st->isValid());
        {
            $st->save();
        }
        $st->free();

        // extract quoted materials as phrases
        $pattern = '/(["])([a-zA-Z0-9\' ]+)\1/';
        $queries = array();
        if(preg_match_all($pattern, $query, $matches))
        {
            $queries = $matches[2];
        }
        $new_query = preg_replace($pattern, '', $query);
        
        // extract single words from the rest of the data
        if(preg_match_all('/[a-zA-Z0-9\']+/', trim($new_query), $matches))
        {
            $queries = array_merge($queries, $matches[0]);
        }

        $terms = array();
        foreach ($queries as $match)
        {
            $match = trim($match);
            if (strlen($match) > 2)
            {
                $terms[] = strtolower($match);
            }
        }

        // get rid of terms in bad terms setting
        $clean_terms = array_diff($terms, self::$bad_terms['Search']);
        
        return $clean_terms;
    }
    
    // }}}
    //{{{ public static function highlight_result($result, $query)
    /**
     * Highlights results based on the query
     * TODO Add support for custom tag
     *
     * @return array
     */
    public static function highlight_result($result, $query)
    {
        $terms = self::tokenize($query);
        foreach ($terms as $term)
        {
            preg_replace('#\b'.$term.'\b#', '<strong>'.$term.'</strong>', $result);
        }
    }

    //}}}
    //{{{ public static function get_modules()
    /**
     * Returns an array modules that are allowed to be searched
     *
     * @return array
     */
    public static function get_modules()
    {
        return self::$search_modules;
    }

    //}}}
    //{{{ public static function get_bad_terms()
    /**
     * Returns array of bad terms
     *
     * @return array
     */
    public static function get_bad_terms()
    {
        return self::$bad_terms;
    }

    //}}}
    // {{{ public static function get_search_results($query, $merge = TRUE)
    public static function get_search_results($query, $merge = TRUE)
    {
        $results = self::tokenize($query);
        $data = array();
        $ids = array();
        foreach ($results as $result)
        {
            $tmp = SearchAPI::find($result, str_word_count($result) > 1);
            $ids = array_merge($ids, $tmp);
            if (!$merge)
            {
                $data[$result] = !empty($tmp)
                    ? SearchAPI::get_data_by_ids($tmp)
                    : array();
            }
        }
        if ($merge)
        {
            $data = !empty($ids)
                ? SearchAPI::get_data_by_ids(array_unique($ids))
                : array();
        }
        return $data;
    }
    // }}}
    //{{{ public static function google_ajax_search_api($query, $options = array(), $array = TRUE)
    public static function google_ajax_search_api($query, $options = array(), $array = TRUE)
    {
        if (!is_null(Data::query('Search', 'ajax_api_key')))
        {
            $options['key'] = Data::query('Search', 'ajax_api_key');
        }
        if (!strlen($query) || !ake('searcher', $options))
        {
            return array();
        }
        $url = 'http://ajax.googleapis.com/ajax/services/search/'.$options['searcher'].'?';
        unset($options['searcher']);
        $options['v'] = ake('v', $options)
            ? $options['v']
            : '1.0';
        $options['q'] = $query;
        $url .= http_build_query($options);
        $referrer = !empty($_SERVER['HTTPS'])
            ? 'https://'
            : 'http://';
        $referrer .= $_SERVER['SERVER_NAME'].'/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $referrer);
        $results = curl_exec($ch);
        curl_close($ch);
        return json_decode($results, $array);
    }

    //}}}
    //{{{ public static function yahoo_boss_search_api($query, $options = array(), $array = TRUE)
    public static function yahoo_boss_search_api($query, $options = array(), $array = TRUE)
    {
        if (!is_null(Data::query('Search', 'yahoo_boss_key')))
        {
            $options['appid'] = Data::query('Search', 'yahoo_boss_key');
        }
        if (isset($options['sites']) && is_array($options['sites']))
        {
            $options['sites'] = implode(',', $options['sites']);
        }
        $options['format'] = 'json';
        $url = 'http://boss.yahooapis.com/ysearch/web/v1/';
        $url .= urlencode($query).'?';
        $url .= http_build_query($options);
        $referrer = !empty($_SERVER['HTTPS'])
            ? 'https://'
            : 'http://';
        $referrer .= $_SERVER['HTTP_HOST'].'/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $referrer);
        $results = curl_exec($ch);
        curl_close($ch);
        return json_decode($results, $array);
    }

    //}}}
    //{{{ public static function bing_search_api($query, $options = array(), $array = TRUE)
    public static function bing_search_api($query, $options = array(), $array = TRUE)
    {
        if (!is_null(Data::query('Search', 'bing_api_key')))
        {
            $options['AppId'] = Data::query('Search', 'bing_api_key');
        }
        if (isset($options['Web.Options']) && is_array($options['Web.Options']))
        {
            $options['Web.Options'] = implode('+', $options['Web.Options']);
        }
        $options['Query'] = $query;
        $url = 'http://api.search.live.net/json.aspx?';
        $url .= http_build_query($options);
        $referrer = !empty($_SERVER['HTTPS'])
            ? 'https://'
            : 'http://';
        $referrer .= $_SERVER['HTTP_HOST'].'/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $referrer);
        $results = curl_exec($ch);
        curl_close($ch);
        return json_decode($results, $array);
    }

    //}}}
}

class SearchAPI
{
    // {{{ public static function delete_data($identifier, $namespace = NULL)
    public static function delete_data($identifier, $namespace = NULL)
    {
        $params = array();
        $spec = array(
            'delete' => 'SearchData sd',
        );
        $where[] = 'sd.identifier = ?';
        $params[] = $identifier;

        if (is_null($namespace))
        {
            $where[] = 'sd.namespace IS NULL';
        }
        else
        {
            $where[] = 'sd.namespace = ?';
            $params[] = $namespace;
        }
        $spec['addWhere'] = $where;

        $data = dql_exec($spec, $params);
        return $data > 0;
    }
    // }}}

    // {{{ public static function find($query, $as_phrase = FALSE)
    public static function find($query, $as_phrase = FALSE)
    {
        if (!is_string($query) || strlen($query) === 0)
        {
            return NULL;
        }
        $data = $relevance = array();
        if ($as_phrase)
        {
            // custom dql using position
            $words = str_word_count($query, 2);
            $words_ordered = array_values($words);
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            
            $sql = 'SELECT * FROM search_data_index sdi WHERE sdi.id IN (';
            $sql_and = '';
            foreach ($words_ordered as $k => $word)
            {
                if ($k === (count($words) - 1))
                {
                    $sql_and .= "sdi.keyword = ?";
                    $sql .= "SELECT id FROM search_data_index WHERE keyword = ?)";
                }
                else
                {
                    $sql_and .= "sdi.keyword = ? OR ";
                    $sql .= "SELECT id FROM search_data_index WHERE keyword = ? AND id IN (";
                }
            }
            for ($i = 0; $i < count($words) - 2; $i++)
            {
                $sql .= ')';
            }
            $sql .= ')';
            $sql .= " AND ($sql_and)";
            $sql .= ' ORDER BY sdi.id ASC, sdi.position ASC';
            $params = array_merge($words, $words);
            $results = $q->fetchAssoc($sql, $params);
            $matches = array();
            foreach ($words_ordered as $k => $word)
            {
                foreach ($results as $n => $result)
                {
                    if ($result['keyword'] == $word)
                    {
                        if (ake($result['id'], $matches))
                        {
                            if (in_array($result['position'] - 1, array_keys($matches[$result['id']])))
                            {
                                $matches[$result['id']][$result['position']] = $result['keyword'];
                            }
                        }
                        elseif ($k === 0)
                        {
                            $matches[$result['id']][$result['position']] = $result['keyword'];
                        }
                        else
                        {
                            break;
                        }
                    }
                }
            }
            foreach ($matches as $id => $match)
            {
                $missing_words = array_diff($words, $match);
                if (empty($missing_words))
                {
                    $relevance[] = count($match);
                    $data[] = $id;
                }
            }
        }
        else
        {
            $sdt = Doctrine::getTable('SearchData');
            $results = $sdt->search($query);
            foreach ($results as $result)
            {
                if (array_search($result['id'], $data) === FALSE)
                {
                    $relevance[] = $result['relevance'];
                    $data[] = $result['id'];
                }
            }
        }
        return array_multisort($relevance, SORT_DESC, $data)
            ? $data
            : array();
    }
    // }}}

    // {{{ public static function get_data($identifier, $namespace = NULL)
    public static function get_data($identifier, $namespace = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                'sd.search_data', 'sd.return_data'
            ),
            'from' => 'SearchData sd',
        );
        $spec['addWhere'][] = 'sd.identifier = ?';
        $params[] = $identifier;

        if (!is_null($namespace))
        {
            $spec['addWhere'][] = 'sd.namespace = ?';
            $params[] = $namespace;
        }

        $data = dql_exec($spec, $params);
        return is_array($data)
            ? array_pop($data)
            : NULL;
    }
    // }}}
    // {{{ public static function get_data_by_ids($ids)
    public static function get_data_by_ids($ids)
    {
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'SearchData sd',
            'where' => 'sd.id IN ?',
        );

        $data = dql_exec($spec, array($ids));
        return is_array($data)
            ? $data
            : NULL;
    }
    // }}}
    // {{{ public static function get_return_data($identifier, $namespace = NULL)
    public static function get_return_data($identifier, $namespace = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                'sd.return_data'
            ),
            'from' => 'SearchData sd',
        );
        $spec['addWhere'][] = 'sd.identifier = ?';
        $params[] = $identifier;

        if (!is_null($namespace))
        {
            $spec['addWhere'][] = 'sd.namespace = ?';
            $params[] = $namespace;
        }

        $data = dql_exec($spec, $params);
        return is_array($data)
            ? array_pop($data)
            : NULL;
    }
    // }}}
    // {{{ public static function get_return_data_by_ids($ids)
    public static function get_return_data_by_ids($ids)
    {
        $spec = array(
            'select' => array(
                'sd.return_data'
            ),
            'from' => 'SearchData sd',
            'where' => 'sd.id IN ?',
        );

        $data = dql_exec($spec, array($ids));
        return is_array($data)
            ? $data
            : NULL;
    }
    // }}}
    // {{{ public static function get_search_data($identifier, $namespace = NULL)
    public static function get_search_data($identifier, $namespace = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                'sd.search_data'
            ),
            'from' => 'SearchData sd',
        );
        $spec['addWhere'][] = 'sd.identifier = ?';
        $params[] = $identifier;

        if (!is_null($namespace))
        {
            $spec['addWhere'][] = 'sd.namespace = ?';
            $params[] = $namespace;
        }

        $data = dql_exec($spec, $params);
        return is_array($data)
            ? array_pop($data)
            : NULL;
    }
    // }}}
    // {{{ public static function get_search_data_by_ids($ids)
    public static function get_search_data_by_ids($ids)
    {
        $spec = array(
            'select' => array(
                'sd.search_data'
            ),
            'from' => 'SearchData sd',
            'where' => 'sd.id IN ?',
        );

        $data = dql_exec($spec, array($ids));
        return is_array($data)
            ? $data
            : NULL;
    }
    // }}}

    // {{{ public static function set_data($identifier, $return, $search, $namespace = NULL
    public static function set_data($identifier, $return, $search, $namespace = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                ''
            ),
            'from' => 'SearchData sd',
        );
        $where[] = 'sd.identifier = ?';
        $params[] = $identifier;

        if (is_null($namespace))
        {
            $where[] = 'sd.namespace IS NULL';
        }
        else
        {
            $where[] = 'sd.namespace = ?';
            $params[] = $namespace;
        }
        $spec['addWhere'] = $where;
        $data = dql_exec($spec, $params);

        $new_data = 0;
        if ($data)
        {
            self::delete_data($identifier, $namespace);
        }
        $sd = new SearchData;
        $sd->identifier = $identifier;
        $sd->namespace = $namespace;
        $sd->return_data = $return;
        $sd->search_data = $search;
        if ($sd->isValid())
        {
            $sd->save();
            $sd->free();
            $new_data = 1;
        }

        return $new_data > 0;
    }
    // }}}
    // {{{ public static function set_return_data($identifier, $value, $namespace = NULL)
    public static function set_return_data($identifier, $value, $namespace = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'SearchData sd',
        );
        $where[] = 'sd.identifier = ?';
        $params[] = $identifier;

        if (is_null($namespace))
        {
            $where[] = 'sd.namespace IS NULL';
        }
        else
        {
            $where[] = 'sd.namespace = ?';
            $params[] = $namespace;
        }
        $spec['addWhere'] = $where;
        $data = dql_exec($spec, $params);

        $new_data = 0;
        if ($data)
        {
            $specs['update'] = 'SearchData sd';
            $specs['set'] = array(
                'sd.return_data' => $value,
            );
            $specs['where'] = array('sd.id' => $data['id']);
            $new_data = dql_exec($spec);
        }
        return $new_data > 0;
    }
    // }}}
    // {{{ public static function set_search_data($identifier, $value, $namespace = NULL)
    public static function set_search_data($identifier, $value, $namespace = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'SearchData sd',
        );
        $where[] = 'sd.identifier = ?';
        $params[] = $identifier;

        if (is_null($namespace))
        {
            $where[] = 'sd.namespace IS NULL';
        }
        else
        {
            $where[] = 'sd.namespace = ?';
            $params[] = $namespace;
        }
        $spec['addWhere'] = $where;
        $data = dql_exec($spec, $params);

        $new_data = 0;
        $sd = new SearchData;
        $sd->identifier = $identifier;
        $sd->namespace = $namespace;
        $sd->search_data = $value;
        $sd->return_data = $data ? $data['return_data'] : array();
        if ($data)
        {
            self::delete_data($identifier, $namespace);
        }
        if ($sd->isValid())
        {
            $sd->save();
            $sd->free();
            $new_data = 1;
        }
        return $new_data > 0;
    }
    // }}}
}

?>
