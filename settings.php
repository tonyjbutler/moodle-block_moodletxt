<?php

/**
 * Definition of system settings for moodletxt block
 * For advanced settings, see settings_*.php
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
 * @version 2013051601
 * @since 2011041701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

// Not really supposed to do this kind of thing here, but I need the account list
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/settings/admin_setting_password_unmask_encrypted.php');

// Grab current ConnectTxt accounts from database, fo sho
$accountDAO = new TxttoolsAccountDAO();
$accountSet = array();

try {
    $accountSet = $accountDAO->getAllTxttoolsAccounts(false, false, true, false); // Check outbound is active
    
} catch (dml_read_exception $ex) {
    /* We're not really supposed to have database code in the settings file
       but we need it here to get a list of accounts. Valid use case, certainly.
       However, if the block is in place at Moodle install time, this file
       may be run before the database tables are in place, so we need this
       try-catch to stop the installation from failing! */
    
    // Do nothing - fail silently and allow installation to continue
}

$accountList = array(0 => get_string('adminselecteventsdisabled', 'block_moodletxt'));

foreach($accountSet as $account)
    $accountList[$account->getId()] = $account->getUsername() . ' (' . $account->getDescription() . ')';


// I would define this as a constant. However, Moodle
// tries to define the exact same constant later on in
// the plugins detection script without running a defined()
// check, so if I use a variable, I won't be causing messages in logs
$MONTHSECS = 2419200;


// Build links to advanced admin sections
$linkEnd = '</a>';
$accountsLink = '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/settings_accounts.php">';
$filtersLink = '<a href="' . $CFG->wwwroot . '/blocks/moodletxt/settings_filters.php">';

// This first heading is a bit of a hack, and is used
// to display links to the advanced settings pages.
// This is the best method I could find to include these
// custom pages along with the mandated settings.php structure
$settings->add(new admin_setting_heading(
            'headeradvancedlinks',
            get_string('adminheaderlinks', 'block_moodletxt'),
            $accountsLink . get_string('adminlinkaccounts', 'block_moodletxt') . $linkEnd .
            ' | ' . $filtersLink . get_string('adminlinkfilters', 'block_moodletxt') . $linkEnd
        ));



// SEND AND RECEIVE SETTINGS

$settings->add(new admin_setting_heading(
            'headersendandreceive',
            get_string('adminheadersendreceive', 'block_moodletxt'),
            get_string('admindescsendreceive', 'block_moodletxt')
        ));

$settings->add(new admin_setting_configcheckbox(
            'moodletxt/Get_Status_On_View',
            get_string('adminlabelsetautoupdate', 'block_moodletxt'),
            get_string('admindescsetautoupdate', 'block_moodletxt'),
            '1'
        ));

$settings->add(new admin_setting_configcheckbox(
            'moodletxt/Get_Inbound_On_View',
            get_string('adminlabelsetautoinbound', 'block_moodletxt'),
            get_string('admindescsetautoinbound', 'block_moodletxt'),
            '1'
        ));

$settings->add(new admin_setting_configtext(
            'moodletxt/Push_Username',
            get_string('adminlabelsetxmluser', 'block_moodletxt'),
            get_string('admindescsetxmluser', 'block_moodletxt'),
            ''
        ));

$settings->add(new admin_setting_password_unmask_encrypted(
            'moodletxt/Push_Password',
            get_string('adminlabelsetxmlpass', 'block_moodletxt'),
            get_string('admindescsetxmlpass', 'block_moodletxt'),
            ''
        ));

$settings->add(new admin_setting_configselect(
            'moodletxt/Use_Protocol',
            get_string('adminlabelprotocol', 'block_moodletxt'),
            get_string('admindescprotocol', 'block_moodletxt'),
            'SSL',
            array(
                'SSL'  => get_string('adminselectprotocolssl', 'block_moodletxt'),
                'HTTP' => get_string('adminselectprotocolhttp', 'block_moodletxt'),
            )
        ));

$settings->add(new admin_setting_configcheckbox(
            'moodletxt/Protocol_Warnings_On',
            get_string('adminlabeldisablewarn', 'block_moodletxt'),
            get_string('admindescdisablewarn', 'block_moodletxt'),
            '1'
        ));

$settings->add(new admin_setting_configselect(
            'moodletxt/Event_Messaging_Account',
            get_string('adminlabeleventaccount', 'block_moodletxt'),
            get_string('admindesceventaccount', 'block_moodletxt'),
            0,
            $accountList
        ));



// RECIPIENT SETTINGS

$settings->add(new admin_setting_heading(
            'headernumbers',
            get_string('adminheaderrecipsettings', 'block_moodletxt'),
            get_string('admindescrecipsettings', 'block_moodletxt')
        ));

$settings->add(new admin_setting_configtext(
            'moodletxt/National_Prefix',
            get_string('adminlabeldefnatprefix', 'block_moodletxt'),
            get_string('admindescdefnatprefix', 'block_moodletxt'),
            '0',
            PARAM_RAW_TRIMMED,
            '6'
        ));

$settings->add(new admin_setting_configtext(
            'moodletxt/Default_International_Prefix',
            get_string('adminlabeldefaultprefix', 'block_moodletxt'),
            get_string('admindescdefaultprefix', 'block_moodletxt'),
            '+44',
            PARAM_RAW_TRIMMED,
            '6'
        ));

$settings->add(new admin_setting_configselect(
            'moodletxt/Phone_Number_Source',
            get_string('adminlabelphonesource', 'block_moodletxt'),
            get_string('admindescphonesource', 'block_moodletxt'),
            'phone2',
            array(
                'phone2' => get_string('adminselectphone2', 'block_moodletxt'),
                'phone1' => get_string('adminselectphone1', 'block_moodletxt')
            )
        ));

$settings->add(new admin_setting_configtext(
            'moodletxt/Default_Recipient_Name',
            get_string('adminlabeldefaultname', 'block_moodletxt'),
            get_string('admindescdefaultname', 'block_moodletxt'),
            ''
        ));



// PROXY SETTINGS

$settings->add(new admin_setting_heading(
            'headerproxy',
            get_string('adminheaderproxysettings', 'block_moodletxt'),
            get_string('admindescproxysettings', 'block_moodletxt')
        ));

$settings->add(new admin_setting_configtext(
            'moodletxt/Proxy_Host',
            get_string('adminlabelproxyaddress', 'block_moodletxt'),
            get_string('admindescproxyaddress', 'block_moodletxt'),
            ''
        ));

$settings->add(new admin_setting_configtext(
            'moodletxt/Proxy_Port',
            get_string('adminlabelproxyport', 'block_moodletxt'),
            get_string('admindescproxyport', 'block_moodletxt'),
            '',
            PARAM_INT,
            '6'
        ));

$settings->add(new admin_setting_configtext(
            'moodletxt/Proxy_Username',
            get_string('adminlabelproxyusername', 'block_moodletxt'),
            get_string('admindescproxyusername', 'block_moodletxt'),
            ''
        ));

$settings->add(new admin_setting_password_unmask_encrypted(
            'moodletxt/Proxy_Password',
            get_string('adminlabelproxypassword', 'block_moodletxt'),
            get_string('admindescproxypassword', 'block_moodletxt'),
            ''
        ));



// MISCELLANEOUS SETTINGS

$settings->add(new admin_setting_heading(
            'headermisc',
            get_string('adminheadermiscsettings', 'block_moodletxt'),
            get_string('admindescmiscsettings', 'block_moodletxt')
        ));

$settings->add(new admin_setting_configcheckbox(
            'moodletxt/jQuery_Include_Enabled',
            get_string('adminlabeljqueryinclude', 'block_moodletxt'),
            get_string('admindescjqueryinclude', 'block_moodletxt'),
            '1'
        ));

$settings->add(new admin_setting_configcheckbox(
            'moodletxt/jQuery_UI_Include_Enabled',
            get_string('adminlabeljqueryuiinclude', 'block_moodletxt'),
            get_string('admindescjqueryuiinclude', 'block_moodletxt'),
            '1'
        ));

$settings->add(new admin_setting_configcheckbox(
            'moodletxt/Show_Inbound_Numbers',
            get_string('adminlabelshowinbound', 'block_moodletxt'),
            get_string('admindescshowinbound', 'block_moodletxt'),
            '0'
        ));

$settings->add(new admin_setting_configselect(
            'moodletxt/RSS_Update_Interval',
            get_string('adminlabelrssupdate', 'block_moodletxt'),
            get_string('admindescrssupdate', 'block_moodletxt'),
            DAYSECS,
            array(
                HOURSECS    => get_string('adminselectrsshourly', 'block_moodletxt'),
                DAYSECS     => get_string('adminselectrssdaily', 'block_moodletxt'),
                WEEKSECS    => get_string('adminselectrssweekly', 'block_moodletxt'),
                $MONTHSECS  => get_string('adminselectrssmonthly', 'block_moodletxt')
            )
        ));

$settings->add(new admin_setting_configselect(
            'moodletxt/RSS_Expiry_Length',
            get_string('adminlabelrssexpire', 'block_moodletxt'),
            get_string('admindescrssexpire', 'block_moodletxt'),
            WEEKSECS,
            array(
                DAYSECS     => get_string('adminselectrssexday', 'block_moodletxt'),
                WEEKSECS    => get_string('adminselectrssexweek', 'block_moodletxt'),
                $MONTHSECS  => get_string('adminselectrssexmonth', 'block_moodletxt')
            )
        ));

?>