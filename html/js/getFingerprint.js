$(function() {


    $('#load').on('click', function() {
        var $this = $(this);
        $this.button('loading');
    });


    $("#contactForm input,#contactForm textarea").jqBootstrapValidation({
        preventSubmit: true,
        submitError: function($form, event, errors) {
            // additional error messages or events
        },
        submitSuccess: function($form, event) {
            // Prevent spam click and default submit behaviour
            $("#btnSubmit").attr("disabled", true);
            event.preventDefault();
            
            // get values from FORM
            var text = $("#text").val();
            var language = $("#annotation_language").val();
            console.log(language);
            $.ajax({
                url: "http://tagtheweb.com.br/wiki/getFingerPrint.php",
                type: "POST",
                data: jQuery.param({
                    text: text,
                    language: language
                }),
                cache: false,
                success: function(data) {
                    // Enable button & show success message
                    $("#fingerPrintModel").modal();
                    start(data);
                    $('#load').button('reset');

                },
                error: function(data) {
                    // Fail message
                    $('#success').html("<div class='alert alert-danger'>");
                    $('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;")
                        .append("</button>");
                        $('#success > .alert-danger')
                        .append("<strong>We are sorry, it was impossible to classify your document.</strong>");
                    $('#success > .alert-danger').append('</div>');

                    //clear all fields
                    $('#contactForm').trigger("reset");
                    $('#load').button('reset');
                },
            });
        },
        filter: function() {
            return $(this).is(":visible");
        },
    });

    $("a[data-toggle=\"tab\"]").click(function(e) {
        e.preventDefault();
        $(this).tab("show");
    });
});

// When clicking on Full hide fail/success boxes
$('#name').focus(function() {
    $('#success').html('');
});
