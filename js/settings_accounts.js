/**
 * jQuery include file for the account listing page
 * 
 * moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "txttools", "moodletxt", "Blackboard", "Blackboard Connect" or "Cy-nap"
 *   - Derivative works must be have their differences from the original material noted,
 *     and must not be misrepresentative of the origin of this material, or of the original service
 * 
 * Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
 * or legal liability arising from their use of this code.
 * 
 * @package uk.co.moodletxt.js
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011062701
 * @since 2011061001
 */

// Constants - not declared as const to mollycoddle IE
var ACCOUNT_TYPE_INVOICED = 0;
var ACCOUNT_TYPE_PREPAID = 1;

// Variables to handle AJAX requests
// I use globals because I have mad wicked skills, yo
var accountArray = new Array();
var activeRequests = new Array(); // Log of current requests
var numberOfAccounts; // Number of accounts displayed on page
var numberProcessed = 0; // Number of accounts in chain that have been processed so far
var ceaseRequests = false; // Whether or not to send any further requests after an error has been encountered

// jQuery page references - instantiate once, use anywhere
var $updateAllButton;
var $progressBar;
var $progressBarTextValue;
var $accountsTable;
var $accountRestrictionsDialog;
var $userSearcher;
var $allowedUserList;
var $saveUserRestrictionsButton;

// Images used in account processing
var $WARNING_ICON;
var $IMAGE_LOADING;
var $IMAGE_UPDATE_SUCCESSFUL;
var $IMAGE_OUTBOUND;
var $IMAGE_INBOUND;
var $IMAGE_ACCESS_DENIED;
var $IMAGE_EDIT_ACCESS;

/**
 * Receives account IDs from PHP
 * @param YUI Y3 object
 * @param accountIds Array of account IDs
 * @version 2011061301
 * @since 2011061301
 */
function receiveAccountIds(YUI, accountIds) {

    accountArray = accountIds;
    
}

/**
 * Sets up the images that are used in
 * page processing/animation
 * @version 2011062701
 * @since 2011061001
 */
function setUpImages() {
    
    $WARNING_ICON = $('<img />')
        .attr('src', 'pix/icons/warning.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.adminaccountupdatefailed)
        .attr('title', M.str.block_moodletxt.adminaccountupdatefailed)
        .css('float', 'left');

    $IMAGE_LOADING = $('<img />')
        .attr('src', 'pix/icons/ajax-loader.gif')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.adminaccountfragloading)
        .attr('title', M.str.block_moodletxt.adminaccountfragloading);

    $IMAGE_UPDATE_SUCCESSFUL = $('<img />')
        .attr('src', 'pix/icons/ok.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.adminaccountupdatesuccess)
        .attr('title', M.str.block_moodletxt.adminaccountupdatesuccess)
        .css('float', 'left');

    $IMAGE_OUTBOUND = $('<img />')
        .attr('src', 'pix/icons/allow_outbound.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessoutbound)
        .attr('title', M.str.block_moodletxt.altaccessoutbound)
        .addClass('clickableIcon')
        .click(toggleOutboundAccess);
        
    $IMAGE_INBOUND = $('<img />')
        .attr('src', 'pix/icons/allow_inbound.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessinbound)
        .attr('title', M.str.block_moodletxt.altaccessinbound)
        .addClass('clickableIcon')
        .click(toggleInboundAccess);

    $IMAGE_ACCESS_DENIED = $('<img />')
        .attr('src', 'pix/icons/access_denied.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessdenied)
        .attr('title', M.str.block_moodletxt.altaccessdenied)
        .addClass('clickableIcon');

    $IMAGE_ACCESS_EDIT = $('<img />')
        .attr('src', 'pix/icons/access_edit.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessedit)
        .attr('title', M.str.block_moodletxt.altaccessedit)
        .addClass('clickableIcon');
        
}

/**
 * Loads up user restriction data when requested
 * @param event Javascript Event object
 * @version 2011062701
 * @since 2011062101
 */
function loadUserRestrictionData(event) {
    
    var $parentRow = $(this).parent().parent();
    var rowNumber = $parentRow.parent().children().index($parentRow) + 1;
    var accountId = accountArray[rowNumber];
    
    // If this account is already being loaded, wait
    if (activeRequests[accountId] != null) {
        alert(M.str.block_moodletxt.erroroperationinprogress);
        return;
    }

    // Chuck in loading image
    $(this).parent().prepend($IMAGE_LOADING.clone().css('float', 'left'));
    $(this).remove();

    // Requests are asynchronous, so store details
    // of the active request.
    activeRequests[accountId] = rowNumber;

    // Build JSON string to request data with
    var requestJSON = $.toJSON({
        mode : 'getAccountDetails',
        accountId : accountId
    });

    // Make request and update accounts
    $.getJSON('settings_accounts_update.php',
        {json : requestJSON},
        handleLoadedUserRestrictionData
    );    
    
}

/**
 * Handles initial form data load for user restrictions
 * @param json JSON response containing user data
 * @version 2011062701
 * @since 2011062101
 */
function handleLoadedUserRestrictionData(json) {

    // If this transaction is not recognised, discard
    if (! json.accountID || activeRequests[json.accountID] == null) {
        return;
    }

    // Grab table references
    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $outboundCell = $accountRow.find('td:nth-child(4)');
   
    $outboundCell.children('img:first').remove();
    $outboundCell.prepend($IMAGE_ACCESS_EDIT.clone(true).css('float', 'left').click(loadUserRestrictionData));

    activeRequests[json.accountID] = null;  //I'd use splice(), but it re-indexes the array

    // Populate form

    $('input[name=accountSelector]').val('');
    $('input[name=currentTxttoolsAccount]').val(json.accountID);
    $allowedUserList.children().remove();
    
    for(var x in json.allowedUsers) {
        $allowedUserList.addOption(x, nameDisplayString(
            json.allowedUsers[x].firstName, 
            json.allowedUsers[x].lastName, 
            json.allowedUsers[x].username
        ));
    }
   
    // Open form dialog up
    $accountRestrictionsDialog.dialog({
        minWidth    :   500,
        minHeight   :   350,
        modal       :   true
    });

}

/**
 * When the user clicks to save restriction data,
 * this function handles the AJAX call to make the save
 * @param event Javascript Event object
 * @version 2011062701
 * @since 2011062401
 */
function updateUserRestrictionData(event) {
    event.preventDefault();

    // Lock down form and prepare data
    $(this).attr('disabled', 'disabled');
    $(this).parent().append($IMAGE_LOADING.clone());
    $allowedUserList.selectAll();
    
    // Run update
    $.getJSON(
        'settings_accounts_update.php',
        {
            json    :   $.toJSON({
                mode            :   'updateAccountRestrictions',
                accountId       :   $('input[name=currentTxttoolsAccount]').val(),
                allowedUsers    :   $allowedUserList.val()
            })
        },
        function(json) {
            $saveUserRestrictionsButton.parent().children('img:last').remove();
            $saveUserRestrictionsButton.removeAttr('disabled');
            if (json.hasError) {
                
            } else {
                $accountRestrictionsDialog.dialog('close');
            }
                
        }
    );
}

/**
 * Event handler for turning outbound access
 * on/off for a given account
 * @param event The click() event
 * @version 2011061001
 * @since 2011061001
 */
function toggleOutboundAccess(event) {
    toggleAccountAccess(event, this, 'toggleOutboundAccess');
}

/**
 * Event handler for turning inbound access
 * on/off for a given account
 * @param event The click() event
 * @version 2011061001
 * @since 2011061001
 */
function toggleInboundAccess(event) {
    toggleAccountAccess(event, this, 'toggleInboundAccess');
}

/**
 * Toggles inbound/outbound access for given accounts
 * (Abstraction of event handlers)
 * @param event The click() event
 * @param obj The element clicked on
 * @param mode Whether to affect inbound/outbound
 * @see toggleOutboundAccess, toggleInboundAccess
 * @version 2011061301
 * @since 2011061001
 */
function toggleAccountAccess(event, obj, mode) {

    var $parentRow = $(obj).parent().parent();
    var rowNumber = $parentRow.parent().children().index($parentRow) + 1;
    var accountId = accountArray[rowNumber];
    
    // If this account is already being updated, wait
    if (activeRequests[accountId] != null) {
        alert(M.str.block_moodletxt.erroroperationinprogress);
        return;
    }

    // Chuck in loading image
    $(obj).parent().append($IMAGE_LOADING.clone());
    $(obj).remove();

    // Requests are asynchronous, so store details
    // of the active request.
    activeRequests[accountId] = rowNumber;

    // Build JSON string to request data with
    var requestJSON = $.toJSON({
        mode : mode,
        accountId : accountId
    });

    // Make request and update accounts
    $.getJSON('settings_accounts_update.php',
        {json : requestJSON},
        handleAccountAccess
    );
}

/**
 * Callback function to handle JSON responses
 * for updating account access
 * @param json JSON response
 * @see toggleAccountAccess
 * @version 2011061601
 * @since 2011061001
 */
function handleAccountAccess(json) {

    // If this transaction is not recognised, discard
    if (! json.accountID || activeRequests[json.accountID] == null) {
        return;
    }

    // Grab table references
    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $outboundCell = $accountRow.find('td:nth-child(4)');
    var $inboundCell = $accountRow.find('td:nth-child(5)');

    // Update table with outbound status
    if (json.allowOutbound) {
        $outboundCell.children('img:last').remove();
        $outboundCell.append($IMAGE_OUTBOUND.clone(true));
    } else {
        $outboundCell.children('img:last').remove();
        $outboundCell.append($IMAGE_ACCESS_DENIED.clone(true).click(toggleOutboundAccess));
    }

    // Update table with inbound status
    if (json.allowInbound) {
        $inboundCell.children().remove();
        $inboundCell.append($IMAGE_INBOUND.clone(true));
    } else {
        $inboundCell.children().remove()
        $inboundCell.append($IMAGE_ACCESS_DENIED.clone(true).click(toggleInboundAccess));
    }
    
    activeRequests[json.accountID] = null;  //I'd use splice(), but it re-indexes the array

}

/**
 * Makes a series of calls to the txttools server
 * to update account credit information
 * @param event Button click event
 * @version 2011061301
 * @since 2011061001
 */
function updateAllAccounts(event) {

    // Make sure the user wants to update if there are many accounts
    if (numberOfAccounts > 5) {
        if (! confirm(M.str.block_moodletxt.adminaccountconfirmupdate)) {
            return;
        }
    }

    // Stop user double-tapping
    $updateAllButton.attr('disabled', 'disabled');

    // Set up progress bar
    $progressBar.slideDown().progressbar({value : 0});
    $progressBarTextValue.text(M.str.block_moodletxt.adminaccountprocessedfrag + ': 0/' + numberOfAccounts)

    numberProcessed = 0;

    // Iterate over accounts defined
    $accountsTable.find('tr:not(:first-child)').each(function(index) {

        // If one of the previous requests returned a fatal error,
        // get the hell out of here, she's gonna blow!
        if (ceaseRequests) {
            $progressBar.progressbar("option", "value", 100);
            $updateAllButton.removeAttr('disabled');
            return false; // Break $.each()
        }

        // Instantiate vars to make the code more readable
        var tableRow = index + 2;
        var $firstCell = $(this).children('td:first');
        var accountId = accountArray[tableRow];

        // Chuck in loading image
        $firstCell.children('img').remove();
        $firstCell.append($IMAGE_LOADING.clone().css('float', 'left'));

        // Requests are asynchronous, so store details
        // of the active request. (Reversed for lookup
        // in the other direction on the return journey.)
        activeRequests[accountId] = tableRow;

        // Build JSON string to request data with
        var requestJSON = $.toJSON({
            mode : 'updateAccountFromTxttools',
            accountId : accountId
        });

        // Make request and pass result to handler
        $.getJSON('settings_accounts_update.php',
            {json : requestJSON},
            handleAccountInfoUpdate
        );

    });
}

/**
 * Response handler for account credit info
 * @param json JSON response
 * @see updateAllAccounts
 * @version 2011061301
 * @since 2011061001
 */
function handleAccountInfoUpdate(json) {

    // If this transaction is not recognised, throw it in the trash
    if (! json.accountID || activeRequests[json.accountID] == null) {
        return;
    }

    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $firstCell = $accountRow.find('td:first');

    // Remove loading image
    $firstCell.children('img').remove();

    // If the response indicates errors...
    if (json.hasError) {

        $accountRow.everyTime(999, function() {
            $accountRow.animate({backgroundColor : '#FC686A'}, 2000).animate({backgroundColor : '#FCF0F0'}, 2000);
        });

        // Create warning icon and make error message its title
        $firstCell.prepend($WARNING_ICON.clone().attr('title', json.errorMessage));

        if (json.makeNoFurtherRequests) {
            breakOut = true;
        }

    } else {
        // Hide negative remaining numbers on invoiced accounts
        if (json.billingType == ACCOUNT_TYPE_INVOICED) {
            json.creditsRemaining = '\u221e';
        }

        if (json.billingType == ACCOUNT_TYPE_INVOICED) {
            json.billingType = M.str.block_moodletxt.billingtypeinvoiced;
        } else {
            json.billingType = M.str.block_moodletxt.billingtypeprepaid;
        }

        // Hey, hey, it's OK
        $firstCell.prepend($IMAGE_UPDATE_SUCCESSFUL.clone());


        // Update row content and highlight as updated
        $accountRow.animate({backgroundColor : '#5CFF8D'}, 2000);
        $accountRow.find('td:nth-child(6)').text(json.creditsUsed).css('font-weight','bold');
        $accountRow.find('td:nth-child(7)').text(json.creditsRemaining).css('font-weight', 'bold');
        $accountRow.find('td:nth-child(8)').text(json.billingType).css('font-weight', 'bold');
        $accountRow.find('td:nth-child(9)').text(json.updateTimeString).css('font-weight', 'bold');
    }

    // Update progress bar
    var currentValue = Math.ceil(100 / numberOfAccounts * ++numberProcessed);
    $progressBar.progressbar("option", "value", currentValue);
    $progressBarTextValue.text(M.str.block_moodletxt.adminaccountprocessedfrag + ': ' + numberProcessed + '/' + numberOfAccounts);

    // Re-enable update button if processing complete
    if (numberOfAccounts == numberProcessed) {

        // Hide progress bar after 3 seconds
        setTimeout(function() {
            $progressBar.slideUp();
            $updateAllButton.removeAttr('disabled');
        }, 3000);
    }

    activeRequests[json.accountID] = null;  //I'd use splice(), but it re-indexes the array

}



// Page load!
$(document).ready(function() {

    // Set up images used
    setUpImages();

    // Instantiate progress bar reference
    $accountsTable = $('table#accountListTable');
    $updateAllButton = $('button#updateAllAccounts');
    $progressBar = $('div#accountProgressBar');
    $progressBarTextValue = $('div#accountProgressTextValue');
    $accountRestrictionsDialog = $('#accountRestrictionsDialog');
    $userSearcher = $('input#id_accountSelector');
    $allowedUserList = $('select#id_restrictedUsers');
    $saveUserRestrictionsButton = $('#id_submitButton');

    numberOfAccounts = $accountsTable.find('tr:not(:first-child)').length;
    
    /*
     * ACCESS RESTRICTIONS FORM BINDS
     */
    
    // Set up hidden account restrictions form before enabling table controls
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
                        $allowedUserList.addOption(ui.item.value, ui.item.label);
                    }
                    $userSearcher.val('').focus();
                    return false; // Prevents values being autofilled into textbox - equivalent of autoFill:false in old plugin
                }
            }
        ); 
                
    $('#id_removeUserButton').click(function(event) {
        $allowedUserList.removeOption(/./, true);
    });
    
    $saveUserRestrictionsButton.click(updateUserRestrictionData);



    /*
     * MAIN PAGE BINDS
     */

    // Get credit info when button is clicked
    $updateAllButton.click(updateAllAccounts);
    $updateAllButton.removeAttr('disabled'); // Weird glitch in FF4

    // Show access dialog when icon is clicked
    $accountsTable.find('td:nth-child(4) img:first').each(function(index) {
        $(this).click(loadUserRestrictionData);
    });
   
   // Toggle outbound access when clicked
    $accountsTable.find('td:nth-child(4) img:last').each(function(index) {
        $(this).click(toggleOutboundAccess);
    });

    // Toggle inbound access when clicked
    $accountsTable.find('td:nth-child(5) img').each(function(index) {
        $(this).click(toggleInboundAccess);
    });
    

    // Iterate through on load and set
    // account IDs as row attributes - makes
    // it far easier to associate them with
    // child elemenets
    for (var x = 2; x < accountArray.length; x++) {
        $accountsTable.find('tr:nth-child(' + x + ')').attr('id', accountArray[x]);
    }

    // Hide the progress bar on load
    $progressBar.hide();
    
});