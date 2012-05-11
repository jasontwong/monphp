<?php if ($group['name'] !== MPUser::GROUP_ADMIN): ?>
<p><a href='/admin/modules/MPUser/delete_group/<?php echo URI_PART_4 ?>/'>Delete the &ldquo;<?php echo htmlentities($group['name'], ENT_QUOTES) ?>&rdquo; group</a></p>
<?php endif ?>

<?php echo $fh ?>
