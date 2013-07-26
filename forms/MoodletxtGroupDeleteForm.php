<?php

/**
 * File container for MoodletxtGroupDeleteForm class
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
 * @version 2012092501
 * @since 2012092201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');

/**
 * Form allows the user to delete addressbook groups
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092501
 * @since 2012092201
 */
class MoodletxtGroupDeleteForm extends MoodletxtAbstractForm {

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
     * @version 2012092301
     * @since 2012092201
     */
    public function definition() {
        
        global $CFG;
                
        $addressbookForm =& $this->_form;
                
        $addressbookForm->addElement('hidden', 'course');
        $addressbookForm->setType('course', PARAM_INT);
        
        $addressbookForm->addElement('hidden', 'instance');
        $addressbookForm->setType('instance', PARAM_INT);
        
        $addressbookForm->addElement('hidden', 'addressbook');
        $addressbookForm->setType('addressbook', PARAM_INT);
        
        $addressbookForm->addElement('header', 'deleteGroup', get_string('headergroupdelete', 'block_moodletxt'));
        
        $addressbookForm->addElement('select', 'groupToDelete', get_string('labelgroupsselect', 'block_moodletxt'), $this->_customdata['existingGroups']);
        $addressbookForm->setType('groupToDelete', PARAM_INT);

        $addressbookForm->addElement('radio', 'deleteGroupAction', get_string('labelgroupdelleavecontacts', 'block_moodletxt'), 
                get_string('descgroupdelleavecontacts', 'block_moodletxt'), 'preserve');
        
        $addressbookForm->addElement('radio', 'deleteGroupAction', get_string('labelgroupdelnukecontacts', 'block_moodletxt'), 
                get_string('descgroupdelnukecontacts', 'block_moodletxt'), 'delete');
        
        $addressbookForm->addElement('radio', 'deleteGroupAction', get_string('labelgroupdelmerge', 'block_moodletxt'), 
                get_string('descgroupdelmerge', 'block_moodletxt'), 'merge');
        
        $addressbookForm->setType('deleteGroupAction', PARAM_ALPHA);
        $addressbookForm->setDefault('deleteGroupAction', 'preserve');

        $addressbookForm->addElement('select', 'groupToMerge', null, $this->_customdata['existingGroups']);
        $addressbookForm->setType('groupToMerge', PARAM_INT);
        
        // Buttons
        
        $buttonArray = array();
        $buttonArray[] = &$addressbookForm->createElement('submit', 'deleteGroupSave', get_string('buttondeleteormerge', 'block_moodletxt'));
        $addressbookForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
        $addressbookForm->closeHeaderBefore('buttonar');
        
    }
    
    /**
     * Validation routine for group deletion form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return array(string => string) Array of errors, if any found
     * @version 2012092501
     * @since 2012092201
     */
    public function validation($formData, $files = null) {
        
        $err = array();
                    
        $formData = $this->cleanupFormData($formData);
                
        if ($formData['deleteGroupAction'] != 'merge' && 
            $formData['deleteGroupAction'] != 'delete' &&
            $formData['deleteGroupAction'] != 'preserve')
            $err['deleteGroupAction'] = get_string('errorinvalidgroupchoice', 'block_moodletxt');

        if ($formData['deleteGroupAction'] == 'merge' &&
            $formData['groupToDelete'] == $formData['groupToMerge'])
            $err['groupToMerge'] = get_string('errordestgroupsame', 'block_moodletxt');
            
        return $err;
        
    }
    
    /**
     * Cleans form data for use
     * @param object|array $formData Raw data
     * @return object|array Cleaned data
     * @version 2012092301
     * @since 2012092201
     */
    public function cleanupFormData($formData) {
        
        if (is_object($formData)) {

            $formData->deleteGroupAction    = trim($formData->deleteGroupAction);
            
        } else {
            
            $formData['deleteGroupAction']  = trim($formData['deleteGroupAction']);
                        
        }
            
        return $formData;
        
    }
    
    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092301
     * @since 2012092201
     */
    public function clearSubmittedValues() {
        $this->get_element('groupToDelete')->setValue('');
        $this->get_element('deleteGroupAction')->setValue('preserve');
        $this->get_element('groupToMerge')->setValue('');
        parent::clearSubmittedValues();
    }

}

?>