$(document).ready(function() {

$('select[name="trigger[trigger][data]"]')
    .change(function() {
        var trigger = $(this);
        $.post(
            '/admin/rpc/Workflow/trigger_params/',
            { json: admin.JSON.make(trigger.val()) },
            function(data, rstatus) {
                workflow_param_trigger(trigger, data);
            },
            'json'
        );
    });
    //.change();

$('select[name="response[response][data]"]')
    .change(function() {
        var response = $(this);
        $.post(
            '/admin/rpc/Workflow/response_params/',
            { json: admin.JSON.make(response.val()) },
            function(data, rstatus) {
                workflow_param_response(response, data);
            },
            'json'
        );
    });
    //.change();

});

function workflow_param_trigger(row, params)
{
    $('.group_trigger_parameters').remove();
    if (params != null)
    {
        var field = row.parent().parent().parent().parent(),
            module = row.val().split(':', 1)[0],
            label, field_name, j = 0,
            param_row, param_field, param,
            response_params, has_params;
        for (x in params)
        {
            response_params = $('.group_trigger_parameters');
            has_params = response_params.length;
            param = param_attr(params[x]);
            param_row = $('<div class="group group_trigger_parameters"><div class="row"><div class="label">Trigger parameter ' + ++j + ': ' + param.label + '</div></div></div>');
            param_fields = $('<div class="fields fields_workflow fields_trigger_parameter" />');
            switch (param.type)
            {
                case 'dropdown':
                    param_field = $('<select name="trigger_param[t:' + module + '_' + x + '][data]" />');
                    for (y in param.options)
                    {
                        param_field.append('<option value="' + y + '">' + param.options[y] + '</option>');
                    }
                break;
                case 'textarea':
                    param_field = '<textarea name="trigger_param[' + module + '_' + x + '][data]" />';
                    if (param.value)
                    {
                        param_field = $(param_field).text(param.value);
                    }
                break;
            }
            param_fields.append(param_field);
            if (param.description)
            {
                param_fields.append('<div class="description">' + param.description + '</div>');
            }
            $('> .row', param_row).append(param_fields);
            has_params ? trigger_params.after(param_row) : field.after(param_row);
        }
    }
}

function workflow_param_response(row, params)
{
    $('.group_response_parameters').remove();
    if (params != null)
    {
        var field = row.parent().parent().parent().parent(),
            module = row.val().split(':', 1)[0],
            param_row, param_field, param,
            label, field_name, j = 0,
            response_params, has_params;
        for (x in params)
        {
            response_params = $('.group_response_parameters');
            has_params = response_params.length;
            param = param_attr(params[x]);
            param_row = $('<div class="group group_response_parameters"><div class="row"><div class="label">Response parameter ' + ++j + ': ' + param.label + '</div></div></div>');
            param_fields = $('<div class="fields fields_workflow fields_workflow_parameter" />');
            switch (param.type)
            {
                case 'dropdown':
                    param_field = $('<select name="response_param[r:' + module + '_' + x + '][data]" />');
                    for (y in param.options)
                    {
                        param_field.append('<option value="' + y + '">' + param.options[y] + '</option>');
                    }
                break;
                case 'textarea':
                    param_field = '<textarea name="response_param[' + module + '_' + x + '][data]" />';
                    if (param.value)
                    {
                        param_field = $(param_field).text(param.value);
                    }
                break;
            }
            param_fields.append(param_field);
            if (param.description)
            {
                param_fields.append('<div class="description">' + param.description + '</div>');
            }
            $('> .row', param_row).append(param_fields);
            has_params ? response_params.after(param_row) : field.after(param_row);
        }
    }
}

function param_attr(param)
{
    var param_description = param.description,
        param_options = param.options,
        param_values = param.values,
        param_value = param.value;
    return {
        label: param.label,
        type: param.type,
        description: (typeof(param_description) != undefined ? param_description : ''),
        options: (typeof(param_options) != undefined ? param_options : {}),
        values: (typeof(param_values) != undefined ? param_values : {}),
        value: (typeof(param_value) != undefined ? param_value : '')
    };
}
