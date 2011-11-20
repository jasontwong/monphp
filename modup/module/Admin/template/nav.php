<div id='nav'>
<?php 

$nav = Module::h('admin_nav'); 
$open_nav = User::setting('admin', 'nav');

foreach ($nav as $title => $links)
{
    if (empty($links))
    {
        continue;
    }
    echo is_array($open_nav) && in_array($title, $open_nav)
        ? '<ul><li class="open"><div>'.$title.'</div><ul>'
        : '<ul><li><div>'.$title.'</div><ul>';
    foreach ($links as $link)
    {
        echo "<li>$link</li>";
    }
    echo '</ul></li></ul>';
}

?>
</div>
