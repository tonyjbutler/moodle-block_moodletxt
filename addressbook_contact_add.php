<?php

/**
 * View used to add a contact to the user's addressbook
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
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtContactAddForm.php');
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
$PAGE->set_url('/blocks/moodletxt/addressbook_contact_add.php');
$PAGE->set_title(get_string('titlecontactadd', 'block_moodletxt') . ' "' . $addressbook->getName() . '"');
$PAGE->set_heading(get_string('headercontactadd', 'block_moodletxt'));
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

// SETUP FORM
$output = $PAGE->get_renderer('block_moodletxt');

$initialFormData = array(
    'course'                => $courseId,
    'instance'              => $instanceId,
    'addressbook'           => $addressbook->getId()
);

$customFormData = array(
    'potentialGroups'       => array()
);

$groupList = $addressbookDAO->getAddressbookGroupsForUser($USER->id, $addressbook->getId());

foreach($groupList as $group)
    $customFormData['potentialGroups'][$group->getId()] = $group->getName();

$GLOBALS['_HTML_QuickForm_default_renderer'] = new QuickFormRendererWithSlides(); // Override renderer for multi-select
$contactForm = new MoodletxtContactAddForm(null, $customFormData);
$notifications = '';

// POST PROCESSING
$formData = $contactForm->get_data();

if ($formData != null) {
    
    $formData = $contactForm->cleanupFormData($formData);
    
    // Create contact, add groups, save to DB
    $newContact = new MoodletxtAddressbookRecipient(
            new MoodletxtPhoneNumber($formData->phoneNumber), 
            $formData->firstName, 
            $formData->lastName,
            $formData->company,
            0,
            $addressbook->getId()
    );
    
    foreach($formData->groups as $groupId)
        $newContact->addGroup($addressbookDAO->getAddressbookGroupById($formData->addressbook, $groupId));
    
    $addressbookDAO->saveContact($newContact);
    
    // Based on which button was pressed, we either send the
    // user back to the addressbook or clear the form
    if ($formData->submitButton == get_string('buttoncontactaddreturn', 'block_moodletxt')) {
        
        $addressbookPageUrl = new moodle_url('/blocks/moodletxt/addressbook_view.php', array(
            'course'      => $courseId,
            'instance'    => $instanceId,
            'addressbook' => $addressbookId
        ));

        redirect($addressbookPageUrl, get_string('redirectcontactadded', 'block_moodletxt'));
            
    } else if ($formData->submitButton == get_string('buttoncontactadd', 'block_moodletxt')) {
        
        $contactForm->clearSubmittedValues();            
        $notifications .= $output->notification(get_string('notifycontactadded', 'block_moodletxt'), 'notifysuccess');
        
    }
    
}

// Initialise form defaults
$contactForm->set_data($initialFormData);


// BEGIN PAGE OUTPUT
echo($output->header());
echo($notifications);    
$contactForm->display();
echo($output->footer());

?>