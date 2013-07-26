<?php

/**
 * "New installation" page - asks user to add
 * a txttools account to the system and provides instructions
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
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013070201
 * @since 2011042601
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/NewTxttoolsAccountForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib/MoodletxtEncryption.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');

require_login();
require_capability('block/moodletxt:adminsettings', context_system::instance());

// OK, so you're legit. Let's load DAOs
$accountDAO = new TxttoolsAccountDAO();
$userDAO = new MoodletxtMoodleUserDAO();

// Check for txttools accounts - if there are no accounts in the system, we'll
// display the "new installation" page introduction, rather than the standard one
$numberOfAccounts = $accountDAO->countTxttoolsRecords();    

admin_externalpage_setup('manageblocks'); // Shortcut function sets up page for block admin

$PAGE->set_url('/blocks/moodletxt/settings_accounts_new.php');
$PAGE->set_button(''); // Clear editing button
$PAGE->set_focuscontrol('id_accountName'); // Focus username field on load
$PAGE->set_docs_path('admin/setting/moodletxtaccountsnew'); // External admin pages get their MoodleDocs links messed up
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), $CFG->wwwroot . '/admin/settings.php?section=blocksettingmoodletxt', navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navaccounts', 'block_moodletxt'), $CFG->wwwroot . '/blocks/moodletxt/settings_accounts.php', navigation_node::TYPE_CUSTOM, 'moodletxt');

if ($numberOfAccounts == 0) {
    $PAGE->set_heading(get_string('headernewinstall', 'block_moodletxt'));
    $PAGE->set_title(get_string('titlenewinstall', 'block_moodletxt'));
    $PAGE->navbar->add(get_string('navnewaccount', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
} else {
    $PAGE->set_heading(get_string('headeraddaccount', 'block_moodletxt'));
    $PAGE->set_title(get_string('titleaccountadd', 'block_moodletxt'));
    $PAGE->navbar->add(get_string('navnewinstall', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
}


$installForm = new NewTxttoolsAccountForm();
$formData = $installForm->get_data();
$formErrors = array();
$connErrors = array();

$output = $PAGE->get_renderer('block_moodletxt');


// POST PROCESSING
if ($formData != null) {

    if ($accountDAO->accountNameExists((string) $formData->accountName)) {
        $formErrors[] = get_string('erroraccountexists', 'block_moodletxt');
    
    } else {
        
        $encrypter = new MoodletxtEncryption();
        $key = get_config('moodletxt', 'EK');
        $xmlController = MoodletxtOutboundControllerFactory::getOutboundController(
            MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML
        );

        $defaultUser = $userDAO->getUserById($formData->accountDefaultInbox);

        $txttoolsAccount = new TxttoolsAccount(
            (string) $formData->accountName, 
            (string) $formData->accountDescription, 
            $defaultUser
        );
        $txttoolsAccount->setEncryptedPassword($encrypter->encrypt($key, $formData->accountPassword1, 20));

        try {
            $txttoolsAccount = $xmlController->updateAccountInfo($txttoolsAccount);
            $accountDAO->saveTxttoolsAccount($txttoolsAccount);
            redirect($CFG->wwwroot . '/blocks/moodletxt/settings_accounts.php', get_string('redirectaccountsfound', 'block_moodletxt'));
        } catch (MoodletxtRemoteProcessingException $ex) {
            $connErrors['remoteError'] = $ex->getCode();
        } catch (Exception $ex) {
            $connErrors['connectError'] = $ex->getCode();
        }
        
    }

}

echo($output->header());

if (count($connErrors) > 0 || count($formErrors) > 0) {
    
    $errorText = get_string('errorconnadmin', 'block_moodletxt');
    
    foreach($formErrors as $formError)
        $errorText .= html_writer::empty_tag('br') . $formError;
    
    foreach($connErrors as $formError)
        $errorText .= html_writer::empty_tag('br') . get_string('errorconn' . $formError, 'block_moodletxt');
    
    echo($output->error_text($errorText));
    
} else if ($numberOfAccounts == 0) {
    
    // Intro paragraph
    echo($output->box(
        $output->heading(get_string('adminintroheader1', 'block_moodletxt')) .
        html_writer::tag('p', get_string('adminintropara1', 'block_moodletxt')) .

        $output->heading(get_string('adminintroheader2', 'block_moodletxt'), 3) .
        html_writer::tag('p', get_string('adminintropara2', 'block_moodletxt')) .
        html_writer::tag('p', get_string('adminintropara3', 'block_moodletxt')) .

        $output->heading(get_string('adminintroheader3', 'block_moodletxt'), 3) .
        html_writer::tag('p', get_string('adminintropara4', 'block_moodletxt')) .
        html_writer::tag('p', get_string('adminintropara5', 'block_moodletxt')),

        'mdltxt_half_centred'
    ));
    
}

$installForm->display();
echo($output->footer());

?>