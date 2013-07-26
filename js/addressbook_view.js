/**
 * jQuery scripting for the user preferences page
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
 * @since 2012090501
 */

var currentCourse = 0;
var currentInstance = 0;
var currentAddressbook = 0;

// Set of variables to hold original cell values while editing
var currentlyOpenRow = -1;
var originalCellTexts = new Array();
var cellMappings = {
    0 : 'id',
    1 : 'firstName',
    2 : 'lastName',
    3 : 'company',
    4 : 'phoneNo'
};

/**
 * Receives and stores the current course
 * ID from the PHP backend
 * @param YUI YUI object, not used
 * @param courseId ID of the course
 * @version 2012090701
 * @since 2012090701
 */
function receiveCourseId(YUI, courseId) {
    currentCourse = courseId;
}

/**
 * Receives and stores the current block
 * instance ID from the PHP backend
 * @param YUI YUI object, not used
 * @param instanceId ID of the instance
 * @version 2012090701
 * @since 2012090701
 */
function receiveInstanceId(YUI, instanceId) {
    currentInstance = instanceId;
}

/**
 * Receives and stores the ID of the currently
 * open addressbook from the PHP backend
 * @param YUI YUI object, not used
 * @param addressbookId ID of the addressbook
 * @version 2012090701
 * @since 2012090701
 */
function receiveAddressbookId(YUI, addressbookId) {
    currentAddressbook = addressbookId;
}

/**
 * Function resets the editing table,
 * removing any open records
 * @param saved Whether this row's data was updated and saved
 * @version 2013010901
 * @since 2012090601
 */
function resetRow(saved) {

    if (currentlyOpenRow >= 0) {

        // Get the open row and its child cells
        var $tableRow = $('table#contactsList tbody tr').eq(currentlyOpenRow);
        var $childCells = $tableRow.children('td');
        
        // Re-enable checkbox - disabled during save
        $childCells.eq(0).children('input').removeAttr('disabled');
        
        // Restore cell text. Use input value if save was successful,
        // and the original text otherwise
        for (var x = 1; x < $childCells.length; x++) {
            
            var newCellText = (saved)
                ? newCellText = $childCells.eq(x).children('input').filter(':first').val()
                : newCellText = originalCellTexts[x];
            
            $childCells.eq(x).empty().text(newCellText);
            
        }

        // Reset variables holding form data
        originalCellTexts = new Array();
        currentlyOpenRow = -1;

    }

}

/**
 * Converts a contact data row into a form,
 * which can then be used to update the contact's data
 * @param event Javascript event object
 * @version 2012090701
 * @since 2012090501
 */
function editTableRow(event) {
        
    var rowNumber = $(this).parent().children('tr').index($(this));

    // Check that you're not double-clicking the same row twice
    if (rowNumber != currentlyOpenRow) {

        resetRow(false); // Clear any other already opened rows
        currentlyOpenRow = rowNumber;
        
        var $childCells = $(this).children('td');

        for (var x = 1; x < $childCells.length; x++) {
            
            // Storing original text of cell in array and replacing it with a textbox
            var $cell = $childCells.eq(x);
            originalCellTexts[x] = $cell.text();
            
            $cell.empty().append(
                $('<input />')
                    .attr('type', 'text')
                    .attr('name', cellMappings[x])
                    .val(originalCellTexts[x])
                    .keypress(captureReturnKey)
            );
                
        }
        
        // Add phone validation and buttons to 5th cell
        $childCells.eq(4).children('input').filter('first').keypress(validatePhoneInput);

        // Create save and cancel buttons
        $childCells.eq(4).append(
        
            $('<input />')
                .attr('type', 'button')
                .val(M.str.block_moodletxt.buttonsave)
                .click(updateContact),
                
            $('<input />')
                .attr('type', 'button')
                .val(M.str.block_moodletxt.buttoncancel)
                .click(function(event) { resetRow(false); } )
        );
            
    }

}

/**
 * When the user submits an edited contact data row,
 * this handler gathers the inputted data and updates
 * the contact record in the database
 * @param event Javascript event object
 * @version 2013010901
 * @since 2012090601
 */
function updateContact(event) {

    // If there is something to submit, grab the form entries
    if (currentlyOpenRow >= 0) {

        var $tableRow = $('table#contactsList tbody tr').eq(currentlyOpenRow);
        var contactObject = { 
            mode : 'updateContact', 
            addressbookId : currentAddressbook,
            contact : {}
        };

        // Grab form values and put them into data object according to mapping
        for (var key in cellMappings) {            
            contactObject['contact'][cellMappings[key]] = $tableRow.children('td').eq(key).children('input').val();
        }

        // Kill off the buttons and lock the form
        $tableRow.find('input[type=button]').remove();
        $tableRow.find('input').attr('disabled', 'disabled');

        // Displays loading image on form row
        $tableRow.children('td').eq(4).append(
            $('<img />')
                .attr('src', 'pix/icons/ajax-loader.gif')
                .attr('width', '16')
                .attr('height', '16')
                .attr('alt', M.str.block_moodletxt.loadtoken)
                .attr('title', M.str.block_moodletxt.loadtoken)
        );

        // Update the contact in DB
        $.post(
            'addressbook_view_update.php?course=' + currentCourse + '&instance=' + currentInstance,
            { json : $.toJSON(contactObject) },
            function(response) {
                
                if (response.hasError == false) {
                    resetRow(true);
                } else {
                    alert(response.errorMessage);
                }
                
            }
        )

    }

}

/**
 * Submits a contact row when the user hits return
 * in any of the available form fields
 * @param event Javascript keypress event
 * @version 2012090601
 * @since 2012090601
 */
function captureReturnKey(event) {
    if (event.which == 13) { 
        updateContact(event);
    }
}

/**
 * When the user clicks on one of the contact delete controls,
 * this function gathers the contact IDs together and submits
 * them to the backend for deletion
 * @param deleteType Whether delete is inclusive or exclusive
 * @version 2012091201
 * @since 2012091201
 */
function deleteContacts(deleteType) {
    
    var arrayOfValues = $('input[name=contactIds\\[\\]]').filter(':checked').map(function() { return this.value }).get();

    if (arrayOfValues.length > 0) {
        $('input[name=deleteContactIds]').val(arrayOfValues.join(','));
        $('input[name=deleteType]').val(deleteType);
        $('form#mform2').submit();
    }
    
}

$(document).ready(function() {

    // When a row on the table is double clicked, edit it
    // (Moodle 2.4 and above render tables with <thead>
    // 2.3 and below did not.)
    if ($('#contactsList').children('thead').length > 0) {
        $('#contactsList tbody tr').dblclick(editTableRow);
    } else {
        $('#contactsList tbody tr').not(':first').dblclick(editTableRow);
    }

    $('a#checkAllBoxes').click(function(event) {
        event.preventDefault();
        $('#contactsList').find('input[type=checkbox]').attr('checked', 'checked');
    });

    $('a#uncheckAllBoxes').click(function(event) {
        event.preventDefault();
        $('#contactsList').find('input[type=checkbox]').removeAttr('checked');
    });

    $('a#deleteSelected').click(function(event) {
        event.preventDefault();
        deleteContacts('inclusive');
    });

    $('a#deleteExceptSelected').click(function(event) {
        event.preventDefault();
        deleteContacts('exclusive');
    });

});