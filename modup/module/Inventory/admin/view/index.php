<?php if ($product_groups): ?>
    <table>
        <thead>
            <tr>
                <th>name</th>
                <th>options - X-axis</th>
                <th>options - Y-axis</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($product_groups as $group)
            {
                echo    '<tr>'.
                            '<td>'.
                                '<a href="/admin/module/Inventory/product_group_edit/'.$group['id'].'/">'.
                                    htmlspecialchars($group['name']).
                                '</a>'.
                            '</td>'.
                            '<td>'.
                                ($group['ogx_name'] ? htmlspecialchars($group['ogx_name']) : 'None').
                            '</td>'.
                            '<td>'.
                                ($group['ogy_name'] ? htmlspecialchars($group['ogy_name']) : 'None').
                            '</td>'.
                            '<td>'.
                                '<a href="/admin/module/Inventory/product_new/'.$group['id'].'/">'.
                                    'New &ldquo;'.$group['name'].'&rdquo; product'.
                                '</a>'.
                            '</td>'.
                        '</tr>';
            }
            ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No product groups</p>
<?php endif ?>

<?php if ($option_groups): ?>
    <table>
        <thead>
            <tr>
                <th>name</th>
                <th>description</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($option_groups as $group)
            {
                echo    '<tr>'.
                            '<td>'.
                                '<a href="/admin/module/Inventory/option_group_edit/'.$group['id'].'/">'.
                                    htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8').
                                '</a>'.
                            '</td>'.
                            '<td>'.
                                htmlspecialchars($group['description'], ENT_QUOTES, 'UTF-8').
                            '</td>'.
                        '</tr>';
            }
            ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No option groups</p>
<?php endif ?>
