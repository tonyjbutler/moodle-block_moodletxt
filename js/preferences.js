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
 * @since 2011072901
 */

// Holds jQuery object for main template box
var $templateText;


/**
 * Adds a "mail merge" %TAG% to the message text
 * @param string tag Tag to add to message
 * @version 2011072901
 * @since 2011072901
 */
function addMessageTag(tag) {

    $templateText.insertAtCaret(tag).keyup().focus();

}

/**
 * On-LLLLLLLLLLOOOOOAAAADDDDDD!
 */
$(document).ready(function() {

    // Man, some of the IDs on this page are confusing.
    // Oh well, let's abstract them!
    var $templateList           = $('select[name=existingTemplate]');
    var $templateEditId         = $('input[name=templateToEdit]');
    var $templateDeleteId       = $('input[name=templateToDelete]')
    $templateText               = $('textarea[name=templateText]');

    var $templateEditButton     = $('input[name=editTemplate]');
    var $templateDeleteButton   = $('input[name=deleteTemplate]');
    var $templateEditHeader     = $('fieldset#headerTemplateNew legend:first');
    var $templateSubmitButton   = $('input#templateEditSubmit');

    /**
     * When a user presses the edit button,
     * load the selected template into the editor
     * for updating
     */
    $templateEditButton.click(function(event) {

        var selectedTemplate = $templateList.val();

        if (selectedTemplate < 1) {
            alert(M.str.block_moodletxt.alertnotemplateselected);
            return false;
        }

        // Set form elements
        $templateEditId.val(selectedTemplate);
        $templateText.val($templateList.children('option[value=' + selectedTemplate + ']').text()).keyup();

        // Update GUI
        $templateEditHeader.text(M.str.block_moodletxt.headertemplatesedit);
        $templateSubmitButton.val(M.str.block_moodletxt.settingsedittemplate);

    });
    
    /**
     * When a user clicks the delete button, confirm that
     * they wish to delete, then save the selected template ID as 
     * appropriate and submit the form.
     */
    $templateDeleteButton.click(function(event) {
        var selectedTemplate = $templateList.val();

        if (selectedTemplate < 1) {
            alert(M.str.block_moodletxt.alertnotemplateselected);
            return false;
        }
        
        if (confirm(M.str.block_moodletxt.alertconfirmdeletetemplate)) {
            $templateDeleteId.val(selectedTemplate);
            $('form#mform1').submit();
        }
    });

    /**
     * Handler for the message box - computes stats and whatnot
     * while user is typing
     */
    $templateText.keyup(function(event) {
        updateMessageBox(this, {
            checkForUnicode : false
        });
    });

    /**
     * Tag handler for first name
     */
    $('input[name=firstName]').click(function(event) {
        addMessageTag('%FIRSTNAME%');
    });

    /**
     * Tag handler for surname
     */
    $('input[name=lastName]').click(function(event) {
       addMessageTag('%LASTNAME%');
    });

    /**
     *  Tag handler for full name
     */
    $('input[name=fullName]').click(function(event) {
       addMessageTag('%FULLNAME%');
    });

    // Update signature length counter
    // (Using the full-fat unicode-checking badassery
    // here would be a bit overkill)
    $('input[name=signature]').keyup(function(event) {
        $('input[name=charsRemaining]').val(25 - $(this).val().length);
    });
    
    $('input[name=signature]').keyup();
    $templateText.keyup();

});