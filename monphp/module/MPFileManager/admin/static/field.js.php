(function($){
    "use strict";
    $(function(){
        var add_image = $('<a href="javascript:;">Add</a>'),
            image_container = $('<div class="MPFileManager_image_container" />'),
            field_caption = $('<input placeholder="Caption" type="text" />'),
            field_uri = $('<input placeholder="URL" type="text" />'),
            // {{{ reindex_images(container)
            reindex_images = function(container){
                if (container instanceof jQuery)
                {
                    var images = [],
                        captions = [],
                        uris = [];
                    container
                        .children()
                            .each(function(i){
                                var el = $(this),
                                    image = $('img', el),
                                    input = $('input', el);
                                image.data('index', i);
                                images.push(image.data('web_path'));
                                input
                                    .data('index', i)
                                    .each(function(){
                                        var el = $(this);
                                        if (el.data('type') == 'caption')
                                        {
                                            captions.push(el.val());
                                        }
                                        else if (el.data('type') == 'uri')
                                        {
                                            uris.push(el.val());
                                        }
                                    });
                            });
                    container
                        .data('images', images)
                        .data('captions', captions)
                        .data('uris', uris);
                    save_fields(container);
                }
            },
            // }}}
            // {{{ update_field()
            update_field = function(){
                var el = $(this),
                    container = el.closest('.MPFileManager_image_container'),
                    values = [];
                if (el.data('type') == 'caption')
                {
                    values = container.data('captions');
                    values[el.data('index')] = el.val();
                    container.data('captions', values);
                }
                else if (el.data('type') == 'uri')
                {
                    values = container.data('uris');
                    values[el.data('index')] = el.val();
                    container.data('uris', values);
                }
                save_fields(container);
            },
            // }}}
            // {{{ save_fields(element)
            save_fields = function(element){
                var el = $(this),
                    container = element instanceof jQuery 
                        ? element 
                        : el.closest('.MPFileManager_image_container'),
                    caption = container.data('field_caption'),
                    uri = container.data('field_uri'),
                    image = container.data('field_image'),
                    caption_data = container.data('type') == 'multi'
                        ? JSON.stringify(container.data('captions'))
                        : container.data('captions')[0],
                    uri_data = container.data('type') == 'multi'
                        ? JSON.stringify(container.data('uris'))
                        : container.data('uris')[0],
                    image_data = container.data('type') == 'multi'
                        ? JSON.stringify(container.data('images'))
                        : container.data('images')[0];
                image.val(image_data);
                if (container.data('has_caption'))
                {
                    caption.val(caption_data);
                }
                if (container.data('has_uri'))
                {
                    uri.val(uri_data);
                }
            },
            // }}}
            // {{{ append_image(element, image)
            append_image = function(element, image) {
                var image_div = $('<div />'),
                    remove = $('<a class="delete" href="javascript:;" />'),
                    field;
                remove
                    .text('Delete')
                    .data('index', image.data('index'))
                    .click(function(){
                        var el = $(this),
                            container = el.closest('.MPFileManager_image_container');
                        el.parent().remove();
                        container
                            .trigger('sortupdate');
                    });

                image_div
                    .append($('<div class="image" />').append(image))
                    .append(remove);
                if (element.data('has_caption'))
                {
                    field = field_caption
                                .clone(true)
                                .data('index', image.data('index'))
                                .val(image.data('caption'));
                    image_div.append(field);
                }
                if (element.data('has_uri'))
                {
                    field = field_uri
                                .clone(true)
                                .data('index', image.data('index'))
                                .val(image.data('uri'));
                    image_div.append(field);
                }
                element
                    .append(image_div)
                    .sortable('refresh');
            };
            // }}}
        // {{{ add_image
        add_image
            .data('is_single_selection', false)
            .click(function(){
                var el = $(this),
                    url = el.data('is_single_selection') 
                        ? '/admin/mod/MPFileManager/browse/single/'
                        : '/admin/mod/MPFileManager/browse/',
                    win_params = [
                        'height=400',
                        'width=850',
                        'scrollbars=yes',
                        'toolbar=no',
                        'location=no',
                        'menubar=no',
                        'copyhistory=no',
                        'directories=no'
                    ];
                url += '?name=' + el.data('input_name') + '&type=' + el.data('view_type');
                window.open(url, 'file_browse', win_params.join());
                $.windowMsg('selected_files', function(data) {
                    var browser_data = JSON.parse(data), 
                        files = browser_data.type === 'multi' 
                            ? browser_data.files
                            : [browser_data.files],
                        container = $('input[name="' + browser_data.input_name + '"]').siblings('.MPFileManager_image_container').data('type', browser_data.type),
                        images = container.data('images'),
                        captions = container.data('captions'),
                        uris = container.data('uris'),
                        img, i;
                    if (container.data('type') == 'single')
                    {
                        container.empty();
                        images = [];
                        captions = [];
                        uris = [];
                    }
                    for (i in files)
                    {
                        img = $('<img src="' + files[i].resized + '" />')
                                .data('web_path', files[i].web)
                                .data('caption', '')
                                .data('uri', '')
                                .data('index', images.length);
                        images.push(files[i].web);
                        captions.push('');
                        uris.push('');
                        append_image(container, img);
                    }
                    container
                        .data('images', images)
                        .data('captions', captions)
                        .data('uris', uris);
                    save_fields(container);
                });
            });
        // }}}
        // {{{ $('input.MPFileManagerBrowser')
        $('input.MPFileManagerBrowser')
            .bind('new_clone', function(){
                var el = $(this),
                    container = el.prevAll('.MPFileManager_image_container'),
                    caption = el.nextAll('.caption'),
                    uri = el.nextAll('.uri');
                container
                    .data('images', [])
                    .data('captions', [])
                    .data('uris', [])
                    .empty();
                el.val('');
                caption.val('');
                uri.val('');
            })
            .bind('name_updated', function(){
                var el = $(this),
                    container = el.prevAll('.MPFileManager_image_container'),
                    caption = el.nextAll('.caption'),
                    uri = el.nextAll('.uri'),
                    add_image_link = el.prevAll('a');
                container
                    .data('field_image', el)
                    .data('field_caption', caption)
                    .data('field_uri', uri);
                add_image_link
                    .data('input_name', el.attr('name'));
            })
            .each(function(i){
                var el = $(this),
                    container = image_container.clone(true).data('type', 'multi'),
                    add_image_link = add_image.clone(true),
                    has_caption = false,
                    has_uri = false,
                    images = [],
                    captions = [],
                    uris = [];
                add_image_link
                    .data('input_name', el.attr('name'));
                if (el.hasClass('SingleFile'))
                {
                    add_image_link
                        .text(add_image_link.text() + ' Single')
                        .data('is_single_selection', true);
                }
                if (el.hasClass('TypeImage'))
                {
                    add_image_link
                        .text(add_image_link.text() + ' Image')
                        .data('view_type', 'image');
                }
                else
                {
                    add_image_link
                        .text(add_image_link.text() + ' File')
                        .data('view_type', 'file');
                }
                el.before(add_image_link).before(container);
                if (el.val().length)
                {
                    images = add_image_link.data('is_single_selection')
                        ? [el.val()]
                        : JSON.parse(el.val());
                }
                el.siblings('input')
                    .each(function(){
                        var input = $(this),
                            value = input.val();
                        if (input.attr('type') == 'text')
                        {
                            if (input.hasClass('caption'))
                            {
                                container.data('field_caption', input);
                                has_caption = true;
                                if (value.length)
                                {
                                    captions = JSON.parse(value);
                                }
                            }
                            else if (input.hasClass('uri'))
                            {
                                container.data('field_uri', input);
                                has_uri = true;
                                if (value.length)
                                {
                                    uris = JSON.parse(value);
                                }
                            }
                            input.hide();
                        }
                    });
                container
                    .bind('sortupdate', function(e, ui){
                        reindex_images($(this));
                    })
                    .sortable()
                    .data('field_image', el)
                    .data('has_caption', has_caption)
                    .data('has_uri', has_caption)
                    .data('images', add_image_link.data('is_single_selection') ? el.val() : images)
                    .data('captions', captions)
                    .data('uris', uris);
                for (i in images)
                {
                    img = $('<img src="' + images[i].replace(/([^\/]+$)/, '_resized/$1').replace(/\.([^.]+$)/, '-browse.$1') + '" />');
                    img
                        .data('web_path', images[i])
                        .data('caption', has_caption ? captions[i] : '')
                        .data('uri', has_uri ? uris[i] : '')
                        .data('index', i)
                        .error(function(){
                            $(this).attr('src', $(this).data('web_path'));
                        });
                    append_image(container, img);
                }
            });
        // }}}
        field_caption
            .data('type', 'caption')
            .focusout(update_field);
        field_uri
            .data('type', 'uri')
            .focusout(update_field);
        $.initWindowMsg();
    });
}(jQuery));
