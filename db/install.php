<?php

/**
 * Post-installation processing file for moodletxt 3.0
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
 * @package uk.co.moodletxt.db
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012062401
 * @since 2010082401
 */

/**
 * Provides the necessary post-installation
 * processing to the Moodle API
 * @global moodle_database $DB Database manager
 * @version 2012103001
 * @since 2010082401
 */
function xmldb_block_moodletxt_install() {

    global $DB;

    // Get defaults set here in case the user skips the settings screen at install
    set_config('Get_Status_On_View',            '1',        'moodletxt');
    set_config('Get_Inbound_On_View',           '1',        'moodletxt');
    set_config('Push_Username',                 '',         'moodletxt');
    set_config('Push_Password',                 '',         'moodletxt');
    set_config('Use_Protocol',                  'SSL',      'moodletxt');
    set_config('Protocol_Warnings_On',          '1',        'moodletxt');
    set_config('RSS_Last_Update',               '0',        'moodletxt');
    set_config('RSS_Update_Interval',           '86400',    'moodletxt');
    set_config('RSS_Expiry_Length',             '604800',   'moodletxt');
    set_config('Default_International_Prefix',  '+44',      'moodletxt');
    set_config('National_Prefix',               '0',        'moodletxt');
    set_config('Phone_Number_Source',           'phone2',   'moodletxt');
    set_config('Show_Inbound_Numbers',          '0',        'moodletxt');
    set_config('Proxy_Host',                    '',         'moodletxt');
    set_config('Proxy_Port',                    '',         'moodletxt');
    set_config('Proxy_Username',                '',         'moodletxt');
    set_config('Proxy_Password',                '',         'moodletxt');
    set_config('jQuery_Include_Enabled',        '1',        'moodletxt');
    set_config('jQuery_UI_Include_Enabled',     '1',        'moodletxt');
    set_config('Event_Messaging_Account',       '0',        'moodletxt');
    
    // If you're installing 3.x or above, mark the upgrades in 3.0 as already having been run
    set_config('Passwords_Upgraded_3_0',        '1',        'moodletxt');
    set_config('Settings_Encrypted_3_0',        '1',        'moodletxt');

    // Config settings that require a wee bit more processing
    set_config('EK', substr(md5(mt_rand()), 0, 10), 'moodletxt');
    set_config('Default_Recipient_Name', get_string('configdefaultrecipient', 'block_moodletxt'), 'moodletxt');
    set_config('Inbound_Last_Update', time(), 'moodletxt');
    
}

?>