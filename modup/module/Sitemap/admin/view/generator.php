<?php echo $form->build(); ?>

<ul>
<?php foreach ($uris as $v): ?>
    <li>
    http://<?php echo $_SERVER['HTTP_HOST'].$v['rel_uri']; ?>/
    <a href='/admin/module/Sitemap/add_child/<?php echo $v['sitemap_entry_id']; ?>/'>Add child</a>
    </li>
<?php endforeach; ?>
</ul>
