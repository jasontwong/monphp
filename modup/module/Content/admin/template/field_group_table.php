<?php if (isset($field_groups) && !empty($field_groups)): ?>

    <form method='get' id='field_groups' action='/admin/module/Content/delete_field/'>
        <div class='form_wrapper'>
        <table>
            <caption>Field Groups</caption>
            <tfoot>
                <tr>
                    <td colspan='2'>
                        <input type='hidden' class='hidden' name='et' value='<?php echo URI_PART_4 ?>'>
                        <input type='hidden' class='hidden' name='do' value='delete_field'>
                        <button type='submit'>Delete Selected Fields</button>
                        <button type='reset'>Reset</button>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($field_groups['groups'] as $id => $field_group): ?>

                    <tr>
                        <th colspan='4'>
                            <a href='/admin/module/Content/edit_field_group/<?php echo $field_group['id'] ?>/'><?php echo htmlentities($field_group['name'], ENT_QUOTES) ?></a>
                        </th>
                    </tr>

                    <?php foreach ($field_group['fields'] as $field): ?>

                        <tr>
                            <td><input type='checkbox' class='checkbox' name='f[]' value='<?php echo $field['id'] ?>'></td>
                            <td><a href='/admin/module/Content/edit_field/<?php echo $field['id'] ?>/'><?php echo htmlentities($field['name'], ENT_QUOTES) ?></a></td>
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
