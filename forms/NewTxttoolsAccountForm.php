<?php

/**
 * File container for NewTxttoolsAccountForm class
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
 * @version 2013070201
 * @since 2011042601
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
 * @version 2013070201
 * @since 2011042601
 */
class NewTxttoolsAccountForm extends MoodletxtAbstractForm {

    /**
     * Sets up form for display to user
     * @global object $CFG Moodle global config
     * @version 2013070201
     * @since 2011042601
     */
    public function definition() {
        global $CFG;

        $installForm =& $this->_form;

        // We need a list of users that can be default inboxes,
        // for the user to choose from for this initial account
        $defaultInboxUsers = get_users_by_capability(context_system::instance(), 'block/moodletxt:defaultinbox');
        $admins = get_admins();
        foreach ($admins as $admin) {
            $defaultInboxUsers[$admin->id] = $admin;
        }

        $defaultInboxList = array();

        foreach($defaultInboxUsers as $defaultInboxUser)
            $defaultInboxList[$defaultInboxUser->id] = MoodletxtStringHelper::formatNameForDisplay(
                $defaultInboxUser->firstname,
                $defaultInboxUser->lastname,
                $defaultInboxUser->username
            );

        // Txttools account

        $installForm->addElement('header', 'addAccount', get_string('adminlabeladdaccount', 'block_moodletxt'));

        $installForm->addElement('text', 'accountName', get_string('adminlabelaccusername', 'block_moodletxt'), array('maxlength' => 20));
        $installForm->setType('accountName', PARAM_ALPHANUMEXT);
        $installForm->addRule('accountName', get_string('errornousername', 'block_moodletxt'), 'required');

        $installForm->addElement('password', 'accountPassword1', get_string('adminlabelaccpassword', 'block_moodletxt'));
        $installForm->setType('accountPassword1', PARAM_ALPHANUMEXT);
        $installForm->addRule('accountPassword1', get_string('errornopassword', 'block_moodletxt'), 'required');
        $installForm->addRule('accountPassword1', get_string('errorpasswordtooshort', 'block_moodletxt'), 'minlength', 8);

        $installForm->addElement('password', 'accountPassword2', get_string('adminlabelaccpassword2', 'block_moodletxt'));
        $installForm->setType('accountPassword2', PARAM_ALPHANUMEXT);
        $installForm->addRule('accountPassword2', get_string('errornopassword', 'block_moodletxt'), 'required');
        $installForm->addRule('accountPassword2', get_string('errorpasswordtooshort', 'block_moodletxt'), 'minlength', 8);

        $installForm->addElement('text', 'accountDescription', get_string('adminlabelaccdesc', 'block_moodletxt'));
        $installForm->setType('accountDescription', PARAM_TEXT);

        $installForm->addElement('select', 'accountDefaultInbox', get_string('adminlabelaccinbox', 'block_moodletxt'), $defaultInboxList);
        $installForm->setType('accountDefaultInbox', PARAM_INT);

        // Buttons

        $buttonarray=array();
        $buttonarray[] = &$installForm->createElement('submit', 'submitButton', get_string('adminbutaddaccount', 'block_moodletxt'));
        $installForm->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $installForm->closeHeaderBefore('buttonar');

    }

    /**
     * Validation routine for account form
     * @param array $formdata Submitted data from form
     * @param object $files File uploads from form
     * @return Array of errors, if any found
     * @version 2011080101
     * @since 2011042601
     */
    public function validation($formdata, $files = null) {
        
        $err = array();

        $formdata['accountName']            = trim($formdata['accountName']);
        $formdata['accountPassword1']       = trim($formdata['accountPassword1']);
        $formdata['accountPassword2']       = trim($formdata['accountPassword2']);
        $formdata['accountDescription']     = trim($formdata['accountDescription']);
        $formdata['accountDefaultInbox']    = trim($formdata['accountDefaultInbox']);

        if ($formdata['accountPassword1'] !== $formdata['accountPassword2']) {
            $err['accountPassword1'] = get_string('errornopasswordmatch', 'block_moodletxt');
        }
        
        return $err;
        
    }

}

?>