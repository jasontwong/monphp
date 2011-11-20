<?php

$controller_time = $this->controller_end - $this->controller_start;
$dh = 'debug_'.sha1(microtime());
$df_swap = substr($dh, 0, -30).'_swap';

?>

<style>
#<?php echo $dh ?> {}
#<?php echo $dh ?> { margin: 10px; padding: 20px; background-color: #EEE; line-height: 1.4em; }
#<?php echo $dh ?>-nav li { float: left; margin: 0 20px 0 0; }
#<?php echo $dh ?> hr { clear: both; visibility: hidden; margin: 0; padding: 0; border-width: 0; height: 0; }
#<?php echo $dh ?> div { clear: both; display: none; margin: 0; }
#<?php echo $dh ?> h1, #<?php echo $dh ?> h2, #<?php echo $dh ?> h3, 
#<?php echo $dh ?> h4, #<?php echo $dh ?> h5, #<?php echo $dh ?> h6
{
    margin: 10px 0; 
    font-weight: bold;
}
#<?php echo $dh ?> h1 { font-size: 2em; }
#<?php echo $dh ?> h2 { font-size: 1.6em; }
#<?php echo $dh ?> h3 { font-size: 1.2em; }
</style>

<script type='text/javascript'>

function <?php echo $df_swap ?>(section)
{
    var debug = document.getElementById('<?php echo $dh ?>'),
        div = document.getElementById('<?php echo $dh ?>-' + section),
        divs = debug.getElementsByTagName('div');
    for (x in divs)
    {
        if (x.search(/^\d+$/) != -1)
        {
            divs[x].style.display = 'none';
        }
    }
    div.style.display = 'block';
}

</script>

<div id='<?php echo $dh ?>'>

<h1>Debug Panel</h1>

<ul id='<?php echo $dh ?>-nav'>
    <li onclick='<?php echo $df_swap ?>("benchmarks")'>Benchmarks</li>
    <li onclick='<?php echo $df_swap ?>("router")'>Router</li>
    <li onclick='<?php echo $df_swap ?>("framework_information")'>Framework Information</li>
    <li onclick='<?php echo $df_swap ?>("predefined_variables")'>Predefined Variables</li>
</ul>

<hr>

<div id='<?php echo $dh ?>-benchmarks'>
<h2>Benchmarks</h2>
<ul>
    <li>Controller and template execution time: <?php echo substr($controller_time, 0, -9) ?> seconds</li>
</ul>
</div>

<div id='<?php echo $dh ?>-router'>
<h2>Router</h2>
<ul>
    <li>Route pattern or URI: <?php echo Router::pattern() ?></li>
    <li>Controller called: <?php echo Router::controller() ?></li>
    <li>Method called: <?php echo Router::method() ?></li>
    <li>Module that specified the route: <?php echo Router::source() ?></li>
</ul>
</div>

<div id='<?php echo $dh ?>-framework_information'>
<h2>Framework Information</h2>
<ul>
    <li>Active modules: <?php echo implode(', ', Data::query('_System', 'modules')) ?></li>
    <li>Version: <?php echo VERSION ?></li>
    <li>Default timezone: <?php echo Data::query('_Site', 'time_zone') ?></li>
</ul>
</div>

<div id='<?php echo $dh ?>-predefined_variables'>
<h2>Predefined Variables</h2>

<h3>SERVER</h3>
<pre><?php var_dump($_SERVER) ?></pre>

<h3>ENV</h3>
<pre><?php var_dump($_ENV) ?></pre>

<h3>SESSION</h3>
<pre><?php var_dump($_SESSION) ?></pre>

<h3>COOKIE</h3>
<pre><?php var_dump($_COOKIE) ?></pre>

<h3>POST</h3>
<pre><?php var_dump($_POST) ?></pre>

<h3>GET</h3>
<pre><?php var_dump($_GET) ?></pre>

<h3>FILES</h3>
<pre><?php var_dump($_FILES) ?></pre>
</div>

</div>
