<ul>
<?php foreach ($stores as $store) : ?>
    <?php if (count($store['Locations']) == 0) continue; ?>
    <li><?php echo $store['name']; ?>
    <ul>
    <?php foreach ($store['Locations'] as $loc) : ?>
        <li><a href='/admin/module/StoreLocator/edit_store/<?php echo $loc['id']; ?>/'>
            <?php echo $loc['address1']; ?>
            <?php echo !empty($loc['address2']) ? ' '.$loc['address2'] : ''; ?>
            <?php echo $loc['city']; ?>
            <?php echo !empty($loc['state']) ? $loc['state'] : ''; ?>
            <?php echo !empty($loc['country']) ? $loc['country'] : ''; ?>
            <?php echo !empty($loc['zip_code']) ? $loc['zip_code'] : ''; ?>
        </a></li>
    <?php endforeach; ?>
    </ul>
    </li>
<?php endforeach; ?>
<ul>
