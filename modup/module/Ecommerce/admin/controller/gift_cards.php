<?php

if (!User::has_perm('edit ecommerce gift cards'))
{
    throw new Exception('You do not have access to this page');
}

Admin::set('title', 'Gift Cards');
Admin::set('header', 'Gift Cards');

if (ake('gift_card', $_POST))
{
    $data = $_POST['gift_card'];
    if (strlen($data['end_date']))
    {
        $data['end_date'] = strtotime($data['end_date']);
    }
    if (!strlen($data['uses']))
    {
        $data['uses'] = -1;
    }
    $gift_card = new EcommerceGiftCard;
    $gift_card->merge($data);
    if ($gift_card->isValid())
    {
        $gift_card->save();
        Admin::notify(Admin::TYPE_SUCCESS, 'Successfully saved');
    }
    else
    {
        Admin::notify(Admin::TYPE_ERROR, 'Unable to save');
    }
    $gift_card->free();
}

?>
