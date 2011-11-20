<h2>Album: <?php echo $album['name']; ?></h2>
<?php echo $fh; ?>
<ul id='image-grid'>
<?php foreach ($images as $image): ?>
    <li>
        <div class='image-container'>
        </div>
    </li>
<?php endforeach; ?>
</ul>
