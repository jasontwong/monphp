<?php if (isset($messages)): ?>
    <?php foreach ($messages as $type => $m): ?>
        <div class='<?php echo $type ?>'>
            <ul>
                <?php foreach ($m as $message): ?>
                    <li><?php echo $message ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endforeach ?>
<?php endif ?>
