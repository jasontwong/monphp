$(document).ready(function(){
    var notification_area = $('<div id="notification_area" />')
            .hide()
            .data('has_messages', false)
            .appendTo('#container'),
        data = {};
    $.post(
        '/admin/rpc/Notification/notify/',
        { data: admin.JSON.make(data) },
        function(notices){
            if (notices.length < 1)
            {
                return;
            }
            for (var i in notices)
            {
                var note = notices[i],
                    messages = note.messages;
                if (messages.length < 1)
                {
                    continue;
                }
                var notification = $('<div class="' + note.type + '" />'),
                    message_ul = $('<ul />');
                for (var j in messages)
                {
                    $('<li>' + messages[j] + '</li>')
                        .appendTo(message_ul);
                }
                message_ul
                    .appendTo(notification);
                notification
                    .appendTo(notification_area);
                notification_area.data('has_messages', true);
            }
            if (notification_area.data('has_messages'))
            {
                var close_area = $('<a>Close</a>');
                close_area
                    .click(function(){
                        $(this)
                            .parent()
                            .slideUp();
                    });
                notification_area
                    .slideDown('slow')
                    .append(close_area);
                /*
                setTimeout(function(){
                    notification_area
                        .slideUp();
                }, 5000);
                */
            }
        }, 'json');
});
