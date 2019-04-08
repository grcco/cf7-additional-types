jQuery(document).ready(function($) {
    var listeners_set, values_field, tag_form, tag_field, default_field = false;

    function default_values_handler(event) {
        var options = event.target.value.split('\n');
        var html = default_field.children()[0].outerHTML
        for(var i = 0;i < options.length;i++) {
            html += '<option value="'+ (i+1) +'">'+ options[i] +'</option>';
        }
        default_field.html(html);
    }

    function check_for_default_attr() {
        if(tag_field.val().indexOf('default:') === -1) {
            set_default(default_field.val());
        }
    }

    function set_default(value) {
        value = parseInt(value);
        if(isNaN(value) || value < 1) {
            value = 1;
        }

        var tag_value = tag_field.val();
        if(tag_value.indexOf('default:') !== -1) {
            tag_field.val( tag_value.replace(/default:\d/g, 'default:'+value) );
        } else {
            var tag_name = $('#tag-generator-panel-rangeslider-name').val();
            tag_field.val( tag_value.replace(tag_name, tag_name+' default:'+value) );
        }
    }

    function default_changed(event) {
        set_default(event.target.value);
    }

    function unset_listeners() {
        values_field.off('change', default_values_handler);
        default_field.off('change', default_changed);
        tag_button.off('click', check_for_default_attr);
        tag_form.find('input').off('change', check_for_default_attr);
        listeners_set = values_field = tag_form = tag_field = default_field = false;
    }

    $('#tag-generator-list').on('click', function(e) {
        if(listeners_set && !default_field.is(':visible')) {
            unset_listeners();
        }
        if(e.target.href.indexOf('tag-generator-panel-rangeslider') > 0) {
            values_field = $('#tag-generator-panel-rangeslider-values');
            default_field = $('#tag-generator-panel-rangeslider-default');
            tag_form = default_field.parents('form');
            tag_field = tag_form.find('.tag');
            tag_button = tag_form.find('.insert-tag');
            values_field.on('change', default_values_handler);
            default_field.on('change', default_changed);
            tag_form.find('input').on('change', check_for_default_attr);
            listeners_set = true;
        }
    });
});
