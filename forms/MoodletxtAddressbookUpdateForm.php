<?php

/**
 * File container for MoodletxtAddressbookUpdateForm class
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
 * @since 2012091001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbook.php');

/**
 * Form for inline-editing an addressbook's title and publicity
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092101
 * @since 2012091001
 */
class MoodletxtAddressbookUpdateForm extends MoodletxtAbstractForm {

    /**
     * Holds the set of addressbook type values that
     * are displayed and considered valid
     * @var array
     */
    private $addressbookTypes;
    
    /**
     * Initialises the field types used
     * within the addressbook form, then sets up
     * the form as usual
     * @param boolean $globalAddressbooks Whether the user can create global addressbooks
     * @param string $action Form destination
     * @param array $customdata Custom data for pre-populating form fields
     * @param string $method Method of form submission - GET or POST
     * @param string $target Form's target
     * @param array $attributes HTML form attributes
     * @param boolean $editable Whether the form can be edited
     * @version 2012091001
     * @since 2012091001
     */
    public function __construct($globalAddressbooks, $action = null, $customdata = null,
            $method = 'post', $target = '', $attributes = null, $editable = true) {
        
        $this->addressbookTypes = array(
            MoodletxtAddressbook::$ADDRESSBOOK_TYPE_PRIVATE => get_string('optionaddressbookprivate', 'block_moodletxt')
        );
        
        if ($globalAddressbooks)
            $this->addressbookTypes[MoodletxtAddressbook::$ADDRESSBOOK_TYPE_GLOBAL] = get_string('optionaddressbookglobal', 'block_moodletxt');
        
        parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
    }
            
    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2012091001
     * @since 2012091001
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
                
        $addressbookForm->addElement('text', 'addressbookName', get_string('labeladdressbookname', 'block_moodletxt'), array('maxlength' => 50));
        $addressbookForm->setType('addressbookName', PARAM_TEXT);
        
        $addressbookForm->addElement('select', 'addressbookType', get_string('labeladdressbooktype', 'block_moodletxt'), $this->addressbookTypes);
        $addressbookForm->setType('addressbookType', PARAM_ALPHA);
        $addressbookForm->setDefault('addressbookType', MoodletxtAddressbook::$ADDRESSBOOK_TYPE_PRIVATE);

        // Buttons
        
        $buttonArray=array();
        $buttonArray[] = &$addressbookForm->createElement('submit', 'submitButton', get_string('buttonsave', 'block_moodletxt'));
        $addressbookForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
        $addressbookForm->closeHeaderBefore('buttonar');
                
    }
    
    /**
     * Validation routine for addressbook form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return array(string => string) Array of errors, if any found
     * @version 2012091001
     * @since 2012091001
     */
    public function validation($formData, $files = null) {
        
        $err = array();
                    
        $formData = $this->cleanupFormData($formData);
        
        // If a new addressbook name has been entered, we check for a valid type
        if ($formData['addressbookName'] != '') {
            
            if (strlen($formData['addressbookName']) > 50) {
                $err['addressbookName'] = get_string('errorbooknamelength', 'block_moodletxt');
                
            } else if (! in_array($formData['addressbookType'], array_keys($this->addressbookTypes))) {
                $err['addressbookType'] = get_string('errorinvalidbooktype', 'block_moodletxt');
            
            }
        
        }
        
        return $err;
        
    }
    
    /**
     * Cleans form data for use
     * @param object|array $formData Raw data
     * @return object|array Cleaned data
     * @version 2012091001
     * @since 2012091001
     */
    public function cleanupFormData($formData) {
        
        if (is_object($formData)) {

            $formData->addressbookName       = strip_tags(trim($formData->addressbookName));
            $formData->addressbookType       = trim(strtolower($formData->addressbookType));
            
        } else {

            $formData['addressbookName']     = strip_tags(trim($formData['addressbookName']));
            $formData['addressbookType']     = trim(strtolower($formData['addressbookType']));
                        
        }
            
        return $formData;
        
    }
    
    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092101
     * @since 2012091001
     */
    public function clearSubmittedValues() {
        $this->get_element('addressbookName')->setValue('');
        parent::clearSubmittedValues();
    }
    
}

?>