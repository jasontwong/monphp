        </div><!-- #content -->
    </section><!-- #body -->

    <footer id='footer' class='clear'>
        <span><strong><?php echo MPData::query('_Site', 'title'); ?></strong> Website Management</span>
        <?php if (MPAdmin::is_logged_in()): ?>
            <span><a href='/admin/'>Admin Dashboard</a></span>
            <span><a href='/admin/logout/'>Logout</a></span>
        <?php endif; ?>
        <span class='site_credit'>Site development by <a href='http://www.jasontwong.com' target='_blank'>Jason T. Wong</a></span>
    </footer>

</div><!-- #container -->

<?php MPModule::h('mpadmin_footer') ?>

</body>
</html>
