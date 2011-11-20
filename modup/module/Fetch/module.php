<?php

class Fetch
{
    //{{{ constants
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Connect to the Fetch api http://www.fetchapp.com/';
    const MODULE_WEBSITE = 'http://www.jasontwong.com/';
    const MODULE_DEPENDENCY = '';
    const ORDER_ID_LENGTH = 5; // digits for order ID

    //}}}
    //{{{ properties
    protected static $api = array();
    //}}}
    //{{{ constructors
    public function __construct()
    {
    }
    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        self::$api['key'] = !is_null(Data::query('Fetch', 'api_key'))
            ? Data::query('Fetch', 'api_key')
            : '';
        self::$api['token'] = !is_null(Data::query('Fetch', 'api_token'))
            ? Data::query('Fetch', 'api_token')
            : '';
        self::$api['uri'] = !is_null(Data::query('Fetch', 'api_uri'))
            ? Data::query('Fetch', 'api_uri')
            : '';
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_data_info()
    public function hook_data_info()
    {
        $fields[] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'API Key'
                    )
                )
            ),
            'name' => 'api_key',
            'type' => 'text',
            'value' => array(
                'data' => self::$api['key']
            )
        );
        $fields[] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'API Token'
                    )
                )
            ),
            'name' => 'api_token',
            'type' => 'text',
            'value' => array(
                'data' => self::$api['token']
            )
        );
        $fields[] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Fetch URL'
                    )
                )
            ),
            'name' => 'api_uri',
            'type' => 'text',
            'value' => array(
                'data' => self::$api['uri']
            )
        );
        return $fields;
    }

    //}}}
    // {{{ protected function send_request($request_uri, $data = NULL)
    protected function send_request($request_uri, $post_data = NULL)
    {
        foreach (self::$api as $val)
        {
            if (!strlen($val)) 
            {
                return array( 
                    'success' => FALSE, 
                    'error' => 'API settings are incorrect',
                    'data' => ''
                );
            }
        }
        $credentials = self::$api['key'] . ':' . self::$api['token'];

        $headers = array(
            'Content-type: application/xml',
            'Authorization: Basic ' . base64_encode($credentials)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$api['uri'] . $request_uri['page']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (!is_null($post_data))
        {
            // Apply the XML to our curl call
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); 
        }

        $ch_data = curl_exec($ch);

        if (curl_errno($ch)) 
        {
            return array( 
                'success' => FALSE, 
                'error' => curl_error($ch),
                'data' => ''
            );
        } 
        else 
        {
            curl_close($ch);
            return array( 
                'success' => TRUE, 
                'error' => '',
                'data' => $ch_data
            );
        }
    }
    // }}}
    //{{{ public function get_order_id()
    /**
     * Get order ID from system data and increment. Even if the ID is not used
     * for an order, calling this method increments the ID in the database
     * anyway.
     */
    public function get_order_id()
    {
        $id = Data::query('Fetch', 'id');
        if (is_null($id))
        {
            $id = 1;
        }
        Data::update('Fetch', 'id', $id + 1);
        $id = str_pad($id, self::ORDER_ID_LENGTH, '0', STR_PAD_LEFT);
        return $id;
    }
    //}}}

    /** 
     * API Start
     */
    // {{{ public function get_account()
    public function get_account()
    {
        $request = array(
            'method' => 'GET',
            'page' => '/api/account'
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml) 
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_downloads($options = array())
    public function get_downloads($options = array())
    {
        $request = array(
            'method' => 'GET',
            'page' => '/api/downloads'
        );
        if (!empty($options))
        {
            $request['page'] .= '?' . http_build_query($options);
        }
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_downloads_by_sku($sku)
    public function get_downloads_by_sku($sku)
    {
        if (!strlen($sku))
        {
            return FALSE;
        }
        $request = array(
            'method' => 'GET',
            'page' => '/api/items/' . $sku . '/downloads'
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_files_by_sku($sku)
    public function get_files_by_sku($sku)
    {
        if (!strlen($sku))
        {
            return FALSE;
        }
        $request = array(
            'method' => 'GET',
            'page' => '/api/items/' . $sku . '/files'
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_orders($options = array())
    public function get_orders($options = array())
    {
        $request = array(
            'method' => 'GET',
            'page' => '/api/orders'
        );
        if (!empty($options))
        {
            $request['page'] .= '?' . http_build_query($options);
        }
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_orders_by_id($id)
    public function get_orders_by_id($id)
    {
        if (!strlen($id))
        {
            return FALSE;
        }
        $request = array(
            'method' => 'GET',
            'page' => '/api/orders/' . $id
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_products($options = array())
    public function get_products($options = array())
    {
        $request = array(
            'method' => 'GET',
            'page' => '/api/items'
        );
        if (!empty($options))
        {
            $request['page'] .= '?' . http_build_query($options);
        }
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_products_by_sku($sku)
    public function get_products_by_sku($sku)
    {
        if (!strlen($sku))
        {
            return FALSE;
        }
        $request = array(
            'method' => 'GET',
            'page' => '/api/items/' . $sku
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function get_uploads($options = array())
    public function get_uploads($options = array())
    {
        $request = array(
            'method' => 'GET',
            'page' => '/api/uploads'
        );
        if (!empty($options))
        {
            $request['page'] .= '?' . http_build_query($options);
        }
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}

    // {{{ public function create_email_for_order($id, $options = array())
    public function create_email_for_order($id, $options = array())
    {
        if (!strlen($id))
        {
            return FALSE;
        }
        $request = array(
            'method' => 'POST',
            'page' => '/api/orders/' . $id . '/send_email'
        );
        if (!empty($options))
        {
            $request['page'] .= '?' . http_build_query($options);
        }
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function create_new_token()
    public function create_new_token()
    {
        $request = array(
            'method' => 'GET',
            'page' => '/api/new_token'
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                Data::update('Fetch', 'api_token', (string)$xml);
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function create_order($details)
    public function create_order($details)
    {
        $request = array(
            'method' => 'POST',
            'page' => '/api/orders/create'
        );
        if (is_array($details))
        {
            $post_data = array_to_xml('order', $details);
        }
        elseif (is_string($details))
        {
            $post_data = $details;
        }
        else
        {
            // assumes SimpleXMLElement
            $post_data = $details->asXML();
        }
        $data = $this->send_request($request, $post_data);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function create_product($details)
    public function create_product($details)
    {
        $request = array(
            'method' => 'POST',
            'page' => '/api/items/create'
        );
        if (is_array($details))
        {
            $post_data = array_to_xml('item', $details);
        }
        elseif (is_string($details))
        {
            $post_data = $details;
        }
        else
        {
            // assumes SimpleXMLElement
            $post_data = $details->asXML();
        }
        $data = $this->send_request($request, $post_data);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}

    // {{{ public function delete_order($id)
    public function delete_order($id)
    {
        $request = array(
            'method' => 'DELETE',
            'page' => '/api/orders/' . $id . '/delete'
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function delete_product($sku)
    public function delete_product($sku)
    {
        $request = array(
            'method' => 'DELETE',
            'page' => '/api/items/' . $sku . '/delete'
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function expire_order($id)
    public function expire_order($id)
    {
        $request = array(
            'method' => 'GET',
            'page' => '/api/orders/' . $id . '/expire'
        );
        $data = $this->send_request($request);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function modify_order($id, $details)
    public function modify_order($id, $details)
    {
        $request = array(
            'method' => 'PUT',
            'page' => '/api/orders/' . $id . '/update'
        );
        if (is_array($details))
        {
            $post_data = array_to_xml('order', $details);
        }
        elseif (is_string($details))
        {
            $post_data = $details;
        }
        else
        {
            // assumes SimpleXMLElement
            $post_data = $details->asXML();
        }
        $data = $this->send_request($request, $post_data);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ public function modify_product($sku, $details)
    public function modify_product($sku, $details)
    {
        $request = array(
            'method' => 'PUT',
            'page' => '/api/items/' . $sku . '/update'
        );
        if (is_array($details))
        {
            $post_data = array_to_xml('item', $details);
        }
        elseif (is_string($details))
        {
            $post_data = $details;
        }
        else
        {
            // assumes SimpleXMLElement
            $post_data = $details->asXML();
        }
        $data = $this->send_request($request, $post_data);
        if ($data['success'])
        {
            $xml = simplexml_load_string($data['data']);
            if ($xml)
            {
                return $xml;
            }
        }
        return FALSE;
    }
    // }}}
}

?>
