// {{{ function get_var(q,s) 
function get_var(q,s) 
{
    s = s ? s : window.location.search;
    var re = new RegExp('&'+q+'(?:=([^&]*))?(?=&|$)','i');
    return (s=s.replace(/^\?/,'&').match(re)) ? (typeof s[1] == 'undefined' ? '' : decodeURIComponent(s[1])) : undefined;
}
// }}}
// {{{ function trim(s)
function trim(s)
{
	var l=0; var r=s.length -1;
	while(l < s.length && s[l] == ' ')
	{	l++; }
	while(r > l && s[r] == ' ')
	{	r-=1;	}
	return s.substring(l, r+1);
}
// }}}
$(function() {
    var files = [],
        root_dir = '<?php echo FileManager::get_file_path(); ?>',
        action, processing, dir, web, hold_dir, hold_files, hold_action, selected_item,
        view_type = get_var('type'),
        field_file = $('input[name="file"]'),
        form_browser = $('form[name="browser"]'),
        div_files = $('#files'),
        div_nav = $('#nav'),
        div_tools = $('#tools'),
        tools = $('<div />').css('float', 'right'),
        tool_create_dir = $('<a />').css('margin-right', '10px'),
        tool_upload_file = tool_create_dir.clone(),
        tool_refresh = tool_create_dir.clone().attr('id', 'refresh'),
        is_tinymce = location.pathname.search('/tinymce/') !== -1,
        is_single_selection = is_tinymce || location.pathname.search('/single/') !== -1,
        input_name = get_var('name'),
        control_delete = $('<span />'),
        control_rename = $('<span />'),
        control_cut = $('<span />'),
        control_copy = $('<span />'),
        control_paste = $('<span />'),
        control_tools = $('<div />'),
        control_select_all = $('<a href="javascript:;" />'),
        control_unselect_all = $('<a href="javascript:;" />'),
        control_submit = $('<a href="javascript:;" />'),
        control_cancel = $('<a href="javascript:;" />'),
        // {{{ control_disable()
        control_disable = function(){
            $(this)
                .data('enabled', false)
                .css({
                    'cursor' : 'normal',
                    'color' : '#000'
                });
        },
        // }}}
        // {{{ control_enable()
        control_enable = function(){
            $(this).data('enabled', true).css({
                'cursor' : 'pointer',
                'color' : '#4896C5'
            });
        },
        // }}}
        // {{{ disable_controls()
        disable_controls = function() {
            var selected = $('.items .selected', div_files);
            if (!selected.length)
            {
                if (hold_dir == undefined || hold_files == undefined)
                {
                    control_paste.trigger('disable');
                }
                control_rename.trigger('disable');
                control_delete.trigger('disable');
                control_cut.trigger('disable');
                control_copy.trigger('disable');
                selected_item = undefined;
            }
        },
        // }}}
        // {{{ enable_controls()
        enable_controls = function() {
            var selected = $('.items .selected', div_files);
            console.log(selected);
            if (selected.length)
            {
                if (selected.length == 1)
                {
                    control_rename.trigger('enable');
                }
                else
                {
                    control_rename.trigger('disable');
                }
                control_paste.trigger('enable');
                control_delete.trigger('enable');
                control_cut.trigger('enable');
                control_copy.trigger('enable');
            }
        },
        // }}}
        // {{{ item_lolight(ele, hex)
        item_lolight = function(ele, hex) {
            ele.trigger('lolight', hex);
            disable_controls();
        },
        // }}}
        // {{{ item_hilight(ele, hex)
        item_hilight = function(ele, hex) {
            ele.trigger('hilight', hex);
            enable_controls();
        };
        // }}}
        // {{{ load_dir(path)
        load_dir = function(path) {
            action = 'list';
            dir = path;
            web = path.substr(path.search('/file/upload'));
            $('.current_path', div_tools).text(web);
            process_action();
        },
        // }}}
        // {{{ build_grid(data)
        build_grid = function(data) {
            var container = $('<div class="grid_view view" />'),
                grid = $('<div class="items" />'),
                item = $('<div class="item selectable" />'),
                items = data.files,
                web_path = data.web,
                info = data.info;
            container
                .append('<span>Folders: ' + info.total_dirs + ', Files: ' + info.total_files + ', Total Size: ' + info.total_size)
                .append(control_tools);
            for (i in items)
            {
                var name = items[i].name,
                    ext = items[i].ext,
                    resized_path = items[i].resized_path,
                    mime = items[i].mime,
                    stat = items[i].stat,
                    type = mime[0],
                    file = $('<span />'),
                    file_path = name == '..' 
                        ? dir.replace(/\/[\w\s-]+$/, '') 
                        : dir + '/' + name,
                    web_path = name == '..' 
                        ? '' 
                        : web + '/' + name,
                    tmp_item = item.clone(),
                    image = $('<img />'),
                    image_info = $('<div class="info" />'),
                    image_path = resized_path;
                if (resized_path === undefined)
                {
                    resized_path = '';
                }
                if (mime[0] == 'folder')
                {
                    stat.nice_size = '';
                    image_path = '/file/module/FileManager/icon-folder.png';
                    tmp_item.removeClass('selectable');
                    if (name == '..')
                    {
                        image_path = '/file/module/FileManager/icon-back.png';
                        type = 'parent';
                        stat.nice_mtime = '';
                    }
                }
                else
                {
                    type = mime[1];
                }
                file.text(name);
                if ((i + 1) % 5 === 0)
                {
                    tmp_item.css('margin-right', '0px');
                }
                tmp_item
                    .data('ext', ext)
                    .data('name', name)
                    .data('resized_path', resized_path)
                    .data('type', mime[0])
                    .data('web_path', web_path)
                    .data('file_path', file_path)
                    .click(function(){
                        var el = $(this);
                        if (el.data('type') == 'folder')
                        {
                            el.siblings('.selected').removeClass('selected');
                            load_dir(el.data('file_path'));
                            disable_controls();
                        }
                        else
                        {
                            if (is_single_selection)
                            {
                                select_files({web: el.data('web_path'), resized: el.data('resized_path')});
                            }
                            else
                            {
                                if (el.hasClass('selected'))
                                {
                                    el.removeClass('selected');
                                    disable_controls();
                                }
                                else
                                {
                                    if (!el.siblings('.selected').length)
                                    {
                                        selected_item = el;
                                    }
                                    el.addClass('selected');
                                    enable_controls();
                                }
                            }
                        }
                    })
                    .dblclick(function(){
                        var el = $(this);
                        if (el.data('type') !== 'folder')
                        {
                            select_files([{web: el.data('web_path'), resized: el.data('resized_path')}]);
                        }
                    })
                    .append(
                        $('<div class="image" />')
                            .append(image.clone().attr('src', image_path))
                    )
                    .append(
                        image_info
                            .clone()
                            .append(file)
                            .append(file.clone().text(stat.nice_size))
                    );
                grid.append(tmp_item);
            }
            grid.append(
                $('<div class="window-tools" />')
                    .append(control_submit.clone(true))
                    .append(' | ')
                    .append(control_cancel.clone(true))
            );
            return container.append(grid);
        },
        // }}}
        // {{{ build_list(data)
        build_list = function(data) {
            // {{{ data prep
            var container = $('<div class="list_view view" />'),
                list = $('<table class="items" />'),
                items = data.files,
                web_path = data.web,
                info = data.info,
                row = $('<tr />'),
                header = $('<th />'),
                cell = $('<td />'),
                list_disable = function(){
                    $(this)
                        .data('enabled', false)
                        .css({
                            'cursor' : 'normal',
                            'color' : '#000'
                        });
                },
                list_enable = function(){
                    $(this).data('enabled', true).css({
                        'cursor' : 'pointer',
                        'color' : '#4896C5'
                    });
                },
                list_tools = $('<div />'),
                list_delete = $('<span />')
                    .text('Delete')
                    .data('enabled', false)
                    .bind('disable', list_disable)
                    .bind('enable', list_enable)
                    .click(function(){
                        if ($(this).data('enabled'))
                        {
                            var overlay = $('<div />'),
                                button_ok = $('<button />')
                                    .text('Okay')
                                    .click(function(){
                                        action = 'delete';
                                        files = [];
                                        $(':checked', list)
                                            .each(function(){
                                                files.push($(this).data('name'));
                                            });
                                        process_action();
                                        overlay.remove();
                                    }),
                                button_cancel = button_ok
                                    .clone()
                                    .text('Cancel')
                                    .click(function(){
                                        overlay.remove();
                                        tool_refresh.click();
                                    });
                            overlay
                                .css({
                                    'background-color' : '#FFF',
                                    'position' : 'fixed',
                                    'border' : '1px solid #000',
                                    'width' : '400px',
                                    'height' : '200px',
                                    'z-index' : '1000',
                                    'top' : '50%',
                                    'left' : '50%'
                                })
                                .append(button_ok)
                                .append(button_cancel)
                                .appendTo('#content');
                        }
                    }),
                list_rename = $('<span />')
                    .text('Rename')
                    .data('enabled', false)
                    .bind('disable', list_disable)
                    .bind('enable', list_enable)
                    .click(function(){
                        if ($(this).data('enabled') && selected_item.data('selected'))
                        {
                            var new_name = $('<input type="text" />'),
                                old_name = $('<input type="hidden" />').val(selected_item.data('name')),
                                extension = $('<span>' + selected_item.data('ext') + '</span>'),
                                submit_btn = $('<input type="image" alt="submit" />'),
                                cancel_btn = $('<input type="image" alt="cancel" />')
                                    .click(function(){
                                        tool_refresh.click();
                                        return false;
                                    }),
                                form = $('<form />')
                                    .submit(function(){
                                        action = 'rename';
                                        files = [old_name.val(), new_name.val()];
                                        process_action();
                                        return false;
                                    });
                            div_files
                                .empty()
                                .append('<span>Current dir is: ' + dir + ', Current name is: ' + old_name.val() + '</span>')
                                .append(form.append(new_name).append(old_name).append(extension).append(submit_btn).append(cancel_btn));
                        }
                    }),
                list_cut = $('<span />')
                    .text('Cut')
                    .data('enabled', false)
                    .bind('disable', list_disable)
                    .bind('enable', list_enable)
                    .click(function(){
                        if ($(this).data('enabled'))
                        {
                            hold_files = [];
                            $(':checked', list)
                                .each(function(){
                                    hold_files.push($(this).data('name'));
                                });
                            if (hold_files.length)
                            {
                                hold_dir = dir;
                                hold_action = 'move';
                                list_paste.trigger('enable');
                            }
                        }
                    }),
                list_copy = $('<span />')
                    .text('Copy')
                    .data('enabled', false)
                    .bind('disable', list_disable)
                    .bind('enable', list_enable)
                    .click(function(){
                        if ($(this).data('enabled'))
                        {
                            hold_files = [];
                            $(':checked', list)
                                .each(function(){
                                    hold_files.push($(this).data('name'));
                                });
                            if (hold_files.length)
                            {
                                hold_dir = dir;
                                hold_action = 'copy';
                                list_paste.trigger('enable');
                            }
                        }
                    }),
                list_paste = $('<span />')
                    .text('Paste')
                    .data('enabled', false)
                    .bind('disable', list_disable)
                    .bind('enable', function(){
                        if (hold_action != undefined && hold_dir != undefined && hold_files != undefined && hold_dir.length && hold_files.length)
                        {
                            $(this).data('enabled', true).css({
                                'cursor' : 'pointer',
                                'color' : '#4896C5'
                            });
                        }
                        else
                        {
                            $(this).trigger('disable');
                        }
                    })
                    .click(function(){
                        if ($(this).data('enabled'))
                        {
                            files = hold_files;
                            files.push(hold_dir);
                            action = hold_action;
                            process_action();
                            hold_files = undefined;
                            hold_dir = undefined;
                            hold_action = undefined;
                        }
                    }),
                list_check_all = $('<a />')
                    .text('Check All')
                    .click(function(){
                        $('input[type="checkbox"]', list)
                            .each(function(){
                                row_hilight($(this).attr('checked', 'checked'));
                            });
                    }),
                list_uncheck_all = $('<a />')
                    .text('Uncheck All')
                    .click(function(){
                        $('input[type="checkbox"]', list)
                            .each(function(){
                                row_lolight($(this).removeAttr('checked'));
                            });
                    }),
                col_group = $('<colgroup />'),
                // {{{ disable_tools()
                disable_tools = function() {
                    var checked = $(':checked', list);
                    if (!checked.length)
                    {
                        if (hold_dir == undefined || hold_files == undefined)
                        {
                            list_paste.trigger('disable');
                        }
                        list_rename.trigger('disable');
                        list_delete.trigger('disable');
                        list_cut.trigger('disable');
                        list_copy.trigger('disable');
                        selected_item = undefined;
                    }
                },
                // }}}
                // {{{ enable_tools()
                enable_tools = function() {
                    var checked = $(':checked', list);
                    if (checked.length)
                    {
                        if (checked.length == 1 && checked.closest('tr').data('selected'))
                        {
                            list_rename.trigger('enable');
                        }
                        else
                        {
                            list_rename.trigger('disable');
                        }
                        list_paste.trigger('enable');
                        list_delete.trigger('enable');
                        list_cut.trigger('enable');
                        list_copy.trigger('enable');
                    }
                },
                // }}}
                // {{{ row_lolight(ele, hex)
                row_lolight = function(ele, hex) {
                    if (hex == undefined)
                    {
                        hex = '#FFF';
                    }
                    ele.closest('tr').data('selected', false).css('background-color', hex);
                    disable_tools();
                },
                // }}}
                // {{{ row_hilight(ele, hex)
                row_hilight = function(ele, hex) {
                    if (hex == undefined)
                    {
                        hex = '#CCC';
                    }
                    ele.closest('tr').css('background-color', hex);
                    enable_tools();
                };
                // }}}
            list_tools
                .append(list_check_all)
                .append(list_uncheck_all)
                .append(
                    $('<div />')
                        .css('float', 'right')
                        .append(list_copy)
                        .append(list_cut)
                        .append(list_paste)
                        .append(list_rename)
                        .append(list_delete)
                );
            container
                .append('<span>Folders: ' + info.total_dirs + ', Files: ' + info.total_files + ', Total Size: ' + info.total_size)
                .append(list_tools);
            list
                .append(col_group.clone().addClass('checkbox'))
                .append(col_group.clone().addClass('file'))
                .append(col_group.clone().addClass('size'))
                .append(col_group.clone().addClass('type'))
                .append(col_group.clone().addClass('mtime'));
            row.clone()
                .append(header.clone())
                .append(header.clone().text('Name'))
                .append(header.clone().text('Size'))
                .append(header.clone().text('Type'))
                .append(header.clone().text('Last Modified'))
                .appendTo(list);
            // }}}
            // {{{ create rows from data
            for (i in items)
            {
                var name = items[i].name,
                    ext = items[i].ext,
                    resized_path = items[i].resized_path,
                    mime = items[i].mime,
                    stat = items[i].stat,
                    type = mime[0],
                    file_path = name == '..' ? dir.replace(/\/[\w\s-]+$/, '') : dir + '/' + name,
                    web_path = name == '..' ? '' : web + '/' + name,
                    tmp_row = row.clone()
                        .data('ext', ext)
                        .data('name', name)
                        .data('resized_path', resized_path)
                        .data('web_path', web_path)
                        .data('file_path', file_path),
                    file = $('<span />')
                        .text(name)
                        .data('ext', ext)
                        .data('name', name)
                        .data('file_path', file_path),
                    checkbox = $('<input type="checkbox" />')
                        .data('ext', ext)
                        .data('name', name)
                        .data('file_path', file_path)
                        .dblclick(function(e){
                            e.stopImmediatePropagation();
                        })
                        .click(function(e){
                            var el = $(this);
                            e.stopImmediatePropagation();
                            if (el.is(':checked'))
                            {
                                if (selected_item != undefined)
                                {
                                    row_hilight(selected_item.data('selected', false));
                                    row_hilight(el);
                                }
                                else
                                {
                                    selected_item = el.closest('tr');
                                    selected_item.data('selected', true);
                                    row_hilight(el, '#4F4');
                                }
                            }
                            else
                            {
                                row_lolight(el);
                            }
                        });
                if (mime[0] == 'folder')
                {
                    stat.nice_size = '';
                    if (name == '..')
                    {
                        type = 'parent';
                        stat.nice_mtime = '';
                        checkbox = $('<span />');
                    }
                }
                else
                {
                    type = mime[1];
                }
                // {{{ add table row
                list.append(
                    tmp_row
                        .css('cursor', 'pointer')
                        .data('type', mime[0])
                        .data('selected', false)
                        .click(function(){
                            var el = $(this);
                            if (el.data('type') == 'folder')
                            {
                                load_dir(el.data('file_path'));
                            }
                            else
                            {
                                var chk = $('input[type="checkbox"]', el);
                                if (is_single_selection)
                                {
                                    select_files({web: el.data('web_path'), resized: el.data('resized_path')});
                                }
                                else
                                {
                                    el.siblings().each(function(){
                                        var tr = $(this);
                                        $(':checked', tr).click();
                                        row_lolight(tr);
                                    });
                                    selected_item = el;
                                    el.data('selected', true);
                                    chk.attr('checked', 'checked');
                                    row_hilight(chk, '#4F4');
                                }
                            }
                        })
                        .dblclick(function(){
                            var el = $(this);
                            if (el.data('selected') && el.data('type') !== 'folder')
                            {
                                select_files([{web: el.data('web_path'), resized: el.data('resized_path')}]);
                            }
                        })
                        .append(cell.clone().append(checkbox))
                        .append(cell.clone().append(file))
                        .append(cell.clone().append(stat.nice_size))
                        .append(cell.clone().append(type))
                        .append(cell.clone().append(stat.nice_mtime))
                );
                // }}}
            }
            // }}}
            list_paste.trigger('enable');
            list.append(
                $('<div class="window-tools" />')
                    .append(control_submit.clone(true))
                    .append(' | ')
                    .append(control_cancel.clone(true))
            );
            return container.append(list);
        },
        // }}}
        // {{{ select_files()
        select_files = function(files) {
            if (is_tinymce)
            {
                field_file.val(files);
                form_browser.submit();
            }
            else
            {
                var type = is_single_selection ? 'single' : 'multi',
                    data = { type: type, files: files, input_name: input_name };
                $.triggerParentEvent('selected_files', JSON.stringify(data));
                window.close();
            }
        },
        // }}}
        // {{{ process_action()
        process_action = function() {
            $.post('/admin/rpc/FileManager/browser/', { view: view_type, action: action, files: admin.JSON.make(files), dir: dir }, function(data) {
                if (data.success)
                {
                    switch (data.action)
                    {
                        // {{{ case 'refresh'
                        case 'refresh':
                            tool_refresh.click();
                        break;
                        // }}}
                        // {{{ case 'list'
                        case 'list':
                            if (dir != root_dir)
                            {
                                data.files.unshift({ name: '..', mime: ['folder'], stat: [], ext: '' });
                            }
                            if (view_type === 'image')
                            {
                                control_tools.detach();
                                div_files
                                    .empty()
                                    .append(build_grid(data));
                            }
                            else
                            {
                                div_files
                                    .empty()
                                    .append(build_list(data));
                            }
                        break;
                        // }}}
                    }
                    files = [];
                }
            }, 'json');
        };
        // }}}
    // {{{ init
    if (view_type === undefined)
    {
        view_type = 'file';
    }
    form_browser
        .submit(function(){
            FileBrowserDialogue.mySubmit();
        });
    tool_create_dir
        .click(function(){
            var dir_name = $('<input type="text" />'),
                submit_btn = $('<input type="image" alt="submit" />'),
                cancel_btn = $('<input type="image" alt="cancel" />')
                    .click(function(){
                        tool_refresh.click();
                        return false;
                    }),
                form = $('<form />')
                    .submit(function(){
                        action = 'add';
                        files = [dir_name.val()];
                        process_action();
                        return false;
                    });
            control_tools.detach();
            div_files
                .empty()
                .append('<span>Current directory is: ' + web + '</span>')
                .append(form.append(dir_name).append(submit_btn).append(cancel_btn));
        })
        .text('Create Directory')
        .appendTo(tools);
    tool_upload_file
        .click(function(){
            var win_params = [
                'height=200',
                'width=400',
                'scrollbars=yes',
                'toolbar=no',
                'location=no',
                'menubar=no',
                'copyhistory=no',
                'directories=no'
            ]
            window.open('/admin/mod/FileManager/browse_upload/?upload_dir='+dir,'file_upload', win_params.join());
        })
        .text('Upload File')
        .appendTo(tools);
    tool_refresh
        .click(function(){
            load_dir(dir);
        })
        .text('Refresh')
        .appendTo(tools);
    div_nav
        .append(
            $('<a />')
                .text('<?php echo basename(FileManager::get_file_path()); ?>')
                .click(function(){
                    load_dir(root_dir)
                }));
    div_tools
        .append($('<span class="current_path" />').text('<?php echo FileManager::get_web_path(); ?>'))
        .append(tools);
    control_delete
        .text('Delete')
        .data('enabled', false)
        .bind('disable', control_disable)
        .bind('enable', control_enable)
        .click(function(){
            if ($(this).data('enabled'))
            {
                var overlay = $('<div />'),
                    button_ok = $('<button />')
                        .text('Okay')
                        .click(function(){
                            action = 'delete';
                            files = [];
                            $('.items .selected', div_files)
                                .each(function(){
                                    files.push($(this).data('name'));
                                });
                            process_action();
                            overlay.remove();
                        }),
                    button_cancel = button_ok
                        .clone()
                        .text('Cancel')
                        .click(function(){
                            overlay.remove();
                            tool_refresh.click();
                        });
                overlay
                    .css({
                        'background-color' : '#FFF',
                        'position' : 'fixed',
                        'border' : '1px solid #000',
                        'width' : '400px',
                        'height' : '200px',
                        'z-index' : '1000',
                        'top' : '50%',
                        'left' : '50%'
                    })
                    .append(button_ok)
                    .append(button_cancel)
                    .appendTo('#content');
            }
        });
    control_rename
        .text('Rename')
        .data('enabled', false)
        .bind('disable', control_disable)
        .bind('enable', control_enable)
        .click(function(){
            if ($(this).data('enabled') && selected_item.hasClass('selected'))
            {
                var new_name = $('<input type="text" />'),
                    old_name = $('<input type="hidden" />').val(selected_item.data('name')),
                    extension = $('<span>' + selected_item.data('ext') + '</span>'),
                    submit_btn = $('<input type="image" alt="submit" />'),
                    cancel_btn = $('<input type="image" alt="cancel" />')
                        .click(function(){
                            tool_refresh.click();
                            return false;
                        }),
                    form = $('<form />')
                        .submit(function(){
                            if (trim(new_name.val()).length)
                            {
                                action = 'rename';
                                files = [old_name.val(), new_name.val()];
                                process_action();
                            }
                            else
                            {
                                tool_refresh.click();
                            }
                            return false;
                        });
                control_tools.detach();
                div_files
                    .empty()
                    .append('<span>Current dir is: ' + dir + ', Current name is: ' + old_name.val() + '</span>')
                    .append(form.append(new_name).append(old_name).append(extension).append(submit_btn).append(cancel_btn));
            }
        });
    control_cut
        .text('Cut')
        .data('enabled', false)
        .bind('disable', control_disable)
        .bind('enable', control_enable)
        .click(function(){
            if ($(this).data('enabled'))
            {
                hold_files = [];
                $('.items .selected', div_files)
                    .each(function(){
                        hold_files.push($(this).data('name'));
                    });
                if (hold_files.length)
                {
                    hold_dir = dir;
                    hold_action = 'move';
                    control_paste.trigger('enable');
                }
            }
        });
    control_copy
        .text('Copy')
        .data('enabled', false)
        .bind('disable', control_disable)
        .bind('enable', control_enable)
        .click(function(){
            if ($(this).data('enabled'))
            {
                hold_files = [];
                $('.items .selected', div_files)
                    .each(function(){
                        hold_files.push($(this).data('name'));
                    });
                if (hold_files.length)
                {
                    hold_dir = dir;
                    hold_action = 'copy';
                    control_paste.trigger('enable');
                }
            }
        });
    control_paste
        .text('Paste')
        .data('enabled', false)
        .bind('disable', control_disable)
        .bind('enable', function(){
            if (hold_action != undefined && hold_dir != undefined && hold_files != undefined && hold_dir.length && hold_files.length)
            {
                $(this).data('enabled', true).css({
                    'cursor' : 'pointer',
                    'color' : '#4896C5'
                });
            }
            else
            {
                $(this).trigger('disable');
            }
        })
        .click(function(){
            if ($(this).data('enabled'))
            {
                files = hold_files;
                files.push(hold_dir);
                action = hold_action;
                process_action();
                hold_files = undefined;
                hold_dir = undefined;
                hold_action = undefined;
            }
        }),
    control_select_all
        .text('Select All')
        .click(function(){
            $('.items .selectable', div_files)
                .each(function(){
                    $(this).click();
                });
        });
    control_unselect_all
        .text('Unselect All')
        .click(function(){
            $('.items .selected', div_files)
                .each(function(){
                    $(this).click();
                });
        });
    control_submit
        .text('Submit')
        .click(function(){
            var files = [];
            $('.items .selected', div_files)
                .each(function(){
                    files.push({web: $(this).data('web_path'), resized: $(this).data('resized_path')});
                });
            select_files(files);
        });
    control_cancel
        .text('Cancel')
        .click(function(){
            window.close();
        });
    control_tools
        .append(control_select_all)
        .append(' | ')
        .append(control_unselect_all)
        .append(
            $('<div />')
                .css('float', 'right')
                .append(control_copy)
                .append(' | ')
                .append(control_cut)
                .append(' | ')
                .append(control_paste)
                .append(' | ')
                .append(control_rename)
                .append(' | ')
                .append(control_delete)
        );
    if (is_single_selection)
    {
        control_tools.hide();
    }
    load_dir(root_dir);
    // }}}
});
