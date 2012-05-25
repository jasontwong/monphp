<?php 
$autoslug = MPData::query('MPContent', 'autoslug');
$autoslug = is_string($autoslug) && strlen($autoslug) === 1 ? $autoslug : '-';
?>
(function($){
    "use strict";
    $(function() {
        // TODO copy this function for fields with default data?
        //{{{ title and auto-slugging
        $('input[name="entry[slug][data]"], ' +
          'input[name="field[slug][data]"], ' + 
          'input[name="category[slug][data]"]')
            .data('auto_slug', true)
            .keypress(function() {
                $(this).data('auto_slug', false);
            });

        $('input[name="entry[title][data]"], ' +
          'input[name="field[name][data]"], ' + 
          'input[name="category[name][data]"]')
            .data('slug', false)
            .keyup(function(e) {
                var input = $(this),
                    slug = input.data('slug'),
                    new_char = '<?php echo $autoslug; ?>';
                if (slug === false)
                {
                    var sname = input.attr('name').replace(/^([^\[]+)\[(?:title|name)\](.+)$/, '$1[slug]$2');
                    slug = $('input[name="' + sname + '"]');
                    input.data('slug', slug);
                }
                if (slug.data('auto_slug'))
                {
                    var val = $(this).val()
                                .replace(/[^\w\s-_]+/g, '')
                                .replace(/[\s-_ ]+/g, new_char)
                                .toLowerCase(),
                        end = val.length - 1;
                    if (val.charAt(0) == new_char)
                    {
                        val = val.substr(1);
                    }
                    if (val.charAt(end) == new_char)
                    {
                        val = val.substr(0, end);
                    }
                    slug.val(val);
                }
                e.stopImmediatePropagation();
            });

        //}}}
    });
}(jQuery));
