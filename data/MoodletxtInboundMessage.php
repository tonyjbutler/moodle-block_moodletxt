<?php

/**
 * File container for the MoodletxtInboundMessage class
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
 * @see MoodletxtInboundMessage
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011040701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');

/**
 * Data bean represents an inbound SMS message
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011040701
 */
class MoodletxtInboundMessage {

    /**
     * The database record ID for this inbound message
     * @var int
     */
    private $id;

    /**
     * The user to whom this message belongs (whose inbox it's in)
     * @var MoodletxtBiteSizedUser
     */
    private $owner = 0;
    
    /**
     * The ticket allocated to this message by the txttools system
     * @var int
     */
    private $messageTicket = 0;

    /**
     * The text of the received SMS message
     * @var string
     */
    private $messageText = '';

    /**
     * The phone number that the SMS message was received from
     * @var MoodletxtPhoneNumber
     */
    private $sourceNumber;

    /**
     * The timestamp at which this SMS message was received
     * @var int
     */
    private $timeReceived;
    
    /**
     * Holds tags assigned to this message
     * @var MoodletxtInboundMessageTag[] 
     */
    private $tags = array();

    /**
     * The first name of the message source, if known
     * @var string
     */
    private $sourceFirstName = '';
    
    /**
     * The last name of the message source, if known
     * @var string
     */
    private $sourceLastName = '';

    /**
     * If we know who the person is that sent this in, they're linked here
     * @var MoodletxtRecipient
     */
    private $associatedSource;
    
    /**
     * Indicates whether or not this message has been read in the inbox
     * @var boolean
     */
    private $hasBeenRead = false;

    /**
     * The phone number the message was sent into
     * @var MoodletxtPhoneNumber
     */
    private $destinationNumber;

    /**
     * The ID of the Moodle account this message was sent into
     * @var int
     */
    private $destinationAccountId = 0;

    /**
     * The username of the Moodle account this message was sent into
     * @var string
     */
    private $destinationAccountUsername = '';

    /**
     * UserIDs to filter this message to
     * (Only used in filtering system.)
     * @var int[]
     */
    private $destinationUserIds = array();
    

    /**
     * Sets up the inbound message bean
     * @param int $messageTicket The ticket of the received SMS message
     * @param string $messageText The text of the received SMS message
     * @param string $sourceNumber The number the message came from
     * @param int $timeReceived The timestamp at which the message arrived
     * @version 2012051101
     * @since 2011040701
     */
    function __construct($messageTicket, $messageText, MoodletxtPhoneNumber $sourceNumber,
                $timeReceived, $hasBeenRead, $id = 0) {

        $this->setId($id);
        $this->setMessageTicket($messageTicket);
        $this->setMessageText($messageText);
        $this->setSourceNumber($sourceNumber);
        $this->setTimeReceived($timeReceived);
        $this->setHasBeenRead($hasBeenRead);

    }

    /**
     * Set the ticket for this message
     * @param int $messageTicket Ticket number
     * @version 2011040701
     * @since 2011040701
     */
    public function setMessageTicket($messageTicket) {

        if (is_numeric($messageTicket))
            $this->messageTicket = (int) $messageTicket;

    }

    /**
     * Set the text of this message
     * @param string $messageText Message text
     * @version 2011040701
     * @since 2011040701
     */
    public function setMessageText($messageText) {
        $this->messageText = $messageText;
    }

    /**
     * Set the source phone number for the message
     * @param MoodletxtPhoneNumber $sourceNumber Source number
     * @version 2012041701
     * @since 2011040701
     */
    public function setSourceNumber(MoodletxtPhoneNumber $sourceNumber) {
        $this->sourceNumber = $sourceNumber;
    }

    /**
     * Set the time at which this message was received
     * @param int $timeReceived Timestamp of arrival
     * @version 2011040701
     * @since 2011040701
     */
    public function setTimeReceived($timeReceived) {

        if (is_numeric($timeReceived))
            $this->timeReceived = (int) $timeReceived;

    }

    /**
     * Get the txttools ticket for this message
     * @return int Message ticket
     * @version 2011040701
     * @since 2011040701
     */
    public function getMessageTicket() {
        return $this->messageTicket;
    }

    /**
     * Get the text of this message
     * @return string Message text
     * @version 2011040701
     * @since 2011040701
     */
    public function getMessageText() {
        return $this->messageText;
    }

    /**
     * Get the source phone number for this message
     * @return MoodletxtPhoneNumber Phone number
     * @version 2012031401
     * @since 2011040701
     */
    public function getSourceNumber() {
        return $this->sourceNumber;
    }

    /**
     * Get the time at which this SMS was received
     * @param string $dateFormat Optional date/time formatting string
     * @return int Time received
     * @version 2011080501
     * @since 2011040701
     */
    public function getTimeReceived($dateFormat = '') {
        
        if ($dateFormat != '')
            return userdate($this->timeReceived, $dateFormat);
        else
            return $this->timeReceived;

    }

    /**
     * Returns the user to whom this message belongs
     * @return MoodletxtBiteSizedUser Moodle user object
     * @version 2012042401
     * @since 2012042401
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * Sets the user to whom this message belongs
     * @param MoodletxtBiteSizedUser $owner Moodle user object
     * @version 2012042401
     * @since 2012042401
     */
    public function setOwner(MoodletxtBiteSizedUser $owner) {
        $this->owner = $owner;
    }

    /**
     * Returns whether or not this message has been 
     * associated with a Moodle user to own it
     * @return boolean Whether owner object exists
     * @version 20120504
     * @since 20120504
     */
    public function hasOwner() {
        return ($this->owner instanceof MoodletxtBiteSizedUser);
    }
    
    /**
     * Clears any Moodle user that has been associated
     * with this message as its owner
     * @version 20120504
     * @since 20120504
     */
    public function clearOwner() {
        $this->owner = null;
    }
    
    /**
     * Returns all tags applied on this message
     * @return MoodletxtInboundMessageTag[] Collection of tags
     * @version 2012042401
     * @since 2012042401
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * Sets the tags applied on this message
     * @param MoodletxtInboundMessageTag[] $tags Collection of tags
     * @version 2012042401
     * @since 2012042401
     */
    public function setTags($tags) {
        $this->tags = $tags;
    }

    /**
     * Applies a single tag to this inbound message
     * @param MoodletxtInboundMessageTag $tag Tag to apply
     * @version 2012050401
     * @since 2012050401
     */
    public function addTag(MoodletxtInboundMessageTag $tag) {
        $this->tags[$tag->getId()] = $tag;
    }
    
    /**
     * Returns a set of IDs of users to whom this message
     * will be forwarded
     * @return array(int) User IDs
     * @version 2012042401
     * @since 2012042401
     */
    public function getDestinationUserIds() {
        return $this->destinationUserIds;
    }

    /**
     * Sets the IDs of users to whom this message should be forwarded
     * @param array $destinationUserIds IDs of users to give this message to
     * @version 2012050401
     * @since 2012042401
     */
    public function setDestinationUserIds(array $destinationUserIds) {
        $this->destinationUserIds = $destinationUserIds;
        
        // Adding destination IDs means we need to clear ownership
        // info if it exists, for when this is written to the DB
        $this->clearOwner();
        $this->setId(0);
    }

    /**
     * Returns the number of users this message is being forwarded to
     * @return int Number of destination users
     * @version 2012042401
     * @since 2012042401
     */
    public function getDestinationUserCount() {
        return count($this->destinationUserIds);
    }

    /**
     * Adds a single user ID to the set of users that will receive this message
     * @param int $userId Destination Moodle user ID
     * @version 2012050401
     * @since 2012042401
     */
    public function addDestinationUserId($userId) {
        array_push($this->destinationUserIds, $userId);

        // Adding destination IDs means we need to clear ownership
        // info if it exists, for when this is written to the DB
        $this->clearOwner();
        $this->setId(0);
    }

    /**
     * Adds a set of Moodle user IDs to the set of users that will receive this message
     * @param array $userIds IDs of users to forward this message to.
     * @version 2012050401
     * @since 2012042401
     */
    public function addDestinationUserIds(array $userIds) {
        $this->destinationUserIds += $userIds;

        // Adding destination IDs means we need to clear ownership
        // info if it exists, for when this is written to the DB
        $this->clearOwner();
        $this->setId(0);
    }    
    
    /**
     * Returns the first name of the human who sent this message in
     * @return string Sender's first name
     * @version 2012051001
     * @since 2012041701
     */
    public function getSourceFirstName() {
        if ($this->associatedSource instanceof MoodletxtRecipient)
            return $this->associatedSource->getFirstName();
        else
            return $this->sourceFirstName;
    }

    /**
     * Sets the first name of the human who sent this message in, if known
     * @param string $sourceFirstName Sender's first name
     * @version 2012041701
     * @since 2012041701
     */
    public function setSourceFirstName($sourceFirstName) {
        $this->sourceFirstName = $sourceFirstName;
    }

    /**
     * Returns the last name of the human who sent this message in
     * @return string Sender's last name
     * @version 2012051001
     * @since 2012041701
     */
    public function getSourceLastName() {
        if ($this->associatedSource instanceof MoodletxtRecipient)
            return $this->associatedSource->getLastName();
        else
            return $this->sourceLastName;
    }

    /**
     * Sets the last name of the human who sent this message in, if known
     * @param string $sourceLastName Sender's last name
     * @version 2012041701
     * @since 2012041701
     */
    public function setSourceLastName($sourceLastName) {
        $this->sourceLastName = $sourceLastName;
    }
    
    /**
     * Sets the associated source of the message, if known.
     * This is whatever Moodle User or addressbook contact sent in the message.
     * @param MoodletxtRecipient $associatedSource Known source for the message
     * @version 2012060101
     * @since 2012041701
     */
    public function setAssociatedSource(MoodletxtRecipient $associatedSource) {
        $this->associatedSource = $associatedSource;
        
        // Update persistent fields while we're at it
        $this->setSourceFirstName($associatedSource->getFirstName());
        $this->setSourceLastName($associatedSource->getLastName());
        
        if ($associatedSource->hasPhoneNumber())
            $this->setSourceNumber($associatedSource->getRecipientNumber());
    }

    /**
     * Returns the source of the message, whether known or unknown
     * @return MoodletxtRecipient Known source for the message
     * @version 2012051001
     * @since 2012041701
     */
    public function getSource() {
        
        if ($this->associatedSource instanceof MoodletxtRecipient)
            return $this->associatedSource;
        else
            return new MoodletxtAdditionalRecipient(
                $this->getSourceNumber(), $this->getSourceFirstName(), $this->getSourceLastName());
        
    }
    
    /**
     * Gets the message source's name, formatted for display
     * @return string Formatted source name
     * @version 2013052301
     * @since 2012042401
     */
    public function getSourceNameForDisplay() {
        
        if ($this->associatedSource instanceof MoodletxtRecipient)
            return $this->associatedSource->getFullNameForDisplay();
        else
            return MoodletxtStringHelper::formatNameForDisplay($this->getSourceFirstName(), $this->getSourceLastName());
        
    }
    
    /**
     * Returns whether this message has been viewed by the user
     * @return boolean True if the message has been read
     * @version 2011040701
     * @since 2011040701
     */
    public function getHasBeenRead() {
        return $this->hasBeenRead;
    }

    /**
     * Sets whether the message has been viewed by the user
     * @param boolean $hasBeenRead True if the message has been read
     * @version 2011040701
     * @since 2011040701
     */
    public function setHasBeenRead($hasBeenRead) {
        $this->hasBeenRead = $hasBeenRead;
    }

    /**
     * Returns the number this message was sent to
     * @return MoodletxtPhoneNumber Destination phone number
     * @version 2011040701
     * @since 2011040701
     */
    public function getDestinationNumber() {
        return $this->destinationNumber;
    }

    /**
     * Sets the number this message was sent to
     * @param MoodletxtPhoneNumber $destinationNumber Destination phone number
     * @version 2012031401
     * @since 2011040701
     */
    public function setDestinationNumber(MoodletxtPhoneNumber $destinationNumber) {
        $this->destinationNumber = $destinationNumber;
    }

    /**
     * Returns the ID of the txttools account this message was sent in to
     * @return int txttools account ID
     * @version 2011040701
     * @since 2011040701
     */
    public function getDestinationAccountId() {
        return $this->destinationAccountId;
    }

    /**
     * Sets the ID of the txttools account this message was sent in to
     * @param int $destinationAccountId txttools account ID
     * @version 2011040701
     * @since 2011040701
     */
    public function setDestinationAccountId($destinationAccountId) {
        $this->destinationAccountId = $destinationAccountId;
    }

    /**
     * Returns the username of the txttools account this message was sent in to
     * @return string txttools account name
     * @version 2011040701
     * @since 2011040701
     */
    public function getDestinationAccountUsername() {
        return $this->destinationAccountUsername;
    }

    /**
     * Sets the username of the txttools account this message was sent in to
     * @param string $destinationAccountUsername txttools account name
     * @version 2011040701
     * @since 2011040701
     */
    public function setDestinationAccountUsername($destinationAccountUsername) {
        $this->destinationAccountUsername = $destinationAccountUsername;
    }

    /**
     * Returns the database record ID for this message
     * @return int Message ID
     * @version 2011040701
     * @since 2011040701
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the database record ID for this message
     * @param int $id Message ID
     * @version 2012051101
     * @since 2011040701
     */
    public function setId($id) {
        $id = (int) $id;
        
        if ($id >= 0)
            $this->id = $id;
    }

}

?>