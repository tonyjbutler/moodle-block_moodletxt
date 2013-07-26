<?php

/**
 * File container for MoodletxtContactAddForm class
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
 * @version 2013052101
 * @since 2012091201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');

/**
 * Addressbook controls - allows user to delete contacts
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052101
 * @since 2012091201
 */
class MoodletxtContactAddForm extends MoodletxtAbstractForm {
    
    /**
     * HTML template used when rendering the advmultiselect
     * or "recipients" lists.
     * @var string
     */
    private static $ADVMULTISELECT_TEMPLATE = '
{javascript}
<div {class} style="width:100%;text-align:center;">
<div style="width:40%;float:left;">
    <h3>{label_2}</h3>
    {unselected}
</div>
<div style="width:20%;float:left;">
    {add}<br /><br />
    {remove}<br />
</div>
<div style="width:40%;float:left;">
    <h3>{label_3}</h3>
    {selected}
</div>
<div class="clearfix"></div>
</div>';
    
    /**
     * Initialises the field types used
     * within the addressbook form, then sets up
     * the form as usual
     * @param string $action Form destination
     * @param array $customdata Custom data for pre-populating form fields
     * @param string $method Method of form submission - GET or POST
     * @param string $target Form's target
     * @param array $attributes HTML form attributes
     * @param boolean $editable Whether the form can be edited
     * @version 2012091201
     * @since 2012091201
     */
    public function __construct($action = null, $customdata = null,
            $method = 'post', $target = '', $attributes = null, $editable = true) {
        
        parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
    }
    
    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2013052101
     * @since 2012091201
     */
    public function definition() {
        
        global $CFG;
        
        $contactForm =& $this->_form;

        $contactForm->registerElementType(
                'advmultiselect',
                $CFG->dirroot . '/blocks/moodletxt/forms/elements/lib/HTML_QuickForm_advmultiselect.php',
                'HTML_QuickForm_advmultiselect'
        );
        
        $contactForm->addElement('hidden', 'course');
        $contactForm->setType('course', PARAM_INT);
        
        $contactForm->addElement('hidden', 'instance');
        $contactForm->setType('instance', PARAM_INT);
        
        $contactForm->addElement('hidden', 'addressbook');
        $contactForm->setType('addressbook', PARAM_INT);
        
        // Contact details section
        $contactForm->addElement('header', 'contactInfo', get_string('headercontactdetails', 'block_moodletxt'));

        $contactForm->addElement('text', 'firstName', get_string('labelfirstname', 'block_moodletxt'), array('maxlength' => 50));
        $contactForm->setType('firstName', PARAM_ALPHAEXT);
        
        $contactForm->addElement('text', 'lastName', get_string('labellastname', 'block_moodletxt'), array('maxlength' => 50));
        $contactForm->setType('lastName', PARAM_ALPHAEXT);
        
        $contactForm->addElement('text', 'company', get_string('labelcompanyname', 'block_moodletxt'), array('maxlength' => 100));
        $contactForm->setType('company', PARAM_NOTAGS);
        
        $contactForm->addElement('text', 'phoneNumber', get_string('labelphonenumber', 'block_moodletxt'), array('maxlength' => 21));
        $contactForm->setType('phoneNumber', PARAM_NOTAGS);

        // Group membership section
        $contactForm->addElement('header', 'groupMembership', get_string('headergroupmembership', 'block_moodletxt'));
        $contactForm->closeHeaderBefore('groupMembership');
        
                $groupSelector =& $contactForm->createElement(
                'advmultiselect',
                'groups',
                array(
                    '',
                    get_string('labelpotentialgroups', 'block_moodletxt'),
                    get_string('labelselectedgroups', 'block_moodletxt')
                ),
                $this->_customdata['potentialGroups'],
                array(
                    'class' => 'mdltxtMultiselect',
                    'style' => '' // Prevent inline styling, it is the work of the devil
                ),
                SORT_ASC
        );

        // Tell multi-select to use our own template
        // Makes advanced buttons available
        $groupSelector->setElementTemplate(self::$ADVMULTISELECT_TEMPLATE);
        
        $contactForm->addElement($groupSelector);
        
        // Buttons
        
        $buttonArray=array();
        $buttonArray[] = &$contactForm->createElement('submit', 'submitButton', get_string('buttoncontactaddreturn', 'block_moodletxt'));
        $buttonArray[] = &$contactForm->createElement('submit', 'submitButton', get_string('buttoncontactadd', 'block_moodletxt'));
        $contactForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
        $contactForm->closeHeaderBefore('buttonar');
                
    }

    /**
     * Validation routine for account form
     * @param array $formData Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2012091201
     * @since 2012091201
     */
    public function validation($formData, $files = null) {
        
        $err = array();
        
        $formData = $this->cleanupFormData($formData);
        
        if ($formData['lastName'] == '' && $formData['company'] == '') {
            
            $err['lastName'] = get_string('errornonameorcompany', 'block_moodletxt');
            $err['company'] = get_string('errornonameorcompany', 'block_moodletxt');
            
        }
        
        if ($formData['phoneNumber'] == '')    
            $err['phoneNumber'] = get_string('errornonumber', 'block_moodletxt');
            
        else if (! MoodletxtPhoneNumber::validatePhoneNumber($formData['phoneNumber']))
            $err['phoneNumber'] = get_string('errorinvalidnumber', 'block_moodletxt');
            

        return $err;
        
    }
    
    /**
     * Cleans form data for use
     * @param object|array $formData Raw data
     * @return object|array Cleaned data
     * @version 2012101602
     * @since 2012091201
     */
    public function cleanupFormData($formData) {
        
        if (is_object($formData)) {

            $formData->firstName        = trim($formData->firstName);
            $formData->lastName         = trim($formData->lastName);
            $formData->company          = trim($formData->company);
            $formData->phoneNumber      = trim($formData->phoneNumber);
            
            if (! isset($formData->groups) || $formData->groups == '')
                $formData->groups = array();
            
            else if (! is_array($formData->groups))
                $formData->groups = array($formData->groups);
            
            foreach($formData->groups as $key => $value)
                if ($value == '')
                    unset($formData->groups[$key]);
            
        } else {

            if (! isset($formData['groups']) || $formData['groups'] == '')
                $formData['groups'] = array();
            
            else if (! is_array($formData['groups']))
                $formData['groups'] = array($formData['groups']);
            
            $formData['firstName']      = trim($formData['firstName']);
            $formData['lastName']       = trim($formData['lastName']);
            $formData['company']        = trim($formData['company']);
            $formData['phoneNumber']    = trim($formData['phoneNumber']);
                        
            foreach($formData['groups'] as $key => $value)
                if ($value == '')
                    unset($formData['groups'][$key]);
                
        }
            
        return $formData;
        
    }    
    
    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092101
     * @since 2012092101
     */
    public function clearSubmittedValues() {
        $this->get_element('firstName')->setValue('');
        $this->get_element('lastName')->setValue('');
        $this->get_element('company')->setValue('');
        $this->get_element('phoneNumber')->setValue('');
        $this->get_element('groups')->setValue('');
        parent::clearSubmittedValues();
    }
    
}

?>