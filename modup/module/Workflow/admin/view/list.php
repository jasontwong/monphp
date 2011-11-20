<?php if ($tasks): ?>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>

        <?php foreach ($tasks as &$task): ?>
            <tr>
                <td>
                    <a href='/admin/module/Workflow/edit/<?php echo $task['id'] ?>/'><?php echo htmlentities($task['name']) ?></a>
                </td>
                <td><?php echo htmlentities($task['description']) ?></td>
            </tr>
        <?php endforeach ?>

    </tbody>
</table>

<?php else: ?>

<p>There are no workflow tasks at this time.</p>

<?php endif ?>
