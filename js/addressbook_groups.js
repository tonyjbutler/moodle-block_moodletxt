/**
 * jQuery scripting for the group management page
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
 * @since 2012092401
 */

/**
 * Holds associations between groups and their member contacts
 */
var CONTACT_ASSOCIATIONS = [];

/**
 * Receives group-member associations from the Moodle/YUI system
 * @param YUI YUI object (not used)
 * @param groupId ID of addressbook group
 * @param contactIds Array of member IDs
 * @version 2012092401
 * @since 2012092401
 */
function receiveGroupContactAssociations(YUI, groupId, contactIds) {
    
    CONTACT_ASSOCIATIONS[groupId] = contactIds;
    
}

$(document).ready(function() {
    
    // Move known group members to "selected members" list
    // when user chooses a group to edit
    $('select[name=editExistingGroup]').change(function(event) {
        
        $('select#editGroupMembers-t option').prop('selected', true);
        $('input[name=remove]').click(); // Use QFAMS handling to prevent conflicts
        
        var selectedGroup = $(this).val();
        for(var contactIndex in CONTACT_ASSOCIATIONS[selectedGroup]) {
            $('select#editGroupMembers-f option[value=' + CONTACT_ASSOCIATIONS[selectedGroup][contactIndex] + ']').prop('selected', true);
        }
        
        $('input[name=add]').click();
        
    });
    
    // Remove QFAMS' stupid disabling of the "selected" list
    $('select#editGroupMembers-t').removeAttr('disabled').children('option').remove();
    
});