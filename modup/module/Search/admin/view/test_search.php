<?php echo $fh; ?>

<?php if (isset($results)): ?>
    <?php foreach ($results as $module => $result): ?>
        <h2><?php echo $module; ?></h2>
        <pre>
        <?php var_dump($result); ?>
        </pre>
    <?php endforeach; ?>
<?php endif; ?>
