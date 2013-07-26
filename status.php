<?php

/**
 * Message status page
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
 * @since 2012031901
 */

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsSentMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStatusIconFactory.php');

$courseId   = required_param('course',   PARAM_INT);
$instanceId = required_param('instance', PARAM_INT);
$messageId  = required_param('message',  PARAM_INT);
$download   = optional_param('download', '', PARAM_ALPHA);
$update     = optional_param('update', 0, PARAM_INT);
$STATUSES_PER_PAGE = 25;

require_login($courseId, false);
$blockcontext = context_block::instance($instanceId);
require_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);

// OK, so you're legit. Let's load DAOs
$sentMessagesDAO = new TxttoolsSentMessageDAO();

// Check that message ID passed in is legit
// (Don't get statuses here - we get those separately later)
$messageObject = $sentMessagesDAO->getSentMessageById($messageId);

if ($messageObject == null)
    print_error('errorbadmessageid', 'block_moodletxt');

else if ($messageObject->getUser()->getId() != $USER->id && ! has_capability('block/moodletxt:adminusers', $blockcontext, $USER->id))
    print_error('errornopermissionmessage', 'block_moodletxt');


// Set up the page for rendering
$PAGE->set_url('/blocks/moodletxt/status.php');
$PAGE->set_title(get_string('titlestatus', 'block_moodletxt') . ' ' . $USER->lastname . ', ' . $USER->firstname);
$PAGE->set_heading(get_string('headerstatus', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navsent', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

$output = $PAGE->get_renderer('block_moodletxt');


/*
 * Create results table
 */
$table = new flexible_table('blocks-moodletxt-sms-status');
$table->define_baseurl($CFG->wwwroot . '/blocks/moodletxt/status.php?course=' . $courseId . '&instance=' . $instanceId . '&message=' . $messageId); // Required in 2.2 for export
$table->set_attribute('id', 'smsStatusList');
$table->set_attribute('class', 'generaltable generalbox boxaligncenter boxwidthwide mtxtCentredCells');

// Set structure
$tablecolumns = array("recipient", "destination", "time", "status");

$tableheaders = array(
                    get_string('tableheaderrecipient',   'block_moodletxt'),
                    get_string('tableheaderdestination', 'block_moodletxt'),
                    get_string('tableheadertimeupdated', 'block_moodletxt'),
                    get_string('tableheaderstatus',      'block_moodletxt'),
                ); // ;)

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->sortable(true, 'time', SORT_ASC);
$table->no_sorting('destination');
$table->no_sorting('recipient');
$table->collapsible(true);
$table->pageable(true);
$table->pagesize($STATUSES_PER_PAGE, $sentMessagesDAO->countMessageRecipients($messageId));

if ($download != '')
    $table->is_downloading($download, get_string('exportsheetsent', 'block_moodletxt'), get_string('exporttitlesent', 'block_moodletxt'));

$table->is_downloadable(true);
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

$table->setup();

// Output page header and everything before the table.
// This should only be output when not exporting the table.
if (! $table->is_downloading()) {

    // Drop in page header
    echo($output->header());

    // Status light key (top right)
    $statusKey  = $output->heading(get_string('headerstatuskey', 'block_moodletxt'));
    
    $statusKey .= $output->render(new moodletxt_icon(moodletxt_icon::$ICON_STATUS_FAILED, 
                        get_string('altstatusfailed', 'block_moodletxt')));
    $statusKey .= get_string('labelstatusfailed', 'block_moodletxt') . html_writer::empty_tag('br');
    
    $statusKey .= $output->render(new moodletxt_icon(moodletxt_icon::$ICON_STATUS_TRANSIT,
                        get_string('altstatustransit', 'block_moodletxt')));
    $statusKey .= get_string('labelstatustransit', 'block_moodletxt') . html_writer::empty_tag('br');
    
    $statusKey .= $output->render(new moodletxt_icon(moodletxt_icon::$ICON_STATUS_DELIVERED,
                        get_string('altstatusdelivered', 'block_moodletxt')));
    $statusKey .= get_string('labelstatusdelivered', 'block_moodletxt');
    
    echo($output->box($statusKey, 'generalbox mdltxt_right'));
    
    // Message details box (top left)
    $messageDetails  = $output->heading(get_string('headermessagedetails', 'block_moodletxt'));
    $messageDetails .= html_writer::tag('dt', get_string('labelmessageauthor', 'block_moodletxt'));
    $messageDetails .= html_writer::tag('dd', $messageObject->getUser()->getFullNameForDisplay(true));
    $messageDetails .= html_writer::tag('dt', get_string('labeltimesent', 'block_moodletxt'));
    $messageDetails .= html_writer::tag('dd', $messageObject->getTimeSent('%H:%M:%S,  %d %B %Y'));
    $messageDetails .= html_writer::tag('dt', get_string('labelscheduledfor', 'block_moodletxt'));
    $messageDetails .= html_writer::tag('dd', $messageObject->getScheduledTime('%H:%M:%S,  %d %B %Y'));
    $messageDetails .= html_writer::tag('dt', get_string('labelmessagetext', 'block_moodletxt'));
    $messageDetails .= html_writer::tag('dd', $messageObject->getMessageText());
    
    $messageDL = html_writer::tag('dl', $messageDetails, array('class' => 'mdltxtInlineList'));
    echo($output->box($messageDL, 'generalbox mdltxt_left'));
    
    echo($output->heading(get_string('headerstatus', 'block_moodletxt'), 2, 'main cleared'));
    
    echo($output->single_button(
        new moodle_url(
            'status.php', 
            array(
                'course'    => $courseId,
                'instance'  => $instanceId,
                'message'   => $messageId,
                'update'    => 1
            )
        ),
        get_string('buttonupdate', 'block_moodletxt')
    ));
    
}

// Figure out which table item the user wants to sort by
$orderBy = '';

foreach($table->get_sort_columns() as $field => $direction) {
    switch($field) {
//        case 'recipient':
//            $databaseOrder = 'u.lastname %DIR%, u.firstname %DIR%';
//            break;
        case 'status':
            $databaseOrder = 'status.status %DIR%';
            break;
        case 'time':
            $databaseOrder = 'status.updatetime %DIR%';
            break;
    }
    
    if ($direction == SORT_ASC)
        $databaseOrder = str_replace('%DIR%', 'ASC', $databaseOrder);
    else
        $databaseOrder = str_replace('%DIR%', 'DESC', $databaseOrder);
    
    if ($orderBy != '') $databaseOrder = ', ' . $databaseOrder;
    $orderBy .= $databaseOrder;
}

// Default ordering
if ($orderBy == '')
    $orderBy = 'sendlastname ASC, sendfirstname ASC';


$messageObject->setSentSMSMessages($sentMessagesDAO->getSentSMSMessagesForMessage($messageObject->getId(), 
        $orderBy, $table->get_page_start(), $table->get_page_size(), true));



/*
 * Check to see if statuses should be updated at this point.
 * This should only be used on-demand, or if the user cannot
 * set up XML Push
 */
$fetchErrors = array();

if ($update == 1 || get_config('moodletxt', 'Get_Status_On_View') == '1') {
    
    try {
    
        $connector = MoodletxtOutboundControllerFactory::getOutboundController(
            MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML);

        $messageObject->setSentSMSMessages(
            $connector->getSMSStatusUpdates($messageObject->getSentSMSMessages(), $messageObject->getTxttoolsAccount())
        ); 
        
        $sentMessagesDAO->saveMessagesSentViaSMS($messageObject->getSentSMSMessages());

    } catch (MoodletxtRemoteProcessingException $ex) {
        
        $fetchErrors[$ex->getCode()] = MoodletxtStringHelper::getLanguageStringForRemoteProcessingException($ex);
        
    }
}

// Display any errors encountered before the table is rendered
if (count($fetchErrors) > 0 && ! $table->is_downloading()) {
    
    if (has_capability('block/moodletxt:adminsettings', $blockcontext, $USER->id)) {
        $errorText  = $output->error_text(get_string('errorconnadmin', 'block_moodletxt'));
        
        $errorSet = '';
        foreach($fetchErrors as $fetchError)
            $errorSet .= html_writer::tag('li', $output->error_text($fetchError));
        
        $errorText .= html_writer::tag('ul', $errorSet);
        
    } else {
        $errorText = get_string('errorconndefault', 'block_moodletxt');
    }
    
    echo($errorText);
    
}


// Populate table
foreach($messageObject->getSentSMSMessages() as $message) {

    $recipient = $message->getRecipientObject(); // Save on making this call
 
    // Create contact name string for display
    if ($recipient instanceof MoodletxtBiteSizedUser && ! $table->is_downloading())
        $displayName = $recipient->getFullNameForDisplay(true); // Linkify usernames
    else
        $displayName = $recipient->getFullNameForDisplay();
    
    $latestStatus = array_pop($message->getStatusUpdates());
    
    if ($table->is_downloading())
        $statusCell = $latestStatus->getStatus() . ' - ' . MoodletxtStringHelper::getLanguageStringForStatusCode($latestStatus->getStatus());
    else
        $statusCell = $output->render(MoodletxtStatusIconFactory::generateStatusIconForCode ($latestStatus->getStatus()));
    
    // Add data set to the table
    $table->add_data(array(
        $displayName,
        $recipient->getRecipientNumber()->getPhoneNumber(),
        userdate($latestStatus->getUpdateTime(), "%H:%M:%S,  %d %B %Y"),
        $statusCell
    ));
    
}

// Finally, output everything!
$table->finish_output();

if (! $table->is_downloading()) {

    echo($output->footer());
    
}

?>