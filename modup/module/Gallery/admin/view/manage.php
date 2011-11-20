<ul id='album-list'>
<?php foreach ($albums as $album): ?>
    <li><a href='/admin/module/gallery/edit_album/<?php echo $album['id']; ?>/'><?php echo $album['name']; ?></a> - <?php echo $album['status'] == gallery::LISTED ? 'Public' : 'Private'; ?> - <a href='/admin/module/gallery/manage_items/<?php echo $album['id']; ?>/'>Edit Items</a></li>
<?php endforeach; ?>
</ul>
