<p><a href='/admin/module/User/create_group/'>Create a new group</a></p>

<?php if (count($groups)): ?>

<table>
    <thead>
        <tr>
            <td colspan='2'>Total groups: <?php echo count($groups) ?></td>
        </tr>
        <tr>
            <th>id</th>
            <th>name</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan='2'>Total groups: <?php echo count($groups) ?></td>
        </tr>
    </tfoot>
    <tbody>
        <?php foreach ($groups as $group): ?>

            <?php
                $id = $group['id'];
                $name = htmlentities($group['name']);
                if (User::perm('edit groups'))
                {
                    $id = '<a href="/admin/module/User/edit_group/'.$group['id'].'/">'.$id.'</a>';
                    $name = '<a href="/admin/module/User/edit_group/'.$group['id'].'/">'.$name.'</a>';
                }
            ?>
            <tr>
                <td><?php echo $id ?></td>
                <td><?php echo $name ?></td>
            </tr>

        <?php endforeach ?>
    </tbody>
</table>

<?php else: ?>

<?php endif ?>
