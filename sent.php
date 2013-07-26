<?php

/**
 * Sent messages page
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
 * @since 2011081101
 */

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsSentMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');

$courseId       = required_param('course',   PARAM_INT);
$instanceId     = required_param('instance', PARAM_INT);
$download       = optional_param('download', '', PARAM_ALPHA);
$includeEvents  = optional_param('events', TxttoolsSentMessageDAO::$EVENT_QUERY_DISCARD, PARAM_INT);
$userToView     = optional_param('user', $USER->id, PARAM_INT);
$MESSAGES_PER_PAGE = 25;

require_login($courseId, false);
$blockcontext = context_block::instance($instanceId);
require_capability('block/moodletxt:sendmessages', $blockcontext, $USER->id);

// Make sure that the user has the ability to change the
// ID of the user whose messages are being shown
$canAdminUsers = has_capability('block/moodletxt:adminusers', $blockcontext, $USER->id);

if (! $canAdminUsers && $userToView != $USER->id)
    $userToView = $USER->id; // Naughty user, don't try that again.



// OK, so you're legit. Let's load DAOs
$sentMessagesDAO = new TxttoolsSentMessageDAO();
$userDAO = new MoodletxtMoodleUserDAO();

// Set up the page for rendering
$pageParams = '?course=' . $courseId . '&instance=' . $instanceId . '&events=' . $includeEvents;
$pageParams .= ($canAdminUsers) ? '&user=' . $userToView : '';

$PAGE->set_url('/blocks/moodletxt/sent.php' . $pageParams);
$PAGE->set_title(get_string('titlesent', 'block_moodletxt') . ' ' . $USER->lastname . ', ' . $USER->firstname);
$PAGE->set_heading(get_string('headersent', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navsent', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

$output = $PAGE->get_renderer('block_moodletxt');


/*
 * Create results table
 */
$table = new flexible_table('blocks-moodletxt-sentmessages');
$table->define_baseurl($CFG->wwwroot . '/blocks/moodletxt/sent.php' . $pageParams); // Required in 2.2 for export
$table->set_attribute('id', 'sentMessagesList');
$table->set_attribute('class', 'generaltable generalbox boxaligncenter boxwidthwide mtxtCentredCells');

// Set structure
$tablecolumns = array("user", "account", "message", "time");

$tableheaders = array(
                    get_string('tableheaderuser',               'block_moodletxt'),
                    get_string('tableheadertxttoolsaccount',    'block_moodletxt'),
                    get_string('tableheadermessagetext',        'block_moodletxt'),
                    get_string('tableheadertimesent',           'block_moodletxt')
                ); // ;)

if ($includeEvents == TxttoolsSentMessageDAO::$EVENT_QUERY_INCLUDE ||
    $includeEvents == TxttoolsSentMessageDAO::$EVENT_QUERY_EXCLUSIVE) {

    array_push($tablecolumns, "generation");
    array_push($tableheaders, get_string('tableheadergeneration', 'block_moodletxt'));
    
}

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->sortable(true, 'time', SORT_DESC);
$table->no_sorting('message');
$table->collapsible(true);
$table->pageable(true);
$table->pagesize($MESSAGES_PER_PAGE, $sentMessagesDAO->countMessagesSent(0, $USER->id));

if ($download != '')
    $table->is_downloading($download, get_string('exportsheetsent', 'block_moodletxt'), get_string('exporttitlesent', 'block_moodletxt'));

$table->is_downloadable(true);
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

$table->setup();


// BEGIN PAGE OUTPUT
if (! $table->is_downloading()) {

    // Drop in page header
    echo($output->header());
    
    echo($output->box(
        $sentMessagesDAO->countMessagesSent() . 
        get_string('sentnoticefrag1', 'block_moodletxt') .
        $sentMessagesDAO->countMessageRecipients() .
        get_string('sentnoticefrag2', 'block_moodletxt') .
        html_writer::empty_tag('br') .
        $sentMessagesDAO->countMessagesSent(0, $userToView) .
        get_string('sentnoticefrag3', 'block_moodletxt')
    ));
    
    // Show select box allow user to show/hide event-generated messages
    $eventSelect = new single_select(
        new moodle_url(
            'sent.php', 
            array('course' => $courseId, 'instance' => $instanceId, 'user' => $userToView)
        ),
        'events',
        array(
            TxttoolsSentMessageDAO::$EVENT_QUERY_DISCARD   => get_string('optioneventhide', 'block_moodletxt'),
            TxttoolsSentMessageDAO::$EVENT_QUERY_INCLUDE   => get_string('optioneventshow', 'block_moodletxt'),
            TxttoolsSentMessageDAO::$EVENT_QUERY_EXCLUSIVE => get_string('optioneventonly', 'block_moodletxt')
        ),
        $includeEvents,
        false
    );
    $eventSelect->set_label(get_string('labelshowmessagebygenerator', 'block_moodletxt'));
    echo($output->render($eventSelect));
    
    // Show user list if the user has permission to see other people's messages
    if ($canAdminUsers) {
    
        $optionSet = array(0 => get_string('optionallusers', 'block_moodletxt'));
        $userList = $userDAO->getAllUsers();
        
        foreach($userList as $userObj)
            $optionSet[$userObj->getId()] = $userObj->getFullNameForDisplay();
        
        $userSelect = new single_select(
            new moodle_url(
                'sent.php', 
                array('course' => $courseId, 'instance' => $instanceId, 'events' => $includeEvents)
            ),
            'user',
            $optionSet, $userToView, false
        );
        
        $userSelect->set_label(get_string('labelshowmessagesforuser', 'block_moodletxt'));
        echo($output->render($userSelect));
        
    }
    
}


// Figure out which table item the user wants to sort by
$orderBy = '';

foreach($table->get_sort_columns() as $field => $direction) {
    switch($field) {
        case 'user':
            $databaseOrder = 'u.lastname %DIR%, u.firstname %DIR%';
            break;
        case 'account':
            $databaseOrder = 'acc.username %DIR%';
            break;
        case 'time':
            $databaseOrder = 'o.timesent %DIR%';
            break;
    }
    
    if ($direction == SORT_ASC)
        $databaseOrder = str_replace('%DIR%', 'ASC', $databaseOrder);
    else
        $databaseOrder = str_replace('%DIR%', 'DESC', $databaseOrder);
    
    if ($orderBy != '') $databaseOrder = ', ' . $databaseOrder;
    $orderBy .= $databaseOrder;
}

// All parameters *MUST* be sorted out by here - getting messages
$sentMessages = $sentMessagesDAO->getSentMessagesForUser($userToView, $orderBy, 0, 0, 
        $table->get_page_start(), $table->get_page_size(), $includeEvents);

// Populate table
foreach($sentMessages as $message) {

    if ($table->is_downloading()) {
        $messageContent = $message->getMessageText();
    } else {
        $messageContent = html_writer::tag('a', $message->getMessageText(), array(
            'href' => 'status.php?message=' . $message->getId() . '&course=' . $courseId . '&instance=' . $instanceId
        ));        
    }
    
    
    $rowData = array(
        $message->getUser()->getFullNameForDisplay(! $table->is_downloading()),
        $message->getTxttoolsAccount()->getUsername(),
        $messageContent,
        $message->getTimeSent('%H:%M:%S,  %d %B %Y')
    );
    
    if ($includeEvents == TxttoolsSentMessageDAO::$EVENT_QUERY_INCLUDE ||
        $includeEvents == TxttoolsSentMessageDAO::$EVENT_QUERY_EXCLUSIVE) {

        $eventStr = ($message->isEventCreated()) ? 
            get_string('fragsystem', 'block_moodletxt') :
            get_string('fraguser', 'block_moodletxt');
        
        array_push($rowData, $eventStr);
    }
    
    $table->add_data($rowData);
    
}

$table->finish_output();

if (! $table->is_downloading()) {

    echo($output->footer());
    
}

?>