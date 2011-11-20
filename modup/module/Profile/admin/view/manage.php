<a href='/admin/module/Profile/new_group/'>Add new group</a>
<?php if (!empty($groups)): ?>
<br /><a href='/admin/module/Profile/new_field/'>Add new field</a>
<?php endif; ?>


<ul id='profile_groups'>
<?php foreach ($groups as $group): ?>
    <li><a href='/admin/module/Profile/edit_group/<?php echo $group['id']; ?>/'><?php echo $group['name']; ?></a> - <?php echo $group['status'] == Profile::UNLISTED ? 'Private' : 'Public'; ?>
        <ul>
        <?php foreach ($group['Fields'] as $field): ?>
            <li><a href='/admin/module/Profile/edit_field/<?php echo $field['id']; ?>/'><?php echo $field['name']; ?></a></li>
        <?php endforeach; ?>
        </ul>
    </li>
<?php endforeach; ?>
</ul>
