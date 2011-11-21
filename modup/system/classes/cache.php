<?php

/*
This emulates Python's memcache library's API with some slight modifications:
http://code.google.com/appengine/docs/python/memcache/functions.html

Unlike SystemData, the Cache class does not wait until the end of the request to
save the data. Also the data is PHP serialized and stored as a string. But that
is just a technical detail and should not be visible.

Any module using the Cache class should use a unique namespace.
*/

class Cache
{
    //{{{ constants
    const REMOVE_FAILURE = 0;
    const REMOVE_ITEM_MISSING = 1;
    const REMOVE_SUCCESSFUL = 2;
    //}}}
    //{{{ public static function set($key, $value, $time=0, $namespace=NULL)
    /**
     * Sets a key's value, regardless of previous contents in cache.
     *
     * @param string $key Key to set. The Key of the data.
     * @param mixed $value Value to set. The value type can be any value supported by PHP's serialize function.
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time.
     * @param string $namespace An optional namespace for the key.
     * @return True if set, False on error.
     */
    public static function set($key, $value, $time=0, $namespace=NULL)
    {
        try
        {
            $query = array(
                'name' => $key,
                'namespace' => is_null($namespace) ? NULL : $namespace,
            );
            $cdc = MonDB::selectCollection('cache_data');
            $data = array(
                'data' => serialize($value),
                'expire' => $time === 0 ? 0 : time() + $time,
            );
            $cdc->update($query, array('$set' => $data), array('upsert' => TRUE));
            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    //}}}
    //{{{ public static function set_multi($mapping, $time=0, $key_prefix='', $namespace=NULL)
    /**
     * Set multiple keys' values at once.
     * 
     * @param array $mapping Associative array of keys to values. 
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time.
     * @param string $key_prefix Prefix to prepend to all keys.
     * @param string $namespace An optional namespace for the keys.
     * @return an array of keys whose values were NOT set. On total success, this list should be empty.
     */
    public static function set_multi($mapping, $time=0, $key_prefix='', $namespace=NULL)
    {
        $result = array();
        foreach ($mapping as $k => $v)
        {
            $key = $key_prefix.$k;
            if (!self::set($key, $v, $time, $namespace))
            {
                $result[] = $k;
            }
        }
        return $result;
    }
    //}}}
    //{{{ public static function get($key, $namespace=NULL)
    /**
     * Looks up a single key in memcache.
     *
     * @param string $key The key to look up.
     * @param string $namespace An optional namespace for the key.
     * @return the value of the key, if found in the cache table, else NULL.
     */
    public static function get($key, $namespace=NULL)
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
        $result = MonDB::selectCollection('cache_data')->find($query, array('data'));
        return $result 
            ? unserialize($result[0]['data'])
            : NULL;
    }
    //}}}
    //{{{ public static function get_multi($keys, $key_prefix='', $namespace=NULL)
    /**
     * Looks up multiple keys from the cache in one operation. This is the recommended way to do bulk loads.
     *
     * @param array $keys array of keys (string) to look up.
     * @param string $key_prefix Prefix to prepend to all keys when talking to the server; not included in the returned array.
     * @param string $namespace An optional namespace for the keys.
     * @return an array of the keys and values that were present in the cache. Even if the key_prefix is specified, that key_prefix is not included on the keys in the returned array.
     */
    public static function get_multi($keys, $key_prefix='', $namespace=NULL)
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
        $rows = MonDB::selectCollection('cache_data')->find($query);

        foreach ($rows as &$row)
        {
            $key = substr($row['name'], $offset);
            $val = unserialize($row['data']);
            $results[$key] = $val;
        }
        return $results;
    }
    //}}}
    //{{{ public static function remove($key, $seconds=0, $namespace=NULL)
    /**
     * Deletes a key from memcache.
     *
     * @param string $key Key to delete.
     * @param int $seconds Optional number of seconds to make deleted items 'locked' for 'add' operations. Value can be a delta from current time (up to 1 month), or an absolute Unix epoch time. Defaults to 0, which means items can be immediately added. With or without this option, a 'set' operation will always work. 
     * @param string $namespace An optional namespace for the key.
     * @return 0 (REMOVE_FAILURE) on network failure, 1 (REMOVE_ITEM_MISSING) if the server tried to delete the item but didn't have it, and 2 (REMOVE_SUCCESSFUL) if the item was actually deleted. This can be used as a boolean value, where a network failure is the only bad condition.
     */
    public static function remove($key, $seconds=0, $namespace=NULL)
    {
        try
        {
            $query = array(
                'name' => $key,
                'namespace' => is_null($namespace) ? NULL : $namespace,
            );
            $cdc = MonDB::selectCollection('cache_data');
            if ($seconds === 0)
            {
                $cdc->remove($query);
            }
            else
            {
                $return = NULL;
                $data = array(
                    'expire' => time() - 1,
                    'lockout' => time() + $seconds,
                );
                $return = $cdc->update($query, array('$set' => $data));
                if (is_null($return))
                {
                    return self::REMOVE_ITEM_MISSING;
                }
            }
            return self::REMOVE_SUCCESSFUL;
        }
        catch (Exception $e)
        {
            return self::REMOVE_FAILURE;
        }
    }
    //}}}
    //{{{ public static function remove_multi($keys, $seconds=0, $key_prefix='', $namespace=NULL)
    /**
     * Delete multiple keys at once.
     *
     * @param array $keys array of keys (strings) to delete. 
     * @param int $seconds Optional number of seconds to make deleted items 'locked' for 'add' operations. Value can be a delta from current time (up to 1 month), or an absolute Unix epoch time. Defaults to 0, which means items can be immediately added. With or without this option, a 'set' operation will always work. 
     * @param string $key_prefix Prefix to put on all keys.
     * @param string $namespace An optional namespace for the keys.
     * @return True if all operations completed successfully. False if one or more failed to complete.
     */
    public static function remove_multi($keys, $seconds=0, $key_prefix='', $namespace=NULL)
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
            $cdc = MonDB::selectCollection('cache_data');
            if ($seconds === 0)
            {
                $cdc->remove($query);
            }
            else
            {
                $return = NULL;
                $data = array(
                    'expire' => time() - 1,
                    'lockout' => time() + $seconds,
                );
                $return = $cdc->update($query, array('$set' => $data), array('multiple' => TRUE));
                if (is_null($return))
                {
                    return self::REMOVE_ITEM_MISSING;
                }
            }
            return self::REMOVE_SUCCESSFUL;
        }
        catch (Exception $e)
        {
            return self::REMOVE_FAILURE;
        }
    }
    //}}}
    //{{{ public static function add($key, $value, $time=0, $namespace=NULL)
    /**
     * Sets a key's value, if and only if the item is not already in the cache table.
     *
     * @param string $key Key to set.
     * @param mixed $value Value to set. The value type can be any value supported by PHP's serialize function.
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time. By default, items never expire.
     * @param string $namespace An optional namespace for the key.
     * @return True if added, False on error.
     */
    public static function add($key, $value, $time=0, $namespace=NULL)
    {
    }
    //}}}
    //{{{ public static function add_multi($mapping, $time=0, $key_prefix='', $namespace=NULL)
    /**
     * Adds multiple values at once, with no effect for keys already in the cache.
     *
     * @param array $mapping An associative array with keys to values.
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time. By default, items never expire, though items may be evicted due to memory pressure. 
     * @param string $key_prefix Prefix to put on all keys. Even if the key_prefix is specified, that key_prefix won't be on the keys in the returned array.
     * @param string $namespace An optional namespace for the keys.
     * @return an array of keys whose values were not set because they were already set in the cache, or an empty array.
     */
    public static function add_multi($mapping, $time=0, $key_prefix='', $namespace=NULL)
    {
    }
    //}}}
    //{{{ public static function replace($key, $value, $time=0, $namespace=NULL)
    /**
     * Replaces a key's value, failing if item isn't already in memcache.
     *
     * @param string $key Key to set.
     * @param mixed $value Value to set. The value type can be any value supported by PHP's serialize function.
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time. By default, items never expire.
     * @param string $namespace An optional namespace for the key.
     * @return True if replaced. False on error or cache miss.
     */
    public static function replace($key, $value, $time=0, $namespace=NULL)
    {
    }
    //}}}
    //{{{ public static function replace_multi($mapping, $time=0, $key_prefix='', $namespace=NULL)
    /**
     * Replaces multiple values at once, with no effect for keys not in the cache.
     *
     * @param array $mapping An associative array of keys to values. See replace() for a description of allowed keys and values. 
     * @param int $time Optional expiration time, either relative number of seconds from current time (up to 1 month), or an absolute Unix epoch time. By default, items never expire.
     * @param string $key_prefix Prefix to put on all keys.
     * @param string $namespace An optional namespace for the keys.
     * @return an array of keys whose values were not set because they were not set in the cache, or an empty list.
     */
    public static function replace_multi($mapping, $time=0, $key_prefix='', $namespace=NULL)
    {
    }
    //}}}
    //{{{ public static function incr($key, $delta=1, $namespace=NULL, $initial_value=NULL)
    /*
     * Increments a key's value if the value is an int.
     * If the key does not yet exist in the cache and you specify an initial_value, the key's value will be set to this initial value and then incremented. If the key does not exist and no initial_value is specified, the key's value will not be set.
     *
     * @param string $key Key to increment, or an array of keys to increment.
     * @param int $delta Non-negative integer value to increment key by, defaulting to 1.
     * @param string $namespace An optional namespace for the key or array of keys.
     * @param int $initial_value An initial value to be used if the key does not yet exist in the cache. Ignored if the key already exists. If None and the key does not exist, the key remains unset.
     * @return The return value is a new integer value, or NULL if key was not in the cache or could not be incremented for any other reason.
     */
    public static function incr($key, $delta=1, $namespace=NULL, $initial_value=NULL)
    {
    }
    //}}}
    //{{{ public static function decr($key, $delta=1, $namespace=NULL, $initial_value=NULL)
    /**
     * Atomically decrements a key's value. 
     * If the key does not yet exist in the cache and you specify an initial_value, the key's value will be set to this initial value and then decremented. If the key does not exist and no initial_value is specified, the key's value will not be set.
     *
     * @param string $key Key to decrement, or an array of keys to decrement.
     * @param int $delta Non-negative integer value (int or long) to decrement key by, defaulting to 1.
     * @param string $namespace An optional namespace for the key.
     * @param int $initial_value An initial value to be used if the key does not yet exist in the cache. Ignored if the key already exists. If None and the key does not exist, the key remains unset.
     * @return a new integer value, or NULL if key was not in the cache or could not be decremented for any other reason.
     */
    public static function decr($key, $delta=1, $namespace=NULL, $initial_value=NULL)
    {
    }
    //}}}
    //{{{ public static function offset_multi($mapping, $key_prefix='', $namespace=NULL, $initial_value=NULL)
    /**
     * Increments or decrements multiple keys with integer values in a single call. Each key can have a separate offset. The offset can be positive or negative.
     * Applying an offset to a single key is atomic. Applying an offset to multiple keys may succeed for some keys and fail for others.
     *
     * @param array $mapping Associative array of keys to offsets. An offset can be a positive or negative integer to be added to the key's value.
     * @param string $key_prefix Prefix to prepend to all keys.
     * @param string $namespace An optional namespace for all keys in the mapping.
     * @param int $initial_value An initial value to be used if a key in the array does not yet exist in the cache. If NULL and a key in the mapping does not exist in the cache, the key remains unset in the cache.
     * @return an associative array of the provided keys to their new values. If there was an error applying an offset to a key, if a key doesn't exist in the cache and no initial_value is provided, or if a key is set with a non-integer value, its return value is NULL.
     */
    public static function offset_multi($mapping, $key_prefix='', $namespace=NULL, $initial_value=NULL)
    {
    }
    //}}}
    //{{{ public static function flush_all()
    /**
     * Deletes everything in the cache. Everything.
     *
     * @return True on success, False on any error.
     */
    public static function flush_all()
    {
        try
        {
            MonDB::selectCollection('cache_data')->drop();
            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    //}}}
}

?>
