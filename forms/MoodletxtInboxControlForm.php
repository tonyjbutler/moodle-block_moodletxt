<?php

/**
 * File container for NewTxttoolsAccountForm class
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
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012100901
 * @since 2011080801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');

/**
 * New account form - takes username/password details
 * from user for any new txttools account added to the system
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012100901
 * @since 2011080801
 */
class MoodletxtInboundControlForm extends MoodletxtAbstractForm {
    
    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2012100901
     * @since 2011080801
     */
    public function definition() {
        
        $inboxForm =& $this->_form;

        // Txttools account

        $inboxForm->addElement('select', 'action', '', array(
            ''              => get_string('optionwithselected', 'block_moodletxt'),
            'killmaimburn'  => get_string('optiondelete', 'block_moodletxt'),
            'copy'          => get_string('optioncopy', 'block_moodletxt'),
            'move'          => get_string('optionmove', 'block_moodletxt')
        ));
        $inboxForm->setType('action', PARAM_ALPHA);

        $inboxForm->addElement('select', 'accounts', '', $this->_customdata['userlist'], array('disabled' => 'disabled'));
        $inboxForm->setType('accounts', PARAM_ALPHA);
        
    }

    /**
     * Validation routine for account form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2012042401
     * @since 2011080801
     */
    public function validation($formdata, $files = null) {
        
        $err = array();

        // This form should never be actually submitted - AJAX controlled
        
        return $err;
        
    }
    
}

?>