<?php if (isset($entry_field_groups) && !empty($entry_field_groups)): ?>

    <form method='post' id='field_groups' action='/admin/module/MPContent/delete_field/'>
        <div class='form_wrapper'>
        <table>
            <caption>Field Groups</caption>
            <tfoot>
                <tr>
                    <td colspan='2'>
                        <input type='hidden' class='hidden' name='et' value='<?php echo URI_PART_4 ?>'>
                        <input type='hidden' class='hidden' name='do' value='delete_field'>
                        <button type='submit'>Delete Selected MPFields</button>
                        <button type='reset'>Reset</button>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($entry_field_groups as &$entry_field_group): ?>
                    <?php $group_url = $entry_type['name'] . '/' . $entry_field_group['name']; ?>
                    <tr>
                        <th colspan='4'>
                            <a href='/admin/module/MPContent/edit_field_group/<?php echo $group_url; ?>/'><?php echo htmlentities($entry_field_group['nice_name'], ENT_QUOTES) ?></a>
                        </th>
                    </tr>

                    <?php foreach ($entry_field_group['fields'] as &$entry_field): ?>
                        <?php $field = MPField::get_field($entry_field['id']); ?>
                        <?php $field_url = $group_url . '/' . $field['name']; ?>
                        <tr>
                            <td><input type='checkbox' class='checkbox' name='f[]' value='<?php echo $field['_id']->{'$id'} ?>'></td>
                            <td><a href='/admin/module/MPContent/edit_field/<?php echo $field_url; ?>/'><?php echo htmlentities($field['nice_name'], ENT_QUOTES) ?></a></td>
                            <td><?php echo $field['type'] ?></td>
                            <td><?php echo $field['description'] ?></td>
                        </tr>
                    <?php endforeach ?>

                <?php endforeach ?>
            </tbody>
        </table>
        </div>
    </form>

<?php endif ?>
