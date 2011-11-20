<?php
$groups = array();
foreach ($products as $product)
{
    $group = $product['group_name'];
    $product_key = $product['product_name'].'----'.$product['product_id']; // used for sorting
    if (!eka($groups, $group))
    {
        $groups[$group] = array();
    }
    $groups[$group][$product_key] = $product;
}
ksort($groups);
?>

<?php foreach ($groups as $group => $products): ?>
    <h2><?php echo $group ?></h2>
    <ul id='inventory-products'>
        <?php ksort($products) ?>
        <?php foreach ($products as $product): ?>
            <li>
                <a href='/admin/module/Inventory/product_edit/<?php echo $product['product_id'] ?>/'
                    ><?php echo htmlspecialchars($product['product_name']) ?></a>
                <span class='delete'>
                    <a href='/admin/module/Inventory/product_delete/<?php echo $product['product_id'] ?>/'
                        >delete</a>
                </span>
            </li>
        <?php endforeach ?>
    </ul>
<?php endforeach ?>
