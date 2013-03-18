<?php

/*
This emulates Python's memcache library's API with some slight modifications:
http://code.google.com/appengine/docs/python/memcache/functions.html

Unlike MPData, the MPCache class does not wait until the end of the request to
save the data. 

Any module using the MPCache class should use a unique namespace.
*/

class MPCache
{
    //{{{ constants
    const REMOVE_FAILURE = 0;
    const REMOVE_ITEM_MISSING = 1;
    const REMOVE_SUCCESSFUL = 2;
    //}}}
    //{{{ public static function set($key, $value, $time = 0, $namespace = NULL, $hook = array())
    /**
     * Sets a key's value, regardless of previous contents in cache.
     *
     * @param string $key Key to set. The Key of the data.
     * @param mixed $value Value to set. The value type can be any value supported by PHP's serialize function.
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time.
     * @param string $namespace An optional namespace for the key.
     * @return True if set, False on error.
     */
    public static function set($key, $value, $time = 0, $namespace = NULL, $hook = array())
    {
        try
        {
            $query = array(
                'name' => $key,
                'namespace' => $namespace,
            );
            $cdc = MPDB::selectCollection('mpcache.data');
            $data = array(
                'data' => $value,
                'expire' => $time === 0 
                    ? 0 
                    : time() + $time,
            );
            $data = array_merge($data, $query);
            $cdc->update(
                $query, 
                array( '$set' => $data ), 
                array( 'upsert' => TRUE )
            );
            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    //}}}
    //{{{ public static function set_multi($mapping, $time = 0, $key_prefix = '', $namespace = NULL, $hook = array())
    /**
     * Set multiple keys' values at once.
     * 
     * @param array $mapping Associative array of keys to values. 
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time.
     * @param string $key_prefix Prefix to prepend to all keys.
     * @param string $namespace An optional namespace for the keys.
     * @return an array of keys whose values were NOT set. On total success, this list should be empty.
     */
    public static function set_multi($mapping, $time = 0, $key_prefix = '', $namespace = NULL, $hook = array())
    {
        $result = array();
        foreach ($mapping as $k => $v)
        {
            $key = $key_prefix.$k;
            if (!self::set($key, $v, $time, $namespace, $hook))
            {
                $result[] = $k;
            }
        }
        return $result;
    }
    //}}}
    //{{{ public static function get($key, $namespace = NULL)
    /**
     * Looks up a single key in memcache.
     *
     * @param string $key The key to look up.
     * @param string $namespace An optional namespace for the key.
     * @return the value of the key, if found in the cache table, else NULL.
     */
    public static function get($key, $namespace = NULL)
    {
        $query = array(
            'name' => $key,
            'namespace' => is_null($namespace) ? NULL : $namespace,
            'expire' => array(
                '$or' => array(
                    0, '$gte' => time(),
                ),
            ),
        );
        $result = MPDB::selectCollection('mpcache.data')->find($query, array('data'));
        return $result 
            ? $result[0]['data']
            : NULL;
    }
    //}}}
    //{{{ public static function get_multi($keys, $key_prefix = '', $namespace = NULL)
    /**
     * Looks up multiple keys from the cache in one operation. This is the recommended way to do bulk loads.
     *
     * @param array $keys array of keys (string) to look up.
     * @param string $key_prefix Prefix to prepend to all keys when talking to the server; not included in the returned array.
     * @param string $namespace An optional namespace for the keys.
     * @return an array of the keys and values that were present in the cache. Even if the key_prefix is specified, that key_prefix is not included on the keys in the returned array.
     */
    public static function get_multi($keys, $key_prefix = '', $namespace = NULL)
    {
        $results = array();
        $offset = strlen($key_prefix);
        foreach ($keys as &$key)
        {
            $key = $key_prefix.$key;
        }

        $query = array(
            'name' => array(
                '$in' => $keys,
            ),
            'namespace' => is_null($namespace) ? NULL : $namespace,
            'expire' => array(
                '$or' => array(
                    0, '$gte' => time(),
                ),
            ),
        );
        $rows = MPDB::selectCollection('mpcache.data')->find($query);

        foreach ($rows as &$row)
        {
            $key = substr($row['name'], $offset);
            $val = $row['data'];
            $results[$key] = $val;
        }
        return $results;
    }
    //}}}
    //{{{ public static function remove($key, $seconds = 0, $namespace = NULL)
    /**
     * Deletes a key from memcache.
     *
     * @param string $key Key to delete.
     * @param int $seconds Optional number of seconds to make deleted items 'locked' for 'add' operations. Value can be a delta from current time (up to 1 month), or an absolute Unix epoch time. Defaults to 0, which means items can be immediately added. With or without this option, a 'set' operation will always work. 
     * @param string $namespace An optional namespace for the key.
     * @return 0 (REMOVE_FAILURE) on network failure, 1 (REMOVE_ITEM_MISSING) if the server tried to delete the item but didn't have it, and 2 (REMOVE_SUCCESSFUL) if the item was actually deleted. This can be used as a boolean value, where a network failure is the only bad condition.
     */
    public static function remove($key, $seconds = 0, $namespace = NULL)
    {
        try
        {
            $query = array(
                'name' => $key,
                'namespace' => is_null($namespace) ? NULL : $namespace,
            );
            $cdc = MPDB::selectCollection('mpcache.data');
            if ($seconds === 0)
            {
                $return = $cdc->remove($query);
            }
            else
            {
                $return = array();
                $data = array(
                    'expire' => time() - 1,
                    'lockout' => time() + $seconds,
                );
                $return = $cdc->update(
                    $query, 
                    array( '$set' => $data ),
                );
            }
            return ake('err', $return) && !is_null($return['err'])
                ? self::REMOVE_ITEM_MISSING
                : self::REMOVE_SUCCESSFUL;
        }
        catch (Exception $e)
        {
            return self::REMOVE_FAILURE;
        }
    }
    //}}}
    // {{{ public static function remove_by_hook($hooks = array())
    /**
     * Deletes cache via hook names. This relies on a callback or a participant of the
     * hook to call it for best use.
     *
     * e.g. in the content callback for add/edit/delete entry, remove caches that have
     *      entries in it
     *
     * @param string|array $hooks an array of multiple hooks or a single string hook
     * @return int status code
     */
    public static function remove_by_hook($hooks = array())
    {
        try
        {
            $query = is_array( $hooks )
                ? array(
                    'hook' => array(
                        '$in' => $hooks,
                    ),
                )
                : array( 'hook' => $hooks );
            $return = MPDB::selectCollection( 'mpcache.data' )
                ->remove( $query );
            return ake( 'err', $return ) && !is_null( $return['err'] )
                ? self::REMOVE_ITEM_MISSING
                : self::REMOVE_SUCCESSFUL;
        }
        catch (Exception $e)
        {
            return self::REMOVE_FAILURE;
        }
    }
    //}}}
    // {{{ public static function remove_by_namespace($namespace = NULL)
    /**
     * Deletes cache via namespace. 
     *
     * @param string $namespace
     * @return int status code
     */
    public static function remove_by_namespace($namespace = NULL)
    {
        try
        {
            $query = array(
                'namespace' => $namespace,
            );
            $return = MPDB::selectCollection('mpcache.data')
                ->remove( $query );
            return ake('err', $return) && !is_null($return['err'])
                ? self::REMOVE_ITEM_MISSING
                : self::REMOVE_SUCCESSFUL;
        }
        catch (Exception $e)
        {
            return self::REMOVE_FAILURE;
        }
    }
    //}}}
    //{{{ public static function remove_multi($keys, $seconds = 0, $key_prefix = '', $namespace = NULL)
    /**
     * Delete multiple keys at once.
     *
     * @param array $keys array of keys (strings) to delete. 
     * @param int $seconds Optional number of seconds to make deleted items 'locked' for 'add' operations. Value can be a delta from current time (up to 1 month), or an absolute Unix epoch time. Defaults to 0, which means items can be immediately added. With or without this option, a 'set' operation will always work. 
     * @param string $key_prefix Prefix to put on all keys.
     * @param string $namespace An optional namespace for the keys.
     * @return True if all operations completed successfully. False if one or more failed to complete.
     */
    public static function remove_multi($keys, $seconds = 0, $key_prefix = '', $namespace = NULL)
    {
        try
        {
            $names = array();
            foreach ($keys as $key)
            {
                $names[] = $key_prefix.$key;
            }
            $query = array(
                'name' => array(
                    '$in' => $names,
                ),
                'namespace' => is_null($namespace) ? NULL : $namespace,
            );
            $cdc = MPDB::selectCollection('mpcache.data');
            if ($seconds === 0)
            {
                $return = $cdc->remove( $query );
            }
            else
            {
                $return = array();
                $data = array(
                    'expire' => time() - 1,
                    'lockout' => time() + $seconds,
                );
                $return = $cdc->update(
                    $query, 
                    array( '$set' => $data ), 
                    array( 'multiple' => true, )
                );
            }
            return ake('err', $return) && !is_null($return['err'])
                ? self::REMOVE_ITEM_MISSING
                : self::REMOVE_SUCCESSFUL;
        }
        catch (Exception $e)
        {
            return self::REMOVE_FAILURE;
        }
    }
    //}}}
    //{{{ public static function flush_all()
    /**
     * Deletes everything in the cache. Everything.
     *
     * @return boolean True on success, False on any error.
     */
    public static function flush_all()
    {
        try
        {
            MPDB::selectCollection('mpcache.data')->drop();
            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    //}}}
}
