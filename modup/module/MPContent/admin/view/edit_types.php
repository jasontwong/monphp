<?php if (count($entry_types)): ?>
    <ul>

        <?php foreach ($entry_types as $entry_type): ?>
            
            <li>
                <span class='name'>
                    <a href='/admin/module/MPContent/edit_type/<?php echo $entry_type['name'] ?>/'><?php echo htmlentities($entry_type['nice_name'], ENT_QUOTES) ?></a></li>
                </span>
                <?php if (strlen($entry_type['description'])): ?>
                    <span class='description'>
                        <?php echo nl2br(htmlentities($entry_type['description'], ENT_QUOTES)) ?>
                    </span>
                <?php endif ?>
            </li>

        <?php endforeach ?>

    </ul>
<?php endif ?>
