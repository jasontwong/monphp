$(document).ready(function() {
//{{{ tabs
var tab_marker = $('.tabbed:first').prev();
// print tab_marker;
tab_marker.after('<ul class="clear">' +
                    $.map($('.tabbed'), function(el, i) {
                        $(el).addClass('tab').attr('id', 'tab-' + i);
                        return '<li><a href="#tab-' + i + '">' + $('> div.label', el).remove().text() + '</a></li>';
                    }).join('') +
                '</ul>');
tab_marker.parent().tabs();

//}}}
});
