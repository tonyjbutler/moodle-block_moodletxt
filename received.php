<?php

/**
 * Received messages page
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
 * @since 2011080501
 */

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');
require_once($CFG->dirroot . '/blocks/moodletxt/inbound/MoodletxtInboundFilterManager.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsReceivedMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/renderers/InlineFormRenderer.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtInboxControlForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');

$courseId   = required_param('course',   PARAM_INT);
$instanceId = required_param('instance', PARAM_INT);
$update     = optional_param('update', 0, PARAM_INT);
$download   = optional_param('download', '', PARAM_ALPHA);

require_login($courseId, false);
$blockcontext = context_block::instance($instanceId);
require_capability('block/moodletxt:receivemessages', $blockcontext, $USER->id);

// OK, so you're legit. Let's load DAOs
$txttoolsAccountDAO   = new TxttoolsAccountDAO();
$receivedMessagesDAO  = new TxttoolsReceivedMessageDAO();
$inboundFilterManager = new MoodletxtInboundFilterManager();
$userDAO              = new MoodletxtMoodleUserDAO();

// Get user's inbox preferences
$globalSourceConfig = get_config('moodletxt', 'Show_Inbound_Numbers');
$userConfig = $userDAO->getUserConfig($USER->id);

$showInboundNumbers = ($userConfig->getUserConfig('hideSources') == '0' && $globalSourceConfig == '1');


// Get tags and counts for user
$tagList = $receivedMessagesDAO->getAllTagsForUser($USER->id);

/*
 * Check to see if inbound messages should be fetched at this point.
 * This should only be used on-demand, or if the user cannot
 * set up XML Push
 */
$fetchErrors = array();

if ($update == 1 || get_config('moodletxt', 'Get_Inbound_On_View') == '1') {
    
    try {
    
        $connector = MoodletxtOutboundControllerFactory::getOutboundController(
            MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML);

        $inboundAccounts = $txttoolsAccountDAO->getAllTxttoolsAccounts(false, false, false, true);
        $inboundMessages = $connector->getInboundMessages($inboundAccounts);

        if (count($inboundMessages) > 0) {
            
            $inboundMessages = $inboundFilterManager->filterMessages($inboundMessages);
            $receivedMessagesDAO->saveInboundMessages($inboundMessages);

        }

    } catch (MoodletxtRemoteProcessingException $ex) {
        
        $fetchErrors[$ex->getCode()] = MoodletxtStringHelper::getLanguageStringForRemoteProcessingException($ex);
        
    }
}


/*
 * Set up page
 */
$PAGE->set_url('/blocks/moodletxt/received.php');
$PAGE->set_title(get_string('titlereceivedmessages', 'block_moodletxt') . ' ' . $USER->lastname . ', ' . $USER->firstname);
$PAGE->set_heading(get_string('headerreceivedmessages', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navreceivedmessages', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

$PAGE->requires->strings_for_js(array(
    'alertconfirmdeletemessages',
    'alertnomessagesselected',
    'fragloading'
), 'block_moodletxt');

// JS/CSS includes and language requirements
if (get_config('moodletxt', 'jQuery_Include_Enabled'))
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.js', true);

if (get_config('moodletxt', 'jQuery_UI_Include_Enabled')) {
    $PAGE->requires->css('/blocks/moodletxt/style/jquery.ui.css');
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.ui.js', true);
}

$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.json.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/received.js', true);

$PAGE->requires->js_init_call('receiveCourseId', array($courseId));
$PAGE->requires->js_init_call('receiveInstanceId', array($instanceId));

$output = $PAGE->get_renderer('block_moodletxt');



/*
 * Inline form for message controls
 */
$userList = array_merge(
    get_users_by_capability(context_system::instance(), 'block/moodletxt:receivemessages'),
    get_users_by_capability(context_course::instance($courseId), 'block/moodletxt:receivemessages')
);
$userArray = array(0 => '');

foreach($userList as $thisUser) {
    
    // Don't add the current user to the destination list
    if ($thisUser->id == $USER->id)
        continue;
    
    $userArray[$thisUser->id] = MoodletxtStringHelper::formatNameForDisplay($thisUser->firstname, $thisUser->lastname, $thisUser->username);
    
}

$GLOBALS['_HTML_QuickForm_default_renderer'] = new InlineFormRenderer();
$customData = array(
    'userlist' => $userArray
);
$inboxForm = new MoodletxtInboundControlForm(null, $customData, 'post', '', array('class' => 'mdltxt_left'));


/*
 * Create results table
 */
$table = new flexible_table('blocks-moodletxt-inboxmessages');
$table->define_baseurl($CFG->wwwroot . '/blocks/moodletxt/received.php?course=' . $courseId . '&instance=' . $instanceId); // Required in 2.2 for export
$table->set_attribute('id', 'mdltxtReceivedMessagesList');
$table->set_attribute('class', 'generaltable generalbox boxaligncenter boxwidthwide mtxtCentredCells');
$table->collapsible(true);

if ($download != '')
    $table->is_downloading($download, get_string('exportsheetinbox', 'block_moodletxt'), get_string('exporttitleinbox', 'block_moodletxt'));

$table->is_downloadable(true);

// Build table structure based on download status and user options
$tablecolumns = array();
$tableheaders = array();

if (! $table->is_downloading()) {
    array_push($tablecolumns, 'checkbox');
    array_push($tableheaders, html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'selectAllMessages', 'value' => '1')));
}

array_push($tablecolumns, 'ticket');
array_push($tablecolumns, 'messagetext');

array_push($tableheaders, get_string('inboxtableheaderticket', 'block_moodletxt'));
array_push($tableheaders, get_string('inboxtableheadermessage', 'block_moodletxt'));

if ($showInboundNumbers) {
    array_push($tablecolumns, 'source');
    array_push($tablecolumns, 'sourcename');
    
    array_push($tableheaders, get_string('inboxtableheaderphone', 'block_moodletxt'));
    array_push($tableheaders, get_string('inboxtableheadername', 'block_moodletxt'));
}

array_push($tablecolumns, 'timereceived');
array_push($tableheaders, get_string('inboxtableheadertime', 'block_moodletxt'));

array_push($tablecolumns, 'tags');
array_push($tableheaders, get_string('inboxtableheadertags', 'block_moodletxt'));

if (! $table->is_downloading()) {
    array_push($tablecolumns, 'options');
    array_push($tableheaders, get_string('inboxtableheaderoptions',   'block_moodletxt'));
}

// Finish off table options
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->sortable(true, 'timereceived', SORT_DESC);
$table->no_sorting('checkbox');
$table->no_sorting('messagetext');
$table->no_sorting('options');
$table->no_sorting('tags');

$table->column_class('tags', 'tagDrop');

$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

$table->setup();


// Output errors if necessary
if (! $table->is_downloading()) {

    // Drop in page header
    echo($output->header());

    $addTagListItems = '';
    
    $tagCloud = new moodletxt_message_tag_cloud(get_string('headertags', 'block_moodletxt'));
    $tagCloud->set_attribute('class', 'mdltxt_right');
    $tagCloud->set_attribute('style', 'text-align:right;');
    
    $maxCount = 1;
    
    // Find tag with largest number of messages assigned
    foreach($tagList as $tag) {
        
        if ($tag->getTagCount() > $maxCount)
            $maxCount = $tag->getTagCount();
        
        $addTagListItems .= $output->render(new moodletxt_message_tag_link(
                $tag->getName(), MoodletxtStringHelper::convertToValidCSSIdentifier($tag->getName()),
                100, $tag->getColourCode()));
        
    }
    
    // @TODO Make these into widgets
    $tagListScroller = html_writer::tag('div', $addTagListItems, array('id' => 'mdltxtTagListScroller'));
    
    $inputBox = html_writer::empty_tag('input', array('type' => 'text', 'width' => '20', 'name' => 'trayNewTag'));
    $newTagContent = get_string('labelnewtag', 'block_moodletxt') . $inputBox .$output->render(
            new moodletxt_icon(moodletxt_icon::$ICON_ADD, get_string('altaddtag', 'block_moodletxt'), array('class' => 'mdltxtAddTagButton')));
    $newTagDiv = html_writer::tag('div', $newTagContent, array('id' => 'mdltxtNewTagForm'));
    
    echo(html_writer::tag('div', $newTagDiv . $tagListScroller, array('id' => 'tagPopup', 'class' => 'mdltxtControlTray')));
    
    $binImage = html_writer::empty_tag('img', array('src' => 'pix/icons/trash48.png', 'width' => 48, 'height' => 48));
    echo(html_writer::tag('div', $binImage, array('id' => 'trashPopup', 'class' => 'mdltxtControlTray')));
    
    // Insert tags into cloud
    foreach($tagList as $tag)
        $tagCloud->add_tag_widget(new moodletxt_message_tag_link(
                $tag->getName(), MoodletxtStringHelper::convertToValidCSSIdentifier($tag->getName()),
                floor(($tag->getTagCount() / $maxCount) * 100), 
                $tag->getColourCode()));
    
    echo($output->render($tagCloud));
    
    // Display any errors encountered before the table is rendered
    if (count($fetchErrors) > 0) {

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
    
    $inboxForm->display();
    
    // Shouldn't have to do this, but Moodle tables get outputted inside a 
    // wrapper DIV, and classes applied to them are applied directly to
    // the table, rather than the wrapper.
    echo(html_writer::tag('div', null, array('class' => 'mdltxtCleared')));
    
}


/*
 * Populate table
 */
$receivedMessages = $receivedMessagesDAO->getReceivedMessagesForUser($USER->id, 0, 0, 'timereceived DESC', true);

foreach($receivedMessages as $message) {
    
    $checkbox = html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'messageids[]', 'value' => $message->getId()));
    $deleteButton = new moodletxt_icon(moodletxt_icon::$ICON_DELETE, get_string('altdelete', 'block_moodletxt'), array('class' => 'mtxtMessageDeleteButton'));
    $replyButton = new moodletxt_icon(moodletxt_icon::$ICON_MESSAGE_REPLY, get_string('altreply', 'block_moodletxt'), array('class' => 'mtxtMessageReplyButton'));
    $tagButton = new moodletxt_icon(moodletxt_icon::$ICON_TAG, get_string('altaddtag', 'block_moodletxt'), array('class' => 'mtxtMessageTagButton'));

    $messageSource = $message->getSource();
    
    if ($messageSource instanceof MoodletxtBiteSizedUser) {
        
        $replyType = 'user';
        $replyValue = $messageSource->getId();
        
    } else if ($messageSource instanceof MoodletxtAddressbookRecipient) {
        
        $replyType = 'contact';
        $replyValue = $messageSource->getContactId();
        
    } else if ($messageSource instanceof MoodletxtAdditionalRecipient) {
        
        $replyType = 'additional';
        $replyValue = $messageSource->getRecipientNumber()->getPhoneNumber();
        
    }
    
    $replyUrl = new moodle_url('/blocks/moodletxt/send.php', array(
        'course'        => $courseId, 
        'instance'      => $instanceId,
        'replyType'     => $replyType,
        'replyValue'    => $replyValue
    ));
    
    $replyLink = html_writer::link($replyUrl, $output->render($replyButton));

    
    // Build data row array
    $tableRow = array();
    
    if (! $table->is_downloading())
        array_push($tableRow, $checkbox);
    
    array_push($tableRow, $message->getMessageTicket());
    array_push($tableRow, $message->getMessageText());
    
    if ($showInboundNumbers) {
        array_push($tableRow, $message->getSourceNumber()->getPhoneNumber());
        array_push($tableRow, $message->getSourceNameForDisplay());
    }
    
    array_push($tableRow, $message->getTimeReceived('%H:%M:%S,  %d %B %Y'));
    
    // Put tags into single string
    $tagString = '';
    
    if ($table->is_downloading()) {
    
        foreach($message->getTags()  as $tag) {
            if ($tagString != '') $tagString .= ', ';
            $tagString .= $tag->getName();
        }

    } else {
        
        foreach($message->getTags()  as $tag)
            $tagString .= html_writer::tag('span', $tag->getName(), array('class' => 'mtxtAppliedTag'));
        
    }
    
    array_push($tableRow, $tagString);
    
    if (! $table->is_downloading())
        array_push($tableRow, $output->render($tagButton) . $output->render($deleteButton) . $replyLink);
    
    // Turn tag names into CSS classes
    $classString = '';
    foreach($message->getTags() as $tag)
        $classString .= ' ' . MoodletxtStringHelper::convertToValidCSSIdentifier($tag->getName());
    
    $table->add_data($tableRow, $classString);
    
}

// Output everything and run away
$table->finish_output();

if (! $table->is_downloading()) {
    $inboxForm->display();
    echo($output->footer());
}

?>