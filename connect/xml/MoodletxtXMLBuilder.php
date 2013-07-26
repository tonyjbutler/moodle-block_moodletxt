<?php

/**
 * File container for the MoodletxtXMLBuilder class
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
 * @see MoodletxtXMLBuilder
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2010090101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLConstants.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib/MoodletxtEncryption.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundMessage.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtRecipient.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/TxttoolsAccount.php');

/**
 * Builds XML for transmission to txttools.
 * Class to build XML requests used by the API. XML is constructed from values passed in, and returned to the controlling object
 * The original builder was ported from moodletxt 2.1 to the PHP5-based txttools API, so I ported it back again from there.
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2010090101
 */
class MoodletxtXMLBuilder {

    /**
     * Holds all XML <Request>s as they are completed
     * @var array
     */
    private $XMLRequests = array();

    /**
     * Holds a buffer of the current <Request> being built
     * @var string
     */
    private $currentRequest = '';

    /**
     *  Holds the current authentication block to use when building <Request>s
     * @var string
     */
    private $currentAuthentication = '';

    /**
     * Holds <Message>, <RequestStatus> etc blocks as they are built
     * @var array
     */
    private $XMLBlocks = array();
    
    /**
     * Holds an Encryption class for decoding account passwords
     * @var MoodletxtEncryption
     */
    private $encrypter;

    /**
     * Number of blocks that can be included in a single <Request>
     * @var int
     * @static
     */
    private static $MAX_BLOCKS_PER_REQUEST;

    
    /**
     * Constructor - sets up builder options
     * @version 2013052301
     * @since 2010090101
     */
    function __construct() {

        $this->encrypter = new MoodletxtEncryption();
        self::$MAX_BLOCKS_PER_REQUEST = MoodletxtXMLConstants::$MESSAGES_PER_BATCH;

    }

    /**
     * Returns the current/completed set of XML requests
     * @version 2010090101
     * @since 2010090101
     * @return array The xml requests created by the builder
     */
    private function getRequests() {

        return $this->XMLRequests;

    }

    /**
     * Build an XML request to send messages.
     * @param MoodletxtOutboundMessage $message The message object to be sent.
     * @return array The completed XML packet(s) to be sent.
     * @version 2010090601
     * @since 2010090101
     */
    public function buildOutboundMessage(MoodletxtOutboundMessage $message) {

        // Set the object into "outbound mode" by building some outbound authentication
        $this->buildAuthentication($message->getTxttoolsAccount());

        $this->buildTextMessageBlocks($message);

        return $this->compileXMLRequests();

    }

    /**
     * Builds a status request
     * Method to build an XML request to retrieve the status of a given sent message or messages
     * @param array(MoodletxtOutboundSMS) $sentMessages Sent messages to get status for
     * @param TxttoolsAccount $txttoolsAccount The txttools account to get status through
     * @return array An array of completed XML packets to be sent by the connector
     * @version 2010090601
     * @since 2010090101
     */
    public function buildStatusRequest($sentMessages, TxttoolsAccount $txttoolsAccount) {

        // Get authentication
        $this->buildAuthentication($txttoolsAccount);

        // Build  status blocks
        $this->buildStatusRequestBlocks($sentMessages);

        return $this->compileXMLRequests();

    }

    /**
     * Builds an authentication-only request, used to check validity of txttools accounts
     * @param string $username txttools account username
     * @param string $password txttools account password
     * @return array(string) An array of completed XML packets to be sent to txttools
     * @version 2011040601
     * @since 2011040601
     */
    public function buildAccountValidityCheck($username, $password) {

        // Get authentication and....well that's about it, really.
        $this->buildAuthenticationFromStrings($username, $password);

        return $this->compileXMLRequests();

    }

    /**
     * Begins a request block
     * @version 2010090101
     * @since 2010090101
     */
    private function openRequest() {

        $this->currentRequest = MoodletxtXMLConstants::$REQUEST_ROOT;

    }

    /**
     * Close the current request block.
     * End the current request block, adds it to the array of request blocks made, and then resets the current block buffer
     * @version 2012052801
     * @since 2010090101
     */
    private function closeRequest() {

        $this->currentRequest .= '
' . MoodletxtXMLConstants::$_REQUEST_ROOT;

//        error_log(get_string('logxmlblockcreated', 'block_moodletxt') . "\r\n\r\n" . $this->currentRequest);

        array_push($this->XMLRequests, $this->currentRequest);
        $this->currentRequest = '';

    }

    /**
     * Clear the current requests.
     * Method to clear out the current cached requests, blocks and authentication - basically, reset the object
     * @version 2010090101
     * @since 2010090101
     */
    private function clearRequests() {

        $this->XMLRequests = array();
        $this->XMLBlocks = array();
        $this->currentAuthentication = '';
        $this->currentRequest = '';

    }

    /**
     * Adds the currently active authentication block to the XML request being built.
     * @version 2010090101
     * @since 2010090101
     */
    private function appendAuthentication() {

        $this->currentRequest .= $this->currentAuthentication;

    }

    /**
     * Add a new XML block to the current cache
     * @param string $requestblock The block to be added to the cache
     * @version 2010090101
     * @since 2010090101
     */
    private function addRequestBlock($requestBlock) {

        array_push($this->XMLBlocks, $requestBlock);

    }

    /**
     * Build text message blocks.
     * Method builds the individual <Message> blocks to be included in the message being built.
     * These blocks are cached into an array, then compiled into <Request> blocks later on.
     * @param MoodletxtOutboundMessage $message The message object to base the block on
     * @version 2011040802
     * @since 2010090101
     */
    private function buildTextMessageBlocks(MoodletxtOutboundMessage $message) {

        $messageRecipients = $message->getMessageRecipients();

        // Loop over recipients and create message blocks
        foreach ($messageRecipients as $recipientKey => $recipient) {

            $messageText = trim(stripslashes($message->getMessageText()));
            $messageText = MoodletxtStringHelper::mergeTagsIntoMessageText($messageText, $recipient);

            // Chunk message text into blocks of 160 chars
            $messagechunks = str_split($messageText, 160);

            // Build message blocks
            foreach($messagechunks as $chunk) {

                // Code written this way so the output is all nice and formatted
                $messageBlock = '
    ' . MoodletxtXMLConstants::$REQUEST_MESSAGE_BLOCK . '
        ' . MoodletxtXMLConstants::$REQUEST_MESSAGE_TEXT . $chunk . MoodletxtXMLConstants::$_REQUEST_MESSAGE_TEXT . '
        ' . MoodletxtXMLConstants::$REQUEST_MESSAGE_PHONE . $recipient->getRecipientNumber()->getPhoneNumber() . MoodletxtXMLConstants::$_REQUEST_MESSAGE_PHONE . '
        ' . MoodletxtXMLConstants::$REQUEST_MESSAGE_TYPE . $message->getType() . MoodletxtXMLConstants::$_REQUEST_MESSAGE_TYPE . '
        ' . MoodletxtXMLConstants::$REQUEST_MESSAGE_UNIQUE_ID . $recipientKey . MoodletxtXMLConstants::$_REQUEST_MESSAGE_UNIQUE_ID;

                // Add scheduled send time if specified
                $messageBlock .= ($message->getScheduledTime() > $message->getTimeSent()) ? '
        ' . MoodletxtXMLConstants::$REQUEST_MESSAGE_SCHEDULE_DATE . $message->getScheduledTime() . MoodletxtXMLConstants::$_REQUEST_MESSAGE_SCHEDULE_DATE : '';

                // Add UTF-8 suppression if specified
                $messageBlock .= ($message->isSuppressUnicode()) ? '
        ' . MoodletxtXMLConstants::$REQUEST_MESSAGE_SUPPRESS_UNICODE : '';

                $messageBlock .= '
    ' . MoodletxtXMLConstants::$_REQUEST_MESSAGE_BLOCK;
                
                $this->addRequestBlock($messageBlock);

            }

        }

    }
    
    /**
     * Builds status requests.
     * Method to build <RequestStatus> blocks for inclusion in the XML requests being built.
     * @param array(MoodletxtOutboundSMS) $sentMessages An Array of messages to get status updates for
     * @version 2010090701
     * @since 2010090101
     */
    private function buildStatusRequestBlocks($sentMessages) {

        foreach($sentMessages as $sentMessage) {

            // Code written this way so the output is all nice and formatted
            $this->addRequestBlock('
    ' . MoodletxtXMLConstants::$REQUEST_STATUS_BLOCK . '
        ' . MoodletxtXMLConstants::$REQUEST_STATUS_TICKET . $sentMessage->getTicketNumber() . MoodletxtXMLConstants::$_REQUEST_STATUS_TICKET . '
    ' . MoodletxtXMLConstants::$_REQUEST_STATUS_BLOCK);


        }

    }

    /**
     * Builds authentication block.
     * Method to build an <Authentication> block for authenticating a request with the API
     * @param TxttoolsAccount $txttoolsAccount txttools account to authenticate against
     * @version 2011041501
     * @since 2010090101
     */
    private function buildAuthentication($txttoolsAccount) {

        $outPass = $this->encrypter->decrypt(
                    get_config('moodletxt', 'EK'),
                    $txttoolsAccount->getEncryptedPassword()
                );

        // Set authentication block
        // Code written this way so the output is all nice and formatted
        $this->currentAuthentication = '
    ' . MoodletxtXMLConstants::$REQUEST_AUTHENTICATION_BLOCK . '
        ' . MoodletxtXMLConstants::$REQUEST_AUTHENTICATION_USERNAME . $txttoolsAccount->getUsername() . MoodletxtXMLConstants::$_REQUEST_AUTHENTICATION_USERNAME . '
        ' . MoodletxtXMLConstants::$REQUEST_AUTHENTICATION_PASSWORD . $outPass . MoodletxtXMLConstants::$_REQUEST_AUTHENTICATION_PASSWORD . '
    ' . MoodletxtXMLConstants::$_REQUEST_AUTHENTICATION_BLOCK;

    }

    /**
     * Builds authentication block directly from username and password strings.
     * Used when first validating a user account - no DB object yet exists
     * @param string $username txttools account username
     * @param string $password txttools account password
     * @version 2011040601
     * @since 2011040601
     */
    private function buildAuthenticationFromStrings($username, $password) {

        $this->currentAuthentication = '
    ' . MoodletxtXMLConstants::$REQUEST_AUTHENTICATION_BLOCK . '
        ' . MoodletxtXMLConstants::$REQUEST_AUTHENTICATION_USERNAME . $username . MoodletxtXMLConstants::$_REQUEST_AUTHENTICATION_USERNAME . '
        ' . MoodletxtXMLConstants::$REQUEST_AUTHENTICATION_PASSWORD . $password . MoodletxtXMLConstants::$_REQUEST_AUTHENTICATION_PASSWORD . '
    ' . MoodletxtXMLConstants::$_REQUEST_AUTHENTICATION_BLOCK;

    }

    /**
     * Builds inbound message request.
     * Method to build an XML request to get inbound messages for a given txttools account. Please see documentation if you're uncertain of parameters.
     * @param TxttoolsAccount $txttoolsAccount Account through which to fetch messages
     * @param int $retrieveNumber The number of messages to fetch
     * @param string $retrieveType Whether to fetch all/unread messages
     * @param int $retrieveSince The UTC timestamp to search forward from for messages. (SEE CONNECTOR DOCUMENTATION!)
     * @version 2011040701
     * @since 2010090101
     */
    public function buildInboundMessageRequest(TxttoolsAccount $txttoolsAccount, $retrieveNumber = 0, $retrieveType = 'ALL', $retrieveSince = 0) {

        // Build authentication for this block
        $this->buildAuthentication($txttoolsAccount);

        // Check number to retrieve is within valid range
        if ((! is_int($retrieveNumber)) || $retrieveNumber < 1 || $retrieveNumber > MESSAGES_PER_BATCH)
            $retrieveNumber = MoodletxtXMLConstants::$MESSAGES_PER_BATCH;

        // Check that retrieve type is one of the valid types
        if ($retrieveType != MoodletxtXMLConstants::$INBOUND_FETCH_ALL && $retrieveType != MoodletxtXMLConstants::$INBOUND_FETCH_UNREAD)
            $retrieveType = MoodletxtXMLConstants::$INBOUND_FETCH_UNREAD;

        // Check that timestamp is valid
        // 978307200 is 2001-01-01 00:00:00 - txttools went live in 2001
        if ($retrieveSince < 978307200 || $retrieveSince > time())
            $retrieveSince = 0;

        // Add inbound request block
        $inboundBlock = '
    ' . MoodletxtXMLConstants::$REQUEST_INBOUND_BLOCK . '
        ' . MoodletxtXMLConstants::$REQUEST_INBOUND_TYPE . $retrieveType . MoodletxtXMLConstants::$_REQUEST_INBOUND_TYPE . '
        ' . MoodletxtXMLConstants::$REQUEST_INBOUND_NUMBER . $retrieveNumber . MoodletxtXMLConstants::$_REQUEST_INBOUND_NUMBER;

        if ($retrieveSince > 0)
            $inboundBlock .= '
        ' . MoodletxtXMLConstants::$REQUEST_INBOUND_SINCE . $retrieveSince . MoodletxtXMLConstants::$_REQUEST_INBOUND_SINCE;

        $inboundBlock .= '
    ' . MoodletxtXMLConstants::$_REQUEST_INBOUND_BLOCK;

        $this->addRequestBlock($inboundBlock);

        return $this->compileXMLRequests();

    }

    /**
     * Build "credit info " block
     * Builds an XML request to check the number of message credits used/remaining on an account
     * @param TxttoolsAccount $txttoolsAccount Account to request credit info for
     * @return string XML block to send
     * @version 2011040801
     * @since 2010090101
     */
    public function buildCreditInfo(TxttoolsAccount $txttoolsAccount) {

        // Build authentication block
        $this->buildAuthentication($txttoolsAccount);

        // Add credit request block
        $requestBlock = '
    ' . MoodletxtXMLConstants::$REQUEST_ACCOUNT_DETAILS . '
            ' . MoodletxtXMLConstants::$REQUEST_GET_ACCOUNT_DETAILS . '
    ' . MoodletxtXMLConstants::$_REQUEST_ACCOUNT_DETAILS;

        $this->addRequestBlock($requestBlock);

        return $this->compileXMLRequests();
    }

    /**
     * Compiles blocks into request document.
     * Function compiles the cached blocks and auth details into a set of properly formatted XML requests, then returns an array of these requests to the calling method
     * @return array An array of completed XML requests to be sent to txttools
     * @version 2010090101
     * @since 2010090101
     */
    private function compileXMLRequests() {

        // Chunk XML blocks
        $chunkedBlocks = array_chunk($this->XMLBlocks, self::$MAX_BLOCKS_PER_REQUEST);

        // Loop through chunks and build a request for each
        foreach($chunkedBlocks as $blockChunk) {

            // Begin request
            $this->openRequest();

            // Add on outbound authentication
            $this->appendAuthentication();

            // Append blocks
            foreach($blockChunk as $block) {

                $this->currentRequest .= $block;

            }

            // Close request
            $this->closeRequest();

        }

        // Get requests from array prior to wipe
        $finalrequests = $this->getRequests();

        // Reset object
        $this->clearRequests();

        return $finalrequests;

    }

}

?>