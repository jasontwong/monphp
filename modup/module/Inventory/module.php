<?php

class Inventory
{
    //{{{ constants
    const MODULE_AUTHOR = 'Glenn Yonemitsu';
    const MODULE_DESCRIPTION = 'Inventory Management';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = '';
    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
    }
    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        if (strpos(URI_PATH, '/admin/module/Inventory/') === 0 ||
            strpos(URI_PATH, '/admin/module/Content/') === 0)
        {
            $js[] = '/admin/static/Inventory/js/field.js/';
        }
        return $js;
    }
    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        $css = array();
        if (strpos(URI_PATH, '/admin/module/Inventory/') === 0 ||
            strpos(URI_PATH, '/admin/module/Content/') === 0)
        {
            $css['screen'][] = '/admin/static/Inventory/css/field.css/';
        }
        return $css;
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
        $uri = '/admin/module/Inventory';
        $links = array('Inventory' => array());

        $links['Inventory'][] = '<a href="'.$uri.'/">Groups And Options</a>';
        $links['Inventory'][] = '<a href="'.$uri.'/products/">Product Inventories</a>';
        $links['Inventory'][] = '<a href="'.$uri.'/product_group_new/">Create A Product Group</a>';
        $links['Inventory'][] = '<a href="'.$uri.'/option_group_new/">Create An Option Group</a>';
        return $links;
    }
    //}}}
    //{{{ public function hook_rpc($action = '')
    public function hook_rpc($action = '')
    {
        if ($_SESSION['admin']['logged_in'])
        {
            $method = '_rpc_'.$action;
            $caller = array($this, $method);
            $args = array_slice(func_get_args(), 1);
            return method_exists($this, $method) 
                ? call_user_func_array($caller, $args)
                : '';
        }
    }
    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        $perms = array();
        $perms['Inventory']['add inventory option groups'] = 'Add inventory option groups';
        $perms['Inventory']['edit inventory option groups'] = 'edit inventory option groups';
        $perms['Inventory']['add inventory options'] = 'Add inventory options';
        $perms['Inventory']['edit inventory options'] = 'edit inventory options';
        return $perms;
    }

    //}}}

    // RPC
    //{{{ public function _rpc_get_options($args)
    public function _rpc_get_options($args)
    {
        $option_id = $args['id'];
        $options = $this->get_options($option_id);
        return json_encode($options);
    }
    //}}}
    //{{{ public function _rpc_get_inventory($args)
    public function _rpc_get_inventory($args)
    {
        $product_id = $args['id'];
        $product = $this->get_product($product_id);
    }
    //}}}
    //{{{ public function _rpc_get_product($args)
    public function _rpc_get_product($args)
    {
        $product_id = $args['id'];
        $product = $this->get_product($product_id);
        return json_encode($product);
    }
    //}}}

    // API
    //{{{ static public function get_product_groups()
    static public function get_product_groups()
    {
        $specs = array(
            'select' => array(
                'pg.id', 'pg.name', 
                'pg.inventory_option_group_x as ogx_id', 
                'ogx.name as ogx_name',
                'ogx.description as ogx_description',
                'pg.inventory_option_group_y as ogy_id',
                'ogy.name as ogy_name',
                'ogy.description as ogy_description',
            ),
            'from' => 'InventoryProductGroup pg',
            'orderBy' => 'pg.name ASC'
        );
        $dql = dql_build($specs);
        $dql->leftJoin('pg.InventoryOptionGroupX as ogx')
            ->leftJoin('pg.InventoryOptionGroupY as ogy');
        $product_groups = $dql->execute(array(), Doctrine::HYDRATE_ARRAY);
        return $product_groups;
    }
    //}}}
    //{{{ static public function get_product_group($id)
    static public function get_product_group($id)
    {
        $specs = array(
            'select' => array(
                'pg.id', 'pg.name', 
                'pg.inventory_option_group_x as ogx_id', 
                'ogx.name as ogx_name',
                'ogx.description as ogx_description',
                'pg.inventory_option_group_y as ogy_id',
                'ogy.name as ogy_name',
                'ogy.description as ogy_description',
            ),
            'from' => 'InventoryProductGroup pg',
            'orderBy' => 'pg.name ASC',
            'where' => 'pg.id = ?'
        );
        $dql = dql_build($specs);
        $dql->leftJoin('pg.InventoryOptionGroupX as ogx')
            ->leftJoin('pg.InventoryOptionGroupY as ogy');
        $product_groups = $dql->execute(array($id), Doctrine::HYDRATE_ARRAY);
        return $product_groups[0];
    }
    //}}}
    //{{{ static public function get_option_groups()
    static public function get_option_groups()
    {
        $specs = array(
            'select' => array(
                'og.id', 'og.name', 'og.description'
            ),
            'from' => 'InventoryOptionGroup og',
            'orderBy' => 'og.name ASC'
        );
        $option_groups = dql_exec($specs);
        return $option_groups;
    }
    //}}}
    //{{{ static public function get_option_group($id)
    static public function get_option_group($id)
    {
        $specs = array(
            'select' => array(
                'og.id', 'og.name', 'og.description'
            ),
            'from' => 'InventoryOptionGroup og',
            'where' => 'og.id = ?'
        );
        $option_group = array_pop(dql_exec($specs, array($id)));
        return $option_group;
    }
    //}}}
    //{{{ static public function get_products($product_group = NULL)
    static public function get_products($product_group = NULL)
    {
        $specs = array(
            'select' => array(
                'pg.id as group_id', 'pg.name as group_name',
                'p.id as product_id', 'p.name as product_name'
            ),
            'from' => 'InventoryProduct p',
            'leftJoin' => 'p.InventoryProductGroup pg',
            'orderBy' => 'pg.name ASC, p.name ASC'
        );
        $params = array();
        if (!is_null($product_group))
        {
            $specs['where'] = 'pg.id = ?';
            $params[] = $product_group;
        }
        return dql_exec($specs, $params);
    }
    //}}}
    //{{{ static public function get_product($id)
    /**
     * 
     * return format
     *  $product['product_name']
                ['product_id']
                ['group_name']
                ['group_id']
                ['inventory'] = array() 
                ['options_x'] = array() of option info
                ['options_y'] = array() of option info
                ['x'] = array() of option ids only
                ['y'] = array() of option ids only
     */
    static public function get_product($id)
    {
        // product
        $specs = array(
            'select' => array(
                'pg.id as group_id', 
                'pg.name as group_name',
                'pg.inventory_option_group_x as group_x',
                'pg.inventory_option_group_y as group_y',
                'p.id as product_id', 
                'p.name as product_name'
            ),
            'from' => 'InventoryProduct p',
            'where' => 'p.id = ?',
            'leftJoin' => 'p.InventoryProductGroup pg'
        );
        $product = dql_exec($specs, array($id));
        $product = $product[0];
        unset($product['InventoryProductGroup']);
        unset($product['id']);

        // options
        $specs = array(
            'select' => array(
                'po.inventory_option_id as id', 
                'po.axis as axis',
                'po.weight as weight',
                'o.name as name', 
                'o.display_name as display_name', 
                'o.image as image'
            ),
            'from' => 'InventoryProductOption po',
            'leftJoin' => 'po.InventoryOption o',
            'where' => 'po.inventory_product_id = ?',
            'orderBy' => 'po.axis ASC, po.weight ASC'
        );
        $options = dql_exec($specs, array($id));
        $product['options_x'] = array();
        $product['options_y'] = array();
        $product['x'] = array();
        $product['y'] = array();
        foreach ($options as $option)
        {
            $axis = $option['axis'];
            $key = 'options_'.$axis;
            unset($option['InventoryOption']);
            unset($option['weight']);
            unset($option['axis']);
            $product[$key][] = $option;
            $product[$axis][] = $option['id'];
        }

        // inventory
        $specs = array(
            'select' => array(
                'q.quantity',
                'q.weight_x as x',
                'q.weight_y as y'
            ),
            'from' => 'InventoryProductQuantity q',
            'where' => 'q.inventory_product_id = ?',
            'orderBy' => 'q.weight_y ASC, q.weight_x ASC'
        );
        $inventory = dql_exec($specs, array($id));
        foreach ($inventory as $qty)
        {
            $product['inventory'][$qty['y']][$qty['x']] = $qty['quantity'];
        }

        return $product;
    }
    //}}}
    //{{{ static public function get_options($option_group_id)
    static public function get_options($option_group_id)
    {
        $specs = array(
            'select' => array(
                'o.id', 'o.weight', 'o.name',
                'o.display_name', 'o.image'
            ),
            'from' => 'InventoryOption o',
            'where' => 'o.inventory_option_group_id = ?',
            'orderBy' => 'o.weight ASC, o.name ASC'
        );
        $options = dql_exec($specs, array($option_group_id));
        return $options;
    }
    //}}}
    //{{{ static public function get_option($option_id)
    static public function get_option($option_id)
    {
        $specs = array(
            'select' => array(
                'o.id', 'o.weight', 'o.name',
                'o.display_name', 'o.image',
                'og.name as group_name', 
                'og.description as group_description'
            ),
            'from' => 'InventoryOption o',
            'leftJoin' => 'o.InventoryOptionGroup og',
            'where' => 'o.id = ?',
            'orderBy' => 'o.weight ASC, o.name ASC'
        );
        $options = dql_exec($specs, array($option_id));
        unset($options[0]['InventoryOptionGroup']);
        return $options[0];
    }
    //}}}

    //{{{ static public function save_product_and_inventory($product)
    /**
     * $product should be similar to the format returned from Inventory::get_product()
     *  $product['product_name']
                ['group_id']
                ['inventory'] = array() 
                ['x'] = array() of option ids only
                ['y'] = array() of option ids only
        all data must already be json decoded
     */
    static public function save_product_and_inventory($product)
    {
        $db = Doctrine_Manager::connection();
        try
        {
            $db->beginTransaction();
            $id = self::save_product($product['group_id'], $product['product_name']);
            self::save_product_options($id, 'x', $product['x']);
            self::save_product_options($id, 'y', $product['y']);
            self::save_product_inventory($id, $product['inventory']);
            $db->commit();
            return $id;
        }
        catch (exception $e)
        {
            $db->rollback();
            return FALSE;
        }
    }
    //}}}
    //{{{ static public function save_product_group($name, $option_x = '', $option_y = '')
    static public function save_product_group($name, $option_x = '', $option_y = '')
    {
        $pg = new InventoryProductGroup;
        $pg->name = $name;
        $pg->inventory_option_group_x = $option_x;
        $pg->inventory_option_group_y = $option_y;
        $pg->save();
        return $pg->id;
    }
    //}}}
    //{{{ static public function save_option_group($name, $description)
    static public function save_option_group($name, $description)
    {
        $og = new InventoryOptionGroup;
        $og->name = $name;
        $og->description = $description;
        $og->save();
        return $og->id;
    }
    //}}}
    //{{{ static public function save_option($group_id, $name, $display_name, $image, $weight)
    static public function save_option($group_id, $name, $display_name, $image, $weight)
    {
        $o = new InventoryOption;
        $o->inventory_option_group_id = $group_id;
        $o->name = $name;
        $o->display_name = $display_name;
        $o->image = $image;
        $o->weight = $weight;
        $o->save();
        return $o->id;
    }
    //}}}
    //{{{ static public function save_product($group_id, $name)
    static public function save_product($group_id, $name)
    {
        $p = new InventoryProduct;
        $p->inventory_product_group_id = $group_id;
        $p->name = $name;
        $p->save();
        return $p->id;
    }
    //}}}
    //{{{ static public function save_product_options($product_id, $axis, $option_ids)
    static public function save_product_options($product_id, $axis, $option_ids)
    {
        foreach ($option_ids as $weight => $option_id)
        {
            $po = new InventoryProductOption;
            $po->inventory_product_id = $product_id;
            $po->inventory_option_id = $option_id;
            $po->axis = $axis;
            $po->weight = $weight;
            $po->save();
            $po->free(TRUE);
        }
    }
    //}}}
    //{{{ static public function save_product_inventory($product_id, $inventory)
    static public function save_product_inventory($product_id, $inventory)
    {
        foreach ($inventory as $y => $row)
        {
            foreach ($row as $x => $qty)
            {
                $pq = new InventoryProductQuantity;
                $pq->inventory_product_id = $product_id;
                $pq->weight_x = $x;
                $pq->weight_y = $y;
                $pq->quantity = $qty;
                $pq->save();
                $pq->free(TRUE);
            }
        }
    }
    //}}}
    
    //{{{ static public function update_product_and_inventory($id, $product)
    /**
     * $product should be similar to the format returned from Inventory::get_product()
     *  $product['product_name']
                ['inventory'] = array() 
                ['x'] = array() of option ids only
                ['y'] = array() of option ids only
        all data must already be json decoded
     */
    static public function update_product_and_inventory($id, $product)
    {
        $db = Doctrine_Manager::connection();
        try
        {
            $db->beginTransaction();
            // clear everything out first, then save again
            $param = array($id);
            $del = array(
                'delete' => 'InventoryProductQuantity',
                'where' => 'inventory_product_id = ?'
            );
            dql_exec($del, $param);
            $del = array(
                'delete' => 'InventoryProductOption',
                'where' => 'inventory_product_id = ?'
            );
            dql_exec($del, $param);

            // start saving
            self::update_product_name($id, $product['product_name']);
            self::save_product_options($id, 'x', $product['x']);
            self::save_product_options($id, 'y', $product['y']);
            self::save_product_inventory($id, $product['inventory']);

            $db->commit();
            return TRUE;
        }
        catch (exception $e)
        {
            $db->rollback();
            return FALSE;
        }
    }
    //}}}
    //{{{ static public function update_product_group($id, $name, $option_x = '', $option_y = '')
    static public function update_product_group($id, $name, $option_x = '', $option_y = '')
    {
        Doctrine_Query::create()
            ->update('InventoryProductGroup')
            ->set('name', '?', $name)
            ->set('inventory_option_group_x', '?', $option_x)
            ->set('inventory_option_group_y', '?', $option_y)
            ->where('id = ?', $id)
            ->execute();
    }
    //}}}
    //{{{ static public function update_product_name($id, $name)
    static public function update_product_name($id, $name)
    {
        return Doctrine_Query::create()
            ->update('InventoryProduct')
            ->set('name', '?', $name)
            ->where('id = ?', $id)
            ->execute();
    }
    //}}}
    //{{{ static public function update_option_group($id, $name, $description)
    static public function update_option_group($id, $name, $description)
    {
        Doctrine_Query::create()
            ->update('InventoryOptionGroup')
            ->set('name', '?', $name)
            ->set('description', '?', $description)
            ->where('id = ?', $id)
            ->execute();
    }
    //}}}
    //{{{ static public function update_option($id, $group_id, $name, $display_name, $image, $weight)
    static public function update_option($id, $group_id, $name, $display_name, $image, $weight)
    {
        Doctrine_Query::create()
            ->update('InventoryOption')
            ->set('inventory_option_group_id', '?', $group_id)
            ->set('name', '?', $name)
            ->set('display_name', '?', $display_name)
            ->set('image', '?', $image)
            ->set('weight', '?', $weight)
            ->where('id = ?', $id)
            ->execute();
    }
    //}}}

    //{{{ static public function delete_option($id)
    static public function delete_option($id)
    {
        Doctrine_Query::create()
            ->delete('InventoryOption')
            ->where('id = ?', $id)
            ->execute();
    }
    //}}}
    //{{{ static public function delete_product($id)
    static public function delete_product($id)
    {
        $db = Doctrine_Manager::connection();
        try
        {
            $db->beginTransaction();

            $param = array($id);
            $del = array(
                'delete' => 'InventoryProductQuantity',
                'where' => 'inventory_product_id = ?'
            );
            dql_exec($del, $param);
            $del = array(
                'delete' => 'InventoryProductOption',
                'where' => 'inventory_product_id = ?'
            );
            dql_exec($del, $param);
            $del = array(
                'delete' => 'InventoryProduct',
                'where' => 'id = ?'
            );
            dql_exec($del, $param);

            $db->commit();
            return TRUE;
        }
        catch (exception $e)
        {
            $db->rollback();
            return FALSE;
        }
    }
    //}}}

    //{{{ static public function product_exists($id)
    static public function product_exists($id)
    {

        $param = array($id);
        $select = array(
            'select' => 'id',
            'from' => 'InventoryProduct',
            'where' => 'id = ?'
        );
        $result = dql_exec($select, $param);
        return count($result) > 0;
    }
    //}}}

}

?>
