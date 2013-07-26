<?php

/**
 * File container for TxttoolsReceivedMessageDAO class
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
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012060101
 * @since 2011071901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtInboundMessage.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtInboundMessageTag.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');

/**
 * Database access controller for received SMS messages
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012060101
 * @since 2011071901
 */
class TxttoolsReceivedMessageDAO {    
    
    /**
     * Returns the number of messages received by a Moodle user
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of Moodle account to search against
     * @return int Number of records found
     * @version 2012042201
     * @since 2011071901
     */
    public function countMessagesInUsersInbox($userId, $newMessages = false) {
        
        global $DB;
        
        $params = array('userid' => $userId);
        
        if ($newMessages)
            $params['hasbeenread'] = 0;
                
        return $DB->count_records('block_moodletxt_inbox', $params);
        
    }

    /**
     * Returns all tags stored within a user's inbox
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of user to fetch tags for
     * @return MoodletxtInboundMessageTag[] Collection of tags found in inbox
     * @version 2012052301
     * @since 2012042301
     */
    public function getAllTagsForUser($userId) {
        
        global $DB;

        $sql = 'SELECT tags.id, tags.userid, tags.name, tags.colour, COUNT(link.tag) AS tagcount
                FROM {block_moodletxt_tags} tags
                LEFT JOIN {block_moodletxt_in_tag} link
                ON tags.id = link.tag
                LEFT JOIN {block_moodletxt_inbox} inbox
                ON link.message = inbox.id
                WHERE tags.userid = :userid
                GROUP BY tags.id, tags.userid, tags.name, tags.colour
                ORDER BY ' . $DB->sql_order_by_text('tags.name');    
        
        $tagSet = array();
        $tagList = $DB->get_records_sql($sql, array('userid' => $userId));
        
        foreach($tagList as $rawTag) {
            $tagSet[$rawTag->id] = new MoodletxtInboundMessageTag($rawTag->name, $rawTag->colour, $rawTag->id);
            $tagSet[$rawTag->id]->setTagCount($rawTag->tagcount);
        }
        
        return $tagSet;
        
    }
    
    /**
     * Returns a specific tag according to its database ID
     * @global moodle_database $DB Moodle database manager
     * @param int $tagId ID of tag to retrieve from DB
     * @param int $userId ID of user that owns tags (used for security)
     * @return MoodletxtInboundMessageTag Tag built from database record
     * @version 2012052701
     * @since 2012052701
     */
    public function getTagById($tagId, $userId = 0) {
        
        global $DB;
        
        $params = array('id' => $tagId);
        
        if ($userId > 0)
            $params['userid'] = $userId;
        
        $rawRecord = $DB->get_record('block_moodletxt_tags', $params);
        return new MoodletxtInboundMessageTag($rawRecord->name, $rawRecord->colour, $rawRecord->id);
        
    }
    
    /**
     * Gets a tag from the database according to its name
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of user that owns tag
     * @param string $tagName Name of tag to retrieve from inbox
     * @return MoodletxtInboundMessageTag Tag built from database record
     * @version 2012052701
     * @since 2012052701
     */
    public function getTagByName($userId, $tagName) {
        
        global $DB;
        
        $rawRecord = $DB->get_record('block_moodletxt_tags', array('userid' => $userId, 'name' => $tagName));
        return new MoodletxtInboundMessageTag($rawRecord->name, $rawRecord->colour, $rawRecord->id);
        
    }
    
    /**
     * Given the details of a tag, adds or updates them within the user's inbox
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of user to update tag for
     * @param string $tagName Name of tag to update
     * @param string $tagColour Colour of tag after update
     * @version 2012052801
     * @since 2012052201
     */
    public function createOrUpdateTag($userId, $tagName, $tagColour = '#ffffff') {
        
        global $DB;
        
        // Check that tag with this name does not already exist
        $existingRecord = $DB->get_record('block_moodletxt_tags', array('userid' => $userId, 'name' => $tagName));
        
        if ($existingRecord === false) {
            
            $newRecord = new object();
            $newRecord->userid = $userId;
            $newRecord->name   = $tagName;
            $newRecord->colour = $tagColour;
            
            $DB->insert_record('block_moodletxt_tags', $newRecord);
            
        } else {
            
            $existingRecord->colour = $tagColour;
            $DB->update_record('block_moodletxt_tags', $existingRecord);
            
        }
            
    }
    
    /**
     * Deletes a single tag from a user's inbox by its record ID
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of user to delete tag from
     * @param int $tagId ID of tag to delete from user
     * @version 2012052801
     * @since 2012052701
     */
    public function deleteTagById($userId, $tagId) {
        
        global $DB;
        
        // Check user owns record
        $existingRecord = $DB->get_record('block_moodletxt_tags', array('userid' => $userId, 'id' => $tagId));
        
        if ($existingRecord === false)
            throw new InvalidArgumentException(get_string('errortagnotfound', 'block_moodletxt'));
        else {
            $DB->delete_records('block_moodletxt_tags', array('userid' => $userId, 'id' => $existingRecord->id));
            $DB->delete_records('block_moodletxt_in_tag', array('tag' => $existingRecord->id));
        }
        
    }

    /**
     * Deletes a tag from the database by its name
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of user who owns the tag to delete
     * @param string $tagName Name of tag to delete from the user's inbox
     * @throws InvalidArgumentException
     * @version 2012052701
     * @since 2012052201
     */
    public function deleteTagByName($userId, $tagName) {
        
        global $DB;
        
        // Check user owns record
        $existingRecord = $DB->get_record('block_moodletxt_tags', array('userid' => $userId, 'name' => $tagName));
        
        if ($existingRecord === false)
            throw new InvalidArgumentException(get_string('errortagnotfound', 'block_moodletxt'));
        else {
            $DB->delete_records('block_moodletxt_tags', array('userid' => $userId, 'id' => $existingRecord->id));
            $DB->delete_records('block_moodletxt_in_tag', array('tag' => $existingRecord->id));
        }
        
    }
    
    /**
     * Associates a given tag with a given message
     * @global moodle_database $DB Moodle database manager
     * @param int $messageId ID of message to add tag to
     * @param int $tagId ID of tag to associate message with tag
     * @version 2012052701
     * @since 2012052701
     */
    public function addTagToMessage($messageId, $tagId) {
        
        global $DB;
        
        $tagLink = new object();
        $tagLink->message = $messageId;
        $tagLink->tag = $tagId;
        
        $DB->insert_record('block_moodletxt_in_tag', $tagLink);
        
    }
    
    /**
     * Disassociates a given tag from a given message
     * @global moodle_database $DB Moodle database manager
     * @param type $messageId ID of message to remove tag from
     * @param type $tagId ID of tag to disassociate from message
     * @version 2012052701
     * @since 2012052701
     */
    public function removeTagFromMessage($messageId, $tagId) {
        
        global $DB;
        
        $DB->delete_records('block_moodletxt_in_tag', 
                array('message' => $messageId, 'tag' => $tagId));
        
    }
        
    /**
     * Returns a fully populated inbox message from the database according to its ID
     * @param int $messageId Id of message to fetch
     * @param boolean $includeTags Whether to get applied tags for the message
     * @param int $userId Can be used to validate that the message belongs to a given user
     * @return MoodletxtInboundMessage Constructed message object from DB
     * @throws InvalidArgumentException Thrown if the message does not exist or the user attempts to access messages they do not own
     * @version 2012052301
     * @since 2012050301
     */
    public function getMessageById($messageId, $includeTags = true, $userId = 0) {
        
        global $DB;
        
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name        
        
        // GO JOE!
        $sql = 'SELECT message.*, owner.firstname AS ownerfirst, owner.lastname AS ownerlast,
                owner.username AS owneruser, sourcecontact.id AS contactid, 
                sourcecontact.phoneno AS contactphone, sourcecontact.company AS contactcompany,
                sourcecontact.firstname AS contactfirst,  sourcecontact.lastname AS contactlast,
                sourceuser.id AS sourceuserid, sourceuser.firstname AS sourceuserfirst,
                sourceuser.lastname AS sourceuserlast, sourceuser.username AS sourceusername,
                sourceuser.' . $numberField . ' AS sourceuserphone
                FROM {block_moodletxt_inbox} message
                INNER JOIN {user} owner
                    ON message.userid = owner.id
                LEFT JOIN {block_moodletxt_in_ab} ablink
                    ON ablink.receivedmessage = message.id
                LEFT JOIN {block_moodletxt_ab_entry} sourcecontact
                    ON ablink.contact = sourcecontact.id
                LEFT JOIN {block_moodletxt_in_u} userlink
                    ON userlink.receivedmessage = message.id
                LEFT JOIN {user} sourceuser
                    ON userlink.userid = sourceuser.id
                WHERE message.id = :id';
        
        $params = array('id' => $messageId);
        
        if ($userId > 0) {
            $sql .= ' AND owner.id = :userid';
            $params['userid'] = $userId;
        }
        
        $messageRecord = $DB->get_record_sql($sql, $params);
        
        // If the record is null, it may be the case that the user
        // is attempting to access data that is not theirs
        if ($messageRecord === false)
            throw new InvalidArgumentException(get_string('errorbadmessageid', 'block_moodletxt'));
        
        $message = $this->convertStandardClassToBeans($messageRecord);
        
        if ($includeTags)
            $message = $this->applyTagsToMessage($message);
        
        return $message;
        
    }
    
    /**
     * Returns a set of a user's received SMS messages. Plenty of
     * filtering and sorting options available as optional params.
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of the user to pull messages for
     * @param string $orderBy ORDER BY clause for database sorting
     * @param int $startFrom Index of first message to retrieve in set (0-indexed)
     * @param int $numberToGet Number of messages to fetch
     * @param boolean $includeTags Whether to link in tags (slower query)
     * @return MoodletxtInboundMessage[] Set of messages built from the database
     * @version 2012052301
     * @since 2011080401
     */
    public function getReceivedMessagesForUser($userId, $startFrom = 0, $numberToGet = 20,
            $orderBy = 'timereceived DESC', $includeTags = false) {
        
        global $DB;
                
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name        
        
        // Needs to be complicated to get ownership info, source info, etc, etc
        $sql = 'SELECT message.*, owner.firstname AS ownerfirst, owner.lastname AS ownerlast,
                owner.username AS owneruser, sourcecontact.id AS contactid, 
                sourcecontact.phoneno AS contactphone, sourcecontact.company AS contactcompany,
                sourcecontact.firstname AS contactfirst,  sourcecontact.lastname AS contactlast,
                sourceuser.id AS sourceuserid, sourceuser.firstname AS sourceuserfirst,
                sourceuser.lastname AS sourceuserlast, sourceuser.username AS sourceusername,
                sourceuser.' . $numberField . ' AS sourceuserphone
                FROM {block_moodletxt_inbox} message
                INNER JOIN {user} owner
                    ON message.userid = owner.id
                LEFT JOIN {block_moodletxt_in_ab} ablink
                    ON ablink.receivedmessage = message.id
                LEFT JOIN {block_moodletxt_ab_entry} sourcecontact
                    ON ablink.contact = sourcecontact.id
                LEFT JOIN {block_moodletxt_in_u} userlink
                    ON userlink.receivedmessage = message.id
                LEFT JOIN {user} sourceuser
                    ON userlink.userid = sourceuser.id
                WHERE owner.id = :userid';
        
        $sql .= ' ORDER BY ' . $orderBy;
        
        $params = array('userid' => $userId);
        
        $receivedMessages = $DB->get_records_sql(
                $sql, $params, $startFrom, $numberToGet
        );
        
        $returnedSet = array();
        
        foreach($receivedMessages as $receivedMessage){
            
            $messageObject = $this->convertStandardClassToBeans($receivedMessage);
            
            if ($includeTags)
                $this->applyTagsToMessage($messageObject);
                
            $returnedSet[$receivedMessage->id] = $messageObject;
            
        }
        
        return $returnedSet;
        
    }
    
    /**
     * Gets all messages from a user's inbox since a given timestamp
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of the user to pull messages for
     * @param int $startTime Unix timestamp to get messages from
     * @param int $numberToGet Number of messages to fetch
     * @param string $orderBy ORDER BY clause for database sorting
     * @param boolean $includeTags Whether to link in tags (slower query)
     * @return MoodletxtInboundMessage[] Set of messages built from the database
     * @version 2012052301
     * @since 2012042501
     */
    public function getReceivedMessagesSinceTime($userId, $startTime, $numberToGet = 0,
            $orderBy = 'timereceived DESC', $includeTags = false) {
        
        global $DB;
        
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name        
        
        // Needs to be complicated to get ownership info, source info, etc, etc
        $sql = 'SELECT message.*, owner.firstname AS ownerfirst, owner.lastname AS ownerlast,
                owner.username AS owneruser, sourcecontact.id AS contactid, 
                sourcecontact.phoneno AS contactphone, sourcecontact.company AS contactcompany,
                sourcecontact.firstname AS contactfirst,  sourcecontact.lastname AS contactlast,
                sourceuser.id AS sourceuserid, sourceuser.firstname AS sourceuserfirst,
                sourceuser.lastname AS sourceuserlast, sourceuser.username AS sourceusername,
                sourceuser.' . $numberField . ' AS sourceuserphone
                FROM {block_moodletxt_inbox} message
                INNER JOIN {user} owner
                    ON message.userid = owner.id
                LEFT JOIN {block_moodletxt_in_ab} ablink
                    ON ablink.receivedmessage = message.id
                LEFT JOIN {block_moodletxt_ab_entry} sourcecontact
                    ON ablink.contact = sourcecontact.id
                LEFT JOIN {block_moodletxt_in_u} userlink
                    ON userlink.receivedmessage = message.id
                LEFT JOIN {user} sourceuser
                    ON userlink.userid = sourceuser.id
                WHERE owner.id = :userid
                AND timereceived > :starttime';
        
        $sql .= ' ORDER BY ' . $orderBy;
        
        $params = array('userid' => $userId, 'starttime' => $startTime);
        
        $receivedMessages = $DB->get_records_sql(
                $sql, $params, 0, $numberToGet
        );
        
        $returnedSet = array();
        
        foreach($receivedMessages as $receivedMessage) {
            $message = $this->convertStandardClassToBeans($receivedMessage);
            
            if ($includeTags)
                $this->applyTagsToMessage($message);
            
            $returnedSet[$receivedMessage->id] = $message;
            
        }
        
        return $returnedSet;
        
    }
    
    /**
     * Finds all tags within the database associated with a message object
     * and adds them to the object
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtInboundMessage $message Message to find tags for
     * @return MoodletxtInboundMessage Updated message object
     * @version 2012050401
     * @since 2012042501
     */
    public function applyTagsToMessage(MoodletxtInboundMessage $message) {
        
        global $DB;
        
        $sql = 'SELECT tags.*
                FROM {block_moodletxt_tags} tags
                INNER JOIN {block_moodletxt_in_tag} link
                    ON link.tag = tags.id
                INNER JOIN {block_moodletxt_inbox} messages
                    ON link.message = messages.id
                WHERE messages.id = :messageid';
        
        $tagSet = $DB->get_records_sql($sql, array('messageid' => $message->getId()));
        
        foreach($tagSet as $tag)
            $message->addTag(new MoodletxtInboundMessageTag($tag->name, $tag->colour, $tag->id));
        
        return $message;
        
    }
    
    /**
     * Saves a single inbound message to the database
     * @param MoodletxtInboundMessage $message Message to save
     * @version 2012041701
     * @since 2012041701
     */
    public function saveInboundMessage(MoodletxtInboundMessage $message) {
        $this->saveInboundMessages(array($message));
    }

    /**
     * Saves inbound messages to the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtInboundMessage[] $messages Messages to save
     * @version 2012050801
     * @since 2012041701
     */
    public function saveInboundMessages(array $messages) {
        
        global $DB;
        
        // Needed to do addressbook/user lookups
        $addressbookDAO = new MoodletxtAddressbookDAO();
        
        foreach($messages as $message) {
            
            if (! $message instanceof MoodletxtInboundMessage)
                continue;
            
            
            // Look for associated users or contacts
            if ($message->hasOwner())
                $addressbookOwners = array($message->getOwner()->getId());
            else
                $addressbookOwners = $message->getDestinationUserIds();
            
            $associatedSources = $addressbookDAO->associateNumberWithRecipient
                    ($message->getSourceNumber(), $addressbookOwners);
            
            if (isset($associatedSources['user'])) {
                $message->setAssociatedSource($associatedSources['user']);
            } else if (isset($associatedSources['addressbook'])) {
                $message->setAssociatedSource ($associatedSources['addressbook']);
            } else {
                $message->setSourceFirstName(get_string('fragunknown', 'block_moodletxt'));
                $message->setSourceLastName(get_string('fragunknown', 'block_moodletxt'));
            }
            
            // Tear-down to basic object
            $rawMessages = $this->convertBeanToStandardClasses($message);
            
            foreach($rawMessages as $rawMessage) {
            
                // Insert or update
                if (isset($rawMessage->id))
                    $DB->update_record('block_moodletxt_inbox', $rawMessage);
                else
                    $rawMessage->id = $DB->insert_record('block_moodletxt_inbox', $rawMessage);


                // If an associated source was found, we need to make a link
                $associatedSource = $message->getSource();

                if ($associatedSource instanceof MoodletxtBiteSizedUser) {

                    // If there was already a link, nuke it
                    $DB->delete_records('block_moodletxt_in_u', array('receivedmessage' => $rawMessage->id));
                    
                    $userLink = new object();
                    $userLink->userid = $associatedSource->getId();
                    $userLink->receivedmessage = $rawMessage->id;

                    $DB->insert_record('block_moodletxt_in_u', $userLink);
                    
                } else if ($associatedSource instanceof MoodletxtAddressbookRecipient) {

                    // If there was already a link, nuke it
                    $DB->delete_records('block_moodletxt_in_ab', array('receivedmessage' => $rawMessage->id));
                    
                    $contactLink = new object();
                    $contactLink->contactid = $associatedSource->getContactId();
                    $contactLink->receivedmessage = $rawMessage->id;

                    $DB->insert_record('block_moodletxt_in_ab', $contactLink);

                }
                
            }
            
        }
        
    }
    
    /**
     * Special-case maintenance function used to remove redundant
     * links to a Moodle user record when that user is deleted from
     * the Moodle system.
     * @global moodle_database $DB Moodle database controller 
     * @param int $userId Moodle user ID
     * @version 2012041801
     * @since 2012041801
     */
    public function removeMessageLinksForUser($userId) {
        
        global $DB;
        
        $DB->delete_records('block_moodletxt_in_u', array('userid' => $userId));
        
    }
    
    /**
     * Deletes a single message from the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtInboundMessage $message Message to delete
     * @version 2012051101
     * @since 2012050301
     */
    public function deleteMessage(MoodletxtInboundMessage $message) {
        
        global $DB;

        $messageIsValid = $DB->record_exists(
            'block_moodletxt_inbox', 
            array(
                'id' => $message->getId(), 
                'userid' => $message->getOwner()->getId())
            );
        
        
        if ($messageIsValid) {
        
            $DB->delete_records('block_moodletxt_inbox', 
                    array(
                        'id' => $message->getId(),
                        'userid' => $message->getOwner()->getId()
                    ));

            $DB->delete_records('block_moodletxt_in_ab', 
                    array('receivedmessage' => $message->getId()));

            $DB->delete_records('block_moodletxt_in_u',
                    array('receivedmessage' => $message->getId()));

            $DB->delete_records('block_moodletxt_in_tag',
                    array('message' => $message->getId()));
        
        }
        
    }
    
    /**
     * Deletes a number of messages from the database according to their record IDs
     * @global moodle_database $DB Moodle database manager
     * @param array $messageIds Record IDs of messages to delete
     * @param int $userId ID of user who owns messages
     * @throws InvalidArgumentException
     * @version 2012051401
     * @since 2012050301
     */
    public function deleteMessagesByIds(array $messageIds, $userId = 0) {
        
        global $DB;
        
        // Check that messages exist and are owned by the user
        list ($inOrEqual, $params) = $DB->get_in_or_equal($messageIds, SQL_PARAMS_NAMED);
        
        $sql = 'id ' . $inOrEqual;
        
        if ($userId > 0) {
            $sql .= ' AND userid = :userid';
            $params['userid'] = $userId;
        }
        
        $validRecordChecks = $DB->get_fieldset_select(
                'block_moodletxt_inbox',
                'id', $sql, $params);

        if (count($validRecordChecks) == 0) {
            
            throw new InvalidArgumentException(get_string('errorbadmessageid', 'block_moodletxt'));
            
        } else {
        
            // Do the same again to do the actual deletion
            list ($inOrEqual, $params) = $DB->get_in_or_equal($validRecordChecks, SQL_PARAMS_NAMED);

            $sql = 'id ' . $inOrEqual;        
            $DB->delete_records_select('block_moodletxt_inbox', $sql, $params);

            $sql = 'receivedmessage ' . $inOrEqual;
            $DB->delete_records_select('block_moodletxt_in_ab', $sql, $params);
            $DB->delete_records_select('block_moodletxt_in_u', $sql, $params);

            $sql = 'message ' . $inOrEqual;
            $DB->delete_records_select('block_moodletxt_in_tag', $sql, $params);
            
        }
        
    }

    /**
     * Converts a raw database record to a full-fat message object
     * @param object $stdObject Database record object
     * @return MoodletxtInboundMessage Constructed message object
     * @TODO Store and retrieve destination txttools accounts
     * @version 2012060101
     * @since 2011080401
     */
    private function convertStandardClassToBeans($stdObject) {
        
        $inboundMessage = new MoodletxtInboundMessage(
            $stdObject->ticket, 
            $stdObject->messagetext, 
            new MoodletxtPhoneNumber($stdObject->source),
            $stdObject->timereceived,
            $stdObject->hasbeenread, 
            $stdObject->id
        );
        
        if (isset($stdObject->owneruser))
            $inboundMessage->setOwner(new MoodletxtBiteSizedUser(
                    $stdObject->userid, 
                    $stdObject->owneruser, 
                    $stdObject->ownerfirst,
                    $stdObject->ownerlast
            ));
        
        if (isset($stdObject->sourceusername)) {
            
            $userObject = new MoodletxtBiteSizedUser(
                    $stdObject->sourceuserid, 
                    $stdObject->sourceusername, 
                    $stdObject->sourceuserfirst, 
                    $stdObject->sourceuserlast        
            );
            
            try {
                $userNumber = new MoodletxtPhoneNumber($stdObject->sourceuserphone);
                $userObject->setRecipientNumber($userNumber);
            } catch(InvalidPhoneNumberException $ex) {
                // Phone number in user's Moodle profile is invalid - ignore it
            }
                    
            $inboundMessage->setAssociatedSource($userObject);
            
        } else if (isset($stdObject->contactid)) {
            
            $inboundMessage->setAssociatedSource(new MoodletxtAddressbookRecipient(
                    new MoodletxtPhoneNumber($stdObject->contactphone),
                    $stdObject->contactfirst,
                    $stdObject->contactlast,
                    $stdObject->contactcompany,
                    $stdObject->contactid
            ));
            
        }
        
        $inboundMessage->setSourceFirstName($stdObject->sourcefirstname);
        $inboundMessage->setSourceLastName($stdObject->sourcelastname);
        
        return $inboundMessage;
        
    }

    /**
     * Converts a message object down to a basic data object for use in the DB
     * @param MoodletxtInboundMessage $bean Message to convert
     * @return array One or more raw database row objects
     * @TODO Store and retrieve destination txttools accounts
     * @version 2012050401
     * @since 2011080401
     */
    private function convertBeanToStandardClasses(MoodletxtInboundMessage $bean) {
        
        $returnedObjects = array();

        // Existing record - update
        if ($bean->getId() > 0) {
        
            $stdObject = new stdClass();
            $stdObject->id              = $bean->getId();
            $stdObject->userid          = $bean->getOwner()->getId();
            $stdObject->ticket          = $bean->getMessageTicket();
            $stdObject->messagetext     = $bean->getMessageText();
            $stdObject->source          = $bean->getSourceNumber()->getPhoneNumber();
            $stdObject->sourcefirstname = $bean->getSourceFirstName();
            $stdObject->sourcelastname  = $bean->getSourceLastName();
            $stdObject->timereceived    = $bean->getTimeReceived();
            $stdObject->hasbeenread     = $bean->getHasBeenRead();
                        
        } else {
            
            // Fresh from being filtered or forwarded - make new
            // message objects for destination users
            foreach($bean->getDestinationUserIds() as $userId) {

                $stdObject = new stdClass();
                $stdObject->userid          = $userId;
                $stdObject->ticket          = $bean->getMessageTicket();
                $stdObject->messagetext     = $bean->getMessageText();
                $stdObject->source          = $bean->getSourceNumber()->getPhoneNumber();
                $stdObject->sourcefirstname = $bean->getSourceFirstName();
                $stdObject->sourcelastname  = $bean->getSourceLastName();
                $stdObject->timereceived    = $bean->getTimeReceived();
                $stdObject->hasbeenread     = $bean->getHasBeenRead();

                array_push($returnedObjects, $stdObject);

            }
            
        }
        
        return $returnedObjects;
        
    }
    
}

?>