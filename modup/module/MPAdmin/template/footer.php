</div>
</div>

<div id='footer' class='clear'>
    <span><strong><?php echo MPData::query('_Site', 'title'); ?></strong> Website Management</span>
    <span><a href='/admin/'>MPAdmin Dashboard</a></span>
    <span><a href='/admin/logout/'>Logout</a></span>
    <span class='site_credit'>Site development by <a href='http://kratedesign.com' target='_blank'>Krate</a></span>
</div>

</div>

<?php echo MPModule::h('admin_js') ?>

<?php MPModule::h('body_end') ?>

</body>
</html>
