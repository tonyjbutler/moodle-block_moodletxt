<?php

/**
 * File container for TxttoolsAccountRestrictionsForm class
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
 * @version 2012092101
 * @since 2011061501
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');

/**
 * Account restrictions form - allows txttools accounts
 * to be restricted to certain Moodle users. This form is defined
 * here, but processed via AJAX
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092101
 * @since 2011061501
 */
class TxttoolsAccountRestrictionsForm extends MoodletxtAbstractForm {

    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2011062401
     * @since 2011061501
     */
    public function definition() {
        
        global $CFG;
        
        $restrictionsForm =& $this->_form;
        
        $restrictionsForm->addElement('header', 'headerRestrictions', get_string('headeraccountrestrictionsfor', 'block_moodletxt'));
        
        $restrictionsForm->addElement('hidden', 'currentTxttoolsAccount');
        $restrictionsForm->setType('currentTxttoolsAccount', PARAM_INT);
        
        $restrictionsForm->addElement('text', 'accountSelector', get_string('labelsearchusers', 'block_moodletxt'));
        $restrictionsForm->setType('accountSelector', PARAM_ALPHANUMEXT);
        
        $restrictionsForm->addElement('select', 'restrictedUsers', get_string('labelrestrictedusers', 'block_moodletxt'), array(), array('size' => 5, 'multiple' => 'multiple', 'style' => 'min-width:100px;'));
        $restrictionsForm->setType('restrictedUsers', PARAM_INT);
        
        $restrictionsForm->addElement('button', 'removeUserButton', get_string('buttonremoveusers', 'block_moodletxt'));
        
        // Buttons
        
        $buttonArray=array();
        $buttonArray[] = &$restrictionsForm->createElement('submit', 'submitButton', get_string('buttonsave', 'block_moodletxt'));
        $restrictionsForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
        $restrictionsForm->closeHeaderBefore('buttonar');
        
    }
    
    /**
     * Validation routine for account form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2011062401
     * @since 2011061501
     */
    public function validation($formData, $files=null) {
        
        return array();
        
    }
        
}

?>
