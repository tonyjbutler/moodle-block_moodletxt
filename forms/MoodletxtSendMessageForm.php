<?php

/**
 * File container for MoodletxtSendMessageForm class
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
 * @see MoodletxtSendMessageForm
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052101
 * @since 2011101702
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/quickforms/MoodleQuickFormWithSlides.php');

/**
 * Form class for message composition form
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052101
 * @since 2011101702
 */
class MoodletxtSendMessageForm extends MoodletxtAbstractForm {

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
     * Overriding the normal constructor in order to use
     * our extended version of MoodleQuickForm, which
     * will enable this form to use slides
     * @param string $action Form destination
     * @param array $customdata Custom data for pre-populating form fields
     * @param string $method Method of form submission - GET or POST
     * @param string $target Form's target
     * @param array $attributes HTML form attributes
     * @param boolean $editable Whether the form can be edited
     * @version 2013050801
     * @since 2011101901
     */
    public function __construct($action = null, array $customdata = array(), 
            $method = 'post', $target = '', array $attributes = array(), $editable = true) {
        if (empty($action)){
            $action = strip_querystring(qualified_me());
        }

        $this->_formname = get_class($this); // '_form' suffix kept in order to prevent collisions of form id and other element
        $this->_customdata = $customdata;
        $this->_form = new MoodleQuickFormWithSlides($this->_formname, $method, $action, $target, $attributes);
        if (!$editable){
            $this->_form->hardFreeze();
        }

        $this->definition();

        $this->_form->addElement('hidden', 'sesskey', null); // automatic sesskey protection
        $this->_form->setType('sesskey', PARAM_RAW);
        $this->_form->setDefault('sesskey', sesskey());
        $this->_form->addElement('hidden', '_qf__'.$this->_formname, null);   // form submission marker
        $this->_form->setType('_qf__'.$this->_formname, PARAM_RAW);
        $this->_form->setDefault('_qf__'.$this->_formname, 1);
        $this->_form->_setDefaultRuleMessages();
        
        // Moodle 2.5 and above have auto-collapsing forms. Not appropriate here!
        // (Using method_exists() so that 2.0-2.4 and 2.5+ can share the same code base)
        if (method_exists($this->_form, 'setDisableShortforms'))
            $this->_form->setDisableShortforms(true);

        // we have to know all input types before processing submission ;-)
        $this->_process_submission($method);
        
    }
    
    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2013052101
     * @since 2011101702
     */
    public function definition() {
        global $CFG;

        $sendForm =& $this->_form;
        
        // Register custom element types
        $sendForm->registerElementType(
                'slide',
                $CFG->dirroot . '/blocks/moodletxt/forms/elements/QuickFormSlide.php',
                'QuickFormSlide'
        );
        
        $sendForm->registerElementType(
                'advmultiselect',
                $CFG->dirroot . '/blocks/moodletxt/forms/elements/QuickFormRecipientMultiselect.php',
                'QuickFormRecipientMultiselect'
        );
        
        
        // Hidden variables for passback data
        $sendForm->addElement('hidden', 'course', $this->_customdata['course']);
        $sendForm->setType('course', PARAM_INT);
        
        $sendForm->addElement('hidden', 'instance', $this->_customdata['instance']);
        $sendForm->setType('instance', PARAM_INT);
        
        
        
        $sendForm->addElement('slide', 'slide1', get_string('headerselectrecipients', 'block_moodletxt'));
        
        $individuals =& $sendForm->createElement(
                'advmultiselect',
                'recipients',
                array(
                    '',
                    get_string('labelpotentialrecipients', 'block_moodletxt'),
                    get_string('labelselectedrecipients', 'block_moodletxt')
                ),
                $this->_customdata['potentialRecipients'],
                array(
                    'class' => 'mdltxtMultiselect',
                    'style' => '' // Prevent inline styling, it is the work of the devil
                ),
                SORT_ASC
        );

        // Tell multi-select to use our own template
        // Makes advanced buttons available
        $individuals->setElementTemplate(self::$ADVMULTISELECT_TEMPLATE);
        
        $sendForm->addElement($individuals);
        
        $recipientTypeSelectors = array();
        $recipientTypeSelectors[] = &$sendForm->createElement('button', 'showUsers', 'Users', array('class' => 'recipientTypeSelector'));
        $recipientTypeSelectors[] = &$sendForm->createElement('button', 'showUserGroups', 'User Groups', array('class' => 'recipientTypeSelector'));
        
        $recipientTypeSelectors[] = &$sendForm->createElement('button', 'showAddressbookContacts', 'Addressbook Contacts', array('class' => 'recipientTypeSelector'));
        $recipientTypeSelectors[] = &$sendForm->createElement('button', 'showAddressbookGroups', 'Addressbook Groups', array('class' => 'recipientTypeSelector'));
        $sendForm->addGroup($recipientTypeSelectors, 'recipientTypeSelectors', get_string('labelrecipienttypeselect', 'block_moodletxt'), array(' '), false);
        
        $sendForm->addElement('header', 'additionalContact', 'Add Additional Contact');
        
        $sendForm->addElement('text', 'addfirstname', get_string('labeladditionalfirstname', 'block_moodletxt'), array('size' => '15', 'maxlength' => '30'));
        $sendForm->setType('addfirstname', PARAM_ALPHANUMEXT);
            
        $sendForm->addElement('text', 'addlastname', get_string('labeladditionallastname', 'block_moodletxt'), array('size' => '15', 'maxlength' => '30'));
        $sendForm->setType('addlastname', PARAM_ALPHANUMEXT);
        
        $sendForm->addElement('text', 'addnumber', get_string('labeladditionalnumber', 'block_moodletxt'), array('size' => '20', 'maxlength' => '20'));
        $sendForm->setType('addnumber', PARAM_ALPHANUMEXT);
        
        $sendForm->addElement('button', 'addAdditionalContact', 'Add Contact');

        
        
        $sendForm->addElement('slide', 'slide2', get_string('headercomposemessagebody', 'block_moodletxt'));
               
        $sendForm->addElement('select', 'messageTemplates', get_string('labeltemplates', 'block_moodletxt'), 
                array_merge(
                    array(0 => get_string('optionchoosetemplate', 'block_moodletxt')),
                    $this->_customdata['existingTemplates']
                ));
        $sendForm->setType('messageTemplates', PARAM_INT);
        
        $cpm = html_writer::tag('span', '160', array('id' => 'charsPerMessage'));
        $sendForm->addElement('static', 'cpm', get_string('labelcharspermessage', 'block_moodletxt'), $cpm);
        
        $sendForm->addElement('text', 'charsUsed', get_string('labelcharsused', 'block_moodletxt'), array('size' => 10));
        $sendForm->setType('charsUsed', PARAM_INT);
        
        $sendForm->addElement('textarea', 'messageText', get_string('labelmessagetext', 'block_moodletxt'), array('rows' => 10, 'cols' => 70));
        $sendForm->setType('messageText', PARAM_TEXT);
        
        $charsetWarning = $sendForm->createElement('static', 'unicodeWarning', '', html_writer::tag('span', get_string('warnunicode', 'block_moodletxt'), array('class' => 'unicodeWarning hidden')));
        $sendForm->addElement($charsetWarning);
        
        $tagButtonArray = array();
        $tagButtonArray[] = &$sendForm->createElement('button', 'tagFirstName', get_string('buttontagfirstname', 'block_moodletxt'));
        $tagButtonArray[] = &$sendForm->createElement('button', 'tagLastName', get_string('buttontaglastname', 'block_moodletxt'));
        $tagButtonArray[] = &$sendForm->createElement('button', 'tagFullName', get_string('buttontagfullname', 'block_moodletxt'));
        $sendForm->addGroup($tagButtonArray, 'tagButtons', get_string('labelmergetags', 'block_moodletxt'), array(''), false);
        
        $sendForm->addElement('checkbox', 'addSig', get_string('labeladdsignature', 'block_moodletxt'));
        $sendForm->setType('addSig', PARAM_INT);
        
        
        
        $sendForm->addElement('slide', 'slide3', get_string('headermessageoptions', 'block_moodletxt'));
        
        $sendForm->addElement('header', 'messageOptions', get_string('sendlegendunicode', 'block_moodletxt'));
        
        $sendForm->addElement('radio', 'suppressUnicode', 
                get_string('labelsuppressunicodeno', 'block_moodletxt'), 
                get_string('labelsuppressunicodenodesc', 'block_moodletxt'), 
        0);
        $sendForm->addElement('radio', 'suppressUnicode', 
                get_string('labelsuppressunicodeyes', 'block_moodletxt'), 
                get_string('labelsuppressunicodeyesdesc', 'block_moodletxt'), 
        1);
        $sendForm->setType('suppressUnicode', PARAM_INT);
        
        $sendForm->addElement('header', 'schedulingOptions', get_string('sendlegendschedule', 'block_moodletxt'));
        $sendForm->closeHeaderBefore('schedulingOptions');
        
        $sendForm->addElement('radio', 'schedule', get_string('labelschedulenow', 'block_moodletxt'), '', 'now');
        $sendForm->addElement('radio', 'schedule', get_string('labelschedulelater', 'block_moodletxt'), '', 'schedule');
        $sendForm->setType('schedule', PARAM_ALPHA);
        
        $dateSelector = $sendForm->createElement('date', 'scheduletime', get_string('labelscheduletime', 'block_moodletxt'), 
                array(
                    'format' => 'dMY Hi',
                    'minYear' => date('Y'),
                    'maxYear' => date('Y', strtotime('+10 years'))
                ));
        
        $dateSelector->setValue(userdate(time()));
        $sendForm->addElement($dateSelector);
            
        $sendForm->addElement('select', 'txttoolsaccount', get_string('labeltxttoolsaccount', 'block_moodletxt'), $this->_customdata['txttoolsAccounts'], array('size' => 1));
        $sendForm->setType('txttoolsaccount', PARAM_INT);
        
        
        
        $sendForm->addElement('slide', 'slide4', get_string('headersendreview', 'block_moodletxt'));
        
        $sendForm->addElement('select', 'confirmRecipients', get_string('labelrecipients', 'block_moodletxt'), array(), array('size' => 5, 'multiple' => 'multiple', 'disabled' => 'disabled'));
        $sendForm->setType('confirmRecipients', PARAM_INT);
        
        $sendForm->addElement('text', 'confirmCharsUsed', get_string('labelcharsused', 'block_moodletxt'), array('size' => 10));
        $sendForm->setType('confirmCharsUsed', PARAM_INT);
        
        $sendForm->addElement('textarea', 'confirmMessage', get_string('labelmessagetext', 'block_moodletxt'), array('rows' => 10, 'cols' => 70, 'disabled' => 'disabled'));
        $sendForm->setType('confirmMessage', PARAM_TEXT);
        
        $charsetWarning = $sendForm->createElement('static', 'unicodeWarning2', '', html_writer::tag('span', get_string('warnunicode', 'block_moodletxt'), array('class' => 'unicodeWarning hidden')));
        $sendForm->addElement($charsetWarning);
        
        $suppressedWarning = $sendForm->createElement('static', 'unicodeSuppressedWarning', '', html_writer::tag('span', get_string('warnunicodesuppressed', 'block_moodletxt'), array('class' => 'unicodeSuppressedWarning hidden')));
        $sendForm->addElement($suppressedWarning);
        
        $sendForm->addElement('submit', 'sendMessage', get_string('buttonsendmessage', 'block_moodletxt'));
        
        // Defaults for radio buttons
        $sendForm->setDefaults(array('suppressUnicode' => 0, 'schedule' => 'now'));
        
    }

    /**
     * Performs validation on the submitted form data
     * @param array $formdata Submitted data
     * @param array $files Uploaded files
     * @return array Set of any errors found
     * @version 2012100401
     * @since 2011101702
     */
    public function validation($formdata, $files = null) {
        
        $err = array();    
        $formdata = $this->cleanFormData($formdata);
        
        
        if (! isset($formdata['recipients']) || $formdata['recipients'] == '')
            $err['recipients'] = get_string('errornorecipientsselected', 'block_moodletxt');
        
        else if (! is_array($formdata['recipients']))
            $formdata['recipients'] = array($formdata['recipients']);
        
        if ($formdata['messageText'] == '')
            $err['messageText'] = get_string('errornomessage', 'block_moodletxt');
        
        if ($formdata['schedule'] != 'now' && $formdata['schedule'] != 'schedule') {
            $err['schedule'] = get_string('errornoscheduling', 'block_moodletxt');
        
        } else if ($formdata['schedule'] == 'schedule') {
            
            $scheduleUTCTime = usertime(gmmktime(
                $formdata['scheduletime']['H'], 
                $formdata['scheduletime']['i'], 
                0, 
                $formdata['scheduletime']['M'], 
                $formdata['scheduletime']['d'], 
                $formdata['scheduletime']['Y']
            ));
            
            if ($scheduleUTCTime < time())
                $err['scheduletime'] = get_string('errordocbrown', 'block_moodletxt');
            
        }
        
        return $err;
        
    }
    
    /**
     * Cleans up form data prior to validation/processing
     * @param array|object $formdata Form data object
     * @return array|object Cleaned form data object
     * @version 2012031401
     * @since 2012030901
     */
    public function cleanFormData($formdata) {
        
        if (is_object($formdata)) {
        
            $formdata->course           = (int)    trim($formdata->course);
            $formdata->instance         = (int)    trim($formdata->instance);
            $formdata->messageText      = (string) trim($formdata->messageText);
            $formdata->txttoolsaccount  = (int)    trim($formdata->txttoolsaccount);
            
            if ($formdata->suppressUnicode != 1) $formdata->suppressUnicode = 0;
            
        } else {

            $formdata['course']         = (int)    trim($formdata['course']);
            $formdata['instance']       = (int)    trim($formdata['instance']);
            $formdata['messageText']    = (string) trim($formdata['messageText']);
            $formdata['txttoolsaccount']= (int)    trim($formdata['txttoolsaccount']);

            if ($formdata['suppressUnicode'] != 1) $formdata['suppressUnicode'] = 0;
            
        }
        
        return $formdata;
        
    }
    
}

?>