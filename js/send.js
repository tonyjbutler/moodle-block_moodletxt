/**
 * jQuery code for the compose page. Handles the slidey wizard
 * interface and all the various messaging options.
 *
 * moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "ConnectTxt", "txttools", "moodletxt", "moodletxt+", "Blackboard", "Blackboard Connect" or "Cy-nap"
 *   - Derivative works must be have their differences from the original material noted,
 *     and must not be misrepresentative of the origin of this material, or of the original service
 * 
 * Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
 * or legal liability arising from their use of this code.
 * 
 * @TODO Ditch QFAMS and build our own two-select element - this one's a pain for compose
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2012 txttools Ltd. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012100401
 * @since 2011102001
 */

var $messageText;
var $sourceRecipients;
var $finalRecipients;
var $confirmRecipients;

var $additionalContactNumber;
var $additionalContactFirstName;
var $additionalContactLastName;

var userSignature;

var fadeColour = '#FF0000';

/**
 * Receives the user's signature from PHP
 * @param YUI Y3 object
 * @param signature User's signature
 * @version 2012022101
 * @since 2012022101
 */
function receiveUserSignature(YUI, signature) {
    userSignature = signature;
}

/**
 * Adds a "mail merge" %TAG% to the message text
 * @param tag Tag string to add to the message
 * @version 2011102001
 * @since 2011102001
 */
function addMessageTag(tag) {
    
    $messageText.insertAtCaret(tag).keyup().focus();

}

/**
 * Displays the specified class of recipient 
 * and hides all others
 * @param source Source element
 * @param className Name of class to show
 * @version 2012052901
 * @since 2011102801
 */
function showRecipientClass(source, className) {
    
    $('.recipientTypeSelector').removeClass('selected');
    $(source).addClass('selected');
    
    $sourceRecipients.detachOptions('option');
    $sourceRecipients.attachOptions('option.' + className);
    $sourceRecipients.data('currentShownClass', className);
    
}

/**
 * "Glows" the navigator tab for each slide that
 * contains an error message
 * @version 201210401
 * @since 2011102001
 */
function glowErrors() {

    $('div.slide').each(function(index, slide) {
        
        if ($(this).find('span.error').length > 0) {
            var $navTab = $('#navigator ul li').eq(index);
            
            setInterval(function() {
                $navTab.effect('highlight', { color : fadeColour }, 2000);
            }, 3000);
        }

    });

}

/**
 * Updates the schedule string shown on the
 * confirmation page, when the schedule element
 * is changed in the form
 * @version 2011102001
 * @since 2011102001
 */
function updateScheduleString() {

    $('#schedule2').attr('checked', 'checked');
    $("input[name=schedule]:checked").val('schedule');
    $('#confirmSchedule').text(buildScheduleString());

}

/**
 * Moves a standard recipient selection
 * from the potential recipients box to the selected
 * box, as well as the confirmation box
 * @version 2012052901
 * @since 2012030501
 */
function addStandardRecipient() {
        
    // Copy options to recipients lists
    $sourceRecipients.copyOptions($finalRecipients, 'selected', false, true);
    $sourceRecipients.copyOptions($confirmRecipients, 'selected', false, true);
    QFAMS.updateHidden($('select[name="recipients[]"]')[0], $('select[name="recipients-t[]"]')[0]);
    $('#recipients-t').children('option').dblclick(removeRecipient);
    
    // Delete selected options from source list
    $sourceRecipients.removeOption(/./, true);
    
    // Sort both lists
    $finalRecipients.sortOptions();
    $confirmRecipients.sortOptions();
    
}

/**
 * Generates a new contact based on user input and
 * adds them to the recipients list
 * @version 2012041201
 * @since 2011102001
 */
function addAdditionalRecipient() {

    // Grab number from form
    var number = $additionalContactNumber.val();
    var firstName = $additionalContactFirstName.val();
    var lastName = $additionalContactLastName.val();

    var errorstring = '';

    // Check that all required fields have been filled
    if (number == '')
        errorstring += M.str.block_moodletxt.errornonumber + '\n';

    if (firstName == '')
        errorstring += M.str.block_moodletxt.errornofirstname + '\n';

    if (lastName == '')
        errorstring += M.str.block_moodletxt.errornolastname + '\n';


    if (errorstring != '') {

        alert(M.str.block_moodletxt.errorlabel + '\n' + errorstring);

    } else {

        // Mash additional contact details together to generate a form value
        var numberval = 'add#' + number + '#' + lastName + '#' + firstName;

        // Remove blanker and enable element if necessary
        // (I honestly don't know why QFAMS does this, but hey)
        if ($finalRecipients.children('option').filter(':first').val() == '') {
            $finalRecipients.children('option').filter(':first').remove();
            $finalRecipients.removeAttr('disabled');
        }

        // Copy number to recipient lists
        $finalRecipients.addOption(numberval, lastName + ', ' + firstName + ' (' + number + ')', false, null, 'additionalRecipient');
        QFAMS.updateHidden($('select[name="recipients[]"]')[0], $('select[name="recipients-t[]"]')[0]);
        $('#recipients-t').children('option').dblclick(removeRecipient);
        
        $confirmRecipients.addOption(numberval, lastName + ', ' + firstName + ' (' + number + ')', false, null, 'additionalRecipient');
        $confirmRecipients.sortOptions();

        // Reset form for additional contacts
        $additionalContactFirstName.val('');
        $additionalContactLastName.val('');
        $additionalContactNumber.val('');

    }

}

/**
 * Moves a recipient from the selected recipients box
 * back to the potential recipients box, or drops them
 * completely if they are an additional recipient.
 * @version 2012100401
 * @since 2012030501
 */
function removeRecipient() {
    
    var selected = $finalRecipients.selectedValues();
    
    $.each(selected, function(index) {
        
        // Anyone that's an additional contact gets dropped
        if (this.split('#')[0] === 'add') {
            $finalRecipients.removeOption(this);
        }
                
    });
    
    $finalRecipients.copyOptions($sourceRecipients, 'selected', false, true);
    $finalRecipients.removeOption(selected);
    $confirmRecipients.removeOption(selected);

    // Update QFAM's weird hidden select nonsense
    QFAMS.updateHidden($('select[name="recipients[]"]')[0], $('select[name="recipients-t[]"]')[0]);
    
    // Show only the elements in the source selector that need to be there
    $('.recipientTypeSelector.selected').click();
    $('#recipients-f').children('option').dblclick(addStandardRecipient);
    
    $finalRecipients.sortOptions();
    $confirmRecipients.sortOptions();
    
}

/**
 * Builds a value string from the <select> boxes used
 * to specify a scheduling date/time
 * @version 2011102001
 * @since 2011102001
 */
function buildScheduleString() {

    var scheduleString = 'on ';

    scheduleString += pad($('#menuschedule_day').val(), 2, '0', 'left') + '/';
    scheduleString += pad($('#menuschedule_month').val(), 2, '0', 'left') + '/';
    scheduleString += $('#menuschedule_year').val() + ' (dd/mm/yyyy) at ';
    scheduleString += pad($('#menuschedule_hour').val(), 2, '0', 'left') + ':';
    scheduleString += pad($('#menuschedule_minute').val(), 2, '0', 'left') + '.';

    return scheduleString;

}

/**
 * Moves the form from one slide to another with
 * horizontal slidey fun wahey
 * @param slideNumber Slide to move to
 * @version 2011102001
 * @since 2011102001
 */
function animateToSlide(slideNumber) {

    // Animate slider
    var percentage = -100 * (parseInt(slideNumber) - 1);
    $('#slidePlate').animate({left:percentage + '%'});
    
    currentSlide = slideNumber;

    // Update navigator
    $('#navigator ul li').each(function(index) {
        $(this).removeClass('menuCurrent');
    });

    $('#navigator ul li:nth-child(' + currentSlide + ')').addClass('menuCurrent');

}

/**
 * When document is ready, begin binds!
 */
$(document).ready(function(){

    $messageText = $('textarea[name=messageText]');
    $sourceRecipients = $('select[name="recipients-f[]"]');
    $finalRecipients = $('select[name="recipients-t[]"]');
    $confirmRecipients = $('select[name="confirmRecipients[]"]');

    $additionalContactNumber = $('input[name=addnumber]');
    $additionalContactFirstName = $('input[name=addfirstname]');
    $additionalContactLastName = $('input[name=addlastname]');

    /*
     ************************************************************
     ************************NAVIGATION**************************
     ************************************************************
     */

    $('#navigator ul li').each(function(index) {
        $(this).click(function(event) {
            animateToSlide(index + 1);
        });
    });

    $('span.nextButton').click(function(event) {
        animateToSlide(currentSlide + 1);
    });

    $('span.prevButton').click(function(event) {
        animateToSlide(currentSlide - 1);
    });


    /*
     ************************************************************
     **************************SLIDE 1***************************
     ************************************************************
     */

    // Show and hide various contact types as appropriate
    $('input[name=showUsers]').click(function(event) {
        showRecipientClass(this, 'userRecipient');
    });

    $('input[name=showUserGroups]').click(function(event) {
        showRecipientClass(this, 'userGroupRecipient');
    });

    $('input[name=showAddressbookContacts]').click(function(event) {
        showRecipientClass(this, 'addressbookRecipient');
    });

    $('input[name=showAddressbookGroups]').click(function(event) {
        showRecipientClass(this, 'addressbookGroupRecipient');
    });

    // I did want to keep this as seamless as possible,
    // but the QFAMS select/deselect JS just can't do what we want.
    // In order to be able to move only visible and selected elements,
    // and ditch additional contacts when removed from the selected list, 
    // we need our own custom code, so here we remove QFAMS and 
    // override it with our own JS
    $('input[name=add]').removeAttr('onclick')
    $('input[name=add]').click(function(event) {
        event.preventDefault();
        addStandardRecipient();
    });
    
    $('input[name=remove]').removeAttr('onclick')
    $('input[name=remove]').click(function(event) {
        event.preventDefault();
        removeRecipient();
    });
    
    $('#recipients-f').children('option').dblclick(addStandardRecipient);
    $('#recipients-t').children('option').dblclick(removeRecipient);
    
    // I don't know why this is disabled, just kill it with fire
    // and murder its children
    $finalRecipients.removeAttr('disabled');
    $finalRecipients.children('option[value=""]').remove();

    // Auto-validation for names and numbers
    $additionalContactFirstName.keypress(function(event) {
        return validateNameInput(event);
    });

    $additionalContactLastName.keypress(function(event) {
        return validateNameInput(event);
    });

    $additionalContactNumber.keypress(function(event) {
        return validatePhoneInput(event);
    });
    
    $('input[name=addAdditionalContact]').click(function(event) {
        addAdditionalRecipient();
    });

    /*
     ************************************************************
     **************************SLIDE 2***************************
     ************************************************************
     */

    /**
     * Handler for the message box - computes stats and whatnot
     * while user is typing
     */
    $messageText.keyup(function(event) {
        updateMessageBox(this, {
            unicodeDetectElements   :   {
                unicodeMessageSelector  :   '.unicodeWarning',
                unicodeSuppressSelector :   '.unicodeSuppressedWarning',
                unicodeSuppressSwitch   :   ($('input[name=suppressUnicode]').filter(':checked').val() == '1'),
                CPMElement              :   '#charsPerMessage'
            }
        });
        $('textarea[name=confirmMessage]').val($(this).val());
    });

    /**
     * Message templates handler - when user selects a template,
     * it is copied to the message box
     */
    $('select[name=messageTemplates]').change(function(event) {
        var templateID = $(this).val();

        if (templateID > 0) {
            $messageText.val($(this).selectedTexts()[0]);
        } else {
            $messageText.val('');
        }

        $messageText.keyup(); // Trigger message box handler

    });

    /**
     * Signature checkbox handler - when selected, signature is
     * added to the message
     */
    $('input[name=addSig]').change(function(event) {

        // If checked, append sig
        if ($(this).attr('checked')) {
            $messageText.val($messageText.val() + userSignature);

        // If unchecked, remove length of sig from end of message
        } else {
            var somestring = $messageText.val();
            $messageText.val(somestring.substring(0, (somestring.length - userSignature.length)));
        }
        $messageText.keyup();  // Trigger message box handler
    });

    /**
     * Tag handler for first name
     */
    $('input[name=tagFirstName]').click(function(event) {
        addMessageTag('%FIRSTNAME%');
    });

    /**
     * Tag handler for surname
     */
    $('input[name=tagLastName]').click(function(event) {
       addMessageTag('%LASTNAME%');
    });

    /**
     *  Tag handler for full name
     */
    $('input[name=tagFullName]').click(function(event) {
       addMessageTag('%FULLNAME%');
    });

    /*
     ************************************************************
     **************************SLIDE 3***************************
     ************************************************************
     */

     $('input[name=suppressUnicode]').change(function(event) {
         $messageText.keyup();
         
         // Override of global function to hide general unicode
         // warning on the confirm page when the unicode suppression
         // warning is shown
         if ($(this).is(':checked') && $(this).val() == '1') {
             $('.unicodeWarning').filter(':last').hide();
         }
     });

    /**
     * Menu scheduling handler - when schedule selection
     * is changed, create schedule string
     */
    $('select.menuschedule').each(function(intIndex) {

        $(this).change(function(event) {

            if ($("input[name=schedule]:checked").val() != "now")
                $('#confirmSchedule').text(buildScheduleString());

        });

    });

    $('#menuschedule_day').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_month').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_year').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_hour').change(function(event) {
        updateScheduleString();
    });

    $('#menuschedule_minute').change(function(event) {
        updateScheduleString();
    });

    /**
     * Handler for txttools accounts list - show
     * account description
     */
    $('#txttoolsaccount').change(function(event) {
        var accountid = $('#txttoolsaccount').val();
        $('#accountDescription').text(accountDescriptions[accountid]);
    });

    /**
     * Set handler for scheduling <select> boxes.  When
     * the user selects a scheduling time, copy that
     * time to the confirmation screen
     */
    $("input[name=schedule]").change(function(event) {

        // Get whether or not user is scheduling
        var value = $("input[name=schedule]:checked").val();

        // If sending now, say so.  If not, copy datetime to confirmation
        if (value == "now")
            $('#confirmSchedule').text('immediately.');
        else
            $('#confirmSchedule').text(buildScheduleString());

    });

    /*
     ************************************************************
     **************************SLIDE 4***************************
     ************************************************************
     */

    /**
     * Prevent user from selecting recipients on the confirmation page
     */
    $confirmRecipients.click(function(event) {
        $(this).deselectAll();
    });
    $confirmRecipients.change(function(event) {
        $(this).deselectAll();
    });


    /**
     * Form submission handler - when form is submitted,
     * check input to see if it's a bit crap
     */
    $('form#mform1').submit(function() {

        var errorArray = new Array();

        // Check recipients have been selected
        if ($finalRecipients.allValues().length == 0)
            errorArray[errorArray.length] = M.str.block_moodletxt.errornorecipientsselected + '\n';

        // Check message has been entered
        if ($messageText.val().length == 0)
            errorArray[errorArray.length] = M.str.block_moodletxt.errornomessage + '\n';

        // Echo errors
        if (errorArray.length > 0) {

            var errorString = M.str.block_moodletxt.errorlabel + '\n\n' + errorArray.join('\n');
            alert(errorString);

            return false;

        } else {

            $('#sendMessage').attr('disabled', 'disabled');
            $finalRecipients.selectAll();
            return true;

        }

    });

    /**
     * Trigger a whole buncha stuff when the page is first loaded,
     * to make sure everything is set up properly
     */
    $finalRecipients.copyOptions($confirmRecipients, 'all', false, true); // Repopulate confirmation box
    $messageText.keyup();
    $('#navigator ul li:first').trigger('click');
    $('input[name=showUsers]').click();
    $('input[name=schedule]').filter(':first').trigger('change');
    $('#txttoolsaccount').trigger('change');
    $('select#abList').sortOptions();
    glowErrors();

});