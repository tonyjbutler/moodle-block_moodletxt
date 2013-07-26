<?php

/**
 * File container for the MoodletxtOutboundController class
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
 * @see MoodletxtOutboundController
 * @package uk.co.moodletxt.connect
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012040301
 * @since 2011040701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundMessage.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/TxttoolsAccount.php');

/**
 * All outbound controllers, be they SOAP, XML, REST, etc, must extend this class
 * @package uk.co.moodletxt.connect
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012040301
 * @since 2011040701
 */
abstract class MoodletxtOutboundController {

    /**
     * Represents the SSL setting for the Use_Protocol config property
     * @var string
     */
    public static $CONNECTION_TYPE_SSL = 'SSL';

    /**
     * Represents the HTTP setting for the Use_Protocol config property
     * @var string
     */
    public static $CONNECTION_TYPE_HTTP = 'HTTP';


    /**
     * Sends an outbound message via the txttools system
     * @param MoodletxtOutboundMessage $outboundMessage The message to send
     * @return MoodletxtOutboundSMS[] Sent message responses
     * @version 2011040701
     * @since 2011040701
     */
    abstract function sendMessage(MoodletxtOutboundMessage $outboundMessage);

    /**
     * Returns SMS status updates for a given message
     * @param array(MoodletxtOutboundSMS) $sentMessages Sent messages
     * @param TxttoolsAccount $txttoolsAccount Account to check against
     * @return MoodletxtOutboundSMS[] Updated SMS messages
     * @version 2011040801
     * @since 2011040801
     */
    abstract function getSMSStatusUpdates($sentMessages, TxttoolsAccount $account);

    /**
     * Checks the validity of newly inputted txttools account info
     * @param string $username txttools username
     * @param string $password txttools password
     * @return object[] Any parsed objects (empty with no exceptions = account valid)
     * @version 2011040701
     * @since 2011040701
     */
    abstract function checkAccountValidity($username, $password);

    /**
     * Returns credit information for a given txttools account
     * @param TxttoolsAccount $txttoolsAccount Account to check
     * @return TxttoolsAccount Updated account object
     * @version 2011040701
     * @since 2011040701
     */
    abstract function updateAccountInfo(TxttoolsAccount $txttoolsAccount);

    /**
     * Fetches all inbound messages for given accounts (normally triggered via cron)
     * @param array(TxttoolsAccount) $txttoolsAccounts The accounts to check
     * @return MoodletxtInboundMessage[] Inbound messages found
     * @version 2011040701
     * @since 2011040701
     */
    abstract function getInboundMessages($txttoolsAccounts = array());

}

?>
