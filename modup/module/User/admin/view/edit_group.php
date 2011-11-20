<?php if ($group['name'] !== User::GROUP_ADMIN): ?>
<p><a href='/admin/modules/User/delete_group/<?php echo URI_PART_4 ?>/'>Delete the &ldquo;<?php echo htmlentities($group['name'], ENT_QUOTES) ?>&rdquo; group</a></p>
<?php endif ?>

<?php echo $fh ?>
