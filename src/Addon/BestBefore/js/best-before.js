(function($) {

    /*
    $("._post_meta_bbx_best_before_expiry_presets").change(function() {
        $("._post_meta_bbx_best_before_expiry_date").val( $(this).val() );
        $("._post_meta_bbx_best_before_expiry_presets").val("");
    });
    */

    $(".click-tile").on("click", function(e) {

        $("._post_meta_bbx_best_before_expiry_date").val( $(this).attr("data-period") );
        e.preventDefault();

    });

})( jQuery );

