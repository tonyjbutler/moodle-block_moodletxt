<?php

/**
 * File container for MoodletxtAddressbookControlForm class
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
 * @since 2012091101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAbstractForm.php');

/**
 * Addressbook controls - allows user to delete contacts
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092101
 * @since 2012091101
 */
class MoodletxtAddressbookControlForm extends MoodletxtAbstractForm {
    
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
     * @version 2012091201
     * @since 2012091101
     */
    public function definition() {
        
        $inboxForm =& $this->_form;

        $inboxForm->addElement('hidden', 'course');
        $inboxForm->setType('course', PARAM_INT);
        
        $inboxForm->addElement('hidden', 'instance');
        $inboxForm->setType('instance', PARAM_INT);
        
        $inboxForm->addElement('hidden', 'addressbook');
        $inboxForm->setType('addressbook', PARAM_INT);

        $inboxForm->addElement('hidden', 'deleteType');
        $inboxForm->setType('deleteType', PARAM_ALPHA);
        
        $inboxForm->addElement('hidden', 'deleteContactIds');
        $inboxForm->setType('deleteContactIds', PARAM_NOTAGS);

        $inboxForm->addElement('html', (html_writer::tag('div', 
            html_writer::tag('p',
                html_writer::empty_tag('img', array('src' => 'pix/select_arrow.png', 'width' => 38, 'height' => 22, 'alt' => get_string('altarrow', 'block_moodletxt'))) .
                html_writer::link('#', get_string('buttoncheckall', 'block_moodletxt'), array('id' => 'checkAllBoxes')) . ' | ' .
                html_writer::link('#', get_string('buttonuncheckall', 'block_moodletxt'), array('id' => 'uncheckAllBoxes')) . 
                html_writer::tag('span', 
                    get_string('labelwithselected', 'block_moodletxt') . 
                    html_writer::link('#', get_string('buttondeleteselected', 'block_moodletxt'), array('id' => 'deleteSelected')) . ' | ' . 
                    html_writer::link('#', get_string('buttondeletenotselected', 'block_moodletxt'), array('id' => 'deleteExceptSelected')),
                    array('style' => 'margin-left:3em;')
                ), array('style' => 'margin-left:2em;')
            )
        )));
        
    }

    /**
     * Validation routine for account form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2012091101
     * @since 2012091101
     */
    public function validation($formdata, $files = null) {
        
        $err = array();
        
        // Populated and submitted via JS - no validation to run

        return $err;
        
    }
    
}

?>