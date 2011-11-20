<?php

// {{{ class Ecommerce
class Ecommerce
{
    //{{{ constants
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Ecommerce system. Product agnostic.';
    const MODULE_WEBSITE = 'http://www.jasontwong.com/';
    const MODULE_DEPENDENCY = '';
    const ORDER_NUMBER_LENGTH = 5;

    //}}}
    //{{{ properties
    protected static $paypal = FALSE;
    protected static $paypal_sandbox = FALSE;
    protected static $paypal_api = array();
    protected static $paypal_api_sandbox = array();
    //}}}
    //{{{ constructors
    public function __construct()
    {
    }
    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        self::$paypal = self::is_using_paypal();
        self::$paypal_sandbox = self::is_using_paypal('sandbox');
        self::$paypal_api = self::get_paypal_credentials();
        self::$paypal_api_sandbox = self::get_paypal_credentials('sandbox');
    }
    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        $css = array();
        if (strpos(URI_PATH, '/admin/module/Ecommerce/') !== FALSE)
        {
            $css['screen'][] = '/admin/static/Ecommerce/screen.css/';
            $css['screen'][] = '/admin/static/Ecommerce/field.css/';
        }
        return $css;
    }

    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();

        if (strpos(URI_PATH, '/admin/module/Ecommerce/') !== FALSE)
        {
            $js[] = '/admin/static/Ecommerce/field.js/';
            $js[] = '/admin/static/Content/field.js/';
        }

        return $js;
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $uri = '/admin/module/Ecommerce';
        $links = array();
        if (User::has_perm('add ecommerce orders'))
        {
            $links['Ecommerce'][] = '<a href="'.$uri.'/add_order/">Add Order</a>';
        }
        if (User::has_perm('view ecommerce orders'))
        {
            $links['Ecommerce'][] = '<a href="'.$uri.'/orders/">Orders</a>';
        }
        if (User::has_perm('edit ecommerce gift cards'))
        {
            $links['Ecommerce'][] = '<a href="'.$uri.'/gift_cards/">Gift Cards</a>';
        }
        if (User::has_perm('edit ecommerce coupons'))
        {
            $links['Ecommerce'][] = '<a href="'.$uri.'/coupons/">Coupons</a>';
        }
        if (User::has_perm('edit ecommerce statuses'))
        {
            $links['Ecommerce'][] = '<a href="'.$uri.'/statuses/">Statuses</a>';
        }
        return $links;
    }

    //}}}
    //{{{ public function hook_data_info()
    public function hook_data_info()
    {
        $fields = array();
        $fields[] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Next order number'
                    )
                )
            ),
            'name' => 'order_number',
            'type' => 'text',
            'value' => array(
                'data' => self::get_order_number()
            )
        );
        $fields[] = array(
            'field' => Field::layout(
                'textarea_array',
                array(
                    'data' => array(
                        'label' => 'Order Options'
                    )
                )
            ),
            'name' => 'order_keys',
            'type' => 'textarea_array',
            'value' => array(
                'data' => self::get_order_keys()
            )
        );
        $fields[] = array(
            'field' => Field::layout(
                'textarea_array',
                array(
                    'data' => array(
                        'label' => 'Product Options'
                    )
                )
            ),
            'name' => 'product_keys',
            'type' => 'textarea_array',
            'value' => array(
                'data' => self::get_product_keys()
            )
        );
        // {{{ paypal fields
        $fields['Paypal'][] = array(
            'field' => Field::layout(
                'checkbox_boolean',
                array(
                    'data' => array(
                        'label' => 'Integrate Paypal'
                    )
                )
            ),
            'name' => 'paypal',
            'type' => 'checkbox_boolean',
            'value' => array(
                'data' => self::$paypal
            )
        );
        $fields['Paypal'][] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Paypal API Username'
                    )
                )
            ),
            'name' => 'paypal_api_user',
            'type' => 'text',
            'value' => array(
                'data' => self::$paypal_api['username']
            )
        );
        $fields['Paypal'][] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Paypal API Password'
                    )
                )
            ),
            'name' => 'paypal_api_pass',
            'type' => 'text',
            'value' => array(
                'data' => self::$paypal_api['password']
            )
        );
        $fields['Paypal'][] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Paypal API Signature'
                    )
                )
            ),
            'name' => 'paypal_api_sig',
            'type' => 'text',
            'value' => array(
                'data' => self::$paypal_api['signature']
            )
        );
        // }}}
        // {{{ paypal sandbox fields
        $fields['Paypal Sandbox'][] = array(
            'field' => Field::layout(
                'checkbox_boolean',
                array(
                    'data' => array(
                        'label' => 'Enable Sandbox'
                    )
                )
            ),
            'name' => 'paypal_sandbox',
            'type' => 'checkbox_boolean',
            'value' => array(
                'data' => self::$paypal_sandbox
            )
        );
        $fields['Paypal Sandbox'][] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Paypal API Username'
                    )
                )
            ),
            'name' => 'paypal_api_sandbox_user',
            'type' => 'text',
            'value' => array(
                'data' => self::$paypal_api_sandbox['username']
            )
        );
        $fields['Paypal Sandbox'][] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Paypal API Password'
                    )
                )
            ),
            'name' => 'paypal_api_sandbox_pass',
            'type' => 'text',
            'value' => array(
                'data' => self::$paypal_api_sandbox['password']
            )
        );
        $fields['Paypal Sandbox'][] = array(
            'field' => Field::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Paypal API Signature'
                    )
                )
            ),
            'name' => 'paypal_api_sandbox_sig',
            'type' => 'text',
            'value' => array(
                'data' => self::$paypal_api_sandbox['signature']
            )
        );
        // }}}
        return $fields;
    }

    //}}}
    //{{{ public function hook_data_validate($name, $data)
    public function hook_data_validate($name, $data)
    {
        $success = TRUE;
        switch ($name)
        {
            case 'order_number':
                $success = is_numeric($data);
            break;
        }
        return array(
            'success' => $success,
            'data' => $data
        );
    }

    //}}}
    //{{{ public function hook_rpc($action, $params = NULL)
    /**
     * Implementation of hook_rpc
     *
     * This looks at the action and checks for the method _rpc_<action> and
     * passes the parameters to that. There is no limit on parameters.
     *
     * @param string $action action name
     * @return string
     */
    public function hook_rpc($action)
    {
        $method = '_rpc_'.$action;
        $caller = array($this, $method);
        $args = array_slice(func_get_args(), 1);
        return method_exists($this, $method) 
            ? call_user_func_array($caller, $args)
            : '';
    }

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        $perms = array();
        $perms['Ecommerce']['add ecommerce orders'] = 'Add ecommerce orders';
        $perms['Ecommerce']['edit ecommerce orders'] = 'Edit ecommerce orders';
        $perms['Ecommerce']['view ecommerce orders'] = 'View ecommerce orders';
        $perms['Ecommerce']['edit ecommerce coupons'] = 'Edit ecommerce coupons';
        $perms['Ecommerce']['edit ecommerce gift cards'] = 'Edit ecommerce gift cards';
        $perms['Ecommerce']['edit ecommerce statuses'] = 'Edit ecommerce statuses';
        return $perms;
    }

    //}}}

    //{{{ protected function _rpc_status($data)
    protected function _rpc_status($data)
    {
        $success = FALSE;
        $action = $data['action'];
        switch ($action)
        {
            // {{{ case 'delete'
            case 'delete':
                $success = EcommerceAPI::delete_status_by_id($data['id']);
            break;
            // }}}
        }
        echo json_encode(
            array(
                'success' => $success,
                'action' => $action,
            )
        );
    }

    //}}}
    //{{{ protected function _rpc_coupon($data)
    protected function _rpc_coupon($data)
    {
        $success = FALSE;
        $action = $data['action'];
        switch ($action)
        {
            // {{{ case 'delete'
            case 'delete':
                $success = EcommerceAPI::delete_coupon_by_id($data['id']);
            break;
            // }}}
        }
        echo json_encode(
            array(
                'success' => $success,
                'action' => $action,
            )
        );
    }

    //}}}
    //{{{ protected function _rpc_gift_card($data)
    protected function _rpc_gift_card($data)
    {
        $success = FALSE;
        $action = $data['action'];
        switch ($action)
        {
            // {{{ case 'delete'
            case 'delete':
                $success = EcommerceAPI::delete_gift_card_by_id($data['id']);
            break;
            // }}}
        }
        echo json_encode(
            array(
                'success' => $success,
                'action' => $action,
            )
        );
    }

    //}}}

    // {{{ public static function get_available_coupons()
    public static function get_available_coupons()
    {
        $coupons = EcommerceAPI::get_coupons();
        $time = strtotime('now');
        $data = array();
        foreach ($coupons as $coupon)
        {
            if ($coupon['uses'] === 0 
                || $time < $coupon['start_date'] 
                || $time > $coupon['end_date'])
                {
                    continue;
                }
            $data[] = $coupon;
        }
        return $data;
    }

    //}}}
    // {{{ public static function get_expired_coupons()
    public static function get_expired_coupons()
    {
        $coupons = EcommerceAPI::get_coupons();
        $time = strtotime('now');
        $data = array();
        foreach ($coupons as $coupon)
        {
            if ($coupon['uses'] === 0 
                || $time < $coupon['start_date'] 
                || $time > $coupon['end_date'])
                {
                    $data[] = $coupon;
                }
        }
        return $data;
    }

    //}}}
    // {{{ public static function is_valid_coupon($code)
    public static function is_valid_coupon($code)
    {
        $coupon = EcommerceAPI::get_coupon_by_code($code);
        $time = strtotime('now');
        $data = array();
        if ($coupon['uses'] === 0 
            || $time < $coupon['start_date'] 
            || $time > $coupon['end_date'])
            {
                return FALSE;
            }
        return TRUE;
    }

    //}}}
    // {{{ public static function use_coupon($code)
    public static function use_coupon($code)
    {
        if (self::is_valid_coupon($code))
        {
            $ect = Doctrine::getTable('EcommerceCoupon');
            $ec = $ect->findOneByCode($code);
            if ($ec->uses > 0)
            {
                $ec->uses--;
                $ec->save();
                $ec->free();
            }
            return TRUE;
        }
        return FALSE;
    }

    //}}}

    // {{{ public static function get_available_gift_cards()
    public static function get_available_gift_cards()
    {
        $gift_cards = EcommerceAPI::get_gift_cards();
        $time = strtotime('now');
        $data = array();
        foreach ($gift_cards as $gift_card)
        {
            if ($gift_card['uses'] === 0 
                || $time > $gift_card['end_date'])
                {
                    continue;
                }
            $data[] = $gift_card;
        }
        return $data;
    }

    //}}}
    // {{{ public static function get_expired_gift_cards()
    public static function get_expired_gift_cards()
    {
        $gift_cards = EcommerceAPI::get_gift_cards();
        $time = strtotime('now');
        $data = array();
        foreach ($gift_cards as $gift_card)
        {
            if ($gift_card['uses'] === 0 
                || $time > $gift_card['end_date'])
                {
                    $data[] = $gift_card;
                }
        }
        return $data;
    }

    //}}}
    // {{{ public static function is_valid_gift_card($code)
    public static function is_valid_gift_card($code)
    {
        $gift_card = EcommerceAPI::get_gift_card_by_code($code);
        $time = strtotime('now');
        $data = array();
        if ($gift_card['balance'] <= 0 || $time > $gift_card['end_date'])
        {
            return FALSE;
        }
        return TRUE;
    }

    //}}}
    // {{{ public static function use_gift_card($code, $order_id, $amount = NULL)
    public static function use_gift_card($code, $order_id, $amount = NULL)
    {
        if (self::is_valid_gift_card($code) && is_numeric($order_id))
        {
            $egct = Doctrine::getTable('EcommerceGiftCard');
            $egc = $egct->findOneByCode($code);
            $misc = array();
            if (is_null($amount))
            {
                $misc[] = array(
                    'order_id' => $order_id,
                    'amount_used' => $egc->balance,
                );
                $egc->balance = 0;
            }
            elseif (is_numeric($amount) && $amount <= $egc->balance)
            {
                $misc[] = array(
                    'order_id' => $order_id,
                    'amount_used' => $amount,
                );
                $egc->balance -= $amount;
            }
            else
            {
                return FALSE;
            }
            if ($egc->isModified() && $egc->isValid())
            {
                $egc->save();
                $egc->free();
            }
            return TRUE;
        }
        return FALSE;
    }

    //}}}

    // {{{ public static function get_order_keys()
    public static function get_order_keys()
    {
        $keys = Data::query('Ecommerce', 'order_keys');
        if (!is_array($keys))
        {
            $keys = array();
        }
        return $keys;
    }

    //}}}
    // {{{ public static function get_order_number()
    public static function get_order_number()
    {
        $number = Data::query('Ecommerce', 'order_number');
        if (is_numeric($number))
        {
            $number++;
        }
        else
        {
            $number = 1;
        }
        $number = str_pad($number, self::ORDER_NUMBER_LENGTH, '0', STR_PAD_LEFT);
        return $number;
    }

    //}}}
    // {{{ public static function get_product_keys()
    public static function get_product_keys()
    {
        $keys = Data::query('Ecommerce', 'product_keys');
        if (!is_array($keys))
        {
            $keys = array();
        }
        return $keys;
    }

    //}}}

    // {{{ public static function get_status_options()
    public static function get_status_options()
    {
        $statuses = EcommerceAPI::get_statuses();
        $options = array();
        foreach ($statuses as $status)
        {
            $options[$status['type']][$status['id']] = $status['name'];
        }
        return empty($options)
            ? array('None' => array('' => 'None'))
            : $options;
    }

    //}}}
    // {{{ public static function get_month_options()
    public static function get_month_options()
    {
        return array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );
    }

    //}}}
    // {{{ public static function get_ca_provinces()
    public static function get_ca_provinces()
    {
        $provinces = array(
            "AB" => 'Alberta',
            "BC" => 'British Columbia',
            "MB" => 'Manitoba',
            "NB" => 'New Brunswick',
            "NF" => 'Newfoundland',
            "NT" => 'Northwest Territories',
            "NS" => 'Nova Scotia',
            "NU" => 'Nunavut',
            "ON" => 'Ontario',
            "PE" => 'Prince Edward Island',
            "QC" => 'Quebec',
            "SK" => 'Saskatchewan',
            "YT" => 'Yukon Territory',
        );

        asort($provinces);
        return $provinces;
    }

    //}}}
    // {{{ public static function get_us_states()
    public static function get_us_states()
    {
        $states = array(
            "AK" => 'Alaska',
            "AL" => 'Alabama',
            "AR" => 'Arkansas',
            "AZ" => 'Arizona',
            "CA" => 'California',
            "CO" => 'Colorado',
            "CT" => 'Connecticut',
            "DC" => 'District of Columbia',
            "DE" => 'Delaware',
            "FL" => 'Florida',
            "GA" => 'Georgia',
            "HI" => 'Hawaii',
            "IA" => 'Iowa',
            "ID" => 'Idaho',
            "IL" => 'Illinois',
            "IN" => 'Indiana',
            "KS" => 'Kansas',
            "KY" => 'Kentucky',
            "LA" => 'Louisiana',
            "MA" => 'Massachusetts',
            "MD" => 'Maryland',
            "ME" => 'Maine',
            "MI" => 'Michigan',
            "MN" => 'Minnesota',
            "MO" => 'Missouri',
            "MS" => 'Mississippi',
            "MT" => 'Montana',
            "NC" => 'North Carolina',
            "ND" => 'North Dakota',
            "NE" => 'Nebraska',
            "NH" => 'New Hampshire',
            "NJ" => 'New Jersey',
            "NM" => 'New Mexico',
            "NV" => 'Nevada',
            "NY" => 'New York',
            "OH" => 'Ohio',
            "OK" => 'Oklahoma',
            "OR" => 'Oregon',
            "PA" => 'Pennsylvania',
            "PR" => 'Puerto Rico',
            "RI" => 'Rhode Island',
            "SC" => 'South Carolina',
            "SD" => 'South Dakota',
            "TN" => 'Tennessee',
            "TX" => 'Texas',
            "UT" => 'Utah',
            "VA" => 'Virginia',
            "VT" => 'Vermont',
            "WA" => 'Washington',
            "WI" => 'Wisconsin',
            "WV" => 'West Virginia',
            "WY" => 'Wyoming',
        );

        asort($states);
        return $states;
    }

    //}}}

    // {{{ public static function get_paypal_countries()
    public static function get_paypal_countries()
    {
        $countries = array(
            'AX' => 'ÅLAND ISLANDS',
            'AL' => 'ALBANIA',
            'DZ' => 'ALGERIA',
            'AS' => 'AMERICAN SAMOA',
            'AD' => 'ANDORRA',
            'AI' => 'ANGUILLA',
            'AQ' => 'ANTARCTICA',
            'AG' => 'ANTIGUA AND BARBUDA',
            'AR' => 'ARGENTINA',
            'AM' => 'ARMENIA',
            'AW' => 'ARUBA',
            'AU' => 'AUSTRALIA',
            'AT' => 'AUSTRIA',
            'BS' => 'BAHAMAS',
            'BH' => 'BAHRAIN',
            'BB' => 'BARBADOS',
            'BE' => 'BELGIUM',
            'BZ' => 'BELIZE',
            'BJ' => 'BENIN',
            'BM' => 'BERMUDA',
            'BT' => 'BHUTAN',
            'BW' => 'BOTSWANA',
            'BV' => 'BOUVET ISLAND',
            'BR' => 'BRAZIL',
            'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
            'BN' => 'BRUNEI DARUSSALAM',
            'BG' => 'BULGARIA',
            'BF' => 'BURKINA FASO',
            'CA' => 'CANADA',
            'CV' => 'CAPE VERDE',
            'KY' => 'CAYMAN ISLANDS',
            'CF' => 'CENTRAL AFRICAN REPUBLIC',
            'CL' => 'CHILE',
            'CN' => 'CHINA',
            'CX' => 'CHRISTMAS ISLAND',
            'CC' => 'COCOS (KEELING) ISLANDS',
            'CO' => 'COLOMBIA',
            'CK' => 'COOK ISLANDS',
            'CR' => 'COSTA RICA',
            'CY' => 'CYPRUS',
            'CZ' => 'CZECH REPUBLIC',
            'DK' => 'DENMARK',
            'DJ' => 'DJIBOUTI',
            'DM' => 'DOMINICA',
            'DO' => 'DOMINICAN REPUBLIC',
            'EG' => 'EGYPT',
            'SV' => 'EL SALVADOR',
            'EE' => 'ESTONIA',
            'FK' => 'FALKLAND ISLANDS (MALVINAS)',
            'FO' => 'FAROE ISLANDS',
            'FJ' => 'FIJI',
            'FI' => 'FINLAND',
            'FR' => 'FRANCE',
            'GF' => 'FRENCH GUIANA',
            'PF' => 'FRENCH POLYNESIA',
            'TF' => 'FRENCH SOUTHERN TERRITORIES',
            'GM' => 'GAMBIA',
            'GE' => 'GEORGIA',
            'DE' => 'GERMANY',
            'GH' => 'GHANA',
            'GI' => 'GIBRALTAR',
            'GR' => 'GREECE',
            'GL' => 'GREENLAND',
            'GD' => 'GRENADA',
            'GP' => 'GUADELOUPE',
            'GU' => 'GUAM',
            'GG' => 'GUERNSEY',
            'HM' => 'HEARD ISLAND AND MCDONALD ISLANDS',
            'VA' => 'HOLY SEE (VATICAN CITY STATE)',
            'HN' => 'HONDURAS',
            'HK' => 'HONG KONG',
            'HU' => 'HUNGARY',
            'IS' => 'ICELAND',
            'IN' => 'INDIA',
            'ID' => 'INDONESIA',
            'IE' => 'IRELAND',
            'IM' => 'ISLE OF MAN',
            'IL' => 'ISRAEL',
            'IT' => 'ITALY',
            'JM' => 'JAMAICA',
            'JP' => 'JAPAN',
            'JE' => 'JERSEY',
            'JO' => 'JORDAN',
            'KZ' => 'KAZAKHSTAN',
            'KI' => 'KIRIBATI',
            'KR' => 'KOREA, REPUBLIC OF',
            'KW' => 'KUWAIT',
            'KG' => 'KYRGYZSTAN',
            'LV' => 'LATVIA',
            'LS' => 'LESOTHO',
            'LI' => 'LIECHTENSTEIN',
            'LT' => 'LITHUANIA',
            'LU' => 'LUXEMBOURG',
            'MO' => 'MACAO',
            'MW' => 'MALAWI',
            'MY' => 'MALAYSIA',
            'MT' => 'MALTA',
            'MH' => 'MARSHALL ISLANDS',
            'MQ' => 'MARTINIQUE',
            'MR' => 'MAURITANIA',
            'MU' => 'MAURITIUS',
            'YT' => 'MAYOTTE',
            'MX' => 'MEXICO',
            'FM' => 'MICRONESIA, FEDERATED STATES OF',
            'MD' => 'MOLDOVA, REPUBLIC OF',
            'MC' => 'MONACO',
            'MN' => 'MONGOLIA',
            'MS' => 'MONTSERRAT',
            'MA' => 'MOROCCO',
            'MZ' => 'MOZAMBIQUE',
            'NA' => 'NAMIBIA',
            'NR' => 'NAURU',
            'NP' => 'NEPAL',
            'NL' => 'NETHERLANDS',
            'AN' => 'NETHERLANDS ANTILLES',
            'NC' => 'NEW CALEDONIA',
            'NZ' => 'NEW ZEALAND',
            'NI' => 'NICARAGUA',
            'NE' => 'NIGER',
            'NU' => 'NIUE',
            'NF' => 'NORFOLK ISLAND',
            'MP' => 'NORTHERN MARIANA ISLANDS',
            'NO' => 'NORWAY',
            'OM' => 'OMAN',
            'PW' => 'PALAU',
            'PA' => 'PANAMA',
            'PY' => 'PARAGUAY',
            'PE' => 'PERU',
            'PH' => 'PHILIPPINES',
            'PN' => 'PITCAIRN',
            'PL' => 'POLAND',
            'PT' => 'PORTUGAL',
            'PR' => 'PUERTO RICO',
            'QA' => 'QATAR',
            'RE' => 'REUNION',
            'RO' => 'ROMANIA',
            'SH' => 'SAINT HELENA',
            'KN' => 'SAINT KITTS AND NEVIS',
            'LC' => 'SAINT LUCIA',
            'PM' => 'SAINT PIERRE AND MIQUELON',
            'VC' => 'SAINT VINCENT AND THE GRENADINES',
            'WS' => 'SAMOA',
            'SM' => 'SAN MARINO',
            'ST' => 'SAO TOME AND PRINCIPE',
            'SA' => 'SAUDI ARABIA',
            'SN' => 'SENEGAL',
            'SC' => 'SEYCHELLES',
            'SG' => 'SINGAPORE',
            'SK' => 'SLOVAKIA',
            'SI' => 'SLOVENIA',
            'SB' => 'SOLOMON ISLANDS',
            'ZA' => 'SOUTH AFRICA',
            'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
            'ES' => 'SPAIN',
            'SR' => 'SURINAME',
            'SJ' => 'SVALBARD AND JAN MAYEN',
            'SZ' => 'SWAZILAND',
            'SE' => 'SWEDEN',
            'CH' => 'SWITZERLAND',
            'TW' => 'TAIWAN, PROVINCE OF CHINA',
            'TZ' => 'TANZANIA, UNITED REPUBLIC OF',
            'TH' => 'THAILAND',
            'TK' => 'TOKELAU',
            'TO' => 'TONGA',
            'TT' => 'TRINIDAD AND TOBAGO',
            'TN' => 'TUNISIA',
            'TR' => 'TURKEY',
            'TC' => 'TURKS AND CAICOS ISLANDS',
            'TV' => 'TUVALU',
            'UA' => 'UKRAINE',
            'AE' => 'UNITED ARAB EMIRATES',
            'GB' => 'UNITED KINGDOM',
            'US' => 'UNITED STATES',
            'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
            'UY' => 'URUGUAY',
            'VN' => 'VIET NAM',
            'VG' => 'VIRGIN ISLANDS, BRITISH',
            'VI' => 'VIRGIN ISLANDS, U.S.',
            'WF' => 'WALLIS AND FUTUNA',
            'ZM ' => 'ZAMBIA',
        );

        asort($countries);
        return $countries;
    }

    //}}}
    // {{{ public static function get_paypal_credentials($environment = 'live')
    public static function get_paypal_credentials($environment = 'live')
    {
        if ($environment === 'sandbox' || $environment === 'beta-sandbox')
        {
            $paypal['username'] = is_null(Data::query('Ecommerce', 'paypal_api_sandbox_user'))
                ? ''
                : Data::query('Ecommerce', 'paypal_api_sandbox_user');
            $paypal['password'] = is_null(Data::query('Ecommerce', 'paypal_api_sandbox_pass'))
                ? ''
                : Data::query('Ecommerce', 'paypal_api_sandbox_pass');
            $paypal['signature'] = is_null(Data::query('Ecommerce', 'paypal_api_sandbox_sig'))
                ? ''
                : Data::query('Ecommerce', 'paypal_api_sandbox_sig');
            $paypal['endpoint'] = "https://api-3t.$environment.paypal.com/nvp";
        }
        else
        {
            $paypal['username'] = is_null(Data::query('Ecommerce', 'paypal_api_user'))
                ? ''
                : Data::query('Ecommerce', 'paypal_api_user');
            $paypal['password'] = is_null(Data::query('Ecommerce', 'paypal_api_pass'))
                ? ''
                : Data::query('Ecommerce', 'paypal_api_pass');
            $paypal['signature'] = is_null(Data::query('Ecommerce', 'paypal_api_sig'))
                ? ''
                : Data::query('Ecommerce', 'paypal_api_sig');
            $paypal['endpoint'] = "https://api-3t.paypal.com/nvp";
        }
        return $paypal;
    }

    //}}}
    // {{{ public static function is_using_paypal($environment = 'live')
    public static function is_using_paypal($environment = 'live')
    {
        $paypal_key = $environment === 'sandbox' || $environment === 'beta-sandbox'
            ? 'paypal_sandbox'
            : 'paypal';
        return is_null(Data::query('Ecommerce', $paypal_key))
            ? FALSE
            : Data::query('Ecommerce', $paypal_key);
    }

    //}}}
    // {{{ public static function paypal_request($method_name, $nvp_query, $environment = 'live', $api_version = 65.1)
    /**
     * Send HTTP POST Request
     *
     * @param	string	The API method name
     * @param	string	The POST Message fields in &name=value pair format
     * @param	string	The environment settings to use
     * @param	array   The credentials override
     * @param	float   The version of the API to use
     * @return	array	Parsed HTTP Response body
     */
    public static function paypal_request($method_name, $nvp_query, $environment = 'live', $credentials = array(), $api_version = 65.1)
    {
        // Set up your API credentials, PayPal end point, and API version.
        if (empty($credentials))
        {
            $credentials = self::get_paypal_credentials($environment);
        }
        $version = urlencode($api_version);

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $credentials['endpoint']);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$method_name&VERSION=$version&PWD={$credentials['password']}&USER={$credentials['username']}&SIGNATURE={$credentials['signature']}$nvp_query";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // Get response from the server.
        $http_response = curl_exec($ch);

        if (!$http_response) 
        {
            throw new Exception ("$method_name failed: ".curl_error($ch).'('.curl_errno($ch).')');
        }

        // Extract the response details.
        $http_response_array = explode("&", $http_response);

        $http_parsed_response_array = array();
        foreach ($http_response_array as $i => $value)
        {
            $tmpAr = explode("=", $value);
            if (sizeof($tmpAr) > 1) 
            {
                $http_parsed_response_array[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ((0 == sizeof($http_parsed_response_array)) || !array_key_exists('ACK', $http_parsed_response_array)) 
        {
            throw new Exception ("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
        }

        return $http_parsed_response_array;
    }
    // }}}
}
// }}}
// {{{ class EcommerceAPI
class EcommerceAPI
{
    // {{{ public static function get_cart($id)
    public static function get_cart($id)
    {
        $ect = Doctrine::getTable('EcommerceCart');
        $ec = $ect->findOneByIdentifier($id);
        if ($ec === FALSE)
        {
            return NULL;
        }
        else
        {
            $data = $ec->data;
            $ec->free();
            return $data;
        }
    }
    // }}}
    // {{{ public static function get_coupon_by_code($code)
    public static function get_coupon_by_code($code)
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'EcommerceCoupon ec',
        );
        $spec['where'] = 'ec.code = ?';
        $params[] = $code;

        $coupon = dql_exec($spec, $params);
        return is_array($coupon)
            ? array_pop($coupon)
            : $coupon;
    }
    // }}}
    // {{{ public static function get_coupons($type = NULL)
    public static function get_coupons($type = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'EcommerceCoupon ec',
            'orderBy' => 'ec.code ASC',
        );
        if (is_string($type))
        {
            $spec['where'] = 'ec.type = ?';
            $params[] = $type;
        }
        $coupons = dql_exec($spec, $params);
        return $coupons;
    }
    // }}}
    // {{{ public static function get_gift_card_by_code($code)
    public static function get_gift_card_by_code($code)
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'EcommerceGiftCard egc',
        );
        $spec['where'] = 'egc.code = ?';
        $params[] = $code;

        $gift_card = dql_exec($spec, $params);
        return is_array($gift_card)
            ? array_pop($gift_card)
            : $gift_card;
    }
    // }}}
    // {{{ public static function get_gift_cards()
    public static function get_gift_cards()
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'EcommerceGiftCard egc',
            'orderBy' => 'egc.code ASC',
        );
        $gift_cards = dql_exec($spec, $params);
        return $gift_cards;
    }
    // }}}
    // {{{ public static function get_order_by_id($id)
    public static function get_order_by_id($id)
    {
        $eot = Doctrine::getTable('EcommerceOrders');
        $order = $eot->find($id, Doctrine::HYDRATE_ARRAY);
        return $order;
    }
    // }}}
    // {{{ public static function get_orders()
    public static function get_orders()
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'EcommerceOrder eo',
        );
        $orders = dql_exec($spec, $params);
        return $orders;
    }
    // }}}
    // {{{ public static function get_orders_paginated($filters = array(), $page = 1, $rows = 25)
    public static function get_orders_paginated($filters = array(), $page = 1, $rows = 25)
    {
        $params = array();
        $spec = array(
            'select' => array(
                'eo.*'
            ),
            'from' => 'EcommerceOrder eo',
        );
        if (is_numeric($filters['start_date']))
        {
            $spec['addWhere'][] = 'eo.modified_date >= ?';
            $params[] = (int)$filters['start_date'];
        }
        if (is_numeric($filters['end_date']))
        {
            $spec['addWhere'][] = 'eo.modified_date <= ?';
            $params[] = strtotime('+1 day', (int)$filters['end_date']);
        }
        if (!empty($filters['statuses']))
        {
            $spec['addWhere'][] = 'eo.order_status_id IN ? ';
            $params[] = $filters['statuses'];
        }
        $spec['orderBy'] = 'eo.'.$filters['sort']['type'].' '.$filters['sort']['order'];
        $dql = dql_build($spec);

        $pager = new Doctrine_Pager($dql, $page, $rows);

        $orders['items'] = $pager->execute($params);
        $orders['total_items'] = $pager->getNumResults();

        return $orders;
    }
    // }}}
    // {{{ public static function get_status_types()
    public static function get_status_types()
    {
        $spec = array(
            'select' => array(
                'DISTINCT eos.type as type'
            ),
            'from' => 'EcommerceOrderStatus eos',
            'orderBy' => 'type ASC',
        );
        $types = dql_exec($spec);
        $tmp = array();
        foreach ($types as $type)
        {
            $tmp[] = $type['type'];
        }
        return $tmp;
    }
    // }}}
    // {{{ public static function get_statuses($type = NULL)
    public static function get_statuses($type = NULL)
    {
        $params = array();
        $spec = array(
            'select' => array(
                '*'
            ),
            'from' => 'EcommerceOrderStatus eos',
            'orderBy' => 'eos.type ASC, eos.name ASC',
        );
        if (is_string($type))
        {
            $spec['where'] = 'eos.type = ?';
            $params[] = $type;
        }
        $statuses = dql_exec($spec, $params);
        return $statuses;
    }
    // }}}

    // {{{ public static function set_cart($id, $data)
    public static function set_cart($id, $data)
    {
        $cart = self::get_cart($id);
        if (is_null($cart))
        {
            $ec = new EcommerceCart;
            $ec->identifier = $id;
        }
        else
        {
            $ect = Doctrine::getTable('EcommerceCart');
            $ec = $ect->findOneByIdentifier($id);
            $ec->data = $data;
        }
        $ec->data = $data;
        if ($ec->isValid())
        {
            $ec->save();
            $ec->free();
            return TRUE;
        }
        else
        {
            $ec->free();
            return FALSE;
        }
    }
    // }}}

    // {{{ public static function delete_cart($id)
    public static function delete_cart($id)
    {
        $ect = Doctrine::getTable('EcommerceCart');
        $ec = $ect->findOneByIdentifier($id);
        if ($ec !== FALSE)
        {
            $ec->delete();
            $ec->free();
            return TRUE;
        }
        return FALSE;
    }
    // }}}
    // {{{ public static function delete_coupon_by_id($id)
    public static function delete_coupon_by_id($id)
    {
        try
        {
            $query = Doctrine_Query::create()
                ->delete('EcommerceOrderCoupons')
                ->addWhere('coupon_id = ?', $id);
            $deleted = $query->execute();
            
            $eost = Doctrine::getTable('EcommerceCoupon');
            $coupon = $eost->find($id);
            $coupon->delete();
            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    // }}}
    // {{{ public static function delete_gift_card_by_id($id)
    public static function delete_gift_card_by_id($id)
    {
        try
        {
            $query = Doctrine_Query::create()
                ->delete('EcommerceOrderGiftCards')
                ->addWhere('gift_card_id = ?', $id);
            $deleted = $query->execute();

            $eost = Doctrine::getTable('EcommerceGiftCard');
            $gift_card = $eost->find($id);
            $gift_card->delete();
            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    // }}}
    // {{{ public static function delete_status_by_id($id)
    public static function delete_status_by_id($id)
    {
        try
        {
            $eost = Doctrine::getTable('EcommerceOrderStatus');
            $status = $eost->find($id);
            $status->delete();
            return TRUE;
        }
        catch (Exception $e)
        {
            return FALSE;
        }
    }
    // }}}
}
// }}}

?>
