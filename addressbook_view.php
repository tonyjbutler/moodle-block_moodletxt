<?php

/**
 * Addressbook contact view 
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
 * @since 2012090401
 */

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAddressbookUpdateForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAddressbookControlForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/renderers/InlineFormRenderer.php');

$courseId       = required_param('course',      PARAM_INT);
$instanceId     = required_param('instance',    PARAM_INT);
$addressbookId  = required_param('addressbook', PARAM_INT);
$download       = optional_param('download', '', PARAM_ALPHA);
$CONTACTS_PER_PAGE = 25;

require_login($courseId, false);
$blockcontext = context_block::instance($instanceId);
require_capability('block/moodletxt:addressbooks', $blockcontext, $USER->id);

// OK, so you're legit. Let's load DAOs
$addressbookDAO = new MoodletxtAddressbookDAO();
if (! $addressbookDAO->checkAddressbookOwnership($addressbookId, $USER->id))
    print_error('errorbadbookid', 'block_moodletxt');

$addressbook = $addressbookDAO->getAddressbookById($addressbookId, $USER->id);


// SETUP PAGE
$pageParams = '?course=' . $courseId . '&instance=' . $instanceId . '&addressbook=' . $addressbookId;
$PAGE->set_url('/blocks/moodletxt/addressbook_view.php');
$PAGE->set_title(get_string('titleaddressbookedit', 'block_moodletxt'));
$PAGE->set_heading(get_string('headeraddressbookedit', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button

$addressbookNav = new moodle_url('/blocks/moodletxt/addressbooks.php', array(
    'course'    => $courseId, 
    'instance'  => $instanceId
));

$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navaddressbooks', 'block_moodletxt'), $addressbookNav, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navaddressbookedit', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

$PAGE->requires->strings_for_js(array(
    'buttonsave',
    'buttoncancel',
    'loadtoken'
), 'block_moodletxt');

if (get_config('moodletxt', 'jQuery_Include_Enabled'))
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.js', true);

$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.json.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/addressbook_view.js', true);

$PAGE->requires->js_init_call('receiveCourseId', array($courseId));
$PAGE->requires->js_init_call('receiveInstanceId', array($instanceId));
$PAGE->requires->js_init_call('receiveAddressbookId', array($addressbookId));

$output = $PAGE->get_renderer('block_moodletxt');

// SETUP ADDRESSBOOK EDITING FORM

// @todo Find some way of switching renderers that isn't so hacky
$GLOBALS['_HTML_QuickForm_default_renderer'] = new InlineFormRenderer();

$initialFormData = array(
    'course'                => $courseId,
    'instance'              => $instanceId,
    'addressbook'           => $addressbook->getId(),
    'addressbookName'       => $addressbook->getName(),
    'addressbookType'       => $addressbook->getType()
);

$updateForm = new MoodletxtAddressbookUpdateForm(
    has_capability('block/moodletxt:globaladdressbooks', $blockcontext, $USER->id)
);
$updateForm->set_data($initialFormData);

$deleteForm = new MoodletxtAddressbookControlForm(null, null, 'post', '', array('id' => 'addressbookControlForm'));
$deleteForm->set_data($initialFormData);

// PERFORM ADDRESSBOOK DELETE PROCESSING BEFORE POPULATION
if ($deleteForm->get_data() != null) {
    
    $formData = $deleteForm->get_data();
    
    if ($formData->deleteContactIds != '') {
        $deleteContactIds = explode(',', $formData->deleteContactIds);
        $deleteInclusive = ($formData->deleteType == 'exclusive') ? false : true; // Test against non-default value
        
        if (count($deleteContactIds) > 0) {
            $addressbookDAO->deleteContactsById($addressbookId, $deleteContactIds, $deleteInclusive);
        }
    }
    
}

// PERFORM ADDRESSBOOK UPDATE PROCESSING BEFORE POPULATION
if ($updateForm->get_data() != null) {
    
    $formData = $updateForm->cleanupFormData($updateForm->get_data());
    $addressbook->setName($formData->addressbookName);
    $addressbook->setType($formData->addressbookType);
    
    $addressbookDAO->saveAddressbook($addressbook);
    
}

// SETUP TABLE
$table = new flexible_table('block-moodletxt-contacts');
$table->define_baseurl($CFG->wwwroot . '/blocks/moodletxt/addressbook_view.php' . $pageParams);
$table->set_attribute('id', 'contactsList');
$table->set_attribute('class', 'generaltable generalbox boxaligncenter boxwidthwide mtxtCentredCells');

if ($download != '')
    $table->is_downloading($download, get_string('exportsheetcontacts', 'block_moodletxt'), get_string('exporttitlecontacts', 'block_moodletxt'));

$table->is_downloadable(true);
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

if ($table->is_downloading()) {
    
    $tableColumns = array('id', 'firstName', 'lastName', 'company', 'phone');
    $tableHeaders = array(
        get_string('tableheadercontactid',  'block_moodletxt'),
        get_string('tableheaderfirstname',  'block_moodletxt'),
        get_string('tableheaderlastname',   'block_moodletxt'),
        get_string('tableheadercompany',    'block_moodletxt'),
        get_string('tableheaderphoneno',    'block_moodletxt')
    );
    
} else {

    $tableColumns = array('checkboxes', 'firstName', 'lastName', 'company', 'phone');
    $tableHeaders = array(
        '',
        get_string('tableheaderfirstname',  'block_moodletxt'),
        get_string('tableheaderlastname',   'block_moodletxt'),
        get_string('tableheadercompany',    'block_moodletxt'),
        get_string('tableheaderphoneno',    'block_moodletxt')
    );
    
}

$table->define_columns($tableColumns);
$table->define_headers($tableHeaders);
$table->sortable(true, 'lastName', SORT_ASC);
$table->no_sorting('checkboxes');
$table->no_sorting('phone');
$table->collapsible(true);
$table->pageable(true);
$table->pagesize($CONTACTS_PER_PAGE, $addressbookDAO->countContactsInAddressbook($addressbook->getId()));

$table->setup();


// Get user's sort options and query DB
$orderBy = '';

foreach($table->get_sort_columns() as $field => $direction) {
    switch($field) {
        case 'lastName':
            $databaseOrder = 'c.lastname %DIR%';
            break;
        case 'firstName':
            $databaseOrder = 'c.firstname %DIR%';
            break;
        case 'company':
            $databaseOrder = 'c.company %DIR%';
            break;
    }
    
    if ($direction == SORT_ASC)
        $databaseOrder = str_replace('%DIR%', 'ASC', $databaseOrder);
    else
        $databaseOrder = str_replace('%DIR%', 'DESC', $databaseOrder);
    
    if ($orderBy != '') $databaseOrder = ', ' . $databaseOrder;
    $orderBy .= $databaseOrder;
}

// Page parameters must be sorted by this point - populating the table
$contactsToDisplay = $addressbookDAO->getAddressbookContactsForUser($USER->id, 
        $addressbook->getId(), $orderBy, $table->get_page_start(), $table->get_page_size());


// BEGIN PAGE OUTPUT
if (! $table->is_downloading()) {
    echo($output->header());
    
    // Links to add contact/manage groups pages
    echo($output->box(
        html_writer::link(
            new moodle_url('addressbook_contact_add.php', array(
                'course'      => $courseId,
                'instance'    => $instanceId,
                'addressbook' => $addressbookId
            )), get_string('linkaddcontact', 'block_moodletxt')
        ) .
        html_writer::empty_tag('br') .
        html_writer::link(
            new moodle_url('addressbook_groups.php', array(
                'course'      => $courseId,
                'instance'    => $instanceId,
                'addressbook' => $addressbookId
            )), get_string('linkmanagegroups', 'block_moodletxt')
        )
    ));
    
    $updateForm->display();
    
    echo(html_writer::tag('p', get_string('notifydoubleclickcontact', 'block_moodletxt'), array('style' => 'font-weight:bold;')));
}

// Table outputs as it is populated, which is why this needs to be here
foreach($contactsToDisplay as $contact) {
    
    if ($table->is_downloading())
        $firstCell = $contact->getContactId();
    else
        $firstCell = html_writer::checkbox('contactIds[]', $contact->getContactId(), false);
    
    $table->add_data(array(
        $firstCell,
        $contact->getFirstName(), 
        $contact->getLastName(), 
        $contact->getCompanyName(), 
        $contact->getRecipientNumber()->getPhoneNumber()
    ));
    
}

$table->finish_output();

if (! $table->is_downloading()) {

    if (count($contactsToDisplay) > 0)
        $deleteForm->display();
    
    echo($output->footer());
}

?>