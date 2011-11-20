<script type="text/javascript">
$(function(){
    var type = $('input#type'),
        coupons = $('.coupon-types');
    $('li', coupons)
        .hover(function(){
            $('div', this).show();
        }, function(){
            $('div', this).hide();
        });
    $('a', coupons)
        .click(function(){
            var el = $(this),
                list = el.closest('ul');
            $.post('/admin/rpc/Ecommerce/coupon/', { action: 'delete', id: el.data('id') }, function(data) {
                if (data.success)
                {
                    el.parent().remove();
                    if (!list.children().length)
                    {
                        list.parent().prev('li').remove();
                        list.parent().remove();
                    }
                }
            }, 'json');
        });
});
</script>

<style>
    .coupon-types li { position: relative; }
    .coupon-types div { background-color: #EEE; border: 1px solid #000; bottom: 15px; display: none; left: 15px; padding: 5px; position: absolute; }
</style>

<h2>Add New Coupon</h2>
<p>
<form method="POST">
    <label>Code: <input name="coupon[code]" type="text" value="" /></label>
    <label>Amount: <input name="coupon[amount]" placeholder="0.00" type="text" value="" /></label>
    <label>Max Uses: <input name="coupon[uses]" placeholder="-1" type="text" value="" /></label>
    <br /> <br />
    <label>Type: 
        <select name="coupon[type]">
            <option value="amount">Amount ($)</option>
            <option value="rate">Rate (%)</option>
        </select>
    </label>
    <label>Free Shipping? <input name="coupon[free_shipping]" type="hidden" value="0" /><input name="coupon[free_shipping]" type="checkbox" value="1" /></label>
    <label>Qualifying Amount: <input name="coupon[qualifier]" placeholder="0.00" type="text" value="" /></label>
    <br /> <br />
    <label>Start Date: <input class="date" name="coupon[start_date]" type="text" value="" /></label>
    <label>End Date: <input class="date" name="coupon[end_date]" type="text" value="" /></label>
    <br /> <br />
    <button>Submit</button>
</form>
</p>

<h2>Available Coupons</h2>
<ul id="available" class="coupon-types">
<?php
$coupons['available'] = Ecommerce::get_available_coupons();
$tmp = array();
foreach ($coupons['available'] as $coupon)
{
        $amount = '';
        if ($coupon['type'] === 'amount')
        {
            $amount = '$'.$coupon['amount'];
        }
        if ($coupon['type'] === 'rate')
        {
            $amount = $coupon['amount'].'%';
        }
        echo '<li>'.$coupon['code'].' - '.$amount.' <a href="javascript:;" data-id="'.$coupon['id'].'">&times;</a><div><p>Uses: '.$coupon['uses'].'<br />Start Date: '.date('Y-m-d', $coupon['start_date']).'<br />End Date: '.date('Y-m-d', $coupon['end_date']).'<br />Free Shipping: '.$coupon['free_shipping'].'<br />Min. Amount: '.$coupon['qualifier'].'<br /></p></div></li>';
}
?>
</ul>
<p>&nbsp;</p>
<h2>Expired Coupons</h2>
<ul id="expired" class="coupon-types">
<?php
$coupons['expired'] = Ecommerce::get_expired_coupons();
foreach ($coupons['expired'] as $coupon)
{
        $amount = '';
        if ($coupon['type'] === 'amount')
        {
            $amount = '$'.$coupon['amount'];
        }
        if ($coupon['type'] === 'rate')
        {
            $amount = $coupon['amount'].'%';
        }
        echo '<li>'.$coupon['code'].' - '.$amount.' <a href="javascript:;" data-id="'.$coupon['id'].'">&times;</a><div><p>Uses: '.$coupon['uses'].'<br />Start Date: '.date('Y-m-d', $coupon['start_date']).'<br />End Date: '.date('Y-m-d', $coupon['end_date']).'<br />Free Shipping: '.$coupon['free_shipping'].'<br />Min. Amount: '.$coupon['qualifier'].'<br /></p></div></li>';
}

?>
</ul>
