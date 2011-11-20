<form method='post' action='<?php echo URI_PATH ?>' id='inventory_option_group_form'>
    <div class='group' id='inventory_option_group'>
        <?php if (isset($option_group)): ?>
            <input type='hidden' name='group_id' value='<?php echo $option_group['id'] ?>'>
        <?php endif ?>
        <div class='row'>
            <div class='label'>
                Name
            </div>
            <div class='fields'>
                <div class='field'>
                    <input
                        type='text'
                        class='text'
                        value='<?php echo isset($option_group) 
                                        ? htmlspecialchars($option_group['name'], ENT_QUOTES, 'UTF-8')
                                        : '' ?>'
                        name='group_name'>
                </div>
            </div>
        </div>
        <div class='row'>
            <div class='label'>
                Description
            </div>
            <div class='fields'>
                <div class='field'>
                    <textarea name='group_description'
                        ><?php 
                        echo isset($option_group) 
                            ? htmlspecialchars($option_group['description'], ENT_QUOTES, 'UTF-8')
                            : '' 
                        ?></textarea>
                </div>
            </div>
        </div>
        <div class='row'>
            <div class='fields'>
                <div class='field'>
                    <button type='submit'>Submit</button>
                    <a href='/admin/module/Inventory/'>Cancel</a>
                </div>
            </div>
        </div>
    </div>
    <div class='group' id='inventory_options'>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Display Name</th>
                    <th>Image</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $io_i = 0;
                $io_max = isset($options) ? count($options) : 0;
                for (; $io_i < $io_max; $io_i++)
                {
                    $option = $options[$io_i];
                    echo    '<tr>'.
                                '<td>'.
                                    '<input '.
                                        'type="hidden" class="hidden" '.
                                        'name="option['.$io_i.'][id]" '.
                                        'value="'.htmlspecialchars($option['id'], ENT_QUOTES, 'UTF-8').'" >'.
                                    '<input '.
                                        'type="text" class="text" '.
                                        'name="option['.$io_i.'][name]" '.
                                        'value="'.htmlspecialchars($option['name'], ENT_QUOTES, 'UTF-8').'" >'.
                                '</td>'.
                                '<td>'.
                                    '<input '.
                                        'type="text" class="text" '.
                                        'name="option['.$io_i.'][display_name]" '.
                                        'value="'.htmlspecialchars($option['display_name'], ENT_QUOTES, 'UTF-8').'" >'.
                                '</td>'.
                                '<td>'.
                                    '<input type="hidden" class="hidden file FileManagerBrowser TypeImage SingleFile" name="option['.$io_i.'][image]" value="'.htmlspecialchars($option['image'], ENT_QUOTES, 'UTF-8').'">'.
                                    '<input type="hidden" class="hidden caption " name="option['.$io_i.'][caption]">'.
                                    '<input type="hidden" class="hidden uri " name="option['.$io_i.'][uri]">'.
                                '</td>'.
                                '<td>'.
                                    '<input '.
                                        'type="checkbox" class="checkbox" '.
                                        'name="option['.$io_i.'][delete]" '.
                                        'value="1">'.
                                '</td>'.
                            '</tr>';
                }
                for ($io_j = 0; $io_j < 4; $io_j++)
                {
                    $io_i++;
                    echo    '<tr>'.
                                '<td>'.
                                    '<input type="text" class="text" name="option['.$io_i.'][name]">'.
                                '</td>'.
                                '<td>'.
                                    '<input type="text" class="text" name="option['.$io_i.'][display_name]">'.
                                '</td>'.
                                '<td>'.
                                    '<input type="hidden" class="hidden file FileManagerBrowser TypeImage SingleFile" name="option['.$io_i.'][image]">'.
                                    '<input type="hidden" class="hidden caption " name="option['.$io_i.'][caption]">'.
                                    '<input type="hidden" class="hidden uri " name="option['.$io_i.'][uri]">'.
                                '</td>'.
                                '<td>&nbsp;</td>'.
                            '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class='group'>
        <div class='row'>
            <div class='fields'>
                <div class='field'>
                    <button type='submit'>Submit</button>
                    <a href='/admin/module/Inventory/'>Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
