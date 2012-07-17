<?php

// $revisions = (int)$entry['revisions'];
$revisions = MPContent::get_revisions_by_entry_id(URI_PART_4, array('revision' => 1, '_id' => 1));
$current_revision = URI_PARTS === 6 ? (int)URI_PART_5 : 0;
$modified = $entry['modified']->sec;

?>

<?php if ($revisions->sort(array('_id' => -1))->hasNext()): ?>

<aside id="sidebar">
    <form name='content_revision_jump' method='post' action='<?php echo URI_PATH ?>'>
        <div class='revisions'>
            <h3>Revisions</h3>
            <select name='revision'>
                <?php foreach ($revisions as $revision): ?>
                    <?php $selected = $revision['revision'] === $current_revision ? ' selected="selected"' : '' ?>
                    <option value='<?php echo $revision['revision']; ?>' <?php echo $selected ?>><?php echo $revision['revision'] ?> - <?php echo date('Y-m-d', $revision['_id']->getTimestamp()); ?></option>
                <?php endforeach ?>
            </select>
            <button name='do' value='jump' type='submit'>
                Go to revision
            </button>
            <?php if ($current_revision !== 0): ?>
                <button name='do' value='set' type='submit'>
                    Set revision #<?php echo urldecode($current_revision) ?> for use
                </button>
                <?php if ($current_revision === $entry['revision']): ?>
                    <p>You are viewing the current revision that this entry is set to show</p>
                <?php endif; ?>
                <p>You are currently looking at revision #<?php echo urldecode($current_revision) ?>, made in <?php echo date('Y-m-d H:i:s', $modified); ?></p>
            <?php endif ?>
        </div>
    </form>
</aside>

<?php endif ?>

<?php

if ($access_level === MPContent::ACCESS_VIEW)
{
    echo '<p class="notice">You only have permission to view this entry. Saving will not work.</p>';
}

echo $efh;

?>
