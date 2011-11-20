<?php

class ContentFieldTypeTable extends Doctrine_Table
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
                ->select('ft.*, et.id as content_entry_type_id')
                ->from('ContentFieldType ft')
                ->leftJoin('ft.ContentFieldGroup fg')
                ->leftJoin('fg.ContentEntryType et')
                ->where('ft.id = ?', $id)
                ->fetchArray();
        if (!empty($info))
        {
            $info = $info[0];
        }
        return $info;
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
               ->from('ContentFieldType ft')
               ->leftJoin('ft.ContentFieldGroup fg')
               ->leftJoin('fg.ContentEntryType et')
               ->where('et.id = ?', $id)
               ->fetchArray();
    }

    //}}}
    //{{{ public function findByIds($ids)
    public function findByIds($ids)
    {
        return Doctrine_Query::create()
               ->from('ContentFieldType')
               ->whereIn('id', $ids)
               ->execute();
    }

    //}}}
    //{{{ public function findByGroupIds($ids)
    public function findByGroupIds($ids)
    {
        return Doctrine_Query::create()
               ->select('id, name, type, multiple, description, content_field_group_id')
               ->from('ContentFieldType')
               ->whereIn('content_field_group_id', $ids)
               ->orderBy('weight ASC, name ASC');
    }

    //}}}
}

?>
