jQuery(document).ready(function($) {
    /* General functions */
    var tag_form, tag_field = false;

    function set_attr(key, value, format) {
        var tag_value = tag_field.val();
        if(tag_value.indexOf(key+':') !== -1) {
            var pattern = key + ':' + format;
            tag_field.val( tag_value.replace(new RegExp(pattern, "g"), key + ':'+value) );
        } else {
            var tag_name = tag_form.find('.tg-name').val();
            tag_field.val( tag_value.replace(tag_name, tag_name+' '+key+':'+value) );
        }
    }

    /* Range slider functions */
    var listeners_set, values_field, default_field = false;

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
            set_attr("default",  rangeslider_default_value_filter( default_field.val() ), "\\d");
        }
    }

    function rangeslider_default_value_filter(value) {
        value = parseInt(value);
        if(isNaN(value) || value < 1) {
            value = 1;
        }
        return value;
    }

    function default_changed(event) {
        set_attr("default", rangeslider_default_value_filter(event.target.value), "\\d");
    }

    function unset_default_field_listeners() {
        values_field.off('change', default_values_handler);
        default_field.off('change', default_changed);
        tag_button.off('click', check_for_default_attr);
        tag_form.find('input').off('change', check_for_default_attr);
        listeners_set = values_field = tag_form = tag_field = default_field = false;
    }

    /* Date picker functions */

    var datepicker_range = {from: false, to: false, noy: false};

    function datepicker_set_attrs() {
        var value = "";
        var noy = datepicker_range.noy.val();
        if(noy.length > 0) {
            value = noy;
        } else {
            var from = datepicker_range.from.val();
            var to = datepicker_range.to.val();
            if(from.length > 0 && to.length > 0) {
                value = from + '-' + to;
            }
        }

        if(value.length > 0) {
            set_attr('years', value, '([0-9\\-]+)');
        }

        if(default_field.val().length > 0) {
            set_attr('default', default_field.val(), '([^\\s]+)');
        }
    }

    function unset_datepicker_listeners() {
        datepicker_range.noy.off('change', datepicker_set_attrs);
        tag_form.find('input').off('change', datepicker_set_attrs);
        datepicker_range.from.off('change', datepicker_set_attrs);
        datepicker_range.to.off('change', datepicker_set_attrs);
        datepicker_range = {from: false, to: false, noy: false};
        $('.ic__datepicker').off('click', datepicker_set_attrs);
        listeners_set = tag_form = tag_field = default_field = false;
    }

    /* Initialisation */

    $('#tag-generator-list').on('click', function(e) {
        if(listeners_set === 'rangeslider') {
            unset_default_field_listeners();
        } else if(listeners_set === 'datepicker') {
            unset_datepicker_listeners();
        }

        if(typeof e.target.href != "string") {
            return;
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
            listeners_set = 'rangeslider';
        } else if(e.target.href.indexOf('tag-generator-panel-datepicker') > 0) {
            default_field = $('#tag-generator-panel-datepicker-default');
            datepicker_range.noy = $('#tag-generator-panel-datepicker-noy');
            datepicker_range.noy.on('change', datepicker_set_attrs);
            tag_form = datepicker_range.noy.parents('form');
            tag_field = tag_form.find('.tag');
            tag_form.find('input').on('change', datepicker_set_attrs);
            datepicker_range.from = $('#tag-generator-panel-datepicker-range-from');
            datepicker_range.to = $('#tag-generator-panel-datepicker-range-to');
            datepicker_range.from.on('change', datepicker_set_attrs);
            datepicker_range.to.on('change', datepicker_set_attrs);
            $('.ic__datepicker').on('click', datepicker_set_attrs);
            listeners_set = 'datepicker';
        }
    });
});
