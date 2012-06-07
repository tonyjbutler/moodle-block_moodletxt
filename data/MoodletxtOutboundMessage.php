<?php

/**
 * File container for MoodletxtOutboundMessage class
 * 
 * moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "txttools", "moodletxt", "Blackboard", "Blackboard Connect" or "Cy-nap"
 *   - Derivative works must be have their differences from the original material noted,
 *     and must not be misrepresentative of the origin of this material, or of the original service
 * 
 * Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
 * or legal liability arising from their use of this code.
 * 
 * @see MoodletxtOutboundMessage
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012032901
 * @since 2010090201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Data class to hold the details of an outbound message
 * @package uk.co.moodletxt.data
 * @see MoodletxtOutboundMessage
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012032901
 * @since 2010090201
 */
class MoodletxtOutboundMessage {

    /**
     * Represents a bulk (standard) message - charged only to sender
     * @var int
     */
    public static $MESSAGE_CHARGE_TYPE_BULK = 1;

    /**
     * Represents a reverse-charged message
     * @var int
     */
    public static $MESSAGE_CHARGE_TYPE_REVERSE = 2;

    /**
     * Represents an interactive message
     * @var int
     */
    public static $MESSAGE_CHARGE_TYPE_INTERACTIVE = 3;

    /**
     * Holds the record ID of the message if known.
     * NOTE: Means absolutely nothing to the txttools system.
     * This is NOT the same as a message ticket.
     * @var int
     */
    private $id;

    /**
     * Holds the txttools account that the message was sent from.
     * @var TxttoolsAccount
     */
    private $txttoolsAccount;

    /**
     * Holds the user who sent the message
     * @var MoodletxtBiteSizedUser
     */
    private $user;

    /**
     * Holds the text of the message sent. (Not parsed for tags)
     * @var string
     */
    private $messageText;

    /**
     * Holds the time that the message was sent at
     * @var int
     */
    private $timeSent;

    /**
     * Holds the time that the message was scheduled for
     * @var int
     */
    private $scheduledTime;

    /**
     * Holds the type of message being sent:
     * @see $validTypes
     * @var int
     */
    private $type;

    /**
     * If set to 1, this message will be restricted
     * at txttools to *only* contain characters
     * in the GSM 03.38 character set
     * @var int
     */
    private $suppressUnicode = 0;

    /**
     * Holds an array of all recipients the message will go to (before this message has been sent)
     * @var MoodletxtRecipient[]
     */
    private $messageRecipients = array();

    /**
     * Holds an array of actual SMS messages sent (if this message has already been sent)
     * @var MoodletxtOutboundSMS[]
     */
    private $sentSMSMessages = array();


    /**
     * Class constructor - takes a set of valid values
     * and initialises the data object.
     *
     * @param TxttoolsAccount $txttoolsAccount The txttools account this message was sent through
     * @param MoodletxtBiteSizedUser $user The user who sent the message
     * @param string $messageText The text of the message being sent
     * @param int $timeSent The time at which the message was sent
     * @param int $type The type of message being sent.
     * @param int $scheduledTime The time the message was scheduled for (Optional)
     * @param boolean $suppressUnicode Whether the message should be GSM 03.38 only (Optional)
     * @param int $id The record ID of the message if known. (Therefore optional)
     * @version 2011081101
     * @since 2010090201
     */
    public function __construct(TxttoolsAccount $txttoolsAccount, MoodletxtBiteSizedUser $user, $messageText, $timeSent, $type,
                                $scheduledTime = 0, $suppressUnicode = 0, $id = 0) {

        // If no scheduled time specified, set it to time sent
        $scheduledTime = ($scheduledTime == 0) ? $scheduledTime = $timeSent : $scheduledTime;

        $this->setId($id);
        $this->setTxttoolsAccount($txttoolsAccount);
        $this->setTimeSent($timeSent);
        $this->setType($type);
        $this->setScheduledTime($scheduledTime);
        $this->setMessageText($messageText);
        $this->setUser($user);
        $this->setSuppressUnicode($suppressUnicode);

    }

    /**
     * Function to return the message's record ID
     * @return int The record ID of this message
     * @version 2010090201
     * @since 2010090201
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns the txttools account this message was sent through
     * @return TxttoolsAccount The txttools account used
     * @version 2010090201
     * @since 2010090201
     */
    public function getTxttoolsAccount() {
        return $this->txttoolsAccount;
    }

    /**
     * Returns the user who sent this message
     * @return MoodletxtBiteSizedUser
     * @version 2011081101
     * @since 2011081101
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Function to return the actual text of the message being sent
     * @return string The message text being sent
     * @version 2010090201
     * @since 2010090201
     */
    public function getMessageText() {
        return $this->messageText;
    }

    /**
     * Function to return the time at which this message was sent
     * @param string $dateFormat Optional date/time formatting string
     * @return int The time the message was sent at
     * @version 2011081101
     * @since 2010090201
     */
    public function getTimeSent($dateFormat = '') {
        if ($dateFormat != '')
            return userdate($this->timeSent, $dateFormat);
        else
            return $this->timeSent;
    }

    /**
     * Function to get the time this message is scheduled to be sent at
     * @param string $dateFormat Optional date/time formatting string
     * @return int The scheduled time for sending
     * @version 2012032901
     * @since 2006092912
     */
    public function getScheduledTime($dateFormat = '') {
        if ($dateFormat != '')
            return userdate($this->scheduledTime, $dateFormat);
        else
            return $this->scheduledTime;
    }

    /**
     * Returns this message's type (bulk, interactive, etc)
     * @return int Message type
     * @version 2011040601
     * @since 2010090201
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Returns whether or not this message is being restricted
     * to the GSM 03.38 character set
     * @return boolean Is UTF-8 suppressed?
     * @version 2010090201
     * @since 2010090201
     */
    public function isSuppressUnicode() {
        return $this->suppressUnicode > 0;
    }

    /**
     * Sets the DB record ID of this message
     * @param int $id DB record ID
     * @version 2012031501
     * @since 2010090201
     */
    public function setId($id) {
        $id = (int) $id;

        if ($id > 0) {
            $this->id = $id;
            
            // Pass down ID to contained objects
            foreach ($this->getSentSMSMessages() as $smsMessage)
                $smsMessage->setMessageId($id);
            
        }
    }

    /**
     * Sets the ID of the txttools account this message was sent via
     * @param TxttoolsAccount $txttoolsAccount Txttools account
     * @version 2010090201
     * @since 2010090201
     */
    public function setTxttoolsAccount(TxttoolsAccount $txttoolsAccount) {
        $this->txttoolsAccount = $txttoolsAccount;
    }

    /**
     * Sets the ID of the Moodle user that sent this message
     * @param MoodletxtBiteSizedUser $userId User ID
     * @version 2011081101
     * @since 2011081101
     */
    public function setUser(MoodletxtBiteSizedUser $user) {
        $this->user = $user;
    }

    /**
     * Sets the text of this message
     * @param string $messageText Message text
     * @version 2010090201
     * @since 2010090201
     */
    public function setMessageText($messageText) {
        $this->messageText = $messageText;
    }

    /**
     * Sets the time at which this message was sent from moodletxt
     * @param int $timeSent Unix timestamp
     * @version 2010090201
     * @since 2010090201
     */
    public function setTimeSent($timeSent) {
        $this->timeSent = (int) $timeSent;
    }

    /**
     * Sets the time at which this message was scheduled to be sent from txttools
     * @param int $scheduledTime Unix timestamp
     * @version 2010090201
     * @since 2010090201
     */
    public function setScheduledTime($scheduledTime) {
        $this->scheduledTime = (int) $scheduledTime;
    }

    /**
     * Sets the type of message being sent (bulk, interactive, etc)
     * @param int $type Message type
     * @version 2011040601
     * @since 2010090201
     */
    public function setType($type) {
        if ($type == self::$MESSAGE_CHARGE_TYPE_BULK ||
            $type == self::$MESSAGE_CHARGE_TYPE_INTERACTIVE ||
            $type == self::$MESSAGE_CHARGE_TYPE_REVERSE)
            $this->type = $type;
    }

    /**
     * Set whether or not this message should be
     * restricted to the GSM 03.38 character set
     * @param boolean $suppressUnicode Suppress Unicode?
     * @version 2010090201
     * @since 2010090201
     */
    public function setSuppressUnicode($suppressUnicode) {
        $this->suppressUnicode = ($suppressUnicode) ? 1 : 0;
    }

    /**
     * Returns an array of all actual SMS messages sent
     * under this top level message
     * @return MoodletxtOutboundSMS[] Sent SMS Messages
     * @version 2010090201
     * @since 2010090201
     */
    public function getSentSMSMessages() {
        return $this->sentSMSMessages;
    }

    /**
     * Sets the actual SMS messages sent under this top level message
     * @param MoodletxtOutboundSMS[] $sentSMSMessages Sent SMS Messages
     * @version 2010090201
     * @since 2010090201
     */
    public function setSentSMSMessages($sentSMSMessages) {
        $this->sentSMSMessages = $sentSMSMessages;
    }

    /**
     * Adds an SMS message sent under this top level message
     * @param MoodletxtOutboundSMS $sentSMSMessage Sent SMS Message
     * @version 2010090701
     * @since 2010090201
     */
    public function addSentSMSMessage(MoodletxtOutboundSMS $sentSMSMessage) {
        array_push($this->sentSMSMessages, $sentSMSMessage);
    }

    /**
     * Returns an array of recipients for the message
     * (This will only be populated before sending. After sending
     * recipients will belong to individual MoodletxtOutboundSMS instances.)
     * @return MoodletxtRecipient[] All future recipients for message
     * @version 2010090701
     * @since 2010090701
     */
    public function getMessageRecipients() {
        return $this->messageRecipients;
    }

    /**
     * Sets the list of recipients for this message
     * (Note: This should only be set prior to sending. After sending
     * recipients should belong to individual MoodletxtOutboundSMS instances.)
     * @param MoodletxtRecipient[] $messageRecipients All future recipients
     * @version 2010090701
     * @since 2010090701
     */
    public function setMessageRecipients($messageRecipients) {
        $this->messageRecipients = $messageRecipients;
    }

    /**
     * Adds a recipient to this message.
     * (Note: Recipients should only be added to the message prior to sending.
     * After sending, recipients will belong to individual MoodletxtOutboundSMS instances.)
     * @param MoodletxtRecipient $recipient New future recipient for message
     * @version 2010090701
     * @since 2010090701
     */
    public function addMessageRecipient(MoodletxtRecipient $recipient) {
        array_push($this->messageRecipients, $recipient);
    }

    /**
     * If you know the key of the message recipient you want to pull back, you can use this method to access them
     * @param int $recipientKey Array index of recipient
     * @return MoodletxtRecipient $recipient Message recipient
     * @version 2011040401
     * @since 2011040401
     */
    public function getMessageRecipientByKey($recipientKey) {
        return (isset($this->messageRecipients[$recipientKey])) ? $this->messageRecipients[$recipientKey] : null;
    }

}

?>