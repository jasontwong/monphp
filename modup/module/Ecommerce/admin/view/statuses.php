<script type="text/javascript">
$(function(){
    var type = $('input#type'),
        statuses = $('#status-types');
    $('> li > a', statuses)
        .click(function(){
            type.val($(this).text());
        });
    $('> li > ul > li > a', statuses)
        .click(function(){
            var el = $(this),
                list = el.closest('ul');
            $.post('/admin/rpc/Ecommerce/status/', { action: 'delete', id: el.data('id') }, function(data) {
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

<h1>Statuses</h1>
<h2>Add New Status</h2>
<p>
<form method="POST">
    <label>Type: <input id="type" name="status[type]" type="text" value="" /></label>
    <label>Name: <input name="status[name]" type="text" value="" /></label>
    <button>Submit</button>
</form>
</p>

<h2>Available Statuses</h2>
<ul id="status-types">
<?php
$statuses = EcommerceAPI::get_statuses();
$tmp = array();
foreach ($statuses as $status)
{
    $tmp[$status['type']][$status['id']] = $status['name'];
}

foreach ($tmp as $type => $stats)
{
    echo '<li><a href="javascript:;">'.$type.'</a></li>';
    echo '<li><ul>';
    foreach ($stats as $id => $stat)
    {
        echo '<li>'.$stat.' <a href="javascript:;" data-id="'.$id.'">&times;</a></li>';
    }
    echo '</ul></li>';
}
?>
</ul>
