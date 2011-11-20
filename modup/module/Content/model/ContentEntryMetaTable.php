<?php

class ContentEntryMetaTable extends Doctrine_Table
{
    //{{{ public function queryEntriesList()
    /**
     * Returns a Doctrine_Query object with basic entry info
     */
    public function queryEntriesList()
    {
        return Doctrine_Query::create()
               ->select('
                em.id, em.created, 
                eti.modified as modified, eti.title as title,
                ety.id, ety.name
               ')
               ->from('ContentEntryMeta em')
               ->leftJoin('em.ContentEntryTitle eti')
               ->leftJoin('em.ContentEntryType ety');
    }

    //}}}
    //{{{ public function queryAllEntries($order = NULL)
    public function queryAllEntries($order = NULL)
    {
        if (is_null($order))
        {
            $order = 'modified DESC';
        }
        return $this->queryEntriesList()
               ->where('eti.revision = em.revision')
               ->orderBy($order);
    }

    //}}}
    //{{{ public function queryTypeEntries($type, $ordering = NULL, $order = NULL)
    /**
     * Gets query for entries for the specified type
     * @param int $type type id
     * @param $ordering bool indicates manual ordering. If null, use type's setting
     * @return Doctrine_Query
     */
    public function queryTypeEntries($type, $ordering = NULL, $order = NULL)
    {
        if (is_null($ordering))
        {
            $cett = Doctrine::getTable('ContentEntryType');
            $info = $cett->find($type)->toArray();
            $ordering = $info['ordering'];
        }
        if (is_null($order))
        {
            $order = 'modified DESC';
        }
        return $this->queryEntriesList()
               ->where('eti.revision = em.revision AND ety.id = ?', $type)
               ->orderby($ordering ? 'em.weight ASC, '.$order : $order);
    }

    //}}}
    //{{{ public function findAllEntries()
    public function findAllEntries()
    {
        $q = $this->queryEntriesList();
        return $q->where('eti.revision = em.revision')
                 ->orderBy('modified DESC')
                 ->fetchArray();
    }

    //}}}
    //{{{ public function findByIds($ids)
    /**
     * @param array $ids array of ids
     */
    public function findByIds($ids)
    {
        return Doctrine_Query::create()
               ->from('ContentEntryMeta em')
               ->whereIn('em.id', $ids);
    }

    //}}}
    // {{{ public function filterByEntriesFieldOrder($type, $field, $limit = NULL, $order = SORT_ASC, $filters = array(), $ids = array())
    /**
     * @param string $type type name
     * @param string $field field meta name to order by
     * @param int $limit how many results to return
     * @param int $offset how many records to offset
     * @param int $order built in PHP constants for sorting
     * @param array $filters use pre-defined module filters (TODO: maybe abstract this into a hook)
     * @param array $ids array of ids to start with, empty for all
     */
    public function filterByEntriesFieldOrder($type, $field, $limit = NULL, $offset = 0, $order = SORT_ASC, $filters = array(), $ids = array())
    {
        $entry_type = Doctrine_Query::create()
                      ->select('
                        ety.name, ety.description, 
                      ')
                      ->from('ContentEntryType ety')
                      ->where('ety.name = ?')
                      ->fetchOne(array($type), Doctrine::HYDRATE_ARRAY);
        if (empty($entry_type))
        {
            throw new Exception('Entry type: "'.$type.'" does not exist');
        }
        $result['type'] = $entry_type;
        $result['data'] = array();
        // {{{ additional filters
        foreach ($filters as $filter)
        {
            try
            {
                switch ($filter['type'])
                {
                    case 'Taxonomy':
                        $ids = empty($ids)
                            ? $this->filterByTaxonomy($type, $filter['terms'], $filter['scheme'], array(), FALSE, FALSE, TRUE)
                            : array_intersect($ids, $this->filterByTaxonomy($type, $filter['terms'], $filter['scheme'], array(), FALSE, FALSE, TRUE));
                    break;
                    case 'Field':
                        $ids = ake('operator', $filter)
                            ? $this->filterByField($type, $filter['field'], $filter['value'], $filter['operator'], $ids, TRUE)
                            : $this->filterByField($type, $filter['field'], $filter['value'], '=', $ids, TRUE);
                    break;
                    default:
                        continue;
                }
            }
            catch (Exception $e)
            {
                $ids = array();
            }
            if (empty($ids))
            {
                return $result;
            }
        }

        // }}}
        $entry_metas = Doctrine_Query::create()
                      ->select('fd.content_entry_meta_id')
                      ->from('ContentFieldData fd')
                      ->leftJoin('fd.ContentEntryMeta em')
                      ->leftJoin('em.ContentEntryType et')
                      ->leftJoin('fd.ContentFieldMeta fm')
                      ->leftJoin('fm.ContentFieldType ft')
                      ->where('fd.revision = em.revision')
                      ->addWhere('et.name = ?')
                      ->andWhere('ft.name = ?')
                      ->andWhereIn('em.id', $ids);
        $fetch_array = array($type, $field);
        if ($order === SORT_DESC)
        {
            $entry_metas->orderBy('fd.cdata DESC');
        }
        elseif ($order === SORT_ASC)
        {
            $entry_metas->orderBy('fd.cdata ASC');
        }
        else
        {
            $entry_metas->orderBy('em.weight ASC');
        }
        if (is_numeric($limit))
        {
            $entry_metas->limit($limit);
        }
        $entry_metas->offset($offset);
        $entry_metas = $entry_metas->fetchArray($fetch_array);
        if ($entry_metas === FALSE || empty($entry_metas))
        {
            throw new Exception('Entry does not exist. Type: '.$type.' Field: '.$field);
        }

        $cfdt = Doctrine::getTable('ContentFieldData');
        foreach ($entry_metas as $entry_meta)
        {
            $entry = $this->findCurrentEntryTitle($entry_meta['content_entry_meta_id']);
            $entry[$field] = $cfdt->getEntryDataByName($entry['id'], $entry['revision'], $field);
            $result['data'][] = $entry;
        }
        return $result;
    }

    //}}}
    //{{{ public function filterByField($type, $field, $value, $operator = '=', $ids = array(), $return_ids = FALSE, $ordering = TRUE)
    /**
     * @param string $type type name
     * @param string $field field meta name to search in
     * @param string $value cdata to search
     * @param string $operator how to compare field to data, default is =
     * @param array $ids a set of ids to check, default is all
     * @param bool $return_ids will return meta_ids instead of full entries
     * @param bool $ordering orders by weight if true
     */
    public function filterByField($type, $field, $value, $operator = '=', $ids = array(), $return_ids = FALSE, $ordering = TRUE)
    {
        $entry_type = Doctrine_Query::create()
                      ->select('
                        ety.name, ety.description, 
                      ')
                      ->from('ContentEntryType ety')
                      ->where('ety.name = ?')
                      ->fetchOne(array($type), Doctrine::HYDRATE_ARRAY);
        if (empty($entry_type))
        {
            throw new Exception('Entry type: "'.$type.'" does not exist');
        }
        $result['type'] = $entry_type;

        $entry_metas = Doctrine_Query::create()
                      ->select('fd.content_entry_meta_id')
                      ->from('ContentFieldData fd')
                      ->leftJoin('fd.ContentEntryMeta em')
                      ->leftJoin('em.ContentEntryType et')
                      ->leftJoin('fd.ContentFieldMeta fm')
                      ->leftJoin('fm.ContentFieldType ft')
                      ->where('fd.revision = em.revision')
                      ->andWhere('et.name = ?')
                      ->andWhere('ft.name = ?');
        $fetch_array = array($result['type']['name'], $field);
        $entry_metas = $entry_metas->andWhere('fd.cdata '.$operator.' ?');
        $fetch_array[] = $operator === 'LIKE' 
            ? '%'.$value.'%' 
            : $value;
        if ($ordering)
        {
            $entry_metas = $entry_metas->orderBy('em.weight ASC');
        }
        $entry_metas->andWhereIn('em.id', $ids);
        $entry_metas = $entry_metas->fetchArray($fetch_array);
        if ($entry_metas === FALSE || empty($entry_metas))
        {
            throw new Exception('Entry does not exist. Type: '.$type.' Field: '.$field.' Value: '.$value);
        }

        $result['data'] = $meta_ids = array();
        foreach ($entry_metas as $entry_meta)
        {
            if ($return_ids)
            {
                $meta_ids[] = $entry_meta['content_entry_meta_id'];
            }
            else
            {
                $result['data'][] = $this->findCurrentEntry($entry_meta['content_entry_meta_id']);
            }
        }
        return !empty($meta_ids)
            ? $meta_ids
            : $result;
    }

    //}}}
    //{{{ public function filterBySlug($type, $slug, $return_fields = array(), $ordering = TRUE, $exact = TRUE)
    /**
     * @param string $type type name
     * @param string $slug slug to search
     * @param array $return_fields if only certain fields want to be returned
     * @param bool $ordering orders by weight if true
     * @param bool $exact use exact matching
     */
    public function filterBySlug($type, $slug, $return_fields = array(), $ordering = TRUE, $exact = TRUE)
    {
        $cett = Doctrine::getTable('ContentEntryType');
        $entry_type = $cett->findOneByName($type);
        if ($entry_type === FALSE)
        {
            throw new Exception('Entry type: "'.$type.'" does not exist');
        }
        $result['type'] = $entry_type->toArray();

        $entry_metas = Doctrine_Query::create()
                       ->select('et.content_entry_meta_id')
                       ->from('ContentEntryTitle et')
                       ->leftJoin('et.ContentEntryMeta em')
                       ->leftJoin('em.ContentEntryType ety')
                       ->where('et.revision = em.revision')
                       ->andWhere('ety.name = ?');
        $fetch_array = array($result['type']['name']);
        if ($exact)
        {
            $entry_metas = $entry_metas->andWhere('et.slug = ?');
            $fetch_array[] = $slug;
        }
        else
        {
            $entry_metas = $entry_metas->andWhere('et.slug LIKE ?');
            $fetch_array[] = '%'.$slug.'%';
        }
        if ($ordering)
        {
            $entry_metas = $entry_metas->orderBy('em.weight ASC');
        }
        $entry_metas = $entry_metas->fetchArray($fetch_array);
        if ($entry_metas === FALSE || empty($entry_metas))
        {
            throw new Exception('Entry does not exist. Type: '.$type.' Slug: '.$slug);
        }

        $result['data'] = array();
        foreach ($entry_metas as $entry_meta)
        {
            if (is_null($return_fields))
            {
                $temp = $this->findCurrentEntryTitle($entry_meta['content_entry_meta_id']);
                $result['data'][] = $temp;
            }
            elseif (!empty($return_fields))
            {
                $cfdt = Doctrine::getTable('ContentFieldData');
                $temp = $this->findCurrentEntryTitle($entry_meta['content_entry_meta_id']);
                foreach ($return_fields as $field_name)
                {
                    if ($field_name === '_TAXONOMY_')
                    {
                        $taxonomy = Module::h('content_get_entry_taxonomy', 'Taxonomy', $result['type']['id'], $id);
                        $temp += $taxonomy['Taxonomy'];
                    }
                    else
                    {
                        $temp[$field_name] = $cfdt->getEntryDataByName($temp['id'], $temp['revision'], $field_name);
                    }
                }
                $result['data'][] = $temp;
            }
            else
            {
                $result['data'][] = $this->findCurrentEntry($entry_meta['content_entry_meta_id']);
            }
        }
        return $result;
    }

    //}}}
    //{{{ public function filterByType($type, $return_fields = array(), $return_ids = FALSE, $ordering = TRUE)
    /**
     * @param string $type type name
     * @param array $return_fields if only certain fields want to be returned
     * @param bool $return_ids will return meta_ids instead of full entries
     * @param bool $ordering orders by weight if true
     */
    public function filterByType($type, $return_fields = array(), $return_ids = FALSE, $ordering = TRUE)
    {
        $cett = Doctrine::getTable('ContentEntryType');
        $entry_type = $cett->findOneByName($type);
        if ($entry_type === FALSE)
        {
            throw new Exception('Entry type: "'.$type.'" does not exist');
        }
        $result['type'] = $entry_type->toArray();

        $entry_metas = Doctrine_Query::create()
                       ->select('et.content_entry_meta_id')
                       ->from('ContentEntryTitle et')
                       ->leftJoin('et.ContentEntryMeta em')
                       ->leftJoin('em.ContentEntryType ety')
                       ->where('et.revision = em.revision')
                       ->andWhere('ety.name = ?');
        $fetch_array = array($result['type']['name']);
        if ($ordering)
        {
            $entry_metas = $entry_metas->orderBy('em.weight ASC');
        }
        $entry_metas = $entry_metas->fetchArray($fetch_array);
        if ($entry_metas === FALSE || empty($entry_metas))
        {
            throw new Exception('Entry does not exist. Type: '.$type);
        }

        $result['data'] = $meta_ids = array();
        foreach ($entry_metas as $entry_meta)
        {
            if ($return_ids)
            {
                $meta_ids[] = $entry_meta['content_entry_meta_id'];
            }
            elseif (is_null($return_fields))
            {
                $temp = $this->findCurrentEntryTitle($entry_meta['content_entry_meta_id']);
                $result['data'][] = $temp;
            }
            elseif (!empty($return_fields))
            {
                $cfdt = Doctrine::getTable('ContentFieldData');
                $temp = $this->findCurrentEntryTitle($entry_meta['content_entry_meta_id']);
                foreach ($return_fields as $field_name)
                {
                    if ($field_name === '_TAXONOMY_')
                    {
                        $taxonomy = Module::h('content_get_entry_taxonomy', 'Taxonomy', $result['type']['id'], $id);
                        $temp += $taxonomy['Taxonomy'];
                    }
                    else
                    {
                        $temp[$field_name] = $cfdt->getEntryDataByName($temp['id'], $temp['revision'], $field_name);
                    }
                }
                $result['data'][] = $temp;
            }
            else
            {
                $result['data'][] = $this->findCurrentEntry($entry_meta['content_entry_meta_id']);
            }
        }
        return !empty($meta_ids)
            ? $meta_ids
            : $result;
    }

    //}}}
    // {{{ public function filterByTaxonomy($type, $terms, $scheme = 'default', $return_fields = array(), $negate = FALSE, $deep = TRUE, $return_ids = FALSE)
    /**
     * This function will filter out any entries that don't match any of the terms.
     * If $negate is true, then filter out entries that do match.
     * If the elements in the $terms array are array, it will be considered a 
     * set of terms to match (similar to using a logical "or" statement).
     *
     * Examples:
     * array('1', '2', '3') // if 1 or 2 or 3
     * array(array('1', '2', array('3'))) // if 1 or 2 and 3
     * array(array('1', '2'), array('3', '4')) // if (1 or 2) and (3 or 4)
     * 
     * @param string $type type name
     * @param array $terms array of terms to filter by
     * @param string $scheme the scheme name associated with the taxonomy module
     * @param array $return_fields if only certain fields want to be returned
     * @param bool $negate look at the child terms when filtering
     * @param bool $deep look at the child terms when filtering
     * @param bool $return_ids if TRUE, returns meta_ids instead of entry arrays
     * @return mixed
     */
    public function filterByTaxonomy($type, $terms, $scheme = 'default', $return_fields = array(), $negate = FALSE, $deep = TRUE, $return_ids = FALSE)
    {
        if (!Module::is_active('Taxonomy'))
        {
            throw new Exception('Taxonomy module is not enabled');
        }
        elseif (!is_array($terms))
        {
            throw new Exception('Terms is not an array');
        }
        $cett = Doctrine::getTable('ContentEntryType');
        $entry_type = $cett->findOneByName($type);
        if ($entry_type === FALSE)
        {
            throw new Exception('Entry type: "'.$type.'" does not exist');
        }
        $result['type'] = $entry_type->toArray();
        $entry_type->free();
        $taxm = new TaxonomyManager('Content', $result['type']['id']);
        $terms_array = !empty($terms) && is_array($terms[0])
            ? $terms
            : array($terms);
        $result['data'] = $meta_ids = array();
        // {{{ loop through terms
        foreach ($terms_array as $k => $terms)
        {
            if (!is_array($terms))
            {
                throw new Exception('Terms is not an array');
            }
            if ($deep)
            {
                $all_terms = $taxm->get_parent_terms($scheme, $terms, TRUE);
                foreach ($all_terms as $term)
                {
                    if (!in_array($term['term'], $terms))
                    {
                        $terms[] = $term['term'];
                    }
                }
            }
            $tax_metas = $taxm->get_entries($terms, $scheme, $negate);
            foreach ($tax_metas as $tax_meta)
            {
                $meta_ids[$k][] = $tax_meta['entry_id'];
            }
        }

        // }}}
        $mids = count($meta_ids)
            ? array_pop($meta_ids)
            : $meta_ids;

        // if multiple term arrays
        if (count($meta_ids))
        {
            foreach ($meta_ids as $mid)
            {
                $mids = array_intersect($mids, $mid);
            }
        }
        if ($return_ids)
        {
            return $mids;
        }
        foreach ($mids as $id)
        {
            try
            {
                if (is_null($return_fields))
                {
                    $temp = $this->findCurrentEntryTitle($id);
                    $result['data'][] = $temp;
                }
                elseif (!empty($return_fields))
                {
                    $cfdt = Doctrine::getTable('ContentFieldData');
                    $temp = $this->findCurrentEntryTitle($id);
                    foreach ($return_fields as $field_name)
                    {
                        if ($field_name === '_TAXONOMY_')
                        {
                            $taxonomy = Module::h('content_get_entry_taxonomy', 'Taxonomy', $result['type']['id'], $id);
                            $temp += $taxonomy['Taxonomy'];
                        }
                        else
                        {
                            $temp[$field_name] = $cfdt->getEntryDataByName($temp['id'], $temp['revision'], $field_name);
                        }
                    }
                    $result['data'][] = $temp;
                }
                else
                {
                    $result['data'][] = $this->findCurrentEntry($id);
                }
            }
            catch (Exception $e)
            {
            }
        }

        return $result;
    }
    //}}}
    //{{{ public function findCurrentEntry($id)
    /**
     * Gets everything about an entry including categories and field groupings
     * The data returned is based on the current revision #, not the latest
     * @param integer $id entry id
     * @return array
     */
    public function findCurrentEntry($id)
    {
        $cfgt = Doctrine::getTable('ContentFieldGroup');
        $cftt = Doctrine::getTable('ContentFieldType');
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        $cfdt = Doctrine::getTable('ContentFieldData');

        $entry = Doctrine_Query::create()
                 ->select('
                    em.id, em.created, em.revision, 
                    em.content_entry_type_id, em.revisions,
                    em.start_date, em.end_date, em.weight,
                    eti.modified as modified, eti.title as title, eti.slug as slug,
                 ')
                 ->from('ContentEntryMeta em')
                 ->leftJoin('em.ContentEntryTitle eti')
                 ->where('eti.revision = em.revision AND em.id = ?', $id)
                 ->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
        if (!$entry)
        {
            throw new Exception('Entry with id '.$id.' does not exist');
        }
        unset($entry['ContentEntryMeta']);

        $entry['field_groups'] = $cfgt->fieldByType($entry['content_entry_type_id']);

        if (Module::is_active('Taxonomy'))
        {
            $taxonomy = Module::h('content_get_entry_taxonomy', 'Taxonomy', $entry['content_entry_type_id'], $id);
            $entry = array_merge($entry, $taxonomy['Taxonomy']);
        }

        foreach ($entry['field_groups'] as $id => &$group)
        {
            $group = array(
                'fields' => array(),
                'id' => $id,
                'name' => $group
            );
        }

        $fields = $cftt->findByGroupIds(array_keys($entry['field_groups']))->fetchArray();
        $data = $cfdt->findEntryData($entry['id'], $entry['revision'])->fetchArray();
        $field_data = array();
        foreach ($data as $row)
        {
            $fm = $cfmt->find($row['content_field_meta_id']);
            if ($fm !== FALSE)
            {
                $akey = $row['akey'];
                $field_data[$fm->content_field_type_id][$fm->name][$akey][] = $row;
                $fm->free();
            }
        }
        foreach ($fields as $field)
        {
            $fid = $field['id'];
            $gid = $field['content_field_group_id'];
            $entry_field = $field;
            if (!isset($field_data[$fid]))
            {
                $field_data[$fid] = array();
            }
            $entry_field['data'] = Field::quick_act('read', $field['type'], $field_data[$fid]);
            $entry['field_groups'][$gid]['fields'][$fid] = $entry_field;
            if ($entry_field['multiple'])
            {
                $temp = array();
                foreach ($entry['field_groups'][$gid]['fields'][$fid]['data'] as $key => $another_field)
                {
                    foreach ($another_field as $akey => $data)
                    {
                        $temp[$akey][$key] = $data;
                    }
                }
                $entry[$field['name']] = $temp;
            }
            else
            {
                $temp = array();
                foreach ($entry['field_groups'][$gid]['fields'][$fid]['data'] as $key => $fdata)
                {
                    $temp[$key] = array_pop($fdata);
                }
                $entry['field_groups'][$gid]['fields'][$fid]['data'] = $entry[$field['name']] = $temp;
            }
        }
        return $entry;
    }

    //}}}
    //{{{ public function findCurrentEntryTitle($id)
    /**
     * Gets the title of an entry and a few other bits
     * The data returned is based on the current revision #, not the latest
     * @param integer $id entry meta id
     * @return array
     */
    public function findCurrentEntryTitle($id)
    {
        $entry = Doctrine_Query::create()
                 ->select('
                    em.id, em.created, em.revision, em.revisions, em.content_entry_type_id, em.weight,
                    eti.modified as modified, eti.title as title, eti.slug as slug,
                 ')
                 ->from('ContentEntryMeta em')
                 ->leftJoin('em.ContentEntryTitle eti')
                 ->where('eti.revision = em.revision AND em.id = ?', $id)
                 ->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
        if (!$entry)
        {
            throw new Exception('Entry with id '.$id.' does not exist');
        }
        unset($entry['ContentEntryTitle']);

        return $entry;
    }

    //}}}
    //{{{ public function findEntryContent($id)
    /**
     * Gets everything about an entry including categories and field groupings
     * The data returned is based on the current revision #, not the latest
     * @param integer $id entry id
     * @return array
     */
    public function findEntryContent($id)
    {
        $entry = $this->findCurrentEntry($id);
        if (eka($entry, 'field_groups'))
        {
            foreach ($entry['field_groups'] as &$fields)
            {
                if (eka($fields, 'fields'))
                {
                    foreach ($fields['fields'] as &$field)
                    {
                        if (is_array($field['data']))
                        {
                            foreach ($field['data'] as &$data)
                            {
                                $data = Field::act('content', $field['type'], $data);
                            }
                        }
                        else
                        {
                            $field['data'] = Field::act('content', $field['type'], $field['data']);
                        }
                    }
                }
            }
        }
        return $entry;
    }

    //}}}
    //{{{ public function findEntryRevision($id, $rev = NULL)
    /**
     * Gets everything about an entry including categories and field groupings
     * The data returned is based on the current revision #, not the latest
     * @param integer $id entry id
     * @return array
     */
    public function findEntryRevision($id, $rev = NULL)
    {
        $cfgt = Doctrine::getTable('ContentFieldGroup');
        $cftt = Doctrine::getTable('ContentFieldType');
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        $cfdt = Doctrine::getTable('ContentFieldData');

        $entry = Doctrine_Query::create()
                 ->select('
                    em.id, em.created, em.revision, 
                    em.content_entry_type_id, em.revisions,
                    em.status, em.flags, em.date_control, em.start_date, em.end_date,
                    eti.modified as modified, eti.title as title, eti.slug as slug,
                 ')
                 ->from('ContentEntryMeta em')
                 ->leftJoin('em.ContentEntryTitle eti')
                ->where('em.id = ?', $id);
        if (is_null($rev))
        {
            $entry->andWhere('eti.revision = em.revision');
        }
        else
        {
            $entry->andWhere('eti.revision = ?', $rev);
        }
        $entry = $entry->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
        if (!$entry)
        {
            throw new Exception('Entry with id '.$id.' does not exist');
        }
        unset($entry['ContentEntryMeta']);

        $revision = is_null($rev) ? $entry['revision'] : $rev;

        $entry['field_groups'] = $cfgt->fieldByType($entry['content_entry_type_id']);
        foreach ($entry['field_groups'] as $id => &$group)
        {
            $group = array(
                'fields' => array(),
                'id' => $id,
                'name' => $group
            );
        }

        $fields = $cftt->findByGroupIds(array_keys($entry['field_groups']))->fetchArray();
        $data = $cfdt->findEntryData($entry['id'], $revision)->fetchArray();
        $field_data = array();
        foreach ($data as $row)
        {
            $fm = $cfmt->find($row['content_field_meta_id']);
            $akey = $row['akey'];
            $field_data[$fm->content_field_type_id][$fm->name][$akey][] = $row;
            $fm->free();
        }
        foreach ($fields as $field)
        {
            $fid = $field['id'];
            $gid = $field['content_field_group_id'];
            $entry_field = $field;
            if (!isset($field_data[$fid]))
            {
                $field_data[$fid] = array();
            }
            $entry_field['data'] = Field::quick_act('read', $field['type'], $field_data[$fid]);
            $entry['field_groups'][$gid]['fields'][$fid] = $entry_field;
            if ($entry_field['multiple'])
            {
                $temp = array();
                foreach ($entry['field_groups'][$gid]['fields'][$fid]['data'] as $another_field)
                {
                    $temp = is_array($another_field)
                        ? array_merge($temp, $another_field)
                        : $temp = array('');
                }
                $entry[$field['name']] = $temp;
                unset($temp);
            }
            else
            {
                foreach ($entry['field_groups'][$gid]['fields'][$fid]['data'] as &$data)
                {
                    $data = array_pop($data);
                }
                $entry[$field['name']] =& $entry['field_groups'][$gid]['fields'][$fid]['data'];
            }
        }
        return $entry;
    }

    //}}}
    //{{{ public function findLatestTitles($count = 5)
    public function findLatestTitles($count = 5)
    {
        $q = Doctrine_Query::create()
             ->select('m.id, m.created, t.title')
             ->from('ContentEntryMeta m')
             ->leftJoin('m.ContentEntryTitle t')
             ->where('m.revision = t.revision')
             ->orderBy('m.created DESC')
             ->limit($count);
        return $q;
    }

    //}}}
    //{{{ public function findMostRevised($count = 5)
    public function findMostRevised($count = 5)
    {
        $q = Doctrine_Query::create()
             ->select('m.id, m.created, t.title, m.revisions')
             ->from('ContentEntryMeta m')
             ->leftJoin('m.ContentEntryTitle t')
             ->where('m.revision = t.revision')
             ->orderBy('m.revisions DESC, m.created DESC')
             ->limit($count);
        return $q;
    }

    //}}}
    //{{{ public function saveEntryRevision($entry, $data, $meta)
    /**
     * Adds a revision to the entry meta
     * @param array $entry title, slug, and array of categories
     * @param array $data data fields
     * @param array $meta entry meta id and others if needed
     * @return boolean
     */
    public function saveEntryRevision($entry, $data, $meta)
    {
        $cemt = Doctrine::getTable('ContentEntryMeta');
        $cfdt = Doctrine::getTable('ContentFieldData');

        $emid = $meta['content_entry_meta_id'];

        $entry_meta = $cemt->find($emid);
        $entry_meta->merge($meta);
        $entry_meta->revision = $revision = ++$entry_meta->revisions;
        $entry_meta->save();

        $entry_title = new ContentEntryTitle();
        $entry_title->merge($entry);
        $entry_title->revision = $revision;
        $entry_title->content_entry_meta_id = $emid;
        $entry_title->save();

        $cfdt->saveEntryData($emid, $data, $revision);

    }

    //}}}
    //{{{ public function setEntryRevision($id, $revision)
    public function setEntryRevision($id, $revision)
    {
        $cemt = Doctrine::getTable('ContentEntryMeta');
        $entry = $cemt->find($id);
        if ($entry)
        {
            $entry->revision = (int)$revision;
            $entry->save();
        }
    }

    //}}}
}

?>
