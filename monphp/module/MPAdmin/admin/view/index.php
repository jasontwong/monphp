<?php include DIR_MODULE.'/MPAdmin/admin/template/header.php' ?>

<div id='admin_dashboard'>

    <?php foreach ($dashboard as $side => $boards): ?>

        <div class='admin_dashboard_side' id='admin_dashboard_<?php echo $side ?>'>

            <?php foreach ($boards as $board): ?>

                <?php
                    $class = 'admin_dashboard_element_'.deka('opened', $board, 'fold');
                ?>

                <div class='admin_dashboard_element <?php echo $class ?>' id='admin_dashboard_element__<?php echo $board['key'] ?>'>
                    <div class='title'><?php echo $board['title'] ?></div>
                    <div class='content'><?php echo $board['content'] ?></div>
                </div>

            <?php endforeach ?>

        </div>

    <?php endforeach ?>

</div>

<?php include DIR_MODULE.'/MPAdmin/admin/template/footer.php' ?>
