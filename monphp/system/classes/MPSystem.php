<?php

class MPSystem
{
    // {{{ protected static function get_dependant_scripts($handle, $scripts, &$js_order, &$counter)
    /*
     * @returns bool
     */
    protected static function get_dependant_scripts($handle, $scripts, &$holder, &$counter)
    {
        if (!ake($handle, $scripts))
        {
            return FALSE;
        }
        global $_mp;
        $l_js = deka(array(), $_mp, 'scripts', 'loaded');
        $script = $scripts[$handle];
        $deps = array_diff($script['deps'], array_keys($holder), $l_js);
        $tmp = array();
        foreach ($deps as &$dep)
        {
            if (!in_array($dep, $holder))
            {
                $load = self::get_dependant_scripts($dep, $scripts, $holder, $counter);
                if (!$load)
                {
                    return FALSE;
                }
                $tmp[] = $dep;
            }
        }
        $tmp[] = $handle;
        $holder = array_merge($holder, $tmp);
        return TRUE;
    }
    // }}}
    // {{{ protected static function get_queued_scripts($for_footer = FALSE)
    /*
     * @returns array
     */
    protected static function get_queued_scripts($for_footer = FALSE)
    {
        global $_mp;
        $e_js = deka(array(), $_mp, 'scripts', 'enqueued');
        $r_js = deka(array(), $_mp, 'scripts', 'registered');
        $l_js = deka(array(), $_mp, 'scripts', 'loaded');
        $scripts = array_merge($e_js, $r_js);
        $js_order = array();
        $counter = 0;
        foreach ($e_js as $handle => &$script)
        {
            if ($script['in_footer'] === $for_footer)
            {
                $deps = array_diff($script['deps'], array_keys($js_order), $l_js);
                $load = TRUE;
                $holder = array();
                foreach ($deps as &$dep)
                {
                    $load = self::get_dependant_scripts($dep, $scripts, $holder, $counter);
                }
                if ($load)
                {
                    foreach ($holder as &$hold)
                    {
                        $js_order[$hold] = $counter++;
                    }
                    $js_order[$handle] = $counter++;
                }
                unset($_mp['scripts']['enqueued'][$handle]);
            }
        }
        asort($js_order);
        $js = array();
        foreach ($js_order as $handle => &$order)
        {
            if (ake($handle, $scripts) && !in_array($handle, $l_js))
            {
                $l_js[] = $handle;
                $script = &$scripts[$handle];
                $js[] = sprintf(
                    '<script src="%s?ver=%s"></script>', 
                    $script['src'],
                    $script['ver'] !== FALSE ? $script['ver'] : MP_VERSION
                );
            }
        }
        $_mp['scripts']['loaded'] = $l_js;
        return $js;
    }
    // }}}
    //{{{ public function cb_mpsystem_foot($mods = array())
    public function cb_mpsystem_foot($mods = array())
    {
        $html = array();
        foreach ($mods as $mod => &$data)
        {
            $html = array_merge($html, $data);
        }
        return $html;
    }
    //}}}
    // {{{ public function cb_mpsystem_head($mods = array())
    public function cb_mpsystem_head($mods = array())
    {
        $html = array();
        foreach ($mods as $mod => &$data)
        {
            $html = array_merge($html, $data);
        }
        return $html;
    }
    // }}}
    // {{{ public function cb_mpsystem_print_head()
    public function cb_mpsystem_print_head()
    {
        $html = MPModule::h('mpsystem_head');
        foreach ($html as &$output)
        {
            echo "$output\n";
        }
    }
    // }}}
    //{{{ public function cb_mpsystem_print_foot()
    public function cb_mpsystem_print_foot()
    {
        $html = MPModule::h('mpsystem_foot');
        foreach ($html as &$output)
        {
            echo "$output\n";
        }
    }
    //}}}
    // {{{ public function hook_mpsystem_foot()
    public function hook_mpsystem_foot()
    {
        $html = array();
        $js = self::get_queued_scripts(TRUE);
        return array_merge($html, $js);
    }
    // }}}
    // {{{ public function hook_mpsystem_head()
    public function hook_mpsystem_head()
    {
        global $_mp;
        $html = array();
        $e_css = deka(array(), $_mp, 'styles', 'enqueued');
        $r_css = deka(array(), $_mp, 'styles', 'registered');
        $styles = array_merge($e_css, $r_css);
        $css_order = array();
        $counter = 0;
        foreach ($e_css as $handle => &$style)
        {
            $deps = array_diff($style['deps'], array_keys($css_order));
            $load = TRUE;
            foreach ($deps as &$dep)
            {
                if (!ake($dep, $styles))
                {
                    $load = FALSE;
                    break;
                }
                $css_order[$dep] = $counter++;
            }
            if ($load)
            {
                $css_order[$handle] = $counter++;
            }
            unset($_mp['styles']['enqueued'][$handle]);
        }
        asort($css_order);
        $l_css = deka(array(), $_mp, 'styles', 'loaded');
        $css = array();
        foreach ($css_order as $handle => &$order)
        {
            if (ake($handle, $styles) && !in_array($handle, $l_css))
            {
                $l_css[] = $handle;
                $style = &$styles[$handle];
                $css[] = sprintf(
                    '<link rel="stylesheet" href="%s?ver=%s" media="%s">',
                    $style['src'],
                    $style['ver'] !== FALSE ? $style['ver'] : MP_VERSION,
                    $style['media']
                );
            }
        }
        $_mp['styles']['loaded'] = $l_css;
        $js = self::get_queued_scripts();
        return array_merge($html, $css, $js);
    }
    // }}}
    // {{{ public function hook_mpsystem_start()
    public function hook_mpsystem_start()
    {
        mp_register_script('jquery', '/js/libs/jquery-1.7.1.min.js', array(), '1.7.1', TRUE);
        mp_enqueue_script('modernizr', '/js/libs/modernizr-2.5.3.min.js', array(), '2.5.3');
        mp_enqueue_style('screen', '/css/screen.css');
    }
    // }}}
}
