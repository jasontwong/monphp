<?php

if (!User::has_perm('edit ecommerce statuses'))
{
    throw new Exception('You do not have access to this page');
}

if (ake('status', $_POST))
{
    $status = new EcommerceOrderStatus;
    $status->merge($_POST['status']);
    if ($status->isValid())
    {
        $status->save();
        Admin::notify(Admin::TYPE_SUCCESS, 'Successfully saved');
    }
    else
    {
        Admin::notify(Admin::TYPE_ERROR, 'Unable to save');
    }
    $status->free();
}

?>
