
<?php if (!is_null($is_valid)): ?>

    <?php if ($is_valid): ?>

            <a href='/install/settings/'>proceed: site settings</a>

    <?php endif ?>

<?php endif ?>

<?php if (is_writeable($dbfile)) echo $fh; ?>
