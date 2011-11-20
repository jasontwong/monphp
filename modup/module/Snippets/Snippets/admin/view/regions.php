<p><a href='/admin/module/snippets/new/'>Add Region</a></p>

<?php if ($regions): ?>

    <ul>
        <?php foreach ($regions as $region): ?>

            <li>
                <a href='/admin/module/snippets/edit/<?php echo $region['id'] ?>/'><?php echo $region['name'] ?></a> 
                (<?php echo $region['active'] ? 'active' : 'inactive' ?>) &mdash; 
                <?php echo $region['description'] ?>
            </li>

        <?php endforeach ?>
    </ul>

<?php else: ?>

<?php endif ?>
