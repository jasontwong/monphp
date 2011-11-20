<?php

class ContentFieldMetaTable extends Doctrine_Table
{
    //{{{ public function fieldInfo($id)
    /**
     * Returns table row info with additional columns
     *
     * @param integer $id field_meta id
     * @return array
     */
    public function fieldInfo($id)
    {
        $info = Doctrine_Query::create()
                ->select('fm.*, et.id as content_entry_type_id')
                ->from('ContentFieldMeta fm')
                ->leftJoin('fm.ContentFieldGroup fg')
                ->leftJoin('fg.ContentEntryType et')
                ->where('fm.id = ?', $id)
                ->fetchArray();
        if (!empty($info))
        {
            $info = $info[0];
        }
        return $info;
    }

    //}}}
    //{{{ public function findByNameAndType($name, $type_id)
    public function findByNameAndType($name, $type_id)
    {
        $info = Doctrine_Query::create()
                ->from('ContentFieldMeta')
                ->addWhere('name = ?', $name)
                ->addWhere('content_field_type_id = ?', $type_id)
                ->fetchArray();
        return count($info) === 1
            ? $info
            : array();
    }

    //}}}
    //{{{ public function findByContentEntryTypeId($id)
    /**
     * Finds rows based on Content Entry Types
     *
     * @param integer $id
     */
    public function findByContentEntryTypeId($id)
    {
        return Doctrine_Query::create()
               ->from('ContentFieldMeta fm')
               ->leftJoin('fm.ContentFieldGroup fg')
               ->leftJoin('fg.ContentEntryType et')
               ->where('et.id = ?', $id)
               ->fetchArray();
    }

    //}}}
    //{{{ public function findByIds($ids)
    public function findByIds($ids)
    {
        return Doctrine_Query::create()
               ->from('ContentFieldMeta')
               ->whereIn('id', $ids)
               ->execute();
    }

    //}}}
    //{{{ public function findByGroupIds($ids)
    public function findByGroupIds($ids)
    {
        return Doctrine_Query::create()
               ->select('id, name, type, multiple, description, content_field_group_id, meta')
               ->from('ContentFieldMeta')
               ->whereIn('content_field_group_id', $ids)
               ->orderBy('weight ASC, name ASC');
    }

    //}}}
}

?>
