/* Admin Script */
jQuery(document).ready(function ($) 
{
    // Create new bonus form validation
    if( $('#arm-bonus-form').length )
    {
        $("#arm-bonus-form").validate({
                rules: {
                    author : {
                    required: true,
                    },
                    amount : {
                    required: true,
                    number: true,
                    },
                    reason : {
                    required: true,
                    },
                },
                messages: {
                    author : {
                    required: arm_translate_handler.is_required,
                    },
                    amount : {
                    required: arm_translate_handler.is_required,
                    },
                    reason : {
                    required: arm_translate_handler.is_required,
                    },
                },
                errorPlacement: function(error, element) 
                {
                    error.insertAfter(element);
                }	
        });
    }

    // Create new penalty form validation
    if( $('#arm-penalty-form').length )
    {
        $("#arm-penalty-form").validate({
                rules: {
                    author : {
                    required: true,
                    },
                    amount : {
                    required: true,
                    number: true,
                    },
                    reason : {
                    required: true,
                    },
                },
                messages: {
                    author : {
                    required: arm_translate_handler.is_required,
                    },
                    amount : {
                    required: arm_translate_handler.is_required,
                    },
                    reason : {
                    required: arm_translate_handler.is_required,
                    },
                },
                errorPlacement: function(error, element) 
                {
                    error.insertAfter(element);
                }	
        });
    }

    // Submit new transaction form validation
    if( $('#arm-transaction-form').length )
    {
        $("#arm-transaction-form").validate({
                rules: {
                    method : {
                    required: true,
                    },
                    file : {
                    required: true,
                    },
                },
                messages: {
                    method : {
                    required: arm_translate_handler.is_required,
                    },
                    file : {
                    required: arm_translate_handler.is_required,
                    },
                },
                errorPlacement: function(error, element) 
                {
                    error.insertAfter(element);
                }	
        });
    }

    // Settings form validation
    if( $('#arm-settings-form').length )
    {
        $("#arm-settings-form").validate({
                rules: {
                    aarm_thousand_seperator_en_US : {
                    required: true,
                    },
                    aarm_decimal_seperator_en_US : {
                    required: true,
                    },
                    aarm_number_of_decimals_en_US : {
                    required: true,
                    },
                    aarm_default_revenue_per_word_en_US : {
                    required: true,
                    number: true,
                    },
                    aarm_thousand_seperator_fa_IR : {
                    required: true,
                    },
                    aarm_decimal_seperator_fa_IR : {
                    required: true,
                    },
                    aarm_number_of_decimals_fa_IR : {
                    required: true,
                    },
                    aarm_default_revenue_per_word_fa_IR : {
                    required: true,
                    number: true,
                    },
                    aarm_auto_bonus_rules : {
                    required: true,
                    },
                    aarm_auto_penalty_rules : {
                    required: true,
                    },
                },
                messages: {
                    aarm_thousand_seperator_en_US : {
                    required: arm_translate_handler.is_required,
                    },
                    aarm_decimal_seperator_en_US : {
                    required: arm_translate_handler.is_required,
                    },
                    aarm_number_of_decimals_en_US : {
                    required: arm_translate_handler.is_required,
                    },
                    aarm_default_revenue_per_word_en_US : {
                    required: arm_translate_handler.is_required,
                    number: arm_translate_handler.invalid_number,
                    },
                    aarm_thousand_seperator_fa_IR : {
                    required: arm_translate_handler.is_required,
                    },
                    aarm_decimal_seperator_fa_IR : {
                    required: arm_translate_handler.is_required,
                    },
                    aarm_number_of_decimals_fa_IR : {
                    required: arm_translate_handler.is_required,
                    },
                    aarm_default_revenue_per_word_fa_IR : {
                    required: arm_translate_handler.is_required,
                    number: arm_translate_handler.invalid_number,
                    },
                    aarm_auto_bonus_rules : {
                    required: arm_translate_handler.is_required,
                    },
                    aarm_auto_penalty_rules : {
                    required: arm_translate_handler.is_required,
                    },
                },
                errorPlacement: function(error, element) 
                {
                    error.insertAfter(element);
                }	
        });
    }

    // Add admin contact option
    $('#add-admin-contact-option').on('click', function()
    {
        $('.arm-table').hide();

        var index = $(document).find('.contact-option').length;
        var field = `<div class="row contact-option mt-4 mb-4">
                        <div class="col-md-3 col-sm-12">
                            <label class="form-label">${arm_translate_handler.slug}</label>
                            <input type="text" class="me-2" name="option[${index}][slug]" id="slug" value="">                       
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <label class="form-label">${arm_translate_handler.english_title}</label>
                            <input type="text" class="me-2" name="option[${index}][title-en_US]" id="title-en_US" value="">                       
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <label class="form-label">${arm_translate_handler.persian_title}</label>
                            <input type="text" class="me-2" name="option[${index}][title-fa_IR]" id="title-fa_IR" value="">                       
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <br>
                            <input class="form-check-input" name="option[${index}][is_required]" type="checkbox" value="1" id="is_required-${index}">
                            <label class="form-check-label" for="is_required-${index}">
                                ${arm_translate_handler.required}
                            </label>
                        </div>
                        <div class="col-md-1 col-sm-12">
                            <br>
                            <span role="button" class="remove-option"><i class="fa fa-trash-alt"></i> </span>
                        </div>
                    </div>`;
        // Add
        $('#contact-options').append(field);
    });

    // Remove admin contact option
    $(document).on('click', '.contact-option .remove-option', function()
    {
        // Remove
        $(this).closest('.contact-option').remove();

        if($(document).find('.contact-option').length == 0)
            $('.arm-table').show();
    });

    // Add author contact option
    $('#add-author-contact-option').on('click', function()
    {
        var title = $( "#contact_option option:selected" ).text();
        var slug = $('#contact_option').val();

        var field = `<div class="col-md-3">
                        <label for="${slug}" class="form-label">${title}</label>
                        <input type="text" class="form-control" name="${slug}" id="${slug}" value="">
                    </div>`;

        // Add if not exist
        if(!$('#contact-options').find('#'+slug).length && $('#contact_option').val() != null)
            $('#contact-options').append(field);
    });

    // Confirm message
    $('.needs-confirmation').on('click', function(e)
    {
        e.preventDefault();

        // Get modal
        var Modal = new bootstrap.Modal(document.getElementById('arm-repeal-adjustment'));
        
        // Show modal
        Modal.show();

        // Set action button url
        $('.arm-action-button').attr('href', $(this).attr('href'));          
    });
});