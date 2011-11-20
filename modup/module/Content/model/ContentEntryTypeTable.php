<?php

class ContentEntryTypeTable extends Doctrine_Table
{
    //{{{ public function fieldLayout($id)
    /** 
     * Gets meta layout of entry type heirarchy
     * The result includes groups and custom fields
     * @param numeric $id entry type id
     * @return array
     */
    public function fieldLayout($id)
    {
        $groups = Doctrine_Query::create()
                  ->select('id, weight, name')
                  ->from('ContentFieldGroup')
                  ->where('content_entry_type_id = ?', $id)
                  ->orderBy('weight ASC, name ASC')
                  ->fetchArray();
        foreach ($groups as &$group)
        {
            $group['fields'] = Doctrine_Query::create()
                               ->select('id, name, type, weight, description, multiple')
                               ->from('ContentFieldType')
                               ->where('content_field_group_id = ?', $group['id'])
                               ->orderBy('weight ASC, name ASC')
                               ->fetchArray();
        }
        return $groups;
    }

    //}}}
    //{{{ public function getTypes()
    public function getTypes()
    {
        return Doctrine_Query::create()
               ->select('id, name')
               ->from('ContentEntryType')
               ->orderBy('name ASC')
               ->fetchArray();
    }

    //}}}
}

?>
