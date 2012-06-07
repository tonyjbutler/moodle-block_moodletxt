<?php

/**
 * File container for MoodletxtOutboundSMSStatus object
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
 * @see MoodletxtOutboundSMSStatus
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012032201
 * @since 2010090201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Data class to hold the details of an SMS status update.
 * @package uk.co.moodletxt.data
 * @see MoodletxtOutboundSMS
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012032201
 * @since 2010090201
 */
class MoodletxtOutboundSMSStatus {

    /**
     * Status representing an immediate failure due to insufficient
     * credits remaining in the user's txttools account
     * @var int
     */
    public static $STATUS_FAILED_INSUFFICIENT_CREDITS = -1;
    
    /**
     * Message has been queued at txttools for sending to the aggregator
     * @var int
     */
    public static $STATUS_TRANSIT_MESSAGE_QUEUED = 0;
    
    /**
     * The message has been delivered to and accepted by the aggregator
     * @var int
     */
    public static $STATUS_TRANSIT_DELIVERED_TO_AGGREGATOR = 1;
    
    /**
     * Message has failed after reaching the network level
     * @var int
     */
    public static $STATUS_FAILED_AT_NETWORK = 2;
    
    /**
     * Message was successfully passed from aggregator to network
     * @var int
     */
    public static $STATUS_TRANSIT_DELIVERED_TO_NETWORK = 3;
    
    /**
     * Message has failed to be delivered, but the system will retry. Not a failure.
     * @var int 
     */
    public static $STATUS_FAILED_WILL_RETRY = 4;
    
    /**
     * Message was successfully delivered to handset
     * @var int
     */
    public static $STATUS_DELIVERED_TO_HANDSET = 5;
    
    /**
     * An unknown error prevented the message from being delivered
     * @var int
     */
    public static $STATUS_FAILED_UNKNOWN_ERROR = 6;


    /**
     * Holds the record ID of the sent message if known.
     * NOTE: Means absolutely nothing to the txttools system.
     * This is NOT the same as a message ticket.
     * @var int
     */
    private $id;

    /**
     * Holds the id of the message to which this status update belongs.
     * NOTE (Once again): "Entered" messages and sent messages are
     * NOT the same thing.
     * @var int
     */
    private $ticketNumber;

    /**
     * Holds the integer-based status flag for the status update.
     * @var int
     */
    private $status;

    /**
     * Holds a plain-english explanation of the status flag.
     * @var string
     */
    private $statusMessage;

    /**
     * Holds the timestamp at which the status update was received
     * @var int
     */
    private $updateTime;

    /**
     * Class constructor - takes a set of valid values and initialises the data object.
     *
     * @param int $ticketNumber The message to which this sent message belongs.
     * @param int $status The status flag returned from the system.
     * @param string $statusMessage A plain english version of the status flag.
     * @param int $updateTime The time at which this update was received
     * @param int $id The record ID of this message if known. (Optional)
     * @version 2010090201
     * @since 2010090201
     */
    public function __construct($ticketNumber, $status, $statusMessage, $updateTime, $id = 0) {

        $this->setTicketNumber($ticketNumber);
        $this->setStatus($status);
        $this->setStatusMessage($statusMessage);
        $this->setUpdateTime($updateTime);
        $this->setId($id);
        
    }

    /**
     * Returns the DB record ID for this status, if known
     * @return int DB record ID
     * @version 2010090201
     * @since 2010090201
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns the message ticket number this status refers to
     * @return int Message ticket
     * @version 2010090201
     * @since 2010090201
     */
    public function getTicketNumber() {
        return $this->ticketNumber;
    }

    /**
     * Returns a txttools status code
     * @return int Status code
     * @version 2010090201
     * @since 2010090201
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Returns a textual description of the status event
     * @return string Status message
     * @version 2010090201
     * @since 2010090201
     */
    public function getStatusMessage() {
        return $this->statusMessage;
    }

    /**
     * Returns the time at which this update occurred
     * @return int Unix timestamp
     * @version 2010090201
     * @since 2010090201
     */
    public function getUpdateTime() {
        return $this->updateTime;
    }

    /**
     * Set the ticket number this status event refers to
     * @param int $ticketNumber Message ticket
     * @version 2010090201
     * @since 2010090201
     */
    public function setTicketNumber($ticketNumber) {
        $ticketNumber = (int) $ticketNumber;

        if ($ticketNumber > 0)
            $this->ticketNumber = $ticketNumber;
    }

    /**
     * Sets the DB record ID for this status event
     * @param int $id DB record ID
     * @version 2010090201
     * @since 2010090201
     */
    public function setId($id) {
        $id = (int) $id;

        if ($id > 0)
            $this->id = $id;
    }

    /**
     * Sets the txttools status code for this event
     * @param int $status Status code
     * @version 2010090201
     * @since 2010090201
     */
    public function setStatus($status) {
        $this->status = (int) $status;
    }

    /**
     * Sets a textual description of this status event
     * @param string $statusMessage Status message
     * @version 2010090201
     * @since 2010090201
     */
    public function setStatusMessage($statusMessage) {
        $this->statusMessage = $statusMessage;
    }

    /**
     * Set the time at which this status update occurred
     * @param int $updateTime Unix timestamp
     * @version 2010090201
     * @since 2010090201
     */
    public function setUpdateTime($updateTime) {
        $updateTime = (int) $updateTime;

        if ($updateTime > 0)
            $this->updateTime = $updateTime;
    }

}

?>