/**
 * jQuery script file for moodletxt admin page
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
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013032101
 * @since 2011070401
 */

var FILTER_TYPE_KEYWORD = 'keyword';
var FILTER_TYPE_PHONENO = 'phoneno';

// Define jQuery objects that need to be referenced outside document.ready
// Globals are manly!
var $filterAccountList;
var $existingKeywordFilterList;
var $newKeywordFilterOperand;
var $existingPhoneNumberFilterList;
var $newPhoneNumberFilterOperand;

var $newKeywordFilterLink;
var $newPhoneNumberFilterLink;
var $cancelKeywordFilterLink;
var $cancelPhoneNumberFilterLink;

var $usersOnFilterList;
var $userSearcher;
var $removeUsersButton;
var $saveFilterButton;

// Images used in filter processing
var $IMAGE_LOADING;
var $BLANKER;

/**
 * Initialises any common elements used by jQuery on the page
 * @version 2011071501
 * @since 2011070401
 */
function setUpElements() {

    $IMAGE_LOADING = $('<img />')
        .attr('src', 'pix/icons/ajax-loader.gif')
        .attr('width', '16')
        .attr('height', '16')
        .css('vertical-align', 'bottom')
        .attr('alt', M.str.block_moodletxt.adminaccountfragloading)
        .attr('title', M.str.block_moodletxt.adminaccountfragloading);
        
    $BLANKER = $('<option />').val('0').text('');
        
}

/**
 * Validates that the users is only putting alphanumeric characters
 * into the keyword field, and prevents them entering anything else
 * @param event Javascript Event object for keypress
 * @version 2011071501
 * @since 2011070401
 */
function validateKeywordInput(event) {

    if ((event.which >= 48 && event.which <= 57) ||
        (event.which >= 97 && event.which <= 122) ||
        (event.which >= 65 && event.which <= 90) ||
        event.which == 8 ||
        event.which == 0) {

        return true;

    } else {

        return false;

    }

}

/**
 * Given a source identifier, this function resets portions
 * of the filter form as appropriate for whatever stage of the process
 * the user is currently at
 * @param $resetSource ID of element that generated request
 * @version 2011071501
 * @since 2011070401
 */
function resetFilterForm($resetSource) {

    // Reset user filter list in all circumstances
    $userSearcher.val(M.str.block_moodletxt.adminlabelfilterusersearch).attr('disabled', 'disabled');
    $usersOnFilterList.find('option').remove();
    $usersOnFilterList.attr('disabled', 'disabled');
    $removeUsersButton.attr('disabled', 'disabled');
    $saveFilterButton.attr('disabled', 'disabled');

    switch($resetSource) {

        // Account selected - clear everything
        case $filterAccountList.attr('id'):

            $existingKeywordFilterList.removeOption(/./);
            $existingPhoneNumberFilterList.removeOption(/./);

            $newKeywordFilterOperand.val('');
            $newPhoneNumberFilterOperand.val('');

            $newKeywordFilterOperand.parent().parent().hide();
            $newPhoneNumberFilterOperand.parent().parent().hide();
            $existingKeywordFilterList.parent().parent().show();
            $existingPhoneNumberFilterList.parent().parent().show();

            break;

        // Existing filters selected - clear user list
        case $existingKeywordFilterList.attr('id'):

            $existingPhoneNumberFilterList.selectOptions('', true);
            $newKeywordFilterOperand.val('');
            $newPhoneNumberFilterOperand.val('');

            break;

        case $existingPhoneNumberFilterList.attr('id'):

            $existingKeywordFilterList.selectOptions('', true);
            $newKeywordFilterOperand.val('');
            $newPhoneNumberFilterOperand.val('');

            break;

        // New filters selected - clear existing list
        case $newKeywordFilterOperand.attr('id'):

            $existingKeywordFilterList.selectOptions('', true);
            $existingPhoneNumberFilterList.selectOptions('', true);
            $newPhoneNumberFilterOperand.val('');
            lockUnlockLowerFilterForm(false);

            break;

        case $newPhoneNumberFilterOperand.attr('id'):

            $existingKeywordFilterList.selectOptions('', true);
            $existingPhoneNumberFilterList.selectOptions('', true);
            $newKeywordFilterOperand.val('');
            lockUnlockLowerFilterForm(false);

            break;

    }

}

/**
 * Locks or unlocks the elements in the upper half
 * of the filter form as appropriate
 * @param lock Whether or not to lock the section
 * @version 2011071501
 * @since 2011070401
 */
function lockUnlockUpperFilterForm(lock) {
    if (lock) {
        $existingKeywordFilterList.attr('disabled', 'disabled');
        $existingPhoneNumberFilterList.attr('disabled', 'disabled');
        $newKeywordFilterOperand.attr('disabled', 'disabled');
        $newPhoneNumberFilterOperand.attr('disabled', 'disabled');
    } else {
        $existingKeywordFilterList.removeAttr('disabled');
        $existingPhoneNumberFilterList.removeAttr('disabled');
        $newKeywordFilterOperand.removeAttr('disabled');
        $newPhoneNumberFilterOperand.removeAttr('disabled');
    }
}

/**
 * Locks or unlocks the elements in the lower half
 * of the filter form as appropriate
 * @param lock Whether or not to lock the section
 * @version 2011071501
 * @since 2011070401
 */
function lockUnlockLowerFilterForm(lock) {

    if (lock) {
        $userSearcher.attr('disabled', 'disabled');
        $usersOnFilterList.attr('disabled', 'disabled');
        $removeUsersButton.attr('disabled', 'disabled');
        $saveFilterButton.attr('disabled', 'disabled');
    } else {
        $userSearcher.removeAttr('disabled');
        $usersOnFilterList.removeAttr('disabled');
        $removeUsersButton.removeAttr('disabled');
        $saveFilterButton.removeAttr('disabled');
    }

}

/**
 * Retrieves details of all existing filters on a txttools
 * account and displays them for the user
 * @param event Javascript event object for the onchange event
 * @version 2012100901
 * @since 2011070401
 */
function getFiltersOnAccount(event) {

    resetFilterForm($(this).attr('id'));

    // User selected the blanker
    if ($(this).val() < 1) {
        lockUnlockUpperFilterForm(true);
    }

    if ($(this).val() > 0) {

        var requestJSON = $.toJSON({
            mode        :   'getAccountDetails',
            accountId   :   $(this).val()
        });

        // Drop in the loading images
        $existingKeywordFilterList.parent().append($IMAGE_LOADING.clone());
        $existingPhoneNumberFilterList.parent().append($IMAGE_LOADING.clone());
        $newKeywordFilterOperand.parent().append($IMAGE_LOADING.clone());
        $newPhoneNumberFilterOperand.parent().append($IMAGE_LOADING.clone());

        // Call up the database and get filter details
        $.getJSON('settings_accounts_update.php',
            {json : requestJSON},
            function(json) {

                // Strip loading images
                $existingKeywordFilterList.parent().children('img:last').remove();
                $existingPhoneNumberFilterList.parent().children('img:last').remove();
                $newKeywordFilterOperand.parent().children('img:last').remove();
                $newPhoneNumberFilterOperand.parent().children('img:last').remove();

                lockUnlockUpperFilterForm(false);

                // Drop in blanker
                $existingPhoneNumberFilterList.append($BLANKER.clone());
                $existingKeywordFilterList.append($BLANKER.clone());

                // Chuck returned filters into appropriate filter lists
                $.each(json.inboundFilters, function(filterid, filter) {
                    var $pageElement = $('<option>').val(filterid).text(filter.operand);

                    if (filter.type == FILTER_TYPE_PHONENO) {
                        $existingPhoneNumberFilterList.append($pageElement);
                    } else {
                        $existingKeywordFilterList.append($pageElement);
                    }
                });
                
                // Sort both lists
                $existingPhoneNumberFilterList.sortOptions();
                $existingKeywordFilterList.sortOptions();

            }

        );

    }

}

/**
 * Retrieves details of exisitng inboxes attached to a filter
 * @param filterId ID of filter to search against
 * @version 2012052301
 * @since 2011070401
 */
function getUsersOnFilter(filterId) {

    // Drop in loading image
    $usersOnFilterList.parent().append($IMAGE_LOADING.clone());
    $userSearcher.parent().append($IMAGE_LOADING.clone());

    var requestJSON = $.toJSON({
        mode        :   'getFilterDetails',
        filterId    :   filterId
    });

    $.getJSON('settings_filters_json.php',
        {json : requestJSON},
        function(json) {

            // Clear and enable user list
            lockUnlockLowerFilterForm(false);

            $.each(json.users, function(index, userData) {
                var $pageElement = $('<option />').val(userData.userId).text(nameDisplayString(userData.firstName, userData.lastName, userData.username));
                $usersOnFilterList.append($pageElement);
            });
            
            // Strip loading images
            $usersOnFilterList.parent().children('img:last').remove();
            $userSearcher.parent().children('img:last').remove();

        }

    );

}

/**
 * Binds event handling functions when the page loads
 */
$(document).ready(function() {

    // Set up elements used in page
    setUpElements();

    // Set up common binds to give a single point of failure and reduce re-walking of the DOM
    $filterAccountList                  = $('select[name=filterAccountList]');

    $existingKeywordFilterList          = $('select[name=existingKeywordFilterList]');
    $existingPhoneNumberFilterList      = $('select[name=existingPhoneNumberFilterList]');


    $newKeywordFilterLink               = $('img#addKeywordFilter');
    $newKeywordFilterOperand            = $('input[name=newKeywordFilter]');
    $cancelKeywordFilterLink            = $('img#cancelKeywordFilter');

    $newPhoneNumberFilterLink           = $('img#addPhoneFilter');
    $newPhoneNumberFilterOperand        = $('input[name=newPhoneNumberFilter]');
    $cancelPhoneNumberFilterLink        = $('img#cancelPhoneFilter');

    $userSearcher                       = $('input[name=textSearcher]');
    $usersOnFilterList                  = $('select#id_usersOnFilter');
    $removeUsersButton                  = $('input[name=removeUsersFromFilter]');
    $saveFilterButton                   = $('input[name=submitButton]');


    $newKeywordFilterOperand.parent().parent().hide();
    $newPhoneNumberFilterOperand.parent().parent().hide();

    // New/cancel new filter handlers
    $newKeywordFilterLink.click(function(event) {
        $(this).parent().parent().hide();
        $newKeywordFilterOperand.parent().parent().show();
    });

    $cancelKeywordFilterLink.click(function(event) {
        $(this).parent().parent().hide();
        $existingKeywordFilterList.parent().parent().show();
    });

    $newPhoneNumberFilterLink.click(function(event) {
        $(this).parent().parent().hide();
        $newPhoneNumberFilterOperand.parent().parent().show();
    });

    $cancelPhoneNumberFilterLink.click(function(event) {
        $(this).parent().parent().hide();
        $existingPhoneNumberFilterList.parent().parent().show();
    });

    /*
     * Account list combo box
     */
    $filterAccountList.change(getFiltersOnAccount);

    
    /*
     * Keyword/phone number fields and handlers
     */
    $existingKeywordFilterList.change(function(event) {
        resetFilterForm($(this).attr('id'));
        if ($(this).val() > 0) {
            getUsersOnFilter($(this).val());
        }
    });

    $existingPhoneNumberFilterList.change(function(event) {
        resetFilterForm($(this).attr('id'));
        if ($(this).val() > 0) {
            getUsersOnFilter($(this).val());
        }
    });

    $newKeywordFilterOperand.focus(function(event) {
        if ($(this).val() == '') {
            resetFilterForm($(this).attr('id'));
        }
    });

    $newPhoneNumberFilterOperand.focus(function(event) {
        if ($(this).val() == '') {
            resetFilterForm($(this).attr('id'));
        }
    });

    // Make sure only valid input appears in new filter boxes
    $newKeywordFilterOperand.keypress(function(event) {
        return validateKeywordInput(event);
    });

    $newKeywordFilterOperand.keyup(function(event) {
        $(this).val($(this).val().toUpperCase());
    });

    $newPhoneNumberFilterOperand.keypress(function(event) {
        return validatePhoneInput(event);
    });
    
    
    /*
     * User search autocompleter
     */
    $userSearcher
        .bind("keydown", function( event ) {
            // The TAB key will not cause the user to leave the input field
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $( this ).data( "autocomplete" ).menu.active ) {
		event.preventDefault();
            }
        })
        // Autocompleter - call user search backend
        .autocomplete(
            {
                delay       :   500,
                minLength   :   2,
                source      :   function(request, response) {
                    $.getJSON("searchMoodleUsers.php", {
                        json    :   $.toJSON({
                            mode    :   'searchByKeyword',
                            operand :   request.term
                        })
                    },
                    function(json) {
                        // On response, map JSON values into local array 
                        response($.map(json.users, function(item, index) {
                            return {
                                label   :   nameDisplayString(item.firstName, item.lastName, item.username),
                                value   :   index
                            }
                        }))
                    });
                },
                focus       :   function(event, ui) {
                    return false; // Prevent values being autofilled into textbox on focus
                },
                select      :   function(event, ui) {
                    // When an item is selected, clone it to the list of selected users
                    if (ui.item) {
                        $usersOnFilterList.addOption(ui.item.value, ui.item.label);
                    }
                    $userSearcher.val('').focus();
                    return false; // Prevents values being autofilled into textbox - equivalent of autoFill:false in old plugin
                }
            }
        ); 
    

    /**
     * Bind prevents return key from submitting
     * the form when the text searcher returns
     * no results
     */
    $userSearcher.keypress(function(event) {
        if (event.which == 13) return false;
    });

    $userSearcher.focus(function(event) {
        $(this).val('');
    });

    $userSearcher.blur(function(event) {
        $(this).val(M.str.block_moodletxt.adminlabelfilterusersearch);
    });

    $removeUsersButton.click(function(event) {
        event.preventDefault();
        $usersOnFilterList.removeOption(/./, true);
    });

    $saveFilterButton.click(function(event) {
        $usersOnFilterList.selectOptions(/./, true);
    });

    /**
     * Special onload handlers for form re-population.
     * If an account is already selected, grab filters.
     * If a new filter has been given a value, populate it.
     */
    $filterAccountList.change();

    if ($newKeywordFilterOperand.val() != '') {
        $newKeywordFilterLink.click();
    }

    if ($newPhoneNumberFilterOperand.val() != '') {
        $newPhoneNumberFilterLink.click();
    }

});