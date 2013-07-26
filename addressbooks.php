<?php

/**
 * Addressbook index page
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
 * @since 2012071701
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtAddressbookManagementForm.php');

$courseId       = required_param('course',   PARAM_INT);
$instanceId     = required_param('instance', PARAM_INT);

require_login($courseId, false);
$blockcontext = context_block::instance($instanceId);
require_capability('block/moodletxt:addressbooks', $blockcontext, $USER->id);

// OK, so you're legit. Let's load DAOs
$addressbookDAO = new MoodletxtAddressbookDAO();
$addressbookList = $addressbookDAO->getAddressbooksForUser($USER->id);

$PAGE->set_url('/blocks/moodletxt/addressbooks.php');
$PAGE->set_title(get_string('titleaddressbooks', 'block_moodletxt') . ' ' . $USER->lastname . ', ' . $USER->firstname);
$PAGE->set_heading(get_string('headeraddressbooks', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navaddressbooks', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

$output = $PAGE->get_renderer('block_moodletxt');

// Builds the form object according to which addressbooks
// are available to the user, and whether they can create
// global addressbooks
$customFormData = array(
    'existingAddressbooks'  => array(0 => '')
);

$initialFormData = array(
    'course'                => $courseId,
    'instance'              => $instanceId
);

foreach($addressbookList as $addressbook)
    $customFormData['existingAddressbooks'][$addressbook->getId()] = $addressbook->getName();

$addressbookForm = new MoodletxtAddressbookManagementForm(
    has_capability('block/moodletxt:globaladdressbooks', $blockcontext, $USER->id),
    null, $customFormData
);
$addressbookForm->set_data($initialFormData);

// Post processing starts here
$notifications = '';
$formData = $addressbookForm->get_data();

if ($formData != null) {
    
    $formData = $addressbookForm->cleanupFormData($formData);
    
    if ($formData->newAddressbookName != '' && $formData->submitButton == get_string('buttonadd', 'block_moodletxt')) {
        
        $newAddressbook = new MoodletxtAddressbook((int) $USER->id, $formData->newAddressbookName, $formData->newAddressbookType);                
        $addressbookDAO->saveAddressbook($newAddressbook);
        
        // Drop new addressbook into form
        $addressbookForm->get_element('existingAddressbook')->addOption($newAddressbook->getName(), $newAddressbook->getId());
        $addressbookForm->get_element('mergeAddressbook')->addOption($newAddressbook->getName(), $newAddressbook->getId());
        $addressbookForm->clearSubmittedValues();
        
        $notifications .= $output->notification(get_string('notifyaddressbookadded', 'block_moodletxt'), 'notifysuccess');
        
    }
    
    if ($formData->existingAddressbook > 0 && $formData->submitButton == get_string('buttondeleteormerge', 'block_moodletxt')) {
        
        // Grab addressbook from database to check ownership (security measure)
        // (This should technically never happen, as PEAR's form library validates select values)
        if (! $addressbookDAO->checkAddressbookOwnership($formData->existingAddressbook, $USER->id)){
            
            $notifications .= $output->notification(get_string('errorbooknotowned', 'block_moodletxt'), 'notifyproblem');
            
        } else {
            
            // If-else technically isn't needed, as merge param is optional, but this just feels...safer
            if ($formData->deleteExistingContacts == 'merge' && $formData->mergeAddressbook > 0)
                $addressbookDAO->deleteOrMergeAddressbookById($formData->existingAddressbook, $formData->mergeAddressbook);

            else
                $addressbookDAO->deleteOrMergeAddressbookById($formData->existingAddressbook);

            $notifications .= $output->notification(get_string('notifyaddressbookdeleted', 'block_moodletxt'), 'notifysuccess');
            
            $addressbookForm->get_element('existingAddressbook')->removeOption($formData->existingAddressbook);
            $addressbookForm->get_element('mergeAddressbook')->removeOption($formData->existingAddressbook);
            $addressbookForm->clearSubmittedValues();
            
        }
        
    }
    
}

// Re-synchronise address book list with DB in case of changes
$addressbookOutputSet = $addressbookDAO->getAddressbooksForUser($USER->id);
$addressbookOutputList = '';

if (count($addressbookOutputSet) > 0) {

    $listContent = '';
    $heading = $output->heading(get_string('headeraddressbooksexisting', 'block_moodletxt'));
    $clearer = html_writer::tag('div', '', array('class' => 'mdltxtCleared'));
    
    foreach($addressbookOutputSet as $addressbookEntry) {
        $className = ($addressbookEntry->getType() == MoodletxtAddressbook::$ADDRESSBOOK_TYPE_GLOBAL) ? 'mdltxtGlobalAddressbook' : 'mdltxtPrivateAddressbook';

        $addressbookLink = new moodle_url('/blocks/moodletxt/addressbook_view.php', array(
            'course'      => $courseId,
            'instance'    => $instanceId,
            'addressbook' => $addressbookEntry->getId()
        ));

        $listContent .= html_writer::tag(
            'li', 
            html_writer::link($addressbookLink, $addressbookEntry->getName()), 
            array('class' => $className)
        );
    }

    $addressbookOutputList = $output->box(
        $heading . html_writer::tag('ul', $listContent) . $clearer,
        'generalbox',
        'mdltxtAddressbookList'
    );
    
}

// BEGIN PAGE OUTPUT
echo($output->header());
echo($addressbookOutputList);
echo($notifications);
$addressbookForm->display();
echo($output->footer());

?>