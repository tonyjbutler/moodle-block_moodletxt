<?php

/**
 * File container for MoodletxtGroupEditForm class
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
 * @since 2012092201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');

/**
 * Group management form - allows user to edit the
 * membership of an existing group
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052101
 * @since 2012092201
 */
class MoodletxtGroupEditForm extends MoodletxtAbstractForm {
    
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
     * @version 2013052101
     * @since 2012092201
     */
    public function definition() {
        
        global $CFG;
        
        $groupForm =& $this->_form;

        $groupForm->registerElementType(
                'advmultiselect',
                $CFG->dirroot . '/blocks/moodletxt/forms/elements/lib/HTML_QuickForm_advmultiselect.php',
                'HTML_QuickForm_advmultiselect'
        );
        
        $groupForm->addElement('hidden', 'course');
        $groupForm->setType('course', PARAM_INT);
        
        $groupForm->addElement('hidden', 'instance');
        $groupForm->setType('instance', PARAM_INT);
        
        $groupForm->addElement('hidden', 'addressbook');
        $groupForm->setType('addressbook', PARAM_INT);
        
        // Group selection 
        $groupForm->addElement('header', 'editGroup', get_string('headergroupedit', 'block_moodletxt'));

        $groupForm->addElement('select', 'editExistingGroup', get_string('labelgroupsselect', 'block_moodletxt'), 
            array(0 => '') + $this->_customdata['existingGroups']
        );
        
        $groupForm->setType('editExistingGroup', PARAM_INT);
        
        // Group membership
        
        $contactSelector =& $groupForm->createElement(
            'advmultiselect',
            'editGroupMembers',
            array(
                '',
                get_string('labelcontactspotential', 'block_moodletxt'),
                get_string('labelcontactsselected', 'block_moodletxt')
            ),
            $this->_customdata['potentialContacts'],
            array(
                'class' => 'mdltxtMultiselect',
                'style' => '' // Prevent inline styling, it is the work of the devil
            ),
            SORT_ASC
        );

        // Tell multi-select to use our own template
        // Makes advanced buttons available
        $contactSelector->setElementTemplate(self::$ADVMULTISELECT_TEMPLATE);
        
        $groupForm->addElement($contactSelector);
        
        // Buttons
        
        $buttonArray=array();
        $buttonArray[] = &$groupForm->createElement('submit', 'editGroupSave', get_string('buttongroupedit', 'block_moodletxt'));
        $groupForm->addGroup($buttonArray, 'buttonar', '', array(' '), false);
                
    }

    /**
     * Validation routine for group editing form
     * @param array $formData Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2012092201
     * @since 2012092201
     */
    public function validation($formData, $files = null) {
        
        $err = array();
        
        $formData = $this->cleanupFormData($formData);
        
        if ($formData['editExistingGroup'] < 1) {
            
            $err['editExistingGroup'] = get_string('errorinvalidgroupid', 'block_moodletxt');
            
        }
        
        return $err;
        
    }
    
    /**
     * Cleans form data for use
     * @param object|array $formData Raw data
     * @return object|array Cleaned data
     * @version 2012092201
     * @since 2012092201
     */
    public function cleanupFormData($formData) {
        
        if (is_object($formData)) {

            $formData->editExistingGroup = (int) trim($formData->editExistingGroup);
            
            foreach($formData->editGroupMembers as $key => $value)
                if ($value == '')
                    unset($formData->editGroupMembers[$key]);
            
        } else {

            $formData['editExistingGroup'] = (int) trim($formData['editExistingGroup']);
                        
            foreach($formData['editGroupMembers'] as $key => $value)
                if ($value == '')
                    unset($formData['editGroupMembers'][$key]);
                
        }
            
        return $formData;
        
    }    
    
    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092201
     * @since 2012092201
     */
    public function clearSubmittedValues() {
        $this->get_element('editExistingGroup')->setValue('');
        $this->get_element('editGroupMembers')->setValue('');
        parent::clearSubmittedValues();
    }
    
}

?>