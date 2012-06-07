<?php

/**
 * Message composition page. This here is the big one!
 * 
 * moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "txttools", "moodletxt", "Blackboard", "Blackboard Connect" or "Cy-nap"
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
 * @version 2012060101
 * @since 2011101701
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/BrowserHelper.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/StringHelper.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtTemplatesDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsSentMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/renderers/QuickFormRendererWithSlides.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtSendMessageForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');

$courseId   = required_param('course',   PARAM_INT);
$instanceId = required_param('instance', PARAM_INT);

require_login($courseId, false);
$blockcontext = get_context_instance(CONTEXT_BLOCK, $instanceId);
require_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);


// OK, so you're legit. Let's load DAOs and required DB data
$templateDAO    = new MoodletxtTemplatesDAO();
$accountDAO     = new TxttoolsAccountDAO();
$addressbookDAO = new MoodletxtAddressbookDAO();
$userDAO        = new MoodletxtMoodleUserDAO();
$messageDAO     = new TxttoolsSentMessageDAO();

$course = $DB->get_record('course', array('id' => $courseId));

// Set up the page for rendering
$PAGE->set_url('/blocks/moodletxt/send.php');
$PAGE->set_title(get_string('titlesend', 'block_moodletxt') . ' ' . $course->fullname);
$PAGE->set_heading(get_string('headersend', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navsent', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

$PAGE->requires->strings_for_js(array(
    'errorlabel',
    'errornonumber',
    'errornofirstname',
    'errornolastname',
    'errornorecipientsselected',
    'errornomessage',
    
), 'block_moodletxt');

if (get_config('moodletxt', 'jQuery_Include_Enabled'))
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.js', true);

if (get_config('moodletxt', 'jQuery_UI_Include_Enabled')) {
    $PAGE->requires->css('/blocks/moodletxt/style/jquery.ui.css');
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.ui.js', true);
}

$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.timers.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.selectboxes.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.json.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.detachselect.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/send.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/qfamsHandler.js', true);

// We don't need overrides for IE6, as Moodle 2 has dropped it!
// Oh, the happiness. The sheer unrelenting joy.
$PAGE->requires->css('/blocks/moodletxt/style/send.css');

// User's signature
$PAGE->requires->js_init_call('receiveUserSignature', 
        array($userDAO->getUserConfig($USER->id)->getUserConfig('signature')));

// Set up send form and load in all our lovely data
$GLOBALS['_HTML_QuickForm_default_renderer'] = new QuickFormRendererWithSlides(); // Override renderer for slides

$potentialRecipients = array();
$customData = array('course' => $courseId, 'instance' => $instanceId, 'existingTemplates' => array(),
        'potentialRecipients' => &$potentialRecipients, 'txttoolsAccounts' => array(), 
        'moodleUsers' => array(), 'moodleGroups' => array());

$templates      = $templateDAO->getAllTemplatesForUserId($USER->id);
$accounts       = $accountDAO->getAllTxttoolsAccounts(false, false);
$moodleUsers    = $userDAO->getUsersOnCourse($courseId);
$moodleGroups   = $userDAO->getUserGroupsOnCourse($courseId);
$abContacts     = $addressbookDAO->getAddressbookContactsForUser($USER->id);
$abGroups       = $addressbookDAO->getAddressbookGroupsForUser($USER->id);

foreach($templates as $template)
    $customData['existingTemplates'][$template->getId()] = $template->getText();

foreach($accounts as $account)
    $customData['txttoolsAccounts'][$account->getId()] = 
        StringHelper::formatAccountForDisplay($account->getUsername(), $account->getDescription());

foreach($moodleUsers as $user)    
    if ($user->hasPhoneNumber())
        $potentialRecipients['u#' . $user->getId()] = array(
            $user->getFullNameForDisplay(),
            array('class' => 'userRecipient') // Fourth level, baby!
        );

foreach($moodleGroups as $group)
    $potentialRecipients['ug#' . $group->getId()] = array(
        $group->getName(),
        array('class' => 'userGroupRecipient')
    );

foreach($abContacts as $contact)
    $potentialRecipients['ab#' . $contact->getContactId()] = array(
        $contact->getFullNameForDisplay(),
        array('class' => 'addressbookRecipient')
    );

foreach($abGroups as $group)
    $potentialRecipients['abg#' . $group->getId()] = array(
        $group->getName(),
        array('class' => 'addressbookGroupRecipient')
    );

$sendForm = new MoodletxtSendMessageForm(null, $customData);


// Check for submitted data
$formData = $sendForm->get_data();

if ($formData != null) {
    
    $moodleUsers = array();
    $moodleUserGroups = array();
    $addressBookContacts = array();
    $addressBookGroups = array();
    $additionalContacts = array();
    $destinations = array();
    
    $formData = $sendForm->cleanFormData($formData);
    
    foreach($formData->recipients as $recipient) {

        $valuefrags = explode('#', $recipient);

        if (count($valuefrags) != 2 && count($valuefrags) != 4)
            continue;

        switch($valuefrags[0]) {

            case 'u':
                if (StringHelper::isIntegerValue($valuefrags[1]))
                    array_push($moodleUsers, $valuefrags[1]);
                break;
            case 'ug':
                if (StringHelper::isIntegerValue($valuefrags[1]))
                    array_push($moodleUserGroups, $valuefrags[1]);
                break;
            case 'ab':
                if (StringHelper::isIntegerValue($valuefrags[1]))
                    array_push($addressBookContacts, $valuefrags[1]);
                break;
            case 'abg':
                if (StringHelper::isIntegerValue($valuefrags[1]))
                    array_push($addressBookGroups, $valuefrags[1]);
                break;
            case 'add':
                $additionalContacts[$valuefrags[1]] = new MoodletxtAdditionalRecipient($valuefrags[1], $valuefrags[3], $valuefrags[2]);
                break;
            default:
                continue;

        }

    }

    // Build destination list, indexed by phone number to prevent duplicates
    
    // Fetch additional contacts first (lowest priority for linking)
    $destinations += $additionalContacts; 
    
    // Addressbook contacts and groups next
    if (count($addressBookGroups) > 0)
        foreach($addressBookGroups as $addressBookGroup)
            $destinations += $addressbookDAO->getAddressbookContactsInGroup($addressBookGroup, 'phone');
    
    if (count($addressBookContacts) > 0)
        $destinations += $addressbookDAO->getAddressbookContactsById($addressBookContacts, 'phone');
            
    
    // Merge Moodle users last - higher priority in linking than other contact type
    if (count($moodleUserGroups) > 0)
        foreach($moodleUserGroups as $userGroup)
            $destinations += $userDAO->getUsersInGroup($userGroup, 'phone');
    
    if (count($moodleUsers) > 0)
        $destinations += $userDAO->getUsersById($moodleUsers, 'phone');
    

    // If there are valid destinations, send the message
    if (count($destinations) > 0) {
        
        $txttoolsAccount = $accountDAO->getTxttoolsAccountById($formData->txttoolsaccount);
        $thisUser = new MoodletxtBiteSizedUser($USER->id, $USER->username, $USER->firstname, $USER->lastname);
        $scheduletime = ($formData->schedule == 'schedule') ? $formData->scheduletime : time();
        
        $outboundMessage = new MoodletxtOutboundMessage($txttoolsAccount, $thisUser, 
                $formData->messageText, time(), 
                MoodletxtOutboundMessage::$MESSAGE_CHARGE_TYPE_BULK, 
                $scheduletime, $formData->suppressUnicode
        );
        
        $outboundMessage->setMessageRecipients($destinations);
        
        try {
        
            $connector = MoodletxtOutboundControllerFactory::getOutboundController(
                MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML);
            
            $outboundMessage->setSentSMSMessages($connector->sendMessage($outboundMessage));
            $messageDAO->saveSentMessage($outboundMessage);

            $sentPageUrl = new moodle_url('/blocks/moodletxt/sent.php', array(
                'course'    => $courseId,
                'instance'  => $instanceId
            ));
            
            redirect($sentPageUrl, get_string('redirectmessagesent', 'block_moodletxt'));
                    
        } catch (MoodletxtRemoteProcessingException $ex) {
            
            die('Could not send message');
            
            // Uh-oh, couldn't send! Display error, please
            
        }
                    
    }
    
}



// Output page
$output = $PAGE->get_renderer('block_moodletxt');

echo($output->header());

echo($output->render(new moodletxt_slide_form_navigator('navigator', array(
        'nav1' => get_string('navsliderecipients', 'block_moodletxt'),
        'nav2' => get_string('navslidemessage', 'block_moodletxt'),
        'nav3' => get_string('navslidemessageopts', 'block_moodletxt'),
        'nav4' => get_string('navslidereview', 'block_moodletxt')
    ))));

$sendForm->display();

echo($output->footer());

?>