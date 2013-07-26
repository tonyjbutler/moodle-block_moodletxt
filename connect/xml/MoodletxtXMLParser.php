<?php

/**
 * File container for MoodletxtXMLParser class
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
 * @see MoodletxtXMLParser
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012101001
 * @since 2011033101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundSMS.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundSMSStatus.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtInboundMessage.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtRemoteProcessingException.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLConstants.php');

/**
 * XML SAX parser for parsing responses from the ConnectTxt system into meaningful objects
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012101001
 * @since 2011033101
 */
class MoodletxtXMLParser {

    /**
     * Holds an array of target elements  that we want to process. The contents of these elements are stored and passed to the method specified in this array (element => function)
     * @var array
     */
    private $targetElements = array();

    /**
     * Stores a reference to a top-level outbound message if we are sending, to be used for recipient lookup
     * @var MoodletxtOutboundMessage
     */
    private $outboundMessageObject = null;

    /**
     * Holds a reference to a txttools account needed for reference during parsing
     * @var TxttoolsAccount
     */
    private $txttoolsAccountObject = null;
    
    /**
     * Holds a reference to previously sent SMS messages that are being updated
     * @var MoodletxtOutboundSMS[]
     */
    private $existingSentMessages = array();

    /**
     * Set up the parser.
     * Sets up a PHP SAX parser for use in parsing XML data.
     * @version 2011033101
     * @since 2011033101
     */
    function __construct() {

        // Define what elements we're interested in,
        // and what method to call when they are encountered (completed)
        $this->targetElements['/' . MoodletxtXMLConstants::$RESPONSE_ROOT . '/' . MoodletxtXMLConstants::$RESPONSE_ERROR_BLOCK]     = 'throwProcessingException';
        $this->targetElements['/' . MoodletxtXMLConstants::$RESPONSE_ROOT . '/' . MoodletxtXMLConstants::$RESPONSE_STATUS_BLOCK]    = 'buildStatusMessage';
        $this->targetElements['/' . MoodletxtXMLConstants::$RESPONSE_ROOT . '/' . MoodletxtXMLConstants::$RESPONSE_INBOUND_BLOCK]   = 'buildInboundMessage';
        $this->targetElements['/' . MoodletxtXMLConstants::$RESPONSE_ROOT . '/' . MoodletxtXMLConstants::$RESPONSE_ACCOUNT_BLOCK]   = 'buildAccountdetail';

    }

    /**
     * Method called by other objects to parse XML packets passed in.
     * @param string[] $xmlpackets Packets to parse
     * @return object[] Response objects built from parsed XML
     * @throws MoodletxtRemoteProcessingException
     * @version 2012040201
     * @since 2011033101
     */
    public function parse($xmlpackets) {

        $returnObjects = array();

        // Ensure packets come in as array
        if (! is_array($xmlpackets))
            $xmlpackets = array($xmlpackets);

        foreach($xmlpackets as $packet) {

            try {
                $returnObjects = array_merge($returnObjects, $this->parsePacket($packet));

            } catch (MoodletxtRemoteProcessingException $exception) {
                throw $exception;
            }
        }
        
        // Include updated SMS messages in response objects
        $returnObjects = array_merge($returnObjects, $this->getExistingSentMessages());

//        error_log(get_string('logxmlparsedobjects', 'block_moodletxt') . "\r\n\r\n" . print_r($returnObjects, true));

        return $returnObjects;

    }

    /**
     * Parses the current XML packet and gets array nodes
     * @param string $packet XML packet to parse
     * @return object[] Response objects from parsed XML
     * @throws MoodletxtRemoteProcessingException
     * @version 2012101001
     * @since 2011033101
     */
    private function parsePacket($packet) {

        $returnObjects = array();

        $parsedObject = simplexml_load_string($packet);

        if ($parsedObject === false) {
            throw new MoodletxtRemoteProcessingException(
                'Could not parse the incoming XML. Check your character encoding and that the XML being parsed is valid.'
            );
        }
        
        try {

            foreach ($this->targetElements as $targetPath => $targetMethod) {

                $nodes = $parsedObject->xpath($targetPath);

                foreach($nodes as $node)
                    $returnObjects = array_merge($returnObjects, $this->$targetMethod($node));

            }

        } catch (MoodletxtRemoteProcessingException $exception) {
            throw $exception;
        }

        return $returnObjects;

    }

    /**
     * Creates a processing exception from a captured error block and throws it
     * @param SimpleXMLElement $node The node collection to create the exception from
     * @throws MoodletxtRemoteProcessingException
     * @version 2011033101
     * @since 2011033101
     */
    private function throwProcessingException($node) {

        if (isset($node->{MoodletxtXMLConstants::$RESPONSE_ERROR_CODE}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_ERROR_MESSAGE})) {

            throw new MoodletxtRemoteProcessingException(
                    (string) $node->{MoodletxtXMLConstants::$RESPONSE_ERROR_MESSAGE},
                    (int)    $node->{MoodletxtXMLConstants::$RESPONSE_ERROR_CODE}
            );

        }

    }

    /**
     * Builds message and status objects from parsed XML
     * @param SimpleXMLElement $node The node collection to create the object from
     * @return mixed[] Sent messages built from the XML
     * @see SentMessageStatus
     * @version 2012031501
     * @since 2011033101
     */
    private function buildStatusMessage($node) {

        $returnObjects = array();

        // Check that all required children for message object
        // build exist within passed data
        if (isset($node->{MoodletxtXMLConstants::$RESPONSE_STATUS_MESSAGE_TEXT}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_STATUS_PHONE}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_STATUS_TICKET}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_STATUS_UNIQUE_ID})) {

            // Build object and shove it onto the parsed objects array
            $sentMessageObject = new MoodletxtOutboundSMS(
                    $this->outboundMessageObject->getId(),
                    (int)    $node->{MoodletxtXMLConstants::$RESPONSE_STATUS_TICKET},
                    (string) $node->{MoodletxtXMLConstants::$RESPONSE_STATUS_MESSAGE_TEXT}
            );

            $sentMessageObject->setRecipientObject(
                $this->outboundMessageObject->getMessageRecipientByKey(
                    (string) $node->{MoodletxtXMLConstants::$RESPONSE_STATUS_UNIQUE_ID}
                )
            );

        }

        // Check if required children for status object exist
        if (isset($node->{MoodletxtXMLConstants::$RESPONSE_STATUS_TICKET}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_STATUS_CODE}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_STATUS_MESSAGE})) {

            // Build status object and shove onto array
            $statusObject = new MoodletxtOutboundSMSStatus(
                    (int)    $node->{MoodletxtXMLConstants::$RESPONSE_STATUS_TICKET},
                    (int)    $node->{MoodletxtXMLConstants::$RESPONSE_STATUS_CODE},
                    (string) $node->{MoodletxtXMLConstants::$RESPONSE_STATUS_MESSAGE},
                    time()
            );

            if (isset($sentMessageObject)) {   
                $sentMessageObject->addStatusUpdate($statusObject);
            
            } else if ($this->getExistingSentMessageByTicketNumber($statusObject->getTicketNumber()) != null) {
                
                $this->getExistingSentMessageByTicketNumber(
                    $statusObject->getTicketNumber()
                )->addStatusUpdate($statusObject);
            
            } else {
                array_push($returnObjects, $statusObject);
            }

        }

        if (isset($sentMessageObject))
            array_push($returnObjects, $sentMessageObject);

        return $returnObjects;

    }

    /**
     * Builds InboundMessage objects from parsed XML
     * @param SimpleXMLElement $node The captured elements to build from
     * @return MoodletxtInboundMessage[] Inbound messages built from the XML
     * @version 2012101001
     * @since 2011033101
     */
    private function buildInboundMessage($node) {

        $receivedMessages = array();

        // Check that required children exist for object build
        if (isset($node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_MESSAGE_TEXT}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_PHONE}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_DELIVERY_DATE}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_TICKET}) &&
            isset($node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_DESTINATION})) {

            try {

                // Create object and shove onto array
                $messageObject = new MoodletxtInboundMessage(
                        (string) $node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_TICKET},
                        (string) $node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_MESSAGE_TEXT},
                        new MoodletxtPhoneNumber((string) $node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_PHONE}),
                        (string) $node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_DELIVERY_DATE},
                        false
                );

                $messageObject->setDestinationNumber(
                    new MoodletxtPhoneNumber((string) $node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_DESTINATION})
                );
                    
                // If this field exists, we're parsing a pushed inbound message,
                // rather than one retrieved manually
                if (isset($node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_DESTINATION_ACC})) {
                    
                    $messageObject->setDestinationAccountUsername(
                        (string) $node->{MoodletxtXMLConstants::$RESPONSE_INBOUND_DESTINATION_ACC}
                    );
                
                } else {
                    $messageObject->setDestinationAccountId($this->getTxttoolsAccountObject()->getId());
                    $messageObject->setDestinationAccountUsername($this->getTxttoolsAccountObject()->getUsername());
                }

            } catch (InvalidPhoneNumberException $ex) {
                // Invalid message content - ignore and continue
            }

            array_push($receivedMessages, $messageObject);

        }

        return $receivedMessages;

    }

    /**
     * Builds AccountDetails object from inbound XML
     * @param SimpleXMLElement $node The captured elements to build from
     * @return TxttoolsAccount[] An array of objects built from the XML
     * @see AccountDetails
     * @version 2011061401
     * @since 2011033101
     */
    private function buildAccountDetail($node) {

        $receivedDetails = array();
        $accountObject = $this->getTxttoolsAccountObject();

        if ($accountObject instanceof TxttoolsAccount) {

            // Check that required children exist for object build
            if (isset($node->{MoodletxtXMLConstants::$RESPONSE_ACCOUNT_MESSAGES_USED}) &&
                isset($node->{MoodletxtXMLConstants::$RESPONSE_ACCOUNT_MESSAGES_REMAIN}) &&
                isset($node->{MoodletxtXMLConstants::$RESPONSE_ACCOUNT_BILLING_TYPE})) {

                // Create object and shove onto array
                $accountObject->setCreditsUsed((int) $node->{MoodletxtXMLConstants::$RESPONSE_ACCOUNT_MESSAGES_USED});
                $accountObject->setCreditsRemaining((int) $node->{MoodletxtXMLConstants::$RESPONSE_ACCOUNT_MESSAGES_REMAIN});
                $accountObject->setBillingType((int) $node->{MoodletxtXMLConstants::$RESPONSE_ACCOUNT_BILLING_TYPE});
                $accountObject->setLastUpdate(time());
                
                array_push($receivedDetails, $accountObject);

            }

        }

        return $receivedDetails;

    }

    /**
     * Returns a reference to the top-level outbound message that was just sent
     * @return MoodletxtOutboundMessage Message reference
     * @version 2011040101
     * @since 2011040101
     */
    public function getOutboundMessageObject() {
        return $this->outboundMessageObject;
    }

    /**
     * Sets a reference to the top-level outbound message that was just sent
     * @param MoodletxtOutboundMessage $outboundMessageObject Message reference
     * @version 2011040101
     * @since 2011040101
     */
    public function setOutboundMessageObject($outboundMessageObject) {
        $this->outboundMessageObject = $outboundMessageObject;
    }

    /**
     * Drops the currently stored message object
     * @version 2011040101
     * @since 2011040101
     */
    public function clearOutboundMessageObject() {
        $this->outboundMessageObject = null;
    }

    /**
     * Returns the currently stored account reference
     * @return TxttoolsAccount Account object
     * @version 2011040801
     * @since 2011040801
     */
    public function getTxttoolsAccountObject() {
        return $this->txttoolsAccountObject;
    }

    /**
     * Sets an account reference to use during parsing
     * @param TxttoolsAccount $txttoolsAcccountObject Account object
     * @version 2011040801
     * @since 2011040801
     */
    public function setTxttoolsAccountObject($txttoolsAcccountObject) {
        $this->txttoolsAccountObject = $txttoolsAcccountObject;
    }

    /**
     * Drops the currently stored account reference
     * @version 2011040801
     * @since 2011040801
     */
    public function clearTxttoolsAccountObject() {
        $this->txttoolsAccountObject = null;
    }

    /**
     * Fetches the previously sent SMS messages currently being updated
     * @return MoodletxtOutboundSMS[] Sent SMS messages
     * @version 2012040201
     * @since 2012040201
     */
    public function getExistingSentMessages() {
        return $this->existingSentMessages;
    }

    /**
     * Stores previously sent SMS messages to be updated during parsing
     * @param MoodletxtOutboundSMS[] $existingSentMessages Sent SMS messages
     * @version 2012040201
     * @since 2012040201
     */
    public function setExistingSentMessages($existingSentMessages) {
        $this->existingSentMessages = array();

        foreach($existingSentMessages as $message)
            $this->existingSentMessages[$message->getTicketNumber()] = $message;
    }

    /**
     * Gets a given saved message by its ticket number
     * @param int $ticketNumber Ticket number to search for
     * @return MoodletxtOutboundSMS|null Message if it exists, null otherwise
     * @version 2012040201
     * @since 2012040201
     */
    private function getExistingSentMessageByTicketNumber($ticketNumber) {
        
        if (is_array($this->existingSentMessages) &&
            array_key_exists($ticketNumber, $this->existingSentMessages)) {
            
            return $this->existingSentMessages[$ticketNumber];
            
        } else {
            return null;
        }
    }
    
}

?>