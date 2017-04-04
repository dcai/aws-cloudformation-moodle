/**
 * Created by MOODLER on 09/16/2016.
 */

// Global function to force trigger call when input change.
/*

(function ($) {
    var originalVal = $.fn.val;
    $.fn.val = function (value) {
        this.trigger("change");
        return originalVal.call(this, value);
    };
})(jQuery);
*/

$( document ).ready(function() {
    // Initialize agree check.
    if ($('#agreecheck_0').is(':checked')) {
        $('#agreecheck_0').prop("checked", false);
        $('.application-submit').prop("disabled", true);
    }
    //$('#agreecheck_0').on('change ifChanged changed.bs.select', function() {
    $('#agreecheck_0').on('ifChanged', function() {
        if ($(this).is(':checked')) {
            $('.application-submit').prop("disabled", false);
        } else {
            $('.application-submit').prop("disabled", true);
        }
    });
});