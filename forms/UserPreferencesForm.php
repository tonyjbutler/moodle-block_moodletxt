<?php

/**
 * File container for UserPreferencesForm class
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
 * @version 2013052301
 * @since 2011072701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');

/**
 * New account form - takes username/password details
 * from user for any new txttools account added to the system
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011072701
 */
class UserPreferencesForm extends MoodletxtAbstractForm {

    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2013052101
     * @since 2011072701
     */
    public function definition() {
        global $CFG;

        $prefsForm =& $this->_form;

        // Moodle 2.5 and above have auto-collapsing forms. Not appropriate here!
        // (Using method_exists() so that 2.0-2.4 and 2.5+ can share the same code base)
        if (method_exists($this->_form, 'setDisableShortforms'))
            $this->_form->setDisableShortforms(true);

        // Hidden fields for processing
        
        $prefsForm->addElement('hidden', 'course', $this->_customdata['course']);
        $prefsForm->setType('course', PARAM_INT);
        
        $prefsForm->addElement('hidden', 'instance', $this->_customdata['instance']);
        $prefsForm->setType('instance', PARAM_INT);
        
        $prefsForm->addElement('hidden', 'templateToEdit', 0);
        $prefsForm->setType('templateToEdit', PARAM_INT);
        $prefsForm->setDefault('templateToEdit', 0);
        
        $prefsForm->addElement('hidden', 'templateToDelete', 0);
        $prefsForm->setType('templateToDelete', PARAM_INT);
        $prefsForm->setDefault('templateToDelete', 0);
        
        // Signature
        
        $prefsForm->addElement('header', 'headerSignature', get_string('headersignature', 'block_moodletxt'));
        
        $prefsForm->addElement('text', 'charsRemaining', get_string('labelcharsremaining', 'block_moodletxt'), array('size' => 4));
        $prefsForm->setType('charsRemaining', PARAM_INT);
        
        $prefsForm->addElement('text', 'signature', get_string('labelsignature', 'block_moodletxt'), array('size' => 35, 'maxlength' => 25));
        $prefsForm->setType('signature', PARAM_MULTILANG);

        // Existing Templates
        
        $prefsForm->addElement('header', 'headerTemplatesExisting', get_string('headertemplatesexist', 'block_moodletxt'));

        $prefsForm->addElement('select', 'existingTemplate', get_string('labeltemplatesexist', 'block_moodletxt'), $this->_customdata['existingTemplate'], array('size' => 5, 'style' => 'width:100%;'));
        $prefsForm->setType('existingTemplate', PARAM_INT);
        $prefsForm->setDefault('existingTemplate', 0);

        $templateButtonArray = array();
        $templateButtonArray[] = &$prefsForm->createElement('button', 'editTemplate', get_string('buttontemplateedit', 'block_moodletxt'));
        $templateButtonArray[] = &$prefsForm->createElement('button', 'deleteTemplate', get_string('buttontemplatedelete', 'block_moodletxt'));
        $prefsForm->addGroup($templateButtonArray, 'templateButtons', get_string('labelactions', 'block_moodletxt'), ' ', false);
        
        // New Template / Edit Box
        
        $prefsForm->addElement('header', 'headerTemplateNew', get_string('headertemplatesnew', 'block_moodletxt'));
                
        $prefsForm->addElement('text', 'charsUsed', get_string('labelcharsused', 'block_moodletxt'), array('size' => 4));
        $prefsForm->setType('charsUsed', PARAM_INT);
        
        $prefsForm->addElement('textarea', 'templateText', get_string('labeltemplatetext', 'block_moodletxt'), array('rows' => 3, 'style' => 'width:100%;'));
        
        $mergeButtonArray = array();
        $mergeButtonArray[] = &$prefsForm->createElement('button', 'firstName', get_string('buttontagfirstname', 'block_moodletxt'));
        $mergeButtonArray[] = &$prefsForm->createElement('button', 'lastName', get_string('buttontaglastname', 'block_moodletxt'));
        $mergeButtonArray[] = &$prefsForm->createElement('button', 'fullName', get_string('buttontagfullname', 'block_moodletxt'));
        $prefsForm->addGroup($mergeButtonArray, 'mergeButtons', get_string('labelmergetags', 'block_moodletxt'), ' ', false);
        
        // Inbound preferences
        $prefsForm->addElement('header', 'headerInboundPrefs', get_string('headerinboundprefs', 'block_moodletxt'));
        
        $prefsForm->addElement('advcheckbox', 'hideSources', get_string('labelhideinboundsources', 'block_moodletxt'));

        $prefsForm->addElement('select', 'liveUpdateInterval', get_string('labelinboundliveinterval', 'block_moodletxt'), 
                array(
                    '60'  => get_string('labelinterval1min',  'block_moodletxt'),
                    '120' => get_string('labelinterval2min',  'block_moodletxt'),
                    '300' => get_string('labelinterval5min',  'block_moodletxt'),
                    '600' => get_string('labelinterval10min', 'block_moodletxt'),
                    '900' => get_string('labelinterval15min', 'block_moodletxt')
                ));
        $prefsForm->setType('liveUpdateInterval', PARAM_INT);
        $prefsForm->setDefault('liveUpdateInterval', 120);
        
        // Buttons

        $buttonarray=array();
        $buttonarray[] = &$prefsForm->createElement('submit', 'submitButton', get_string('buttonsave', 'block_moodletxt'));
        $prefsForm->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $prefsForm->closeHeaderBefore('buttonar');

    }

    /**
     * Validation routine for account form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2011080101
     * @since 2011072701
     */
    public function validation($formdata, $files = null) {
        
        $err = array();
        $formdata = $this->cleanFormData($formdata);

        if (strlen($formdata['signature'])> 25)
            $err['signature'] = get_string('errorsigtoolong', 'block_moodletxt');
        
        if ($formdata['templateToEdit'] > 0 && $formdata['templateText'] == '')
            $err['templateText'] = get_string('errornotemplate', 'block_moodletxt');
        
        return $err;
        
    }
    
    /**
     * Cleans up submitted form data prior to processing
     * @param object|array $formdata Submitted form data
     * @return object|array Cleaned form data
     * @version 2011102001
     * @since 2011080101
     */
    public function cleanFormData($formdata) {
        
        // Can be object or array depending on whether the PEAR library
        // is still running the show or Moodle has taken over after POST.
        // No, I don't get the point of this either
        if (is_object($formdata)) {
            
            $formdata->course                   = (int)     trim($formdata->course);
            $formdata->instance                 = (int)     trim($formdata->instance);
            $formdata->templateToEdit           = (int)     trim($formdata->templateToEdit);
            $formdata->templateToDelete         = (int)     trim($formdata->templateToDelete);
            $formdata->signature                = (string)  trim($formdata->signature);
            $formdata->templateText             = (string)  trim($formdata->templateText);            

            $formdata->existingTemplate         = (isset($formdata->existingTemplate)) ? (int) trim($formdata->existingTemplate) : 0;            
            
        } else {
            
            $formdata['course']                 = (int)     trim($formdata['course']);
            $formdata['instance']               = (int)     trim($formdata['instance']);
            $formdata['templateToEdit']         = (int)     trim($formdata['templateToEdit']);
            $formdata['templateToDelete']       = (int)     trim($formdata['templateToDelete']);
            $formdata['signature']              = (string)  trim($formdata['signature']);
            $formdata['templateText']           = (string)  trim($formdata['templateText']);            
        
            $formdata['existingTemplate']       = (isset($formdata['existingTemplate'])) ? (int) trim($formdata['existingTemplate']) : 0;            
            
        }
        
        return $formdata;
    }
    
    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092101
     * @since 2011080401
     */
    public function clearSubmittedValues() {
        $this->get_element('templateText')->setValue('');
        parent::clearSubmittedValues();
    }

}

?>