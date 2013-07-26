<?php

/**
 * Settings page for setting up inbound filters
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
 * @since 2011062901
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtInboundFilterDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/MoodletxtFiltersForm.php');

require_login();
require_capability('block/moodletxt:adminsettings', context_system::instance());

// Set up DAOs
$accountDAO = new TxttoolsAccountDAO();
$userDAO = new MoodletxtMoodleUserDAO();
$filterDAO = new MoodletxtInboundFilterDAO();

admin_externalpage_setup('manageblocks'); // Shortcut function sets up page for block admin

// Set up the page
$PAGE->set_url('/blocks/moodletxt/settings_filters.php');
$PAGE->set_heading(get_string('adminheaderaccountslist', 'block_moodletxt'));
$PAGE->set_title(get_string('admintitleaccountlist', 'block_moodletxt'));
$PAGE->set_docs_path('admin/setting/moodletxtfilters'); // External admin pages get their MoodleDocs links messed up
$PAGE->set_button(''); // Clear editing button

$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), $CFG->wwwroot . '/admin/settings.php?section=blocksettingmoodletxt', navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navfilters', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

// JS/CSS includes and language requirements
$PAGE->requires->strings_for_js(array(
    'adminaccountfragloading',
    'adminlabelfilterusersearch'
), 'block_moodletxt');

if (get_config('moodletxt', 'jQuery_Include_Enabled')) {
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.js', true);
}

if (get_config('moodletxt', 'jQuery_UI_Include_Enabled')) {
    $PAGE->requires->css('/blocks/moodletxt/style/jquery.ui.css');
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.ui.js', true);
}

$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.json.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.selectboxes.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/settings_filters.js', true);

$accountList = $accountDAO->getAllTxttoolsAccounts(false, true, false, true);

// Create form and initialise data
$customData = array('filterAccountList' => array(0 => ''));

foreach ($accountList as $account)
    $customData['filterAccountList'][$account->getId()] = $account->getUsername();

$filterForm = new MoodletxtFiltersForm(null, $customData);
$formData = $filterForm->get_data();

// Form processing
if ($formData != null) {

    $formData = $filterForm->cleanupFormData($formData);
    $usersOnFilter = array();
 
    // Run over the users selected for the filter and ensure they
    // are ready for saving
    if (isset($formData->usersOnFilter) && is_array($formData->usersOnFilter))
        $usersOnFilter = $userDAO->getUsersById($formData->usersOnFilter);
        
    // Create or fetch filter object 
    if ($formData->newKeywordFilter != '' || $formData->newPhoneNumberFilter != '') {
        
        $type = ($formData->newKeywordFilter != '') ? MoodletxtInboundFilter::$FILTER_TYPE_KEYWORD : MoodletxtInboundFilter::$FILTER_TYPE_PHONE_NUMBER;
        $value = ($formData->newKeywordFilter != '') ? $formData->newKeywordFilter : $formData->newPhoneNumberFilter;
            
        $filter = new MoodletxtInboundFilter($formData->filterAccountList, $type, $value);
        
    } else {
        
        $filterId = ($formData->existingKeywordFilterList > 0) ? $formData->existingKeywordFilterList : $formData->existingPhoneNumberFilterList;
        $filter = $filterDAO->getFilterById($filterId);
        
    }
    
    // The easiest way of doing the filter links, rather than my old method
    // of finding the ones to remove and the ones to add, is simply to drop
    // them all and replace them with what's in the form. So let's do that!
    $filter->clearDestinationUsers();
    $filter->setDestinationUsers($usersOnFilter);
        
    $filterDAO->saveFilter($filter);
    
}

$output = $PAGE->get_renderer('block_moodletxt');

// Chuck the page out and go home
echo($output->header());
$filterForm->display();
echo($output->footer());

?>