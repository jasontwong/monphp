<?php

class ContentFieldDataTable extends Doctrine_Table
{
    //{{{ public function saveEntryData($entry_id, $data, $revision = 0)
    /**
     * Saves all field data by looping through the $data array
     * The data array must be in a specific format. To best see how to prepare
     * it, look at the field_save method in the field class.
     */
    public function saveEntryData($entry_id, $data, $revision = 0)
    {
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        foreach ($data as $type_id => $fm)
        {
            foreach ($fm as $key => $fd)
            {
                $meta = $cfmt->findByNameAndType($key, $type_id);
                if (!eka($meta, 0, 'id'))
                {
                    continue;
                }
                $field_id = $meta[0]['id'];
                foreach ($fd as $row)
                {
                    $field_data = new ContentFieldData;
                    $field_data->merge($row);
                    $field_data->content_entry_meta_id = $entry_id;
                    $field_data->content_field_meta_id = $field_id;
                    $field_data->revision = $revision;
                    $field_data->meta = (array)$field_data->meta;
                    $field_data->save();
                }
            }
        }
    }

    //}}}
    //{{{ public function savePostData($entry_id, $data, $revision = 0)
    /**
     * Saves all field data by looping through the $data array
     * The data array must be in a specific format. To best see how to prepare
     * it, look at the field_save method in the field class.
     */
    public function savePostData($entry_id, $data, $revision = 0)
    {
        $cfmt = Doctrine::getTable('ContentFieldMeta');
        foreach ($data as $field_id => $array)
        {
            $field_meta = $cfmt->findOneById($field_id);
            $multiple = $field_meta['multiple'];
            foreach ($array as $akey => $rows)
            {
                if (array_key_exists('cdata', $rows) || array_key_exists('bdata', $rows))
                {
                    $field_data[$i]->merge($rows);
                    $field_data[$i]->akey = $multiple ? $akey : 0;
                    $field_data[$i]->content_entry_meta_id = $entry_id;
                    $field_data[$i]->content_field_meta_id = $field_meta->id;
                    $field_data[$i]->revision = $revision;
                    $field_data[$i]->meta = (array)$field_data[$i]->meta;
                }
                else
                {
                    foreach ($rows as $row)
                    {
                        $field_data[$i]->merge($row);
                        $field_data[$i]->akey = $multiple ? $akey : 0;
                        $field_data[$i]->content_entry_meta_id = $entry_id;
                        $field_data[$i]->content_field_meta_id = $field_meta->id;
                        $field_data[$i]->revision = $revision;
                        $field_data[$i]->meta = (array)$field_data[$i]->meta;
                    }
                }
            }
        }
        $field_data->save();
    }

    //}}}
    //{{{ public function getEntryDataByGroup($id, $revision = 0, $group)
    /**
     * Get raw DQL from findEntryDataByGroup and convert to array and group
     * fields by field name then pass each field to clean_raw_data
     * @param integer $id entry meta id
     * @param integer $revision revision number
     * @param string $group field group name
     * @return array
     */
    public function getEntryDataByGroup($id, $revision = 0, $group)
    {
        $data_raw = $this->findEntryDataByGroup($id, $revision, $group)->fetchArray();
        $fields = $data = array();
        foreach ($data_raw as $field_data)
        {
            $fields[$field_data['name']][] = $field_data;
        }
        foreach ($fields as $name => $field)
        {
            $data[$name] = $this->clean_raw_data($field);
        }
        return $data;
    }

    //}}}
    //{{{ public function getEntryDataByName($id, $revision = 0, $name)
    /**
     * Get raw DQL from findEntryDataByName and convert to array to pass to
     * clean_raw_data
     * @param integer $id entry meta id
     * @param integer $revision revision number
     * @param string $name field name
     * @return array
     */
    public function getEntryDataByName($id, $revision = 0, $name)
    {
        $data_raw = $this->findEntryDataByName($id, $revision, $name)->fetchArray();
        return $this->clean_raw_data($data_raw);
    }

    //}}}
    //{{{ public function findEntryData($id, $revision = 0)
    /**
     * Get data rows based on entry meta id
     * @param integer $id entry meta id
     * @param integer $revision revision number
     * @return Doctrine_Query
     */
    public function findEntryData($id, $revision = 0)
    {
        return Doctrine_Query::create()
               ->select('content_field_meta_id, cdata, bdata, akey, meta')
               ->from('ContentFieldData')
               ->where('content_entry_meta_id = ? AND revision = ?', array($id, $revision))
               ->orderBy('akey ASC');
    }

    //}}}
    //{{{ public function findEntryDataByGroup($id, $revision = 0, $group)
    /**
     * Get data rows based on entry meta id
     * @param integer $id entry meta id
     * @param integer $revision revision number
     * @param string $group field group name
     * @return Doctrine_Query
     */
    public function findEntryDataByGroup($id, $revision = 0, $group)
    {
        return Doctrine_Query::create()
               ->select('fd.content_field_meta_id, fd.cdata, fd.bdata, fd.akey, fd.meta, fm.id, fm.name as meta_name, ft.name as name, ft.type as type, ft.multiple as multiple')
               ->from('ContentFieldData fd')
               ->leftJoin('fd.ContentFieldMeta fm')
               ->leftJoin('fm.ContentFieldType ft')
               ->leftJoin('ft.ContentFieldGroup fg')
               ->where('fd.content_entry_meta_id = ? AND fd.revision = ? AND fg.name = ?', array($id, $revision, $group))
               ->orderBy('akey ASC');
    }

    //}}}
    //{{{ public function findEntryDataByName($id, $revision = 0, $name)
    /**
     * Get data rows based on entry meta id
     * @param integer $id entry meta id
     * @param integer $revision revision number
     * @param string $name field name
     * @return Doctrine_Query
     */
    public function findEntryDataByName($id, $revision = 0, $name)
    {
        return Doctrine_Query::create()
               ->select('fd.content_field_meta_id, fd.cdata, fd.bdata, fd.akey, fd.meta, fm.id, fm.name as meta_name, ft.name as name, ft.type as type, ft.multiple as multiple')
               ->from('ContentFieldData fd')
               ->leftJoin('fd.ContentFieldMeta fm')
               ->leftJoin('fm.ContentFieldType ft')
               ->where('fd.content_entry_meta_id = ? AND fd.revision = ? AND ft.name = ?', array($id, $revision, $name))
               ->orderBy('akey ASC');
    }

    //}}}
    //{{{ public function findCurrentEntryFieldData($mid, $fid)
    /**
     * Get data of entry field, current revision
     * @param integer $mid entry meta id
     * @param integer $fid field id
     * @return Doctrine_Query
     */
    public function findCurrentEntryFieldData($mid, $fid, $fname = NULL)
    {
        $query = Doctrine_Query::create()
               ->from('ContentFieldData fd')
               ->where('fd.content_entry_meta_id = ?', $mid);
        $query = is_null($fname)
            ? $query->addWhere('fd.content_field_meta_id IN (
                                SELECT  fm.id
                                FROM    ContentFieldMeta fm
                                WHERE   fm.content_field_type_id = ?
                            )', $fid)
            : $query->addWhere('fd.content_field_meta_id IN (
                                SELECT  fme.id
                                FROM    ContentFieldMeta fme
                                WHERE   fme.content_field_type_id = ?
                                AND     fme.name = ?
                            )', array($fid, $fname));
        $query->addWhere('fd.revision = (
                                SELECT  em.revision
                                FROM    ContentEntryMeta em
                                WHERE   em.id = ?
                            )', $mid)
               ->orderBy('fd.akey ASC');
        return $query;
    }

    //}}}
    //{{{ private function clean_raw_data($raw_data)
    /**
     * Clean data from getEntryDataBy* functions
     * @param array $raw_data structure of field data with joins
     * @return array
     */
    private function clean_raw_data($raw_data)
    {
        $data = array();
        foreach ($raw_data as $k => $v)
        {
            $field_data = array($v['meta_name'] => array(array($v)));
            $tmp = array_pop(array_pop(Field::quick_act('read', $v['type'], $field_data)));
            if ($v['multiple'])
            {
                $data[$v['akey']][$v['meta_name']] = isset($data[$v['akey']][$v['meta_name']])
                    ? array_merge($data[$v['akey']][$v['meta_name']], $tmp)
                    : $tmp;
            }
            else
            {
                $data[$v['meta_name']] = isset($data[$v['meta_name']])
                    ? array_merge($data[$v['meta_name']], $tmp)
                    : $tmp;
            }
        }
        return $data;
    }

    //}}}
}

?>
