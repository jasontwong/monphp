<?php

class ContentFieldGroupTable extends Doctrine_Table
{
    //{{{ public function fieldByType($type)
    /**
     * Gets all groups for a type in an array format for the field
     *
     * @param integer $type type id
     * @return array
     */
    public function fieldByType($type)
    {
        $types = Doctrine_Query::create()
                 ->from('ContentFieldGroup c')
                 ->select('c.id, c.name')
                 ->where('c.content_entry_type_id = ?', $type)
                 ->orderBy('c.weight ASC, c.name ASC')
                 ->fetchArray();
        $result = array();
        foreach ($types as $type)
        {
            $result[$type['id']] = $type['name'];
        }
        return $result;
    }

    //}}}
}

?>
