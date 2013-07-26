<?php

/**
 * User preferences page
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
 * @since 2011072601
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtTemplatesDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/UserPreferencesForm.php');

$courseId = required_param('course', PARAM_INT);
$instanceId = required_param('instance', PARAM_INT);

require_login($courseId, false);
$blockcontext = context_block::instance($instanceId);
require_capability('block/moodletxt:personalsettings', $blockcontext, $USER->id);

// Load DAOs
$userDAO = new MoodletxtMoodleUserDAO();
$templateDAO = new MoodletxtTemplatesDAO();

// Set up page object and JavaScript requirements
$PAGE->set_url('/blocks/moodletxt/preferences.php');
$PAGE->set_heading(get_string('headerpreferences', 'block_moodletxt'));
$PAGE->set_title(get_string('titlepreferences', 'block_moodletxt'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_button(''); // Clear editing button
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navpreferences', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

$PAGE->requires->strings_for_js(array(
    'headertemplatesedit',
    'alertnotemplateselected',
    'alertconfirmdeletetemplate',
    'settingsedittemplate',
    'warnunicode'
), 'block_moodletxt');

if (get_config('moodletxt', 'jQuery_Include_Enabled'))
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.js', true);

$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.json.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.timers.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.selectboxes.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/preferences.js', true);

$output = $PAGE->get_renderer('block_moodletxt');

// Load custom data into form
$customData = array('course' => $courseId, 'instance' => $instanceId, 'existingTemplate' => array());
$templates = $templateDAO->getAllTemplatesForUserId($USER->id); // Load again in case of change

foreach($templates as $template)
    $customData['existingTemplate'][$template->getId()] = $template->getText();

$preferencesForm = new UserPreferencesForm(null, $customData);

// POST PROCESSING
$formData = $preferencesForm->get_data();
$notifications = '';

if ($formData != null) {

    $formData = $preferencesForm->cleanFormData($formData);
    $userConfig = $userDAO->getUserConfig($USER->id);
    $templates = $templateDAO->getAllTemplatesForUserId($USER->id);
    
    // Process submitted signatures
    if ($formData->signature != $userConfig->getUserConfig('signature'))
        $userConfig->setUserConfig('signature', $formData->signature);

    if ($formData->hideSources != $userConfig->getUserConfig('hideSources'))
        $userConfig->setUserConfig('hideSources', $formData->hideSources);
    
    if ($formData->liveUpdateInterval != $userConfig->getUserConfig('liveUpdateInterval'))
        $userConfig->setUserConfig('liveUpdateInterval', $formData->liveUpdateInterval);

    // Save config to table
    if ($userDAO->saveUserConfig($userConfig))
        $notifications .= $output->notification(get_string('notifyprefsupdated', 'block_moodletxt'), 'notifysuccess');
    else
        $notifications .= $output->notification(get_string('errorprefsupdatefail', 'block_moodletxt'), 'notifyproblem');
    
    
    // On to templates!
        
    // Delete template if necessary
    if ($formData->templateToDelete > 0) {
        
        if ($templateDAO->deleteTemplate($formData->templateToDelete, $USER->id)) {
            $preferencesForm->get_element('existingTemplate')->removeOption($formData->templateToDelete);
            $preferencesForm->clearSubmittedValues();
            
            $notifications .= $output->notification(get_string('notifytemplatedeleted', 'block_moodletxt'), 'notifysuccess');
        } else
            $notifications .= $output->notification(get_string('errortemplatedeletefail', 'block_moodletxt'), 'notifyproblem');
        
        
    // Edit template if necessary
    } else if ($formData->templateToEdit > 0) {
        
        $existingTemplate = $templateDAO->getTemplateById($formData->templateToEdit, $USER->id);
        
        if (is_object($existingTemplate)) {
            
            $existingTemplate->setText($formData->templateText);
            
            if ($templateDAO->saveTemplate($existingTemplate)) {
                $preferencesForm->get_element('existingTemplate')->removeOption($formData->templateToEdit);
                $preferencesForm->get_element('existingTemplate')->addOption($formData->templateText, $formData->templateToEdit);
                $preferencesForm->clearSubmittedValues();
            
                $notifications .= $output->notification(get_string('notifytemplateupdated', 'block_moodletxt'), 'notifysuccess');
            } else
                $notifications .= $output->notification(get_string('errortemplateupdatefail', 'block_moodletxt'), 'notifyproblem');
            
        }
        
    
    // Add template if necessary  
    } else if ($formData->templateText != '') {

        $newTemplate = new MoodletxtTemplate($USER->id, $formData->templateText);
        $saveResult = $templateDAO->saveTemplate($newTemplate);
        
        if ($saveResult > 0) {
            $preferencesForm->get_element('existingTemplate')->addOption($formData->templateText, $saveResult);
            $preferencesForm->clearSubmittedValues();
            
            $notifications .= $output->notification(get_string('notifytemplateadded', 'block_moodletxt'), 'notifysuccess');
        } else
            $notifications .= $output->notification(get_string('errortemplateinsertfail', 'block_moodletxt'), 'notifyproblem');   
        
    }
       
}


// Load initial data into form
$userConfig = $userDAO->getUserConfig($USER->id); // Reload in case of change
$initialFormData = new stdClass();
$initialFormData->signature = $userConfig->getUserConfig('signature');
$initialFormData->hideSources = $userConfig->getUserConfig('hideSources');
$initialFormData->liveUpdateInterval = $userConfig->getUserConfig('liveUpdateInterval');
$preferencesForm->set_data($initialFormData);

// OUTPUT

echo($output->header());
echo($notifications);
$preferencesForm->display();
echo($output->footer());

?>