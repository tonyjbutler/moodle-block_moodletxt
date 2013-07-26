<?php

/**
 * txttools accounts page
 * Shows all txttools accounts stored along with access controls
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
 * @since 2011042601
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsSentMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/TxttoolsAccountEditForm.php');
require_once($CFG->dirroot . '/blocks/moodletxt/forms/TxttoolsAccountRestrictionsForm.php');

require_login();
require_capability('block/moodletxt:adminsettings', context_system::instance());

// OK, so you're legit. Let's load DAOs
$accountDAO = new TxttoolsAccountDAO();
$sentMessageDAO = new TxttoolsSentMessageDAO();

// Grab account details - if there are no accounts to see,
// send the user to the new installation screen
$accountList = $accountDAO->getAllTxttoolsAccounts();

if (count($accountList) == 0)
    redirect($CFG->wwwroot . '/blocks/moodletxt/settings_accounts_new.php', get_string('redirectnoaccountsfound', 'block_moodletxt'));

// Account IDs are passed to JS for AJAX transactions
// Shifted up to match onscreen table rows
$accountIds = array();

// To further complicate things, Moodle 2.4 and above
// actually render tables correctly, with <tbody> tags
// and everything, so the index is different
$accountTableIndex = ($CFG->version >= 2012120300) ? 1 : 2;

foreach($accountList as $accountId => $account) {
    $accountIds[$accountTableIndex++] = $accountId;
}
    
/*
 * Set up page
 */
admin_externalpage_setup('manageblocks'); // Shortcut function sets up page for block admin

$PAGE->set_url('/blocks/moodletxt/settings_accounts.php');
$PAGE->set_heading(get_string('adminheaderaccountslist', 'block_moodletxt'));
$PAGE->set_title(get_string('admintitleaccountlist', 'block_moodletxt'));
$PAGE->set_button(''); // Clear editing button
$PAGE->set_focuscontrol('id_accountName'); // Focus username field on load
$PAGE->set_docs_path('admin/setting/moodletxtaccounts'); // External admin pages get their MoodleDocs links messed up
$PAGE->navbar->add(get_string('navmoodletxt', 'block_moodletxt'), $CFG->wwwroot . '/admin/settings.php?section=blocksettingmoodletxt', navigation_node::TYPE_CUSTOM, 'moodletxt');
$PAGE->navbar->add(get_string('navaccounts', 'block_moodletxt'), null, navigation_node::TYPE_CUSTOM, 'moodletxt');

// JS/CSS includes and language requirements
$PAGE->requires->strings_for_js(array(
    'adminaccountconfirmupdate',
    'adminaccountfragloading',
    'adminaccountprocessedfrag',
    'adminaccountupdatefailed',
    'adminaccountupdatesuccess',
    'altaccessoutbound',
    'altaccessinbound',
    'altaccessdenied',
    'altaccessedit',
    'billingtypeinvoiced',
    'billingtypeprepaid',
    'erroroperationinprogress',
    'headeraccountrestrictionsfor'
), 'block_moodletxt');

if (get_config('moodletxt', 'jQuery_Include_Enabled')) {
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.js', true);
}

if (get_config('moodletxt', 'jQuery_UI_Include_Enabled')) {
    $PAGE->requires->css('/blocks/moodletxt/style/jquery.ui.css');
    $PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.ui.js', true);
}

$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.json.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.timers.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.colour.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib/jquery.selectboxes.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/lib.js', true);
$PAGE->requires->js('/blocks/moodletxt/js/settings_accounts.js', true);

$PAGE->requires->js_init_call('receiveAccountIds', array($accountIds));

$output = $PAGE->get_renderer('block_moodletxt');


/*
 * Create results table
 */
$table = new flexible_table('block-moodletxt-accountlist');
$table->define_baseurl($CFG->wwwroot . '/blocks/moodletxt/settings_accounts.php'); // Required in 2.2 for export
$table->set_attribute('id', 'accountListTable');
$table->set_attribute('class', 'generaltable generalbox boxaligncenter boxwidthwide');
$table->collapsible(true);

// Set structure
$tablecolumns = array("username", "description", "messagessent", "allowoutbound", 
    "allowinbound", "creditsused", "creditsremaining", "accounttype", "lastupdate");

$tableheaders = array(
    get_string('tableheaderusername',        'block_moodletxt'),
    get_string('tableheaderdescription',     'block_moodletxt'),
    get_string('tableheadermessages',        'block_moodletxt'),
    get_string('tableheaderallowoutbound',   'block_moodletxt'),
    get_string('tableheaderallowinbound',    'block_moodletxt'),
    get_string('tableheadercreditsused',     'block_moodletxt'),
    get_string('tableheadercreditsleft',     'block_moodletxt'),
    get_string('tableheaderaccounttype',     'block_moodletxt'),
    get_string('tableheaderlastupdate',      'block_moodletxt')
);

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->setup();


/*
 * Let's get to the output, baby
 */
$editImage     = new moodletxt_icon(moodletxt_icon::$ICON_EDIT, get_string('altaccountedit', 'block_moodletxt'), array('class' => 'mdltxtClickableIcon', 'style' => 'float:left;'));
$accessImage   = new moodletxt_icon(moodletxt_icon::$ICON_ACCESS_EDIT, get_string('altaccessedit', 'block_moodletxt'), array('class' => 'mdltxtClickableIcon', 'style' => 'float:left;'));
$deniedImage   = new moodletxt_icon(moodletxt_icon::$ICON_ACCESS_DENIED, get_string('altaccessdenied', 'block_moodletxt'), array('class' => 'mdltxtClickableIcon'));
$outboundImage = new moodletxt_icon(moodletxt_icon::$ICON_ALLOW_OUTBOUND, get_string('altaccessoutbound', 'block_moodletxt'), array('class' => 'mdltxtClickableIcon'));
$inboundImage  = new moodletxt_icon(moodletxt_icon::$ICON_ALLOW_INBOUND, get_string('altaccessinbound', 'block_moodletxt'), array('class' => 'mdltxtClickableIcon'));

echo($output->header());

// Page intro
echo($output->box(
    $output->heading(get_string('adminheaderaccountslist', 'block_moodletxt')) .
    html_writer::tag('p', get_string('adminaccountintropara1', 'block_moodletxt')) .
    html_writer::tag('p', get_string('adminaccountintropara2', 'block_moodletxt')) .
    html_writer::tag('p',
        html_writer::tag('a', get_string('linkaddaccount', 'block_moodletxt'), array('href' => $CFG->wwwroot . '/blocks/moodletxt/settings_accounts_new.php'))
    )   
));

// Update button and progress bar
echo(html_writer::tag('button', get_string('adminaccountbutupdateall', 'block_moodletxt'), array('id' => 'mdltxtUpdateAllAccounts')));
echo($output->render(new moodletxt_ui_progress_bar('accountProgressBar', 'accountProgressTextValue')));

// Populate table
foreach($accountList as $account) {

    if ($account->getLastUpdate() > 0)
        $lastUpdate = userdate($account->getLastUpdate(), "%H:%M:%S,  %d %B %Y");
    else
        $lastUpdate = get_string('adminaccountfragneverupdated', 'block_moodletxt');

    $outboundEnabled = ($account->isOutboundEnabled()) ? $outboundImage : $deniedImage;
    $inboundEnabled = ($account->isInboundEnabled()) ? $inboundImage : $deniedImage;

    $creditsRemaining = ($account->getBillingType() == TxttoolsAccount::$BILLING_TYPE_INVOICED) ?
            "&infin;" :
            $account->getCreditsRemaining();

    $accountTypeString = ($account->getBillingType() == TxttoolsAccount::$BILLING_TYPE_PREPAID) ?
            get_string('billingtypeprepaid', 'block_moodletxt') :
            get_string('billingtypeinvoiced', 'block_moodletxt');

    $table->add_data(array(
        $output->render($editImage) . $account->getUsername(),
        $account->getDescription(),
        $sentMessageDAO->countMessagesSent($account->getId()),
        $output->render($accessImage) . $output->render($outboundEnabled),
        $output->render($inboundEnabled),
        $account->getCreditsUsed(),
        $creditsRemaining,
        $accountTypeString,
        $lastUpdate
    ));

}

$table->finish_output();

// Dialog box used for account editing form
$editForm = new TxttoolsAccountEditForm();
$editDialog = new moodletxt_ui_dialog(
    'accountEditDialog', 
    $editForm->toHtml(), 
    get_string('titleaccountedit', 'block_moodletxt'), 
    '', 
    array('style' => 'display:none;')
);

// Dialog box used for outbound access restrictions
$restrictionsForm = new TxttoolsAccountRestrictionsForm();
$dialog = new moodletxt_ui_dialog(
    'mdltxtAccountRestrictionsDialog', 
    $restrictionsForm->toHtml(), 
    get_string('titleaccountrestrictions', 'block_moodletxt'), 
    '', array('style' => 'display:none;')
);

echo($output->render($editDialog));
echo($output->render($dialog));

echo($output->footer());

?>