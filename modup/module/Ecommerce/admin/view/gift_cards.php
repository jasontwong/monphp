<script type="text/javascript">
$(function(){
    var type = $('input#type'),
        gift_cards = $('.gift-card-types');
    $('li', gift_cards)
        .hover(function(){
            $('div', this).show();
        }, function(){
            $('div', this).hide();
        });
    $('a', gift_cards)
        .click(function(){
            var el = $(this),
                list = el.closest('ul');
            $.post('/admin/rpc/Ecommerce/gift_card/', { action: 'delete', id: el.data('id') }, function(data) {
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
    .gift-card-types li { position: relative; }
    .gift-card-types div { background-color: #EEE; border: 1px solid #000; bottom: 15px; display: none; left: 15px; padding: 5px; position: absolute; }
</style>

<h2>Add New Gift Card</h2>
<p>
<form method="POST">
    <label>Code: <input name="gift_card[code]" type="text" value="" /></label>
    <label>Amount: <input name="gift_card[amount]" placeholder="0.00" type="text" value="" /></label>
    <label>Balance: <input name="gift_card[balance]" placeholder="0.00" type="text" value="" /></label>
    <br /> <br />
    <label>Max Uses: <input name="gift_card[uses]" placeholder="-1" type="text" value="" /></label>
    <label>End Date: <input class="date" name="gift_card[end_date]" type="text" value="" /></label>
    <br /> <br />
    <button>Submit</button>
</form>
</p>

<h2>Available Gift Cards</h2>
<ul id="available" class="gift-card-types">
<?php
$gift_cards['available'] = Ecommerce::get_available_gift_cards();
$tmp = array();
foreach ($gift_cards['available'] as $gift_card)
{
        $amount = '$'.$gift_card['amount'];
        echo '<li>'.$gift_card['code'].' - '.$amount.' <a href="javascript:;" data-id="'.$gift_card['id'].'">&times;</a><div><p>Uses: '.$gift_card['uses'].'<br />End Date: '.date('Y-m-d', $gift_card['end_date']).'<br />Balance: '.$gift_card['balance'].'<br /></p></div></li>';
}
?>
</ul>
<p>&nbsp;</p>
<h2>Expired Gift Cards</h2>
<ul id="expired" class="gift-card-types">
<?php
$gift_cards['expired'] = Ecommerce::get_expired_gift_cards();
foreach ($gift_cards['expired'] as $gift_card)
{
        $amount = '$'.$gift_card['amount'];
        echo '<li>'.$gift_card['code'].' - '.$amount.' <a href="javascript:;" data-id="'.$gift_card['id'].'">&times;</a><div><p>Uses: '.$gift_card['uses'].'<br />End Date: '.date('Y-m-d', $gift_card['end_date']).'<br />Balance: '.$gift_card['balance'].'<br /></p></div></li>';
}

?>
</ul>
