<?php if ($success): ?>
<script type='text/javascript'>
    window.opener.$('#refresh').click();
    window.close();
</script>
<?php endif; ?>
<div id='file-upload'>
    <form method='post' enctype='multipart/form-data'>
        <input type='file' name='upload' />
        <button type='submit'>Upload</button>
    </form>
</div>
