<?php if ($files): ?>

<ul id='filemanager_grid'>
    <?php foreach ($files['dirs'] as $file): ?>
        <li>
            <div class='image'>
                folder image
            </div>
            <div class='label'>
                <a href='/admin/module/FileManager/browse<?php echo htmlentities($file['link'], ENT_QUOTES) ?>'><?php echo htmlentities($file['name']) ?></a>
            </div>
        </li>
    <?php endforeach ?>
    <?php foreach ($files['files'] as $file): ?>
        <li>
            <div class='image'>
                file image
            </div>
            <div class='label'>
                <a href='/admin/module/FileManager/browse<?php echo htmlentities($file['link'], ENT_QUOTES) ?>'><?php echo htmlentities($file['name']) ?></a>
            </div>
        </li>
    <?php endforeach ?>
</ul>

<?php endif ?>
