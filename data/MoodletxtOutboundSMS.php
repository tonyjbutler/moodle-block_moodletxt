<?php

/**
 * File container for MoodletxtOutboundSMS object
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
 * @see MoodletxtOutboundSMS
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012031501
 * @since 20100082001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Whereas a MoodletxtOutboundMessage object is a top-level
 * representation of a transmission, this object
 * represents an actual SMS message sent to a phone number
 * 
 * @package uk.co.moodletxt.data
 * @see MoodletxtOutboundMessage
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012031501
 * @since 20100082001
 */
class MoodletxtOutboundSMS {

    /**
     * Holds the record ID of the sent message if known.
     * NOTE: Means absolutely nothing to the txttools system.
     * This is NOT the same as a message ticket.
     * @var int
     */
    private $id;

    /**
     * Holds the id of the message to which this sent message belongs.
     * NOTE (Once again): "Entered" messages and sent messages are
     * NOT the same thing.
     * @var int
     */
    private $messageId;

    /**
     * Holds the ticket number that was assigned to this text message
     * by the txttools system.
     * @var int
     */
    private $ticketNumber;

    /**
     * Holds the text of the message sent. (Parsed for tags)
     * @var string
     */
    private $messageText;

    /**
     * Holds a moodletxtRecipient object representing the recipient
     * of this sent message
     * @var MoodletxtRecipient
    */
    private $recipientObject;


    /**
     * An array of status updates received for this SMS message
     * @var MoodletxtOutboundSMSStatus[]
     */
    private $statusUpdates = array();


    /**
     * Constructor - Initialises bean with given data
     * @param int $messageId Record ID of top-level message
     * @param int $ticketNumber Ticket number of SMS message sent
     * @param string $messageText Text for this individual SMS message
     * @param MoodletxtRecipient $recipientObject SMS message recipient
     * @param MoodletxtOutboundSMSStatus[] $statusUpdates Status updates for message
     * @param int $id The DB record ID of this SMS
     * @version 2012031501
     * @since 2010082001
     */
    public function  __construct($messageId, $ticketNumber, $messageText, MoodletxtRecipient $recipientObject = null,
                                array $statusUpdates = array(), $id = 0) {

        $this->setMessageId($messageId);
        $this->setTicketNumber($ticketNumber);
        $this->setRecipientObject($recipientObject);
        $this->setMessageText($messageText);
        $this->setStatusUpdates($statusUpdates);
        $this->setId($id);

    }

    /**
     * Get the DB record ID of this message if known
     * @return int DB Record ID
     * @version 2010082001
     * @since 2010082001
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the DB record ID of this message
     * @param int $id DB Record ID
     * @version 2010082001
     * @since 2010082001
     */
    public function setId($id) {
        $id = (int) $id;

        if ($id > 0)
            $this->id = $id;
    }

    /**
     * Gets the top-level message that this SMS belongs to
     * @return int Message ID
     * @version 2010082001
     * @since 2010082001
     */
    public function getMessageId() {
        return $this->messageId;
    }

    /**
     * Sets the top-level message that this SMS belongs to
     * @param int $messageId Message ID
     * @version 2010082001
     * @since 2010082001
     */
    public function setMessageId($messageId) {
        $messageId = (int) $messageId;

        if ($messageId > 0)
            $this->messageId = $messageId;
    }

    /**
     * Gets the ticket number assigned to this SMS
     * by the txttools server
     * @return int SMS Ticket number
     * @version 2010082001
     * @since 2010082001
     */
    public function getTicketNumber() {
        return $this->ticketNumber;
    }

    /**
     * Sets the ticket number assigned to this SMS
     * by the txttools server
     * @param int $ticketNumber Ticket number
     * @version 2012031501
     * @since 2010082001
     */
    public function setTicketNumber($ticketNumber) {
        $ticketNumber = (int) $ticketNumber;
        
        if ($ticketNumber > 0) {
            $this->ticketNumber = $ticketNumber;

            // Pass down ID to contained objects
            foreach($this->getStatusUpdates() as $statusUpdate)
                $statusUpdate->setTicketNumber($ticketNumber);
            
        }
    }

    /**
     * Gets details of the recipient of this SMS
     * @return moodletxtRecipient SMS recipient
     * @version 2010082001
     * @since 2010082001
     */
    public function getRecipientObject() {
        return $this->recipientObject;
    }

    /**
     * Sets details of the recipient of this SMS message
     * @param moodletxtRecipient $recipientObject SMS recipient
     * @version 2010082001
     * @since 2010082001
     */
    public function setRecipientObject($recipientObject) {
        $this->recipientObject = $recipientObject;
    }

    /**
     * Returns an array of all status updates received for this SMS
     * @return MoodletxtOutboundSMSStatus[]
     * @version 2010090201
     * @since 2010090201
     */
    public function getStatusUpdates() {
        return $this->statusUpdates;
    }

    /**
     * Sets the previously received status updates for this SMS
     * @param MoodletxtOutboundSMSStatus[] $statusUpdates Received updates
     * @version 2010090201
     * @since 2010090201
     */
    public function setStatusUpdates($statusUpdates) {
        $this->statusUpdates = $statusUpdates;
    }

    /**
     * Adds a status update to the existing set
     * @param MoodletxtOutboundSMSStatus $statusUpdate Status update
     * @version 2010090201
     * @since 2010090201
     */
    public function addStatusUpdate(MoodletxtOutboundSMSStatus $statusUpdate) {
        array_push($this->statusUpdates, $statusUpdate);
    }

    /**
     * Returns the tag-parsed text for this individual SMS
     * @return string Message Text
     * @version 2011040101
     * @since 2011040101
     */
    public function getMessageText() {
        return $this->messageText;
    }

    /**
     * Sets the tag-parsed text for this individual SMS
     * @param string $messageText Message Text
     * @version 2011040101
     * @since 2011040101
     */
    public function setMessageText($messageText) {
        $this->messageText = $messageText;
    }

}

?>