<?php

/**
 * File container for MoodletxtFiltersForm class
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
 * @since 2011062901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtInboundFilterDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');
require_once($CFG->dirroot . '/blocks/moodletxt/renderer.php');

/**
 * Inbound filters form. AJAX populated but traditionally validated/processed.
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092101
 * @since 2011062901
 */
class MoodletxtFiltersForm extends MoodletxtAbstractForm {

    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2012052301
     * @since 2011062901
     */
    public function definition() {
        
        global $CFG;
        
        $restrictionsForm =& $this->_form;
        
        $restrictionsForm->registerElementType(
                'selectdynamic',
                $CFG->dirroot . '/blocks/moodletxt/forms/elements/QuickFormSelectDynamic.php',
                'QuickFormSelectDynamic'
        );
        
        $restrictionsForm->registerElementType(
                'selectdynamicwithimage',
                $CFG->dirroot . '/blocks/moodletxt/forms/elements/QuickFormSelectDynamicWithImage.php',
                'QuickFormSelectDynamicWithImage'
        );
            
        $restrictionsForm->registerElementType(
                'textwithimage',
                $CFG->dirroot . '/blocks/moodletxt/forms/elements/QuickFormTextWithImage.php',
                'QuickFormTextWithImage'
        );
        
        $restrictionsForm->addElement('header', 'filtersForm', get_string('headerfilters', 'block_moodletxt'));
        
        $restrictionsForm->addElement('select', 'filterAccountList', get_string('adminlabelselectacc', 'block_moodletxt'), $this->_customdata['filterAccountList']);
        $restrictionsForm->setType('filterAccountList', PARAM_INT);

        $restrictionsForm->addElement('selectdynamicwithimage', 'existingKeywordFilterList', get_string('adminlabelfilterskeyword', 'block_moodletxt'), array(), array('class' => 'mdltxt'), new moodletxt_icon(moodletxt_icon::$ICON_ADD, get_string('altaddfilter', 'block_moodletxt'), array('id' => 'addKeywordFilter', 'class' => 'mdltxtClickableIcon')));
        $restrictionsForm->setType('existingKeywordFilterList', PARAM_INT);
        
        $restrictionsForm->addElement('textwithimage', 'newKeywordFilter', get_string('adminlabelfilterkeyword', 'block_moodletxt'), array(), new moodletxt_icon(moodletxt_icon::$ICON_DELETE, get_string('altcancelfilter', 'block_moodletxt'), array('id' => 'cancelKeywordFilter', 'class' => 'mdltxtClickableIcon')));
        $restrictionsForm->setType('newKeywordFilter', PARAM_ALPHA);
        
        $restrictionsForm->addElement('selectdynamicwithimage', 'existingPhoneNumberFilterList', get_string('adminlabelfiltersphone', 'block_moodletxt'), array(), array('class' => 'mdltxt'), new moodletxt_icon(moodletxt_icon::$ICON_ADD, get_string('altaddfilter', 'block_moodletxt'), array('id' => 'addPhoneFilter', 'class' => 'mdltxtClickableIcon')));
        $restrictionsForm->setType('existingPhoneNumberFilterList', PARAM_INT);
        
        $restrictionsForm->addElement('textwithimage', 'newPhoneNumberFilter', get_string('adminlabelfilterphone', 'block_moodletxt'), array(), new moodletxt_icon(moodletxt_icon::$ICON_DELETE, get_string('altcancelfilter', 'block_moodletxt'), array('id' => 'cancelPhoneFilter', 'class' => 'mdltxtClickableIcon')));
        $restrictionsForm->setType('newPhoneNumberFilter', PARAM_RAW);
        
        $restrictionsForm->addElement('selectdynamic', 'usersOnFilter', '', array(), array('size' => 5, 'multiple' => 'multiple', 'style' => 'min-width:100px;'));
        $restrictionsForm->setType('usersOnFilter', PARAM_INT);
        
        $restrictionsForm->addElement('text', 'textSearcher', get_string('adminlabelfilteraddusers', 'block_moodletxt'));
        $restrictionsForm->setType('textSearcher', PARAM_ALPHANUM);
        
        $restrictionsForm->addElement('button', 'removeUsersFromFilter', get_string('adminbutremovefilterusers', 'block_moodletxt'), array('disabled' => 'disabled'));
        
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
     * @return array(string => string) Array of errors, if any found
     * @version 2012081401
     * @since 2011062901
     */
    public function validation($formData, $files=null) {
        $err = array();
        
        $accountDAO = new TxttoolsAccountDAO();
        $filterDAO = new MoodletxtInboundFilterDAO();

        $formData = $this->cleanupFormData($formData);

        // Check for valid account ID
        if ($formData['filterAccountList'] <= 0 || 
            ! $accountDAO->accountIdExists($formData['filterAccountList']))
            $err['filterAccountList'] = get_string('errorfilternoaccount', 'block_moodletxt');
        
        // Clean up any potential data cockups on the user list
        if (! isset($formData['usersOnFilter']) || $formData['usersOnFilter'] == '')
            $formData['usersOnFilter'] = array();
        
        else if (! is_array($formData['usersOnFilter']))
            $formData['usersOnFilter'] = array($formData['usersOnFilter']);
            
        
        // Check that, if a new phone number filter has been entered, it is valid
        if ($formData['newPhoneNumberFilter'] != '' && ! MoodletxtPhoneNumber::validatePhoneNumber($formData['newPhoneNumberFilter']))
            $err['newPhoneNumberFilter'] = get_string('errorfilterbadphoneno', 'block_moodletxt');
        
        if ($formData['newKeywordFilter'] != '' || $formData['newPhoneNumberFilter'] != '') {
            
            // When creating a new filter, the user must have selected recipient inboxes
            if (count($formData['usersOnFilter']) == 0)
                $err['usersOnFilter'] = get_string('errorfilternousers', 'block_moodletxt');
            
            $type = ($formData['newKeywordFilter'] != '') ? MoodletxtInboundFilter::$FILTER_TYPE_KEYWORD : MoodletxtInboundFilter::$FILTER_TYPE_PHONE_NUMBER;
            $value = ($formData['newKeywordFilter'] != '') ? $formData['newKeywordFilter'] : $formData['newPhoneNumberFilter'];
            
            if ($filterDAO->filterExists($formData['filterAccountList'], $type, $value)) {
                if ($type == MoodletxtInboundFilter::$FILTER_TYPE_KEYWORD)
                    $err['newKeywordFilter'] = get_string('errorfilterexists', 'block_moodletxt');
                else
                    $err['newPhoneNumberFilter'] = get_string('errorfilterexists', 'block_moodletxt');
            }
                    
        } else if ($formData['existingKeywordFilterList'] <= 0 && $formData['existingPhoneNumberFilterList'] <= 0) { 
            $err['existingKeywordFilterList'] = get_string('errorfilternotselected', 'block_moodletxt');
        }

        return $err;
        
    }
    
    /**
     * Cleans form data for use
     * @param object|array $formData Raw data
     * @return object|array Cleaned data
     * @version 2011071501
     * @since 2011071201
     */
    public function cleanupFormData($formData) {
        
        if (is_object($formData)) {
            
            $formData->filterAccountList                = trim($formData->filterAccountList);
            $formData->existingKeywordFilterList        = trim($formData->existingKeywordFilterList);
            $formData->newKeywordFilter                 = trim($formData->newKeywordFilter);
            $formData->existingPhoneNumberFilterList    = trim($formData->existingPhoneNumberFilterList);
            $formData->newPhoneNumberFilter             = trim($formData->newPhoneNumberFilter);
            $formData->textSearcher                     = trim($formData->textSearcher);

            if (! isset($formData->usersOnFilter))
                $formData->usersOnFilter = array();
            else if (! is_array($formData->usersOnFilter))
                $formData->usersOnFilter = array($formData->usersOnFilter);
            
        } else {
            
            $formData['filterAccountList']              = trim($formData['filterAccountList']);
            $formData['existingKeywordFilterList']      = trim($formData['existingKeywordFilterList']);
            $formData['newKeywordFilter']               = trim($formData['newKeywordFilter']);
            $formData['existingPhoneNumberFilterList']  = trim($formData['existingPhoneNumberFilterList']);
            $formData['newPhoneNumberFilter']           = trim($formData['newPhoneNumberFilter']);
            $formData['textSearcher']                   = trim($formData['textSearcher']);
            
            if (! isset($formData['usersOnFilter']))
                $formData['usersOnFilter'] = array();
            else if (! is_array($formData['usersOnFilter']))
                $formData['usersOnFilter'] = array($formData['usersOnFilter']);
            
        }
            
        return $formData;
        
    }
        
}

?>