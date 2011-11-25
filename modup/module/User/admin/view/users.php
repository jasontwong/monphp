<p><a href='/admin/module/User/create_user/'>Create a new user account</a></p>

<?php if (count($users)): ?>

<style>
    table td { padding: 4px; }
</style>

<table>
    <thead>
        <tr>
            <td colspan='4'>Total users: <?php echo count($users) ?></td>
        </tr>
        <tr>
            <th>username</th>
            <th>name</th>
            <th>email</th>
            <th>joined</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan='4'>Total users: <?php echo count($users) ?></td>
        </tr>
    </tfoot>
    <tbody>
        <?php foreach ($users as $user): ?>

            <?php
                $username = htmlentities($user['name']);
                $name = htmlentities($user['nice_name']);
                $email = htmlentities($user['email']);
                $joined = htmlentities(date('F jS, Y', $user['_id']->getTimestamp()));
                if (User::perm('edit users'))
                {
                    $username = '<a href="'.$href.'/'.$user['name'].'/">'.$username.'</a>';
                }
            ?>
            <tr>
                <td><?php echo $username ?></td>
                <td><?php echo $name ?></td>
                <td><?php echo $email ?></td>
                <td><?php echo $joined ?></td>
            </tr>

        <?php endforeach ?>
    </tbody>
</table>

<?php else: ?>

<?php endif ?>
