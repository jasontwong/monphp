    <div id='messages'>
    <?php if (eka($_SESSION, 'admin', 'messages') && !empty($_SESSION['admin']['messages'])): ?>

            <?php $messages = $_SESSION['admin']['messages']; ?>
            <?php foreach ($messages as $level => $msgs): ?>

                <ul class='<?php echo $level; ?>'>

                    <?php foreach ($msgs as $msg): ?>

                        <li><?php echo $msg; ?></li>

                    <?php endforeach ?>

                </ul>

            <?php endforeach; ?>
            <?php unset($_SESSION['admin']['messages']); ?>

    <?php endif ?>
    </div>
