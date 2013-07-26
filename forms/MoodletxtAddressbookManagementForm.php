<?php

/**
 * File container for MoodletxtAddressbookManagementForm class
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
 * @version 2013050801
 * @since 2012071701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbook.php');

/**
 * Form to create or edit top-level addressbook entries
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013050801
 * @since 2012071701
 */
class MoodletxtAddressbookManagementForm extends MoodletxtAbstractForm {

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
     * @version 2012090401
     * @since 2012071701
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
     * @version 2013050801
     * @since 2012071701
     */
    public function definition() {
        
        global $CFG;
                
        $addressbookForm =& $this->_form;
                
        $addressbookForm->addElement('hidden', 'course');
        $addressbookForm->setType('course', PARAM_INT);
        
        $addressbookForm->addElement('hidden', 'instance');
        $addressbookForm->setType('instance', PARAM_INT);
        
        $addressbookForm->addElement('header', 'addAddressbook', get_string('headeraddressbookadd', 'block_moodletxt'));
        
        $addressbookForm->addElement('text', 'newAddressbookName', get_string('labeladdressbookname', 'block_moodletxt'), array('maxlength' => 50));
        $addressbookForm->setType('newAddressbookName', PARAM_TEXT);
        
        $addressbookForm->addElement('select', 'newAddressbookType', get_string('labeladdressbooktype', 'block_moodletxt'), $this->addressbookTypes);
        $addressbookForm->setType('newAddressbookType', PARAM_ALPHA);
        $addressbookForm->setDefault('newAddressbookType', MoodletxtAddressbook::$ADDRESSBOOK_TYPE_PRIVATE);

        $buttonArray=array();
        $buttonArray[] = &$addressbookForm->createElement('submit', 'submitButton', get_string('buttonadd', 'block_moodletxt'));
        $addressbookForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
        
        $addressbookForm->addElement('header', 'deleteAddressbook', get_string('headeraddressbookdelete', 'block_moodletxt'));
        $addressbookForm->closeHeaderBefore('deleteAddressbook');

        $addressbookForm->addElement('select', 'existingAddressbook', get_string('labeladdressbookdelete', 'block_moodletxt'), $this->_customdata['existingAddressbooks']);
        $addressbookForm->setType('existingAddressbook', PARAM_INT);

        $addressbookForm->addElement('radio', 'deleteExistingContacts', get_string('labelcontactsdelete', 'block_moodletxt'), get_string('desccontactsdelete', 'block_moodletxt'), 'delete');
        $addressbookForm->addElement('radio', 'deleteExistingContacts', get_string('labelcontactsmerge', 'block_moodletxt'), get_string('desccontactsmerge', 'block_moodletxt'), 'merge');
        $addressbookForm->setType('deleteExistingContacts', PARAM_ALPHA);
        $addressbookForm->setDefault('deleteExistingContacts', 'delete');

        $addressbookForm->addElement('select', 'mergeAddressbook', null, $this->_customdata['existingAddressbooks']);
        $addressbookForm->setType('mergeAddressbook', PARAM_INT);
        
        // Buttons
        
        $buttonArray2=array();
        $buttonArray2[] = &$addressbookForm->createElement('submit', 'submitButton', get_string('buttondeleteormerge', 'block_moodletxt'));
        $addressbookForm->addGroup($buttonArray2, 'buttonar2', '', array(' '), false);
        
    }
    
    /**
     * Validation routine for addressbook form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return array(string => string) Array of errors, if any found
     * @version 2012102901
     * @since 2012071701
     */
    public function validation($formData, $files = null) {
        
        $err = array();
                    
        $formData = $this->cleanupFormData($formData);
        
        // If a new addressbook name has been entered, we check for a valid type
        if ($formData['newAddressbookName'] != '') {
            
            if (strlen($formData['newAddressbookName']) > 50) {
                $err['newAddressbookName'] = get_string('errorbooknamelength', 'block_moodletxt');
                
            } else if (! in_array($formData['newAddressbookType'], array_keys($this->addressbookTypes))) {
                $err['newAddressbookType'] = get_string('errorinvalidbooktype', 'block_moodletxt');
            
            // Check against the collection of existing addressbooks we already
            // pulled from the DB to make sure the name is not a duplicate
            } else {
                
                foreach($this->_customdata['existingAddressbooks'] as $addressbookName)
                    if ($formData['newAddressbookName'] == $addressbookName)
                        $err['newAddressbookName'] = get_string('errorbooknameexists', 'block_moodletxt');
            }
        
        }
        
        // If the user has chosen an addressbook to kill, check parameters
        if ($formData['existingAddressbook'] > 0) {
            
            if ($formData['deleteExistingContacts'] != 'merge' && 
                $formData['deleteExistingContacts'] != 'delete')
                $err['deleteExistingContacts'] = get_string('errorinvalidbookmerge', 'block_moodletxt');
            
            if ($formData['deleteExistingContacts'] == 'merge' &&
                $formData['mergeAddressbook'] <= 0)
                $err['mergeAddressbook'] = get_string('errordestbooknull', 'block_moodletxt');
            
            else if ($formData['deleteExistingContacts'] == 'merge' &&
                $formData['existingAddressbook'] == $formData['mergeAddressbook'])
                $err['mergeAddressbook'] = get_string('errordestbooksame', 'block_moodletxt');
            
        }
            
        return $err;
        
    }
    
    /**
     * Cleans form data for use
     * @param object|array $formData Raw data
     * @return object|array Cleaned data
     * @version 2012090301
     * @since 2012072201
     */
    public function cleanupFormData($formData) {
        
        if (is_object($formData)) {

            $formData->newAddressbookName       = strip_tags(trim($formData->newAddressbookName));
            $formData->newAddressbookType       = trim(strtolower($formData->newAddressbookType));
            $formData->existingAddressbook      = (int) trim($formData->existingAddressbook);
            $formData->deleteExistingContacts   = trim(strtolower($formData->deleteExistingContacts));
            $formData->mergeAddressbook         = (int) trim($formData->mergeAddressbook);
            
        } else {
            
            $formData['newAddressbookName']     = strip_tags(trim($formData['newAddressbookName']));
            $formData['newAddressbookType']     = trim(strtolower($formData['newAddressbookType']));
            $formData['existingAddressbook']    = (int) trim($formData['existingAddressbook']);
            $formData['deleteExistingContacts'] = trim(strtolower($formData['deleteExistingContacts']));
            $formData['mergeAddressbook']       = (int) trim($formData['mergeAddressbook']);
                        
        }
            
        return $formData;
        
    }
    
    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092101
     * @since 2012090301
     */
    public function clearSubmittedValues() {
        $this->get_element('newAddressbookName')->setValue('');
        parent::clearSubmittedValues();
    }

}

?>