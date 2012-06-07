<?php

/**
 * File container for TxttoolsSentMessageDAO class
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
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012042401
 * @since 2011060901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/TxttoolsAccount.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtBiteSizedUser.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbookRecipient.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAdditionalRecipient.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundMessage.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundSMS.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundSMSStatus.php');

/**
 * Database access controller for sent SMS messages
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012042401
 * @since 2011060901
 */
class TxttoolsSentMessageDAO {    
    
    /**
     * Returns the number of messages sent from a txttools account
     * @global moodle_database $DB Moodle database manager
     * @param int $txttoolsAccountId ID of txttools account to search against
     * @param int $moodleUserId ID of Moodle user to search against
     * @return int Number of records found
     * @version 2011101001
     * @since 2011101001
     */
    public function countMessagesSent($txttoolsAccountId = 0, $moodleUserId = 0) {
        
        global $DB;
        
        $params = array();
        
        if ($txttoolsAccountId > 0)
            $params['txttoolsaccount'] = $txttoolsAccountId;
        
        if ($moodleUserId > 0)
            $params['userid'] = $moodleUserId;
        
        return $DB->count_records('block_moodletxt_outbox', $params);
        
    }
    
    /**
     * Returns the number of recipients within the database, or for a given message ID
     * @global moodle_database $DB Moodle database manager
     * @param int $messageId ID of processed message to search against
     * @return int Number of records found
     * @version 2012041001
     * @since 2011101001
     */
    public function countMessageRecipients($messageId = 0) {
        
        global $DB;
        
        $params = array();
        
        if ($messageId > 0)
            $params['messageid'] = $messageId;
        
        return $DB->count_records('block_moodletxt_sent', $params);
        
    }
    
    /**
     * Returns a single outbound message according to its database ID
     * @global moodle_database $DB Moodle database manager
     * @param int $messageId ID of message to fetch
     * @param boolean $includeSent Whether to fetch child/protocol-specific messages
     * @return MoodletxtOutboundMessage Message from database
     * @version 2012042401
     * @since 2012031901
     */
    public function getSentMessageById($messageId, $includeSent = false, $includeStatuses = false) {
        
        global $DB;
        
        $sql = 'SELECT o.id, u.id AS moodleuserid, u.username AS moodleuser, 
                u.firstname, u.lastname, acc.username AS txttoolsuser, acc.password AS encrypted,
                u2.firstname AS defaultfirst, u2.lastname AS defaultlast, u2.username AS defaultusername,
                acc.description, acc.defaultuser, o.messagetext, o.timesent, o.type
                FROM {block_moodletxt_outbox} o
                INNER JOIN {block_moodletxt_accounts} acc
                    ON o.txttoolsaccount = acc.id
                INNER JOIN {user} u
                    ON o.userid = u.id
                INNER JOIN {user} u2
                    ON acc.defaultuser = u2.id
                WHERE o.id = :messageid';
        
        $message = $DB->get_record_sql($sql, array('messageid' => $messageId));
        $messageObject = $this->convertStandardClassToBean($message);
        
        if ($includeSent) {
            
            $messageObject->setSentSMSMessages(
                $this->getSentSMSMessagesForMessage($messageObject->getId(),
                    'sendlastname ASC, sendfirstname ASC', 0, 0, $includeStatuses));
            
        }
        
        return $messageObject;
        
    }
    
    /**
     * Retrieves sent messages from the database. These can be filtered down by user, time, and limits,
     * and sorted accordingly. This functionality is now all abstracted into a nice
     * DAO method, so we don't have to run it inside the script. 
     * @global moodle_database $DB Moodle database manager
     * @param int $userId Database identifier of user
     * @param string $orderBy SQL fragment indicating sort field and direction
     * @param int $timeFrom Unix UTC timestamp to retrieve messages after
     * @param int $timeTo Unix UTC timestamp to retrieve messages before
     * @param int $limitFrom Number of first record in table to retrieve (0-indexed)
     * @param int $limitNum Number of records to retrieve from table
     * @version 2012042401
     * @since 2011081101
     */
    public function getSentMessagesForUser($userId = 0, $orderBy = 'o.timesent DESC', $timeFrom = 0, $timeTo = 0, $limitFrom = 0, $limitNum = 0) {
        
        global $DB;
        
        $sqlParams = array();
        
        $sql = 'SELECT o.id, u.id AS moodleuserid, u.username AS moodleuser, 
                u.firstname, u.lastname, acc.username AS txttoolsuser, 
                u2.firstname AS defaultfirst, u2.lastname AS defaultlast, u2.username AS defaultusername,
                acc.description, acc.defaultuser, o.messagetext, o.timesent, o.type
                FROM {block_moodletxt_outbox} o
                INNER JOIN {block_moodletxt_accounts} acc
                    ON o.txttoolsaccount = acc.id
                INNER JOIN {user} u
                    ON o.userid = u.id
                INNER JOIN {user} u2
                    ON acc.defaultuser = u2.id';
        
        if ($userId > 0) {
            $sql .= (strpos($sql, 'WHERE')) ? ' AND ' : ' WHERE ';
            $sql .= 'u.id = :userid';
            $sqlParams['userid'] = $userId;
        }
        
        if ($timeFrom > 0) {
            $sql .= (strpos($sql, 'WHERE')) ? ' AND ' : ' WHERE ';
            $sql .= 'o.timesent >= :timefrom';
            $sqlParams['timefrom'] = $timeFrom;
        }
        
        if ($timeTo > 0) {
            $sql .= (strpos($sql, 'WHERE')) ? ' AND ' : ' WHERE ';
            $sql .= 'o.timesent <= :timeto';
            $sqlParams['timeto'] = $timeTo;
        }

        $sql .= ' ORDER BY ' . $orderBy;

        $sentMessages = $DB->get_records_sql($sql, $sqlParams, $limitFrom, $limitNum);
        
        $returnArray = array();
        
        foreach($sentMessages as $sentMessage)
            array_push($returnArray, $this->convertStandardClassToBean($sentMessage));
        
        return $returnArray;
        
    }

    /**
     * Gets individual SMS messages for a given message ID
     * @global moodle_database $DB Moodle database manager
     * @param int $messageId Message ID to get SMS messages for
     * @param string $sort SQL fragment to sort results
     * @param int $limitFrom Number of first record in table to retrieve (0-indexed)
     * @param int $limitNum Number of records to retrieve from table
     * @return MoodletxtOutboundSMS[] SMS messages found under message ID
     * @version 2012041001
     * @since 2012031901
     */
    public function getSentSMSMessagesForMessage($messageId, $sort = 'sendlastname ASC, 
        sendfirstname ASC', $limitFrom = 0, $limitNum = 0, $includeLatestStatuses = false) {
        
        global $DB;

        // Big complicated query to get sent messages, latest status updates,
        // recipient details, the works. Lots of joins!
        // Need to pull out links to addressbook contacts and user profiles
        // so that names and profile links can be accurately displayed
        $sql = 'SELECT sent.*, messages.messagetext, contact.id AS contactid,
                contact.lastname AS contactlast, contact.firstname AS contactfirst, 
                contact.company, userrec.id AS userid, userrec.username, 
                userrec.lastname AS userlast, userrec.firstname AS userfirst';
        
        if ($includeLatestStatuses)
            $sql .= ', status.id AS statusid, status.status, status.statusmessage, status.updatetime';
                
        $sql .= '
                FROM {block_moodletxt_sent} sent
                INNER JOIN {block_moodletxt_outbox} messages
                ON sent.messageid = messages.id
                LEFT JOIN {block_moodletxt_sent_ab} contactlink
                ON sent.id = contactlink.sentmessage
                LEFT JOIN {block_moodletxt_ab_entry} contact
                ON contactlink.contact = contact.id
                LEFT JOIN {block_moodletxt_sent_u} userlink
                ON sent.id = userlink.sentmessage
                LEFT JOIN {user} userrec
                ON userlink.userid = userrec.id';
        
        if ($includeLatestStatuses)
            $sql .= '
                LEFT OUTER JOIN {block_moodletxt_status} status
                ON status.ticketnumber = sent.ticketnumber
                LEFT OUTER JOIN {block_moodletxt_status} statusmirror
                ON status.ticketnumber = statusmirror.ticketnumber
                AND statusmirror.updatetime > status.updatetime
                WHERE statusmirror.id IS NULL
                AND sent.messageid = :messageid';
        
        else
            $sql .= '
                WHERE sent.messageid = :messageid';
        
        $sql .='
                ORDER BY :sort';
                
        $params = array('messageid' => $messageId, 'sort' => $sort);
        
        $sentMessages = $DB->get_records_sql($sql, $params, $limitFrom, $limitNum);        
        $returnedMessages = array();
        
        foreach($sentMessages as $sentMessage)
            $returnedMessages[$sentMessage->id] = $this->convertSMSStandardClassToBeans($sentMessage);
        
        return $returnedMessages;
        
    }
        
    /**
     * Special-case method used by cron and other automated update routines
     * to find all messages requiring status updates on a given txttools account
     * @global moodle_database $DB Moodle database manager
     * @param TxttoolsAccount $txttoolsAccount Account to find messages for
     * @return MoodletxtInboundMessage[] Messages requiring update
     * @version 2012041701
     * @since 2012041701
     */
    public function getAllNonFinalisedSMSMessagesForAccount(TxttoolsAccount $txttoolsAccount) {
        
        global $DB;
        
        $sql = 'SELECT sent.*
                FROM {block_moodletxt_sent} as sent
                INNER JOIN {block_moodletxt_outbox} AS messages
                    ON sent.messageid = messages.id
                LEFT OUTER JOIN {block_moodletxt_status} status
                    ON status.ticketnumber = sent.ticketnumber
                LEFT OUTER JOIN {block_moodletxt_status} statusmirror
                    ON status.ticketnumber = statusmirror.ticketnumber
                    AND statusmirror.updatetime > status.updatetime
                WHERE statusmirror.id IS NULL
                AND messages.txttoolsaccount = :accountid
                AND status.status <> :failurestatus1 
                AND status.status <> :failurestatus2
                AND status.status <> :failurestatus3
                AND status.status <> :deliveredstatus';
        
        $params = array(
            'accountid' => $txttoolsAccount->getId(),
            'failurestatus1' => MoodletxtOutboundSMSStatus::$STATUS_FAILED_INSUFFICIENT_CREDITS,
            'failurestatus2' => MoodletxtOutboundSMSStatus::$STATUS_FAILED_AT_NETWORK,
            'failurestatus3' => MoodletxtOutboundSMSStatus::$STATUS_FAILED_UNKNOWN_ERROR,
            'deliveredstatus'=> MoodletxtOutboundSMSStatus::$STATUS_DELIVERED_TO_HANDSET
        );
        
        $sentMessages = $DB->get_records_sql($sql, $params);
        $returnedMessages = array();
        
        foreach($sentMessages as $sentMessage)
            $returnedMessages[$sentMessage->id] = $this->convertSMSStandardClassToBeans($sentMessage);
        
        return $returnedMessages;
        
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
        
        $DB->delete_records('block_moodletxt_sent', array('userid' => $userId));
        
    }
    
    /**
     * Saves a top-level sent message to the database
     * @global moodle_database $DB Moodle database controller 
     * @param MoodletxtOutboundMessage $outboundMessage Message sent out
     * @return MoodletxtOutboundMessage Message object (may have been modified with IDs, etc)
     * @version 2012041001
     * @since 2012031501
     */
    public function saveSentMessage(MoodletxtOutboundMessage $outboundMessage) {
        
        global $DB;
        
        $action = ($outboundMessage->getId() > 0) ? 'update' : 'insert';
        
        if ($action == 'insert') {
            
            $insertClass = $this->convertBeanToStandardClass($outboundMessage);
            $outboundMessage->setId($DB->insert_record('block_moodletxt_outbox', $insertClass));
            
        } else {
            
            $updateClass = $this->convertBeanToStandardClass($outboundMessage, 
                $DB->get_record('block_moodletxt_outbox', array('id' => $outboundMessage->getId())));
            
            $DB->update_record('block_moodletxt_outbox', $updateClass);
            
        }
        
        // That's the top level object saved - now to save individual SMS messages
        if (count($outboundMessage->getSentSMSMessages()) > 0)
            $outboundMessage->setSentSMSMessages(
                $this->saveMessagesSentViaSMS($outboundMessage->getSentSMSMessages())
            );
        
        return $outboundMessage;
        
    }
    
    /**
     * Saves messages sent via SMS to the database
     * @TODO ADD USER/CONTACT LINKS TO SENT MESSAGES
     * @global moodle_database $DB Moodle database controller 
     * @param MoodletxtOutboundSMS[] $smsMessages SMS message objects
     * @return MoodletxtOutboundSMS[] Message objects, potentially updated
     * @version 2012041001
     * @since 2012031501
     */
    public function saveMessagesSentViaSMS(array $smsMessages) {
        
        global $DB;
        
        // Saving sent messages is a one-time write operation in moodletxt,
        // so updating existing records is unnecessary :-)
        
        foreach($smsMessages as $smsMessage) {
            
            if ($smsMessage->getId() < 1) {
            
                $writeObject = $this->convertSMSBeanToStandardClass($smsMessage);
                $smsMessage->setId($DB->insert_record('block_moodletxt_sent', $writeObject));

            }
            
            if (count($smsMessage->getStatusUpdates()) > 0) {
                $smsMessage->setStatusUpdates(
                    $this->saveSMSStatusUpdates($smsMessage->getStatusUpdates())
                );
            }
            
        }
        
        return $smsMessages;
        
    }
    
    /**
     * Saves SMS message status updates to the database
     * @global moodle_database $DB Moodle database controller 
     * @param MoodletxtOutboundSMSStatus[] $statusUpdates Status updates
     * @return MoodletxtOutboundSMSStatus[] Status updates, potentially modified
     * @version 2012041001
     * @since 2012031501
     */
    public function saveSMSStatusUpdates(array $statusUpdates) {
        
        global $DB;
        
        // Writing status messages is a one-time write operation in moodletxt,
        // so updating existing records is unnecessary. If it has an ID, skip it.
        
        foreach($statusUpdates as $statusUpdate) {
            
            if ($statusUpdate->getId() < 1) {
            
                $writeObject = $this->convertSMSStatusBeanToStandardClass($statusUpdate);
                $statusUpdate->setId($DB->insert_record('block_moodletxt_status', $writeObject));
                
            }
            
        }
        
        return $statusUpdates;
        
    }
    
    /**
     * Takes the vanilla object returned as a database row and creates a full-fledged
     * sent message object from it, complete with dependent beans, methods, the works
     * @param stdClass $stdObject Raw database-level data object
     * @return MoodletxtOutboundMessage Constructed message object
     * @version 2012042401
     * @since 2011081101
     */
    private function convertStandardClassToBean($stdObject) {
        
        $defaultUser = new MoodletxtBiteSizedUser($stdObject->defaultuser, 
                $stdObject->defaultusername, $stdObject->defaultfirst, $stdObject->defaultlast);
        
        $txttoolsAccount = new TxttoolsAccount($stdObject->txttoolsuser, $stdObject->description, $defaultUser);
        
        if (isset($stdObject->encrypted))
            $txttoolsAccount->setEncryptedPassword($stdObject->encrypted);
        
        $messageOwner = new MoodletxtBiteSizedUser($stdObject->moodleuserid, 
                $stdObject->moodleuser, $stdObject->firstname, $stdObject->lastname);
        
        $outboundMessage = new MoodletxtOutboundMessage($txttoolsAccount, $messageOwner, 
                $stdObject->messagetext, $stdObject->timesent, $stdObject->type);
        
        if (isset($stdObject->scheduledfor))
            $outboundMessage->setScheduledTime($stdObject->scheduledfor);
        
        if (isset($stdObject->id))
            $outboundMessage->setId($stdObject->id);
        
        return $outboundMessage;
        
    }

    /**
     * Converts a basic SMS object from the database to a useful
     * full-fat data object
     * @param object $stdObject DB object to upgrade
     * @return MoodletxtOutboundSMS Full data object with children
     * @version 2012040201
     * @since 2012031901
     */
    private function convertSMSStandardClassToBeans($stdObject) {
        
        $sms = new MoodletxtOutboundSMS($stdObject->messageid, $stdObject->ticketnumber, $stdObject->messagetext);
        $sms->setId($stdObject->id);
        
        // Build recipient
        if ($stdObject->contactfirst != null || $stdObject->contactlast != null || 
            $stdObject->company != null) {
            
            $sms->setRecipientObject(new MoodletxtAddressbookRecipient(
                    new MoodletxtPhoneNumber($stdObject->destination), 
                    $stdObject->contactfirst, $stdObject->contactlast, 
                    $stdObject->company, $stdObject->contactid));
            
        } else if ($stdObject->userfirst != null || $stdObject->userlast != NULL) {
            
            $sms->setRecipientObject(new MoodletxtBiteSizedUser(
                    $stdObject->userid, $stdObject->username, $stdObject->userfirst, 
                    $stdObject->userlast, new MoodletxtPhoneNumber($stdObject->destination)));
            
        } else {
            
            $sms->setRecipientObject(new MoodletxtAdditionalRecipient(
                    new MoodletxtPhoneNumber($stdObject->destination), $stdObject->sendfirstname, 
                    $stdObject->sendlastname));
            
        }
        
        // Optionally drop in statuses, if they were retrieved
        if (isset($stdObject->status) && $stdObject->status != null) {
            
            $sms->setStatusUpdates(array(new MoodletxtOutboundSMSStatus(
                    $stdObject->ticketnumber, $stdObject->status, 
                    $stdObject->statusmessage, $stdObject->updatetime,
                    $stdObject->statusid)));
            
        }
        
        return $sms;
                        
    }
    
    /**
     * Converts an outbound message down to a basic object for writing to the DB
     * @param MoodletxtOutboundMessage $bean Object to convert
     * @param object $existingRecord Existing DB record, if one exists
     * @return object Object for writing to the database
     * @version 2012031501
     * @since 2011081101
     */
    private function convertBeanToStandardClass(MoodletxtOutboundMessage $bean, object $existingRecord = null) {
        
        if ($existingRecord == null)
            $existingRecord = new object();
        
        $existingRecord->userid             = $bean->getUser()->getId();
        $existingRecord->txttoolsaccount    = $bean->getTxttoolsAccount()->getId();
        $existingRecord->messagetext        = $bean->getMessageText();
        $existingRecord->timesent           = $bean->getTimeSent();
        $existingRecord->scheduledfor       = $bean->getScheduledTime();
        $existingRecord->type               = $bean->getType();        
        
        return $existingRecord;
        
    }
    
    /**
     * Converts a sent SMS message down to a basic object for writing to the DB
     * @param MoodletxtOutboundSMS $sms Object to convert
     * @param object $existingRecord Existing DB record, if one exists
     * @return object Object for writing to the database
     * @version 2012031901
     * @since 2012031501
     */
    private function convertSMSBeanToStandardClass(MoodletxtOutboundSMS $sms, object $existingRecord = null) {
        
        if ($existingRecord == null)
            $existingRecord = new object();

        $existingRecord->messageid      = $sms->getMessageId();
        $existingRecord->ticketnumber   = $sms->getTicketNumber();
        $existingRecord->destination    = $sms->getRecipientObject()->getRecipientNumber()->getPhoneNumber();
        $existingRecord->sendfirstname  = $sms->getRecipientObject()->getFirstName();
        $existingRecord->sendlastname   = $sms->getRecipientObject()->getLastName();
        
        return $existingRecord;
    }
    
    /**
     * Converts a status message down to a basic object for writing to the DB
     * @param MoodletxtOutboundSMSStatus $status Object to convert
     * @param object $existingRecord Existing DB record, if one exists
     * @return object Object for writing to the database
     * @version 2012031501
     * @since 2012031501
     */
    private function convertSMSStatusBeanToStandardClass(MoodletxtOutboundSMSStatus $status, object $existingRecord = null) {
        
        if ($existingRecord == null)
            $existingRecord = new object();

        $existingRecord->ticketnumber   = $status->getTicketNumber();
        $existingRecord->status         = $status->getStatus();
        $existingRecord->statusmessage  = $status->getStatusMessage();
        $existingRecord->updatetime     = $status->getUpdateTime();
        
        return $existingRecord;
        
    }
    
}

?>