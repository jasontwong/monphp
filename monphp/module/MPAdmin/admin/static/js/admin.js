(function($){
    "use strict";
    $(function() {
        var nav_ul = $('#nav > ul'),
            quicklinks = $('#quicklinks'),
            ql_list = $('> ul', quicklinks),
            ql_add = $('<li>Add this page</li>'),
            ql_add_button = $('<button>Add</button>'),
            ql_cancel_button = $('<button>Cancel</button>'),
            ql_input = $('<li><input type="text" /></li>'),
            messages = $('#body > #messages');
        //{{{ navigation 
        $('> li[class!="open"] > ul', nav_ul).hide();
        $('> li', nav_ul)
            .click(function() {
                $(this).toggleClass('open');
                var titles = [];
                $('> li.open > div', nav_ul).each(function() {
                    titles.push($(this).text());
                });
                $('> ul', this).slideToggle(300, function(){
                    $.post(
                        '/admin/rpc/MPAdmin/nav/',
                        { json: JSON.stringify(titles) },
                        function(data, tStatus)
                        {
                            // nothing?
                        }
                    );
                });
            })
            .mouseover(function() {
                var li = $(this);
                if (!li.hasClass('open'))
                {
                    li.addClass('hover');
                }
            })
            .mouseout(function() {
                $(this).removeClass('hover');
            });
        $('> li > ul', nav_ul)
            .click(function(e) {
                e.stopImmediatePropagation();
            });

        //}}}
        //{{{ messages
        $('ul > li', messages)
            .on({
                add_closer: function() {
                    $(this)
                        .append('<div class="close" style="display: none;"><a><span>close</span></a></div>')
                        .data('closer', $('> div.close', this));
                },
                mouseover: function() {
                    $(this).data('closer').show();
                },
                mouseout: function() {
                    $(this).data('closer').hide();
                }
            })
            .trigger('add_closer');
        $('ul > li > div.close', messages)
            .on({
                click: function() {
                    var el = $(this),
                        ul = el.closest('ul');
                    el.parent().remove();
                    if (ul.children().length < 1)
                    {
                        ul.remove();
                    }
                }
            });
        //}}}
        // {{{ quicklinks
        ql_cancel_button
            .click(function(){
                $(this).parent().remove();
            });

        ql_add_button
            .click(function(){
                var el = $(this),
                    elements = {},
                    label = el.prev().val();
                if (label.length)
                {
                    el.next().click();
                    ql_add
                        .before('<li><a href="'+location.pathname+'">'+label+'</a></li>')
                        .siblings()
                            .each(function(){
                                var anchor = $('> a', this);
                                elements[anchor.attr('href')] = anchor.text();
                            });
                    $.post(
                        '/admin/rpc/MPAdmin/quicklinks/',
                        { json: JSON.strinigfy(elements) },
                        function(data, tStatus)
                        {
                            ql_list.hide();
                        }
                    );
                }
            });
        ql_input
            .append(ql_add_button)
            .append(ql_cancel_button);

        $('> button', ql_input).click(function(){
        });

        ql_add.click(function(){
            ql_input
                .append(ql_add_button)
                .append(ql_cancel_button);
            $(this)
                .after(ql_input.clone(true));
        });

        ql_list.append(ql_add);
        quicklinks.hover(
            function()
            {
                ql_list.show();
            },
            function()
            {
                ql_list.hide();
            }
        );

        // }}}
    });
}(jQuery));
