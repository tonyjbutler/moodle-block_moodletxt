<?php

/**
 * File container for TxttoolsAccountEditForm class
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
 * @version 2012100801
 * @since 2012100501
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');

/**
 * Account edit form - allows for descriptions and passwords
 * to be updated on ConnectTxt forms. Defined and displayed
 * from here, but processed via AJAX.
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012100801
 * @since 2012100501
 */
class TxttoolsAccountEditForm extends MoodletxtAbstractForm {

    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2012100801
     * @since 2012100501
     */
    public function definition() {
        
        global $CFG;
        
        $editForm =& $this->_form;
        
        $editForm->addElement('header', 'headerRestrictions', get_string('headeraccountedit', 'block_moodletxt'));
        
        $editForm->addElement('hidden', 'editedTxttoolsAccount');
        $editForm->setType('editedTxttoolsAccount', PARAM_INT);
        
        $editForm->addElement('static', 'accountName', get_string('adminlabelaccusername', 'block_moodletxt'));
        
        $editForm->addElement('text', 'accountDescription', get_string('adminlabelaccdesc', 'block_moodletxt'));
        $editForm->setType('accountDescription', PARAM_TEXT);
        
        $editForm->addElement('password', 'accountPassword', get_string('labelpasswordnew', 'block_moodletxt'));
        $editForm->setType('accountPassword', PARAM_ALPHANUMEXT);
                
        // Buttons
        
        $buttonArray=array();
        $buttonArray[] = &$editForm->createElement('submit', 'submitButton', get_string('buttonsave', 'block_moodletxt'));
        $editForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
        $editForm->closeHeaderBefore('buttonar');
        
    }
    
    /**
     * Validation routine for account form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2012100501
     * @since 2012100501
     */
    public function validation($formData, $files=null) {
        
        return array();
        
    }
        
}

?>
