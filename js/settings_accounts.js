/**
 * jQuery include file for the account listing page
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
 * @package uk.co.moodletxt.js
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
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
var $accountEditDialog;
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
var $IMAGE_ACCOUNT_EDIT;

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
 * @version 2012101601
 * @since 2011061001
 */
function setUpImages() {
    
    $WARNING_ICON = $('<img />')
        .attr('src', 'pix/icons/warning.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.adminaccountupdatefailed)
        .attr('title', M.str.block_moodletxt.adminaccountupdatefailed)
        .css('float', 'left')
        .addClass('warningIcon');

    $IMAGE_LOADING = $('<img />')
        .attr('src', 'pix/icons/ajax-loader.gif')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.adminaccountfragloading)
        .attr('title', M.str.block_moodletxt.adminaccountfragloading)
        .addClass('loadingIcon');

    $IMAGE_UPDATE_SUCCESSFUL = $('<img />')
        .attr('src', 'pix/icons/ok.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.adminaccountupdatesuccess)
        .attr('title', M.str.block_moodletxt.adminaccountupdatesuccess)
        .css('float', 'left')
        .addClass('successIcon');

    $IMAGE_OUTBOUND = $('<img />')
        .attr('src', 'pix/icons/allow_outbound.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessoutbound)
        .attr('title', M.str.block_moodletxt.altaccessoutbound)
        .addClass('mdltxtClickableIcon')
        .click(toggleOutboundAccess);
        
    $IMAGE_INBOUND = $('<img />')
        .attr('src', 'pix/icons/allow_inbound.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessinbound)
        .attr('title', M.str.block_moodletxt.altaccessinbound)
        .addClass('mdltxtClickableIcon')
        .click(toggleInboundAccess);

    $IMAGE_ACCESS_DENIED = $('<img />')
        .attr('src', 'pix/icons/access_denied.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessdenied)
        .attr('title', M.str.block_moodletxt.altaccessdenied)
        .addClass('mdltxtClickableIcon');

    $IMAGE_ACCESS_EDIT = $('<img />')
        .attr('src', 'pix/icons/access_edit.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccessedit)
        .attr('title', M.str.block_moodletxt.altaccessedit)
        .addClass('mdltxtClickableIcon');
        
    $IMAGE_ACCOUNT_EDIT = $('<img />')
        .attr('src', 'pix/icons/edit.png')
        .attr('width', '16')
        .attr('height', '16')
        .attr('alt', M.str.block_moodletxt.altaccountedit)
        .attr('title', M.str.block_moodletxt.altaccountedit)
        .addClass('mdltxtClickableIcon');
        
}

/**
 * Loads the given type of editing dialog for 
 * a selected account from the list
 * @param event JavaScript event object
 * @param loadHandler Handler function to call when data has been retrieved
 * @version 2012101601
 * @since 2012100501
 */
function loadAccountDialog(event, loadHandler) {
    
    var $icon = $(event.target);
    var $parentRow = $icon.parent().parent();
    var rowNumber  = $parentRow.parent().children().index($parentRow) + 1;
    var accountId  = accountArray[rowNumber];
    
    // If this account is already being loaded, wait
    if (typeof(activeRequests[accountId]) !== 'undefined') {
        alert(M.str.block_moodletxt.erroroperationinprogress);
        return;   
    }

    // Chuck in loading image
    $icon.parent().children('img').filter(':first').after(
        $IMAGE_LOADING.clone().css('float', 'left')
    );
    $icon.remove();

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
        function(json) { loadHandler(json); }
    );    
    
}

/**
 * Handles initial form data load for user restrictions
 * @param json JSON response containing user data
 * @version 2013052301
 * @since 2011062101
 */
function handleLoadedUserRestrictionData(json) {

    // If this transaction is not recognised, discard
    if (! json.accountID || 
            typeof(activeRequests[json.accountID]) === 'undefined') {
        return;
    }

    // Grab table references
    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $outboundCell = $accountRow.find('td:nth-child(4)');
   
    $outboundCell.children('img.loadingIcon').remove();
    $outboundCell.prepend($IMAGE_ACCESS_EDIT.clone(true).css('float', 'left').click(function(event) { 
        loadAccountDialog(event, handleLoadedUserRestrictionData);
    }));

    delete activeRequests[json.accountID];  

    // Populate form

    $('input[name=accountSelector]').val('');
    $('input[name=currentTxttoolsAccount]').val(json.accountID);
    $accountRestrictionsDialog.find('p.error').remove();
    
    for(var x in json.allowedUsers) {
        $allowedUserList.addOption(x, nameDisplayString(
            json.allowedUsers[x].firstName, 
            json.allowedUsers[x].lastName, 
            json.allowedUsers[x].username
        ));
    }
   
    // Open form dialog up
    $accountRestrictionsDialog.dialog({
        close       :   function(event, ui) {
            $('input[name=accountSelector]').val('');
            $('select[name=restrictedUsers\\[\\]]').children().remove();
        },
        minWidth    :   500,
        minHeight   :   350,
        modal       :   true
    });

}

/**
 * When the user clicks to save restriction data,
 * this function handles the AJAX call to make the save
 * @param event Javascript Event object
 * @version 2012100901
 * @since 2011062401
 */
function updateUserRestrictionData(event) {
    event.preventDefault();

    // Lock down form and prepare data
    $accountRestrictionsDialog.find('p.error').remove();
    $(this).attr('disabled', 'disabled');
    $(this).parent().append($IMAGE_LOADING.clone());
    $allowedUserList.selectAll();
    
    var $saveUserRestrictionsButton = $(this);
    
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
                $accountRestrictionsDialog.append(
                    $('<p />').addClass('error').text(json.errorMessage)
                );
            } else {
                $allowedUserList.children().remove();
                $accountRestrictionsDialog.dialog('close');
            }
                
        }
    );
}

/**
 * Handler function, called when the user
 * is editing basic details of an account.
 * Opens the editing dialog and populates the form.
 * @param json Account info from database
 * @version 2013052301
 * @since 2012100501
 */
function handleLoadedUserEditData(json) {
    
    // If this transaction is not recognised, discard
    if (! json.accountID || 
            typeof(activeRequests[json.accountID]) === 'undefined') {
        return;
    }

    // Grab table references
    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $accountCell = $accountRow.find('td:nth-child(1)');
   
    // Reset icons to as they were before the load
    $accountCell.children('img').filter(':last').after(
        $IMAGE_ACCOUNT_EDIT.clone(true).css('float', 'left').click(function(event) { 
            loadAccountDialog(event, handleLoadedUserEditData);
        })
    );
    $accountCell.children('img.loadingIcon').remove();

    delete activeRequests[json.accountID];  

    // Populate form

    $('input[name=editedTxttoolsAccount]').val(json.accountID);
    $('input[name=accountDescription]').val(json.description);
    $accountEditDialog.find('div.fstatic').filter(':first').text(json.username);
    $accountEditDialog.find('p.error').remove();
    $accountEditDialog.data('accountTableRow', $accountRow);

    // Open form dialog up
    $accountEditDialog.dialog({
        close       :   function(event, ui) {
            $('input[name=accountDescription]').val('');
            $('input[name=accountPassword]').val('');
        },
        minWidth    :   500,
        minHeight   :   350,
        modal       :   true
    });

}

/**
 * Saves edited account data back
 * to the database
 * @param event Submit button click event
 * @version 2013052301
 * @since 2012100801
 */
function updateAccountData(event) {
    
    event.preventDefault();

    // Lock down form and prepare data
    $accountEditDialog.find('p.error').remove();
    $(this).attr('disabled', 'disabled');
    $(this).parent().append($IMAGE_LOADING.clone());
    
    var $saveAccountButton = $(this);
    var params = {
        mode            :   'updateAccountFromUser',
        accountId       :   $('input[name=editedTxttoolsAccount]').val(),
        description     :   $('input[name=accountDescription]').val()
    };
    
    if ($('input[name=accountPassword]').val().length) {
        params.newPassword = $('input[name=accountPassword]').val();
    }
    
    // Run update
    $.getJSON(
        'settings_accounts_update.php',
        {
            json    :   $.toJSON(params)
        },
        function(json) {
            $saveAccountButton.parent().children('img:last').remove();
            $saveAccountButton.removeAttr('disabled');
            if (json.hasError) {
                $accountEditDialog.append(
                    $('<p />').addClass('error').text(json.errorMessage)
                );
            } else {
                $accountEditDialog.data('accountTableRow').find('td:nth-child(2)')
                        .text($('input[name=accountDescription]').val());
                
                $('input[name=editedTxttoolsAccount]').val(0);
                $('input[name=accountDescription]').val('');
                $('input[name=accountPassword]').val('');
                $accountEditDialog.dialog('close');
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
 * @version 2012101601
 * @since 2011061001
 */
function toggleAccountAccess(event, obj, mode) {

    var $parentRow = $(obj).parent().parent();
    var rowNumber = $parentRow.parent().children().index($parentRow) + 1;
    var accountId = accountArray[rowNumber];
    
    // If this account is already being updated, wait
    if (typeof(activeRequests[accountId]) !== 'undefined') {
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
 * @version 2012101601
 * @since 2011061001
 */
function handleAccountAccess(json) {

    // If this transaction is not recognised, discard
    if (! json.accountID || typeof(activeRequests[json.accountID]) === 'undefined') {
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
    
    delete activeRequests[json.accountID];

}

/**
 * Makes a series of calls to the txttools server
 * to update account credit information
 * @param event Button click event
 * @version 2012121201
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
    
    var $tableRowSet = [];
    var tableFudge = 0;

    // In Moodle 2.4 and above, tables are rendered with <thead> tags
    if ($accountsTable.children('thead').length > 0) {
        $tableRowSet = $accountsTable.find('tbody tr');
        tableFudge = 1;
    } else {
        $tableRowSet = $accountsTable.find('tr:not(:first-child)');
        tableFudge = 2;
    }

    // Iterate over accounts defined
    $tableRowSet.each(function(index) {

        // If one of the previous requests returned a fatal error,
        // get the hell out of here, she's gonna blow!
        if (ceaseRequests) {
            $progressBar.progressbar("option", "value", 100);
            $updateAllButton.removeAttr('disabled');
            return false; // Break $.each()
        }

        // Instantiate vars to make the code more readable
        var tableRow = index + tableFudge;
        var $firstCell = $(this).children('td:first');
        var accountId = accountArray[tableRow];

        // Chuck in loading image
        $firstCell.children('img.warningIcon').remove();
        $firstCell.children('img.successIcon').remove();
        $firstCell.prepend($IMAGE_LOADING.clone().css('float', 'left'));

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
 * @version 2012101601
 * @since 2011061001
 */
function handleAccountInfoUpdate(json) {

    // If this transaction is not recognised, throw it in the trash
    if (! json.accountID || typeof(activeRequests[json.accountID]) === 'udnefined') {
        return;
    }

    var tableRow = activeRequests[json.accountID];
    var $accountRow = $accountsTable.find('tr:nth-child(' + tableRow + ')');
    var $firstCell = $accountRow.find('td:first');

    // Remove loading image
    $firstCell.children('img.loadingIcon').remove();

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

    delete activeRequests[json.accountID];

}



// Page load!
$(document).ready(function() {

    // Set up images used
    setUpImages();

    // Instantiate progress bar reference
    $accountsTable = $('table#accountListTable');
    $updateAllButton = $('button#mdltxtUpdateAllAccounts');
    $progressBar = $('div#accountProgressBar');
    $progressBarTextValue = $('div#accountProgressTextValue');
    $accountEditDialog = $('#accountEditDialog');
    $accountRestrictionsDialog = $('#mdltxtAccountRestrictionsDialog');
    $userSearcher = $('input#id_accountSelector');
    $allowedUserList = $('select#id_restrictedUsers');

    // In Moodle 2.4 and above, tables are rendered with <thead> tags
    if ($accountsTable.children('thead').length > 0) {
        numberOfAccounts = $accountsTable.find('tbody tr').length;
    } else {
        numberOfAccounts = $accountsTable.find('tr:not(:first-child)').length;
    }
    
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
                            };
                        }));
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
    


    /*
     * MAIN PAGE BINDS
     */

    // Get credit info when button is clicked
    $updateAllButton.click(updateAllAccounts);
    $updateAllButton.removeAttr('disabled'); // Weird glitch in FF4

    // Show editing dialog when icon is clicked
    $accountsTable.find('td:first-child img').each(function(index) {
        $(this).click(function(event) {
            loadAccountDialog(event, handleLoadedUserEditData);
        });
    });
    
    // Save edited account info when form is submitted
    $accountEditDialog.find('input[type=submit]').click(updateAccountData);

    // Show access dialog when icon is clicked
    $accountsTable.find('td:nth-child(4) img:first-child').each(function(index) {
        $(this).click(function(event) {
            loadAccountDialog(event, handleLoadedUserRestrictionData);
        });
    });
    
    // Save access info when form is submitted
    $accountRestrictionsDialog.find('input[type=submit]').click(updateUserRestrictionData);
   
   // Toggle outbound access when clicked
    $accountsTable.find('td:nth-child(4) img:last-child').each(function(index) {
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