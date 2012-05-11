<?php

$revisions = (int)$entry['revisions'];
$entry_revision = (int)$entry['revision'];
$current_revision = URI_PARTS === 6 ? URI_PART_5 : $entry_revision;
$modified = (int)$entry['modified'];

?>

<?php if ($revisions): ?>

<?php $r_dates = Doctrine_Query::create()->select('modified')->from('MPContentEntryTitle')->where('content_entry_meta_id = ?', array($entry['id']))->fetchArray(); ?>
<div id='sidebar'>
    <form name='content_revision_jump' method='post' action='<?php echo URI_PATH ?>'>
        <div class='revisions'>
            <h3>Revisions</h3>
            <select name='revision'>
                <?php for ($i = 0; $i <= $revisions; ++$i): ?>
                    <?php $selected = $i == $current_revision ? ' selected="selected"' : '' ?>
                    <option value='<?php echo $i; ?>' <?php echo $selected ?>><?php echo $i ?> - <?php echo date('Y-m-d', $r_dates[$i]['modified']); ?></option>
                <?php endfor ?>
            </select>
            <button name='do' value='jump' type='submit'>
                Go to revision
            </button>
            <?php if ($entry_revision != $current_revision): ?>
                <button name='do' value='set' type='submit'>
                    Set revision #<?php echo urldecode($current_revision) ?> for use
                </button>
            <?php else: ?>
                <?php if ($current_revision != $revisions): ?>
                    <p>You are viewing the current revision that this entry is set to show</p>
                <?php endif ?>
            <?php endif ?>
            <?php if ($current_revision != $revisions): ?>
                <p>You are currently looking at revision #<?php echo urldecode($current_revision) ?>, made in <?php echo date('Y-m-d H:i:s', $modified); ?></p>
            <?php endif ?>
        </div>
    </form>
</div>

<?php endif ?>

<?php

if ($access_level === MPContent::ACCESS_VIEW)
{
    echo '<p class="notice">You only have permission to view this entry. Saving will not work.</p>';
}

echo $efh;

?>
