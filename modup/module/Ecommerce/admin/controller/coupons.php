<?php

if (!User::has_perm('edit ecommerce coupons'))
{
    throw new Exception('You do not have access to this page');
}

Admin::set('title', 'Coupons');
Admin::set('header', 'Coupons');

if (ake('coupon', $_POST))
{
    $data = $_POST['coupon'];
    $data['start_date'] = strtotime($data['start_date']);
    $data['end_date'] = strtotime($data['end_date']);
    if (!strlen($data['uses']))
    {
        $data['uses'] = -1;
    }
    $coupon = new EcommerceCoupon;
    $coupon->merge($data);
    if ($coupon->isValid())
    {
        $coupon->save();
        Admin::notify(Admin::TYPE_SUCCESS, 'Successfully saved');
    }
    else
    {
        Admin::notify(Admin::TYPE_ERROR, 'Unable to save');
    }
    $coupon->free();
}

?>
