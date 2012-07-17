<?php 

echo $form->build();

$table_class = '';
if ($ordering)
{
    if ($filter['limit'] == 0)
    {
        $table_class = 'class="manual_ordering"';
    }
    else
    {
        $_GET['filter']['limit']['data'] = 0;
        unset($_GET['filter']['query']);
        unset($_GET['filter']['submit']);
        echo '<p>This entry type can be manually ordered. But you must first <a href="'.URI_PATH.'?'.http_build_query($_GET).'">view all entries</a> to begin ordering.</p>';
    }
}

$colgroup = array(
    'content_title',
    'content_type',
    'content_modified',
);

$thead = array(
    'Title',
    'Entry Type',
    'Modified',
);

$thead_desc = array(
    'click to edit specific entry',
    'click to view all entries of this type',
    'date this entry was last changed'
);

$colgroup[] = 'content_status';
$thead[] = 'Status';
$thead_desc[] = '';

?>

<table <?php echo $table_class ?> id='content_entries'>
    <colgroup>
        <?php foreach ($colgroup as $id): ?>
            <col id='<?php echo $id; ?>'>
        <?php endforeach; ?>
    </colgroup>
    <thead>
        <tr>
            <?php foreach ($thead as $th): ?>
                <th><?php echo $th; ?></th>
            <?php endforeach; ?>
        </tr>
        <tr class='description'>
            <?php foreach ($thead_desc as $th): ?>
                <th><?php echo $th; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (count($entries)): ?>
            <?php foreach ($entries as $entry): ?>
                <?php
                    $title = '<a href="/admin/module/MPContent/edit_entry/' . $entry['_id']->{'$id'} . '/">' . $entry['title'] . '</a>';
                    $type = '<a href="/admin/module/MPContent/edit_entries/' . $entry['entry_type']['name'] . '/">' . $entry['entry_type']['nice_name'] . '</a>';
                    $created = date('r', $entry['_id']->getTimestamp());
                    // $modified = date('m-d-Y h:i A', $entry['modified']);
                    $modified = date('r', $entry['modified']->sec);
                    if ($modified === $created)
                    {
                        $modified = 'Never';
                    }
                    $status = strlen($entry['status']) ? $entry['status'] : '&mdash;';
                    $data = array(
                        $title,
                        $type,
                        $modified,
                        $status,
                    );
                ?>
                <tr>
                    <?php foreach ($data as $td): ?>
                    <td><?php echo $td; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach ?>
        <?php else: ?>
            <tr><td colspan='4'>There are no entries</td></tr>
        <?php endif ?>
    </tbody>
</table>
