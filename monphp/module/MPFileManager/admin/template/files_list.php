<?php if ($files): ?>

<table>
    <colgroup>
        <col id='file_link'>
        <col id='file_name'>
        <col id='file_size'>
        <col id='file_check'>
    </colgroup>
    <thead>
        <tr>
            <th>Link</th>
            <th>Name</th>
            <th>Size</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($files['dirs'] as $file): ?>
            <tr>
                <td><a href='/admin/module/MPFileManager/browse<?php echo htmlentities($file['link'], ENT_QUOTES) ?>'>dir</a></td>
                <td><a href='/admin/module/MPFileManager/browse<?php echo htmlentities($file['link'], ENT_QUOTES) ?>'><?php echo htmlentities($file['name']) ?></a></td>
                <td></td>
                <td></td>
            </tr>
        <?php endforeach ?>
        <?php foreach ($files['files'] as $file): ?>
            <tr>
                <td><a href='<?php echo htmlentities($web_path.$file['link'], ENT_QUOTES) ?>'>file</a></td>
                <td><a href='<?php echo htmlentities($web_path.$file['link'], ENT_QUOTES) ?>'><?php echo htmlentities($file['name']) ?></a></td>
                <td><?php echo $file['size'] ?></td>
                <td><input type='checkbox' name='file[]' value='<?php echo htmlentities($file['link'], ENT_QUOTES) ?>'></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php endif ?>
