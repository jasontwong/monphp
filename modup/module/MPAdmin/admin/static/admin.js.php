//{{{ admin object
/**
 * To be used globally
 */
var admin = {
    //{{{ messenger
    messenger: {
        checked: false,
        board: false,
        has_board: function() 
        {
            if (!this.checked)
            {
                this.board = $('#body > #messages');
                if (this.board.length == 0)
                {
                    this.board = false;
                }
            }
            return this.board != false;
        },
        has_level: function(level)
        {
            return $('#body > #messages > ul.' + level).length;
        },
        add_board: function()
        {
            if (!this.has_board())
            {
                $('#body').prepend('<div id="messages" />');
                this.board = $('#body > #messages');
            }
        },
        add_level: function(level)
        {
            if (!this.has_level(level))
            {
                this.board.prepend('<ul class="' + level + '" />');
            }
        },
        add: function(level, message)
        {
            this.add_board();
            this.add_level(level);
            var item = $('<li>' + message + '</li>');
            $('#body > #messages > ul.' + level)
                .append(item);
            item
                .trigger('add_closer');
        }
    },

    //}}}
    //{{{ JSON
    /**
     * Adapted from http://www.json.org/json_parse.js
     * Usage:
     *      admin.JSON.make({foo: 'bar'});
     *      admin.JSON.read('{"foo":"bar"}');
     */
    JSON: (function () {
        //{{{ prototype tweaking
        if (typeof Date.prototype.toJSON !== 'function') 
        {
            Date.prototype.toJSON = function (key) 
            {
                return this.getUTCFullYear()   + '-' +
                     f(this.getUTCMonth() + 1) + '-' +
                     f(this.getUTCDate())      + 'T' +
                     f(this.getUTCHours())     + ':' +
                     f(this.getUTCMinutes())   + ':' +
                     f(this.getUTCSeconds())   + 'Z';
            };
            String.prototype.toJSON =
            Number.prototype.toJSON =
            Boolean.prototype.toJSON = function (key) 
            {
                return this.valueOf();
            };
        }
        //}}}
        //{{{ vars
        var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
            escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
            gap,
            indent,
            meta = {    // table of character substitutions
                '\b': '\\b',
                '\t': '\\t',
                '\n': '\\n',
                '\f': '\\f',
                '\r': '\\r',
                '"' : '\\"',
                '\\': '\\\\'
            },
            rep,
        //}}}
        //{{{ f = function(n)
            f = function(n) 
            {
                return n < 10 ? '0' + n : n;
            },

        //}}}
        //{{{ quote = function(string)
            quote = function(string) 
            {
                escapable.lastIndex = 0;
                return escapable.test(string) ?
                    '"' + string.replace(escapable, function (a) {
                        var c = meta[a];
                        return typeof c === 'string' ? c :
                            '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                    }) + '"' :
                    '"' + string + '"';
            },

        //}}}
        //{{{ str = function(key, holder)
            str = function(key, holder) 
            {
                var i,          // The loop counter.
                    k,          // The member key.
                    v,          // The member value.
                    length,
                    mind = gap,
                    partial,
                    value = holder[key];
                if (value && typeof value === 'object' &&
                    typeof value.toJSON === 'function') 
                    {
                        value = value.toJSON(key);
                    }
                if (typeof rep === 'function') 
                {
                    value = rep.call(holder, key, value);
                }
                switch (typeof value) 
                {
                    case 'string':
                        return quote(value);
                    case 'number':
                        return isFinite(value) ? String(value) : 'null';
                    case 'boolean':
                    case 'null':
                        return String(value);
                    case 'object':
                        if (!value) 
                        {
                            return 'null';
                        }
                        gap += indent;
                        partial = [];
                        if (Object.prototype.toString.apply(value) === '[object Array]') 
                        {
                            length = value.length;
                            for (i = 0; i < length; i += 1) 
                            {
                                partial[i] = str(i, value) || 'null';
                            }
                            v = partial.length === 0 ? '[]' :
                                gap ? '[\n' + gap +
                                    partial.join(',\n' + gap) + '\n' +
                                        mind + ']' :
                                  '[' + partial.join(',') + ']';
                            gap = mind;
                            return v;
                        }
                        if (rep && typeof rep === 'object') 
                        {
                            length = rep.length;
                            for (i = 0; i < length; i += 1) 
                            {
                                k = rep[i];
                                if (typeof k === 'string') 
                                {
                                    v = str(k, value);
                                    if (v) 
                                    {
                                        partial.push(quote(k) + (gap ? ': ' : ':') + v);
                                    }
                                }
                            }
                        } 
                        else 
                        {
                            for (k in value) 
                            {
                                if (Object.hasOwnProperty.call(value, k)) 
                                {
                                    v = str(k, value);
                                    if (v) 
                                    {
                                        partial.push(quote(k) + (gap ? ': ' : ':') + v);
                                    }
                                }
                            }
                        }
    
                        v = partial.length === 0 ? '{}' :
                            gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' +
                                mind + '}' : '{' + partial.join(',') + '}';
                        gap = mind;
                        return v;
                }
            },

        //}}}
        //{{{ stringify = function(value, replacer, space)
            stringify = function(value, replacer, space) 
            {
                var i, gap = '', indent = '';
                if (typeof space === 'number') 
                {
                    for (i = 0; i < space; i += 1) 
                    {
                        indent += ' ';
                    }
                } 
                else if (typeof space === 'string') 
                {
                    indent = space;
                }
                rep = replacer;
                if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                        typeof replacer.length !== 'number')) 
                        {
                            throw new Error('JSON.stringify');
                        }
                return str('', {'': value});
            },

        //}}}
        //{{{ parse = function (text, reviver)
            parse = function (text, reviver) 
            {
                var j;
                function walk(holder, key) 
                {
                    var k, v, value = holder[key];
                    if (value && typeof value === 'object') 
                    {
                        for (k in value) 
                        {
                            if (Object.hasOwnProperty.call(value, k)) 
                            {
                                v = walk(value, k);
                                if (v !== undefined) 
                                {
                                    value[k] = v;
                                } 
                                else 
                                {
                                    delete value[k];
                                }
                            }
                        }
                    }
                    return reviver.call(holder, key, value);
                }
                cx.lastIndex = 0;
                if (cx.test(text)) 
                {
                    text = text.replace(cx, function (a) {
                        return '\\u' +
                            ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                    });
                }
    
                if (/^[\],:{}\s]*$/.
    test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
    replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
    
                    j = eval('(' + text + ')');
    
                    return typeof reviver === 'function' ?
                        walk({'': j}, '') : j;
                }
    
                throw new SyntaxError('JSON.parse');
            };
        //}}}
        return { 
            make: stringify, 
            read: parse 
        };
    })()
    //}}}
};

//}}}
$(document).ready(function() {
$('.nojs').remove();
//{{{ messages
$('#body > #messages > ul > li')
    .live('add_closer', function() {
        $(this)
            .append('<div class="close" style="display: none;"><a><span>close</span></a></div>')
            .data('closer', $('> div.close', this));
    })
    .live('mouseover', function() {
        $(this).data('closer').show();
    })
    .live('mouseout', function() {
        $(this).data('closer').hide();
    })
    .trigger('add_closer');
$('#body > #messages > ul > li > div.close')
    .live('click', function() {
        var el = $(this),
            ul = el.closest('ul');
        el.parent().remove();
        if (ul.children().length < 1)
        {
            ul.remove();
        }
    });
//}}}
//{{{ navigation 
var nav_ul = $('#nav > ul');
$('> li[class!="open"] > ul', nav_ul).hide();
$('> li', nav_ul)
    .click(function() {
        $(this).toggleClass('open');
        var titles = new Array();
        $('> li.open > div', nav_ul).each(function() {
            titles.push($(this).text());
        });
        $('> ul', this).slideToggle(300, function(){
            $.post(
                '/admin/rpc/MPAdmin/nav/',
                { json: admin.JSON.make(titles) },
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
// {{{ quicklinks
var quicklinks = $('#quicklinks'),
    ql_list = $('> ul', quicklinks),
    ql_add = $('<li>Add this page</li>'),
    ql_add_button = $('<button>Add</button>'),
    ql_cancel_button = $('<button>Cancel</button>'),
    ql_input = $('<li><input type="text" /></li>');

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
                { json: admin.JSON.make(elements) },
                function(data, tStatus)
                {
                    ql_list.hide();
                }
            );
        }
    });
ql_input
    .append(ql_add_button)
    .append(ql_cancel_button)

$('> button', ql_input).click(function(){
});

ql_add.click(function(){
    ql_input
        .append(ql_add_button)
        .append(ql_cancel_button)
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
