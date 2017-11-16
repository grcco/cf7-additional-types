jQuery(document).ready(function($) {
    $('input.wpcf7-rangeslider').each(function(index, element) {
        var values = JSON.parse($(element).data('values-json').replace(/'/g,'"'));
        $(element).ionRangeSlider({input_values_separator: ';', values: values});
    });
});