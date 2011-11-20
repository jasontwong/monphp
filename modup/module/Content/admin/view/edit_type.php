
<p><a href='/admin/module/Content/delete_type/<?php echo URI_PART_4 ?>/'>Delete this content type</a></p>

<p>Other links</p>
<ul>
    <?php foreach ($other_links as $link): ?>
        <li><a href='<?php echo $link['uri'] ?>'><?php echo $link['text'] ?></a></li>
    <?php endforeach ?>
    <li><a href='/admin/module/Content/field_groups/<?php echo URI_PART_4 ?>/'>field groups</a></li>
    <li><a href='/admin/module/Content/fields/<?php echo URI_PART_4 ?>/'>fields</a></li>
</ul>

<?php echo $tfh ?>

