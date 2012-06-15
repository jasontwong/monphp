<p><a href='/admin/module/MPUser/create_group/'>Create a new group</a></p>

<?php if ($groups->hasNext()): ?>

<table>
    <thead>
        <tr>
            <td colspan='2'>Total groups: <?php echo $groups->count() ?></td>
        </tr>
        <tr>
            <th>name</th>
            <th>display name</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan='2'>Total groups: <?php echo $groups->count() ?></td>
        </tr>
    </tfoot>
    <tbody>
        <?php foreach ($groups as $group): ?>

            <?php
                $name = $group['name'];
                $nice_name = htmlentities($group['nice_name']);
                if (MPUser::perm('edit groups'))
                {
                    $name = '<a href="/admin/module/MPUser/edit_group/'.$group['name'].'/">'.$name.'</a>';
                    $nice_name = '<a href="/admin/module/MPUser/edit_group/'.$group['nice_name'].'/">'.$nice_name.'</a>';
                }
            ?>
            <tr>
                <td><?php echo $name ?></td>
                <td><?php echo $nice_name ?></td>
            </tr>

        <?php endforeach ?>
    </tbody>
</table>

<?php else: ?>

<?php endif ?>
