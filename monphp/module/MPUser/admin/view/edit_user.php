<?php if ($user['name'] !== MPUser::USER_ADMIN): ?>
<p><a href='/admin/module/MPUser/delete_user/<?php echo URI_PART_4 ?>/'>Delete user &ldquo;<?php echo htmlentities($user['name'], ENT_QUOTES) ?>&rdquo;</a></p>
<?php endif ?>

<p>Joined <?php echo date('F jS, Y', MPUser::i('_id')->getTimestamp()) ?></p>
<p>Last logged in <?php echo date('F jS, Y @ h:i A', MPUser::i('logged_in')->sec) ?></p>

<?php echo $fh ?>
