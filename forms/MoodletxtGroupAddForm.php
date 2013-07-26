<?php

/**
 * File container for MoodletxtGroupAddForm class
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
 * @version 2012092401
 * @since 2012092201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');

/**
 * Form allows user to add groups to their addressbook
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092401
 * @since 2012092201
 */
class MoodletxtGroupAddForm extends MoodletxtAbstractForm {
        
    /**
     * Sets up the form object with initial data/attributes
     * @param string $action Form destination
     * @param array $customdata Custom data for pre-populating form fields
     * @param string $method Method of form submission - GET or POST
     * @param string $target Form's target
     * @param array $attributes HTML form attributes
     * @param boolean $editable Whether the form can be edited
     * @version 2012092201
     * @since 2012092201
     */
    public function __construct($action = null, $customdata = null,
            $method = 'post', $target = '', $attributes = null, $editable = true) {
        
        parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
    }
    
    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2012092201
     * @since 2012092201
     */
    public function definition() {
        
        global $CFG;
        
        $groupForm =& $this->_form;

        $groupForm->addElement('hidden', 'course');
        $groupForm->setType('course', PARAM_INT);
        
        $groupForm->addElement('hidden', 'instance');
        $groupForm->setType('instance', PARAM_INT);
        
        $groupForm->addElement('hidden', 'addressbook');
        $groupForm->setType('addressbook', PARAM_INT);
        
        // Group details section
        $groupForm->addElement('header', 'addGroup', get_string('headergroupadd', 'block_moodletxt'));

        $groupForm->addElement('text', 'newGroupName', get_string('labelgroupname', 'block_moodletxt'), array('maxlength' => 50));
        $groupForm->setType('newGroupName', PARAM_TEXT);

//        TO BE USED WHEN GROUP DISPLAY IS EXPANDED        
//        $groupForm->addElement('textarea', 'newGroupDesc', get_string('labelgroupdesc', 'block_moodletxt'));
//        $groupForm->setType('newGroupDesc', PARAM_TEXT);
                
        
        // Buttons
        
        $buttonArray=array();
        $buttonArray[] = &$groupForm->createElement('submit', 'newGroupSave', get_string('buttongroupadd', 'block_moodletxt'));
        $groupForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
//        $contactForm->closeHeaderBefore('buttonar');
                
    }

    /**
     * Validation routine for group addition form
     * @param array $formData Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2012092401
     * @since 2012092201
     */
    public function validation($formData, $files = null) {
        
        $err = array();
        
        $formData = $this->cleanupFormData($formData);
        
        if ($formData['newGroupName'] == '') {
            
            $err['newGroupName'] = get_string('errornogroupname', 'block_moodletxt');
            
        } else {
            
            foreach($this->_customdata['existingGroups'] as $existingGroup)
                if ($formData['newGroupName'] == $existingGroup)
                    $err['newGroupName'] = get_string('errorgroupnameexists', 'block_moodletxt');
            
        }
        
        return $err;
        
    }
    
    /**
     * Cleans form data for use
     * @param object|array $formData Raw data
     * @return object|array Cleaned data
     * @version 2012092401
     * @since 2012092201
     */
    public function cleanupFormData($formData) {
        
        if (is_object($formData)) {

            $formData->newGroupName     = trim($formData->newGroupName);
//            $formData->newGroupDesc     = trim($formData->newGroupDesc);
            
        } else {

            $formData['newGroupName']   = trim($formData['newGroupName']);
//            $formData['newGroupDesc']   = trim($formData['newGroupDesc']);
                
        }
            
        return $formData;
        
    }    
    
    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092401
     * @since 2012092201
     */
    public function clearSubmittedValues() {
        
        $this->get_element('newGroupName')->setValue('');
//        $this->get_element('newGroupDesc')->setValue('');
        parent::clearSubmittedValues();
        
    }
    
}

?>