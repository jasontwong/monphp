
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Active</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($snippets): ?>
            <?php foreach ($snippets as $snippet): ?>
                <tr>
                    <td><a href='/admin/module/Snippets/edit/<?php echo $snippet['id'] ?>/'><?php echo $snippet['name'] ?></a></td>
                    <td><?php echo htmlentities($snippet['description']) ?></td>
                    <td><?php echo $snippet['active'] ? 'yes' : 'no' ?></td>
                    <td><a href='/admin/module/Snippets/delete/<?php echo $snippet['id'] ?>/'>delete</a></td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    </tbody>
</table>
