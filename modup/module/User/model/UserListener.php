<?php

class UserListener extends Doctrine_Record_Listener
{
    public function preInsert(Doctrine_Event $event)
    {
        $salt = random_string(5);
        $row = $event->getInvoker();
        $pass = $row->pass;
        $row->joined = time();
        $row->salt = $salt;
        $row->pass = sha1($salt.$pass);
    }
    public function preUpdate(Doctrine_Event $event)
    {
        $row = $event->getInvoker();
    }
}

?>
