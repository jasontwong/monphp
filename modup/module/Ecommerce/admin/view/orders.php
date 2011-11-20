<div id="ecommerce_filters">
    <form action="<?php echo URI_PATH; ?>" method="post">
        <div>
            <label>Start Date: <input type="text" class="date" name="filter[start_date]" value="<?php echo $filter['start_date']; ?>"></label>
            <label>End Date: <input type="text" class="date" name="filter[end_date]" value="<?php echo $filter['end_date']; ?>"></label>
            <label>State: <input type="text" class="date" name="filter[state]" placeholder="2 letter code for US and CA" value="<?php echo $filter['state']; ?>"></label>
        </div>
        <div>
            <h2>Statuses</h2>
            <?php foreach (Ecommerce::get_status_options() as $type => $options): foreach ($options as $k => $v): ?>
                <?php if (in_array($k, $filter['statuses'])): ?>
                <label><input checked="checked" type="checkbox" name="filter[statuses][]" value="<?php echo $k; ?>"> <?php echo $type.' => '.$v; ?></label>
                <?php else: ?>
                <label><input type="checkbox" name="filter[statuses][]" value="<?php echo $k; ?>"> <?php echo $type.' => '.$v; ?></label>
                <?php endif; ?>
            <?php endforeach; endforeach; ?>
        </div>
        <div>
            <h2>Sort</h2>
            <select name="filter[sort][type]">
            <?php foreach ($types as $type => $name): ?>
                <?php if ($type === $filter['sort']['type']): ?>
                <option selected="selected" value="<?php echo $type; ?>"><?php echo $name; ?></option>
                <?php else: ?>
                <option value="<?php echo $type; ?>"><?php echo $name; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
            </select>
            <select name="filter[sort][order]">
                <option <?php if ($filter['sort']['order'] === 'ASC') echo 'selected="selected" '; ?>value="ASC">Ascending</option>
                <option <?php if ($filter['sort']['order'] === 'DESC') echo 'selected="selected" '; ?>value="DESC">Descending</option>
            </select>
            <select name="filter[rows]">
            <?php foreach ($rows as $row): ?>
                <?php if ($filter['rows'] == $row): ?>
                <option selected="selected" value="<?php echo $row; ?>"><?php echo $row; ?></option>
                <?php else: ?>
                <option value="<?php echo $row; ?>"><?php echo $row; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>
<table id="ecommerce_orders">
    <thead>
        <tr>
        <?php foreach ($columns as $col): ?>
            <th><?php echo $col; ?></th>
        <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $order): ?>
        <tr>
        <?php foreach ($columns as $key => $col): ?>
            <td><?php echo $order[$key]; ?></td>
        <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
