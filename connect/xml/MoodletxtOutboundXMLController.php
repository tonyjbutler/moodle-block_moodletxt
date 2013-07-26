<?php

/**
 * File container for the MoodletxtOutboundXMLController class
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
 * @see MoodletxtOutboundXMLController
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012041801
 * @since 2010090301
*/

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundController.php');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLBuilder.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLParser.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLConnector.php');

/**
 * Controls transmissions to and from the txttools XML API
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012041801
 * @since 2010090301
*/
class MoodletxtOutboundXMLController extends MoodletxtOutboundController {

    /**
     * Builds XML for sending to txttools
     * @var MoodletxtXMLBuilder
     */
    private $XMLBuilder;

    /**
     * Parses incoming XML from txttools
     * @var MoodletxtXMLParser
     */
    private $XMLParser;

    /**
     * Sends data to and from txttools system
     * @var MoodletxtXMLConnector
     */
    private $outboundConnector;

    /**
     * Constructor - sets up processing objects
     * @version 2011041501
     * @since 2010090301
     */
    public function __construct() {
        $this->XMLBuilder = new MoodletxtXMLBuilder();
        $this->XMLParser = new MoodletxtXMLParser();
        $this->outboundConnector = new MoodletxtXMLConnector(get_config('moodletxt', 'Use_Protocol'));
                
    }

    /**
     * Sends an outbound message via the txttools system
     * @param MoodletxtOutboundMessage $outboundMessage The message to send
     * @return MoodletxtOutboundSMS[] Sent message responses
     * @version 2011040801
     * @since 2010090301
     */
    public function sendMessage(MoodletxtOutboundMessage $outboundMessage) {
        $requests = $this->XMLBuilder->buildOutboundMessage($outboundMessage);
        $response = $this->outboundConnector->sendData($requests);
        $this->XMLParser->setOutboundMessageObject($outboundMessage);
        return $this->XMLParser->parse($response);
    }

    /**
     * Updates given SMS messages with their latest status updates
     * @param MoodletxtOutboundSMS[] $sentMessages Sent messages
     * @param TxttoolsAccount $txttoolsAccount Account to check against
     * @return MoodletxtOutboundSMS[] Updated SMS messages
     * @version 2012040201
     * @since 2010090301
     */
    public function getSMSStatusUpdates($sentMessages, TxttoolsAccount $txttoolsAccount) {
        $requests = $this->XMLBuilder->buildStatusRequest($sentMessages, $txttoolsAccount);
        $response = $this->outboundConnector->sendData($requests);
        $this->XMLParser->setExistingSentMessages($sentMessages);
        return $this->XMLParser->parse($response);
    }

    /**
     * Checks the validity of newly inputted txttools account info
     * @param string $username txttools username
     * @param string $password txttools password
     * @return object[] Any parsed objects (empty with no exceptions = account valid)
     * @version 2010090301
     * @since 2010090301
     */
    public function checkAccountValidity($username, $password) {
        $this->XMLBuilder->buildAccountValidityCheck($username, $password);
        $response = $this->outboundConnector->sendData($requests);
        return $this->XMLParser->parse($response);
    }

    /**
     * Returns credit information for a given txttools account
     * @param TxttoolsAccount $txttoolsAccount Account to check
     * @return TxttoolsAccount Updated account object
     * @version 2011060701
     * @since 2011040701
     */
    public function updateAccountInfo(TxttoolsAccount $txttoolsAccount) {
        $requests = $this->XMLBuilder->buildCreditInfo($txttoolsAccount);
        $response = $this->outboundConnector->sendData($requests);
        $this->XMLParser->setTxttoolsAccountObject($txttoolsAccount);
        $parsed = $this->XMLParser->parse($response);
        return $parsed[0];
    }

    /**
     * Fetches all inbound messages for given accounts (normally triggered via cron)
     * @param TxttoolsAccount[] $txttoolsAccounts The accounts to check
     * @return MoodletxtInboundMessage[] Inbound messages found
     * @version 2012041801
     * @since 2010090301
     */
    public function getInboundMessages($txttoolsAccounts = array()) {
        $responses = array();

        $lastUpdate = get_config('moodletxt', 'Inbound_Last_Update');
        
        foreach($txttoolsAccounts as $account) {
            $requests = $this->XMLBuilder->buildInboundMessageRequest($account, 0, 'ALL', $lastUpdate);
            $response = $this->outboundConnector->sendData($requests);
            $this->XMLParser->setTxttoolsAccountObject($account); // Needed to set destination account
            $responses = array_merge($responses, $this->XMLParser->parse($response));
        }

        set_config('Inbound_Last_Update', time(), 'moodletxt');        
        
        return $responses;
    }

}

?>