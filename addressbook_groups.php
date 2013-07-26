<?php

/**
 * View used to manage groups within a user's addressbook
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
 * @since 2012092401
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtGroupAddForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtGroupEditForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtGroupDeleteForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/renderers/QuickFormRendererWithSlides.php');

$courseId       = required_param('course',      PARAM_INT);
$instanceId     = required_param('instance',    PARAM_INT);
$addressbookId  = required_param('addressbook', PARAM_INT);

require_login($courseId, false);
$blockcontext = context_block::instance($instanceId);
require_capability('block/moodletxt:addressbooks', $blockcontext, $USER->id);

// OK, so you're legit. Let's load DAOs
$addressbookDAO = new MoodletxtAddressbookDAO();
if (! $addressbookDAO->checkAddressbookOwnership($addressbookId, $USER->id))
    print_error('errorbadbookid', 'block_moodletxt');

$addressbook = $addressbookDAO->getAddressbookById($addressbookId, $USER->id);


// SETUP PAGE
$PAGE->set_url('/blocks/moodletxt/addressbook_groups.php');
$PAGE->set_title(get_string('titlegroupsmanage', 'block_moodletxt') . ' "' . $addressbook->getName() . '"');
$PAGE->set_heading(get_string('headergroupsmanage', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button

$addressbookNav = new moodle_url('/blocks/moodletxt/addressbooks.php', array(
    'course'    => $courseId, 
    'instance'  => $instanceId
));

$addressbookEditNav = new moodle_url('/blocks/moodletxt/addressbook_view.php', array(
    'course'      => $courseId, 
    'instance'    => $instanceId,
    'addressbook' => $addressbook->getId()
));

$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navaddressbooks', 'block_moodletxt'), $addressbookNav, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add($addressbook->getName(), $addressbookEditNav, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navcontactadd', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

if (get_config('moodletxt', 'jQuery_Include_Enabled'))
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.js', true);

$PAGE->requires->js('/blocks/moodletxt/js/lib.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/qfamsHandler.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/addressbook_groups.js', true);


// SETUP FORMS WITH INITIAL DATA
$output = $PAGE->get_renderer('block_moodletxt');

$initialFormData = array(
    'course'                => $courseId,
    'instance'              => $instanceId,
    'addressbook'           => $addressbook->getId()
);

$customFormData = array(
    'existingGroups'      => array(),
    'potentialContacts'   => array()
);


$groupList = $addressbookDAO->getAddressbookGroupsForUser($USER->id, $addressbook->getId());
$contactList = $addressbookDAO->getAddressbookContactsForUser($USER->id, $addressbook->getId());

foreach($groupList as $group)
    $customFormData['existingGroups'][$group->getId()] = $group->getName();    

foreach($contactList as $contact)
    $customFormData['potentialContacts'][$contact->getContactId()] = $contact->getFullNameForDisplay();

$GLOBALS['_HTML_QuickForm_default_renderer'] = new QuickFormRendererWithSlides(); // Override renderer for multi-select
$notifications = '';



// POST PROCESSING FOR ADD FORM
$addForm = new MoodletxtGroupAddForm(null, $customFormData); // Already have this data out, so pass it for inline validation

$addData = $addForm->get_data();

if ($addData != null) {
    
    $addData = $addForm->cleanupFormData($addData);
    $newGroup = new MoodletxtAddressbookGroup($addData->newGroupName, $addressbook->getId());
    
    $addressbookDAO->saveGroup($newGroup);    
    $addForm->clearSubmittedValues();
    
    $notifications .= $output->notification(get_string('notifygroupadded', 'block_moodletxt'), 'notifysuccess');
        
    // Update list of groups for forms that follow
    $customFormData['existingGroups'][$newGroup->getId()] = $newGroup->getName();
    natcasesort($customFormData['existingGroups']);

}



// POST PROCESSING FOR DELETION FORM
$deleteForm = new MoodletxtGroupDeleteForm(null, $customFormData);

$deleteData = $deleteForm->get_data();

if ($deleteData != null) {
    
    // Nuke group DB records - switch actions according to radio buttons
    $deleteData = $deleteForm->cleanupFormData($deleteData);
    $nukeContacts = ($deleteData->deleteGroupAction == 'delete');
    $mergeId = ($deleteData->deleteGroupAction == 'merge') ? $deleteData->groupToMerge : 0;
    
    $addressbookDAO->deleteOrMergeGroupById($addressbook->getId(), $deleteData->groupToDelete, $nukeContacts, $mergeId);
    $deleteForm->clearSubmittedValues();

    $notifications .= $output->notification(get_string('notifygroupdeleted', 'block_moodletxt'), 'notifysuccess');
    
    // Update list of groups in this form
    $deleteForm->get_element('groupToDelete')->removeOption($deleteData->groupToDelete);
    $deleteForm->get_element('groupToMerge')->removeOption($deleteData->groupToDelete);
    
    // Update list of groups and contacts for forms that follow
    // (Updating group list is easy but contacts may need a full refresh)
    unset($customFormData['existingGroups'][$deleteData->groupToDelete]);
    
    if ($nukeContacts) {

        $customFormData['potentialContacts'] = array(); // Flattens existing data
        $contactList = $addressbookDAO->getAddressbookContactsForUser($USER->id, $addressbook->getId());
        
        foreach($contactList as $contact)
            $customFormData['potentialContacts'][$contact->getContactId()] = $contact->getFullNameForDisplay();
        
    }
    
}



// POST PROCESSING FOR EDIT FORM
$editForm = new MoodletxtGroupEditForm(null, $customFormData);

$editData = $editForm->get_data();

if ($editData != null) {
    
    $editData = $editForm->cleanupFormData($editData);
        
    $groupObject = $addressbookDAO->getAddressbookGroupById($addressbook->getId(), $editData->editExistingGroup);
    
    if ($groupObject != null) {
        
        // Make sure collection is initialised, but empty
        $groupObject->setContacts(array());
        
        foreach($editData->editGroupMembers as $contactId)
            $groupObject->addContact($addressbookDAO->getAddressbookContactById($addressbook->getId(), $contactId));
        
        $addressbookDAO->saveGroup($groupObject);
        $editForm->clearSubmittedValues();
        $notifications .= $output->notification(get_string('notifygroupupdated', 'block_moodletxt'), 'notifysuccess');
        
    }
    
}



// Populate group-contact associations
// after all updates are complete
$groupAssociationList = $addressbookDAO->getAddressbookGroupsForUser($USER->id, $addressbook->getId(), true);

foreach($groupAssociationList as $group) {
    
    // Populate JS with contact IDs for each group
    if ($group->hasContacts()) {
        $contactIds = array();
        
        foreach($group->getContacts() as $contact)
            array_push($contactIds, $contact->getContactId());
        
        $PAGE->requires->js_init_call('receiveGroupContactAssociations', array($group->getId(), $contactIds));
    }
    
}

// Initialise form defaults
$addForm->set_data($initialFormData);
$editForm->set_data($initialFormData);
$deleteForm->set_data($initialFormData);


// BEGIN PAGE OUTPUT
echo($output->header());
echo($notifications);

$addForm->display();
$editForm->display();
$deleteForm->display();

echo($output->footer());

?>