<?php

/**
 * File container for MoodletxtAddressbookDAO class
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
 * @version 2012092601
 * @since 2011080501
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbook.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbookRecipient.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbookGroup.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAdditionalRecipient.php');

/**
 * Database access controller for sent SMS messages
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092601
 * @since 2011080501
 */
class MoodletxtAddressbookDAO {    

    /**
     * Utility method for checking that the user has access to
     * the addressbook they're trying to manipulate
     * @global moodle_database $DB Moodle database manager
     * @param type $addressbookId Addressbook being used
     * @param int $userId ID of user attempting to edit addressbook
     * @return boolean True if the user has access
     */
    public function checkAddressbookOwnership($addressbookId, $userId) {
        
        global $DB;
        
        return $DB->record_exists('block_moodletxt_ab', array('owner' => $userId, 'id' => $addressbookId));
        
    }
    
    /**
     * Finds the Moodle user or addressbook contact that owns a phone number
     * @param MoodletxtPhoneNumber $phoneNumber 
     * @param int[] $addressbookOwners IDs of users in whose addressbooks we should search
     * @return array Any user or addressbook sources found
     * @global moodle_database $DB Moodle database manager
     * @version 2012092601
     * @since 2011080501
     * @TODO Move user bit out into user DAO where it belongs
     * @TODO Change global string constant for Addressbook class constant when books are implemented
     */
    public function associateNumberWithRecipient(MoodletxtPhoneNumber $phoneNumber, array $addressbookOwners) {
        
        global $DB;
        
        $associatedSources = array('user' => null, 'addressbook' => null);
        
        // Check for number in Moodle user list
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $matchedUser = $DB->get_record('user', array($numberSource => $phoneNumber->getPhoneNumber()));
        
        if (is_object($matchedUser))
            $associatedSources['user'] = new MoodletxtBiteSizedUser(
                $matchedUser->id, $matchedUser->username, $matchedUser->firstname, 
                $matchedUser->lastname, new MoodletxtPhoneNumber($matchedUser->$numberSource)
            );
        
        // Check for number in moodletxt address books (that the user owns or has access to)
        list ($inOrEqual, $params) = $DB->get_in_or_equal($addressbookOwners, SQL_PARAMS_NAMED);
        
        $sql = 'SELECT entry.* FROM {block_moodletxt_ab_entry} entry
                INNER JOIN {block_moodletxt_ab} book
                    ON entry.addressbook = book.id
                WHERE entry.phoneno = :phoneno AND
                (book.owner ' . $inOrEqual . '
                OR book.type = \'global\')';
        
        $params['phoneno'] = $phoneNumber->getPhoneNumber();
        
        $matchedContact = $DB->get_record_sql($sql, $params);
        
        if (is_object($matchedContact))
            $associatedSources['addressbook'] = new MoodletxtAddressbookRecipient(
                new MoodletxtPhoneNumber($matchedContact->phoneno), $matchedContact->firstname, 
                $matchedContact->lastname, $matchedContact->company, $matchedContact->id,
                $matchedContact->addressbook
            );
        
        return $associatedSources;
        
    }
    
    /**
     * Fetches all addressbooks for the given user ID, and optionally
     * populates them with contacts and groups.
     * @todo Link contacts and groups together in response
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of the user to search against
     * @param boolean $includeContacts Whether to pull contacts for the addressbook
     * @param boolean $includeGroups Whether to pull groups for the addressbook
     * @return MoodletxtAddressbook[] Populated addressbook objects
     * @version 2012090301
     * @since 2012080701
     */
    public function getAddressbooksForUser($userId, $includeContacts = false, $includeGroups = false) {
        
        global $DB;
        
        $returnSet = array();
        $addressbookRecords = $DB->get_records('block_moodletxt_ab', array('owner' => $userId), 'name ASC');
        
        foreach($addressbookRecords as $addressbook) {
            
            $returnSet[$addressbook->id] = new MoodletxtAddressbook(
                (int) $addressbook->owner, 
                      $addressbook->name, 
                      $addressbook->type, 
                (int) $addressbook->id
            );
        
            if ($includeContacts)
                $returnSet[$addressbook->id]->setContacts(
                    $this->getAddressbookContactsForUser($userId, $addressbook->id));
            
            if ($includeGroups)
                $returnSet[$addressbook->id]->setGroups(
                    $this->getAddressbookGroupsForUser($userId, $addressbook->id));
            
        }
        
        return $returnSet;
        
    }
    
    /**
     * Fetches a single addressbook and asssociated records from the database
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookId The ID of the addressbook to fetch
     * @param int $userId The ID of the user who owns the addressbook
     * @param boolean $includeContacts Whether to include contacts in the fetch
     * @param boolean $includeGroups Whether to include groups in the fetch
     * @return MoodletxtAddressbook Addressbook found in database
     * @throws InvalidArgumentException If addressbook does not exist for this user
     * @version 2012090401
     * @since 2012090401
     */
    public function getAddressbookById($addressbookId, $userId, $includeContacts = false, $includeGroups = false) {
        
        global $DB;
        
        $addressbookRecord = $DB->get_record('block_moodletxt_ab', array('id' => $addressbookId, 'owner' => $userId));
        
        if ($addressbookRecord == null)
            throw new InvalidArgumentException('The addressbook you are attempting to pull does not exist in the database.');
        
        $constructedAddressbook = new MoodletxtAddressbook(
            (int) $addressbookRecord->owner, 
                  $addressbookRecord->name, 
                  $addressbookRecord->type, 
            (int) $addressbookRecord->id
        );
        
        if ($includeContacts)
            $constructedAddressbook->setContacts(
                $this->getAddressbookContactsForUser($userId, $addressbook->id));
        
        if ($includeGroups)
            $constructedAddressbook->setGroups(
                $this->getAddressbookGroupsForUser($userId, $addressbook->id));
        
        return $constructedAddressbook;
        
    }
    
    /**
     * Fetches a single contact from the database
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookId ID of addressbook to search
     * @param int $contactId ID of contact to fetch
     * @param boolean $includeGroups Whether the DB should fetch groups for this contact
     * @return MoodletxtAddressbookRecipient Contact built from DB
     * @throws InvalidArgumentException If invalid contact ID is provided
     * @version 2012092601
     * @since 2012090501
     */
    public function getAddressbookContactById($addressbookId, $contactId, $includeGroups = false) {
        
        global $DB;
                
        $contactRecord = $DB->get_record('block_moodletxt_ab_entry', array(
            'id' => $contactId,
            'addressbook' => $addressbookId
        ));
        
        if ($contactRecord == null) {
            throw new InvalidArgumentException('The contact ID you specified does not exist within this addressbook.');
        
        } else {
            
            $contact = new MoodletxtAddressbookRecipient(
                new MoodletxtPhoneNumber($contactRecord->phoneno), 
                $contactRecord->firstname,
                $contactRecord->lastname, 
                $contactRecord->company, 
                $contactRecord->id,
                $contactRecord->addressbook
            );
            
            if ($includeGroups)
                $contact->setGroups ($this->getAddressbookGroupsForContact($addressbookId, $contactId));
            
            return $contact;
                
        }
        
    }
    
    /**
     * Gets a number of addressbook contacts by their database record IDs
     * @param array(int) $contactIds IDs to get contacts for
     * @param string $indexField Field to use as return array key - 'id' or 'phone'
     * @global moodle_database $DB Moodle database manager
     * @version 2012092601
     * @since 2012031301
     */
    public function getAddressbookContactsById(array $contactIds, $indexField = 'id') {
        
        global $DB;
        
        $contactRecords = $DB->get_records_list('block_moodletxt_ab_entry', 'id', $contactIds);
        $returnedContacts = array();
        
        foreach($contactRecords as $contact) {
            
            $arrayIndex = ($indexField == 'phone') ? $contact->phoneno : $contact->id;
            $returnedContacts[$arrayIndex] = new MoodletxtAddressbookRecipient(
                new MoodletxtPhoneNumber($contact->phoneno), $contact->firstname, 
                $contact->lastname, $contact->company, $contact->id, $contact->addressbook
            );
            
            
            
        }    
        
        return $returnedContacts;
        
    }
    
    /**
     * Pulls back every group that a contact is a member of
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookId ID of addressbook contact is in
     * @param int $contactId ID of contact to get groups for
     * @return MoodletxtAddressbookGroup[]
     * @version 2012091301
     * @since 2012091301
     */
    public function getAddressbookGroupsForContact($addressbookId, $contactId) {
        
        global $DB;
        
        $sql = 'SELECT group.* 
                FROM {block_moodletxt_ab_group} group
                INNER JOIN {block_moodletxt_ab_grpmem} link
                    ON group.id = link.groupid
                INNER JOIN {block_moodletxt_ab_entry} contact
                    ON contact.id = link.contact
                WHERE contact.id = :contactid
                AND contact.addressbook = :addressbookid
                AND group.addressbook = :addressbookid';
        
        $groupRecords = $DB->get_records_sql($sql, array(
            'contactid'     => $contactId, 
            'addressbookid' => $addressbookId
        ));
        
        $foundGroups = array();
        
        foreach($groupRecords as $groupRecord)
            $foundGroups[$groupRecord->id] = new MoodletxtAddressbookGroup(
                $groupRecord->id,
                $groupRecord->name,
                $groupRecord->addressbook
            );
        
        return $foundGroups;
        
    }
    
    /**
     * Gets all the addressbook contacts owned by a given user
     * @param int $userId Moodle user ID
     * @param int $addressbookId ID of addressbook to pull from
     * @param string $orderBy SQL fragment indicating sort field and direction
     * @param int $limitFrom Number of first record in table to retrieve (0-indexed)
     * @param int $limitNum Number of records to retrieve from table
     * @global moodle_database $DB Moodle database manager
     * @return MoodletxtAddressbookRecipient[] 
     * @version 2012092601
     * @since 2011102601
     */
    public function getAddressbookContactsForUser($userId, $addressbookId = 0, 
            $orderBy = 'c.lastname ASC', $limitFrom = 0, $limitNum = 0) {
        
        global $DB;
        
        $sql = 'SELECT c.*
                FROM {block_moodletxt_ab_entry} c
                INNER JOIN {block_moodletxt_ab} a
                    ON c.addressbook = a.id
                INNER JOIN {user} u
                    ON a.owner = u.id
                WHERE u.id = :userid';
        
        if ($addressbookId > 0)
            $sql .= ' AND a.id = :addressbookid';
        
        $sql .= ' ORDER BY ' . $orderBy;
        
        $foundContacts = $DB->get_records_sql($sql, array(
            'userid'        => $userId, 
            'addressbookid' => $addressbookId
        ), $limitFrom, $limitNum);
        
        $returnedContacts = array();
        
        foreach($foundContacts as $contact)
            $returnedContacts[$contact->id] = new MoodletxtAddressbookRecipient(
                new MoodletxtPhoneNumber($contact->phoneno), $contact->firstname, 
                $contact->lastname, $contact->company, $contact->id, $contact->addressbook
            );
        
        return $returnedContacts;
        
    }
    
    /**
     * Returns the number of contacts present in a given addressbook. Useful
     * when making paged queries on the database
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookId ID of addressbook to count contacts for
     * @return int Number of contacts in addressbook
     * @version 2012090501
     * @since 2012090501
     */
    public function countContactsInAddressbook($addressbookId) {
        
        global $DB;
        
        return $DB->count_records('block_moodletxt_ab_entry', array('addressbook' => $addressbookId));
        
    }

    /**
     * Gets all contacts in a given addressbook group
     * @param int $groupId Database ID of group to get members for
     * @param string $indexField Field to use as return array key - 'id' or 'phone'
     * @global moodle_database $DB Moodle database manager
     * @return MoodletxtAddressbookRecipient 
     * @version 2012092601
     * @since 2011102601
     */
    public function getAddressbookContactsInGroup($groupId, $indexField = 'id') {
        
        global $DB;
        
        $sql = 'SELECT contacts.*
                FROM {block_moodletxt_ab_entry} contacts
                INNER JOIN {block_moodletxt_ab_grpmem} link
                    ON contacts.id = link.contact
                INNER JOIN {block_moodletxt_ab_group} groups
                    ON link.groupid = groups.id
                WHERE groups.id = :groupid';
        
        $foundContacts = $DB->get_records_sql($sql, array('groupid' => $groupId));
        
        $returnedContacts = array();
        
        foreach($foundContacts as $contact) {
        
            $arrayIndex = ($indexField == 'phone') ? $contact->phoneno : $contact->id;
            $returnedContacts[$arrayIndex] = new MoodletxtAddressbookRecipient(
                new MoodletxtPhoneNumber($contact->phoneno), $contact->firstname, 
                $contact->lastname, $contact->company, $contact->id, $contact->addressbook
            );
            
        }
        
        return $returnedContacts;
        
    }

    /**
     * Deletes a set of contacts from the database according
     * to their database record IDs
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookId ID of addressbook to delete contacts from
     * @param array $contactIds IDs of contacts to delete
     * @param boolean $deleteInclusive True to delete contacts with these IDs, false deletes all other contacts
     * @version 2012092101
     * @since 2012091201
     */
    public function deleteContactsById($addressbookId, array $contactIds, $deleteInclusive = true) {
        
        global $DB;

        list ($contactsInFrag, $queryParams) = $DB->get_in_or_equal($contactIds, SQL_PARAMS_NAMED, 'param', $deleteInclusive);

        $sqlFragment = 'contact ' . $contactsInFrag;
        $DB->delete_records_select('block_moodletxt_ab_grpmem', $sqlFragment, $queryParams);        
        $DB->delete_records_select('block_moodletxt_in_ab',     $sqlFragment, $queryParams);
        $DB->delete_records_select('block_moodletxt_sent_ab',   $sqlFragment, $queryParams);
        
        $sqlFragment = 'addressbook = :addressbookid AND id ' . $contactsInFrag;
        $queryParams['addressbookid'] = $addressbookId;        
        $DB->delete_records_select('block_moodletxt_ab_entry',  $sqlFragment, $queryParams);        
        
    }
    
    /**
     * Gets a single addressbook group from the database.
     * Optionally populates it with member contacts.
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookId Addressbook group is in
     * @param int $groupId ID of group to fetch
     * @param boolean $includeContactsInGroup True to include contacts
     * @return MoodletxtAddressbookGroup
     * @throws InvalidArgumentException
     * @version 2012092401
     * @since 2012091201
     */
    public function getAddressbookGroupById($addressbookId, $groupId, $includeContactsInGroup = false) {
        
        global $DB;
        
        $groupRecord = $DB->get_record('block_moodletxt_ab_group', array(
            'id' => $groupId, 
            'addressbook' => $addressbookId
        ));
        
        if ($groupRecord == null) {
            throw new InvalidArgumentException('The group you specified does not exist within this addressbook.');
        
        } else {
            $group = new MoodletxtAddressbookGroup($groupRecord->name, (int) $groupRecord->addressbook, (int) $groupRecord->id);
            
            if ($includeContactsInGroup)
                $group->setContacts ($this->getAddressbookContactsInGroup($group->getId()));
            
            return $group;
            
        }  
    }

    /**
     * Gets all addressbook groups owned by a given Moodle user
     * @param int $userId Moodle user ID
     * @param int $addressbookId ID of addressbook to limit search to
     * @param boolean $includeContactsInGroup True if group members should be retrieved
     * @global moodle_database $DB Moodle database manager
     * @return MoodletxtAddressbookGroup 
     * @version 2012092401
     * @since 2011102601
     */
    public function getAddressbookGroupsForUser($userId, $addressbookId = 0, $includeContactsInGroup = false){

        global $DB;
        
        $sql = 'SELECT groups.*
                FROM {block_moodletxt_ab_group} groups
                INNER JOIN {block_moodletxt_ab} addressbook
                    ON groups.addressbook = addressbook.id
                INNER JOIN {user} usertable
                    ON addressbook.owner = usertable.id
                WHERE usertable.id = :userid';
        
        if ($addressbookId > 0)
            $sql .= ' AND addressbook.id = :addressbookid';
        
        $sql .= ' ORDER BY groups.name ASC';
        
        $foundGroups = $DB->get_records_sql($sql,
            array('userid' => $userId, 'addressbookid' => $addressbookId));
        
        $returnedGroups = array();
        
        foreach($foundGroups as $group)
            $returnedGroups[$group->id] = new MoodletxtAddressbookGroup(
                $group->name, (int) $group->addressbook, (int) $group->id);
        
        if ($includeContactsInGroup)
            foreach($returnedGroups as $group)
                $group->setContacts($this->getAddressbookContactsInGroup($group->getId()));
        
        return $returnedGroups;
        
    }

    /**
     * Saves a top-level addressbook object to the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtAddressbook $addressbook
     * @version 2012090301
     * @since 2012090301
     */
    public function saveAddressbook(MoodletxtAddressbook $addressbook) {
        
        global $DB;
        
        $objectToWrite = $this->convertAddressbookBeanToStandardClass($addressbook);
        
        // Update
        if ($addressbook->getId() > 0)
            $DB->update_record('block_moodletxt_ab', $objectToWrite);
            
        else
            $addressbook->setId($DB->insert_record('block_moodletxt_ab', $objectToWrite, true));
        
    }
    
    /**
     * Saves a top-level contact object to the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtAddressbookRecipient $contact Contact to save
     * @version 2012092401
     * @since 2012090501
     */
    public function saveContact(MoodletxtAddressbookRecipient $contact) {
        
        global $DB;
        
        $objectToWrite = $this->convertContactBeanToStandardClass($contact);
        
        if ($contact->getContactId() > 0)
            $DB->update_record('block_moodletxt_ab_entry', $objectToWrite);
        
        else
            $contact->setId((int) $DB->insert_record('block_moodletxt_ab_entry', $objectToWrite, true));
        
        // Flatten any existing group links and replace them
        // with those present in the object
        if ($contact->hasGroups()) {
            
            $DB->delete_records('block_moodletxt_ab_grpmem', array('contact' => $contact->getContactId()));
            
            foreach($contact->getGroups() as $group) {
                
                $linkRecord = new object();
                $linkRecord->contact = $contact->getContactId();
                $linkRecord->groupid = $group->getId();
                
                $DB->insert_record('block_moodletxt_ab_grpmem', $linkRecord);
                
            }
            
        }
        
    }
    
    /**
     * Saves a top-level addressbook group object to the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtAddressbookGroup $group Group to save
     * @version 2012092401
     * @since 2012092401
     */
    public function saveGroup(MoodletxtAddressbookGroup $group) {
        
        global $DB;
        
        $writeObject = $this->convertGroupBeanToStandardClass($group);
        
        if ($group->getId() > 0)
            $DB->update_record('block_moodletxt_ab_group', $writeObject);
        
        else
            $group->setId((int) $DB->insert_record('block_moodletxt_ab_group', $writeObject, true));
        
        // Flatten any existing group links and
        // replace them with those present in the object
        if ($group->hasContacts()) {
            
            $DB->delete_records('block_moodletxt_ab_grpmem', array('groupid' => $group->getId()));
            
            foreach($group->getContacts() as $contact) {
                
                $linkRecord = new object();
                $linkRecord->contact = $contact->getContactId();
                $linkRecord->groupid = $group->getId();
                
                $DB->insert_record('block_moodletxt_ab_grpmem', $linkRecord);
                
            }
            
        }
        
    }
    
    /**
     * Delete's a user's addressbook group from the database,
     * optionally either nuking all the member contact records,
     * or moving them into another group
     * @param MoodletxtAddressbookGroup $groupToNuke Group to be deleted
     * @param MoodletxtAddressbookGroup $mergeDestination Optional group to move contacts to
     * @version 2012092501
     * @since 2012092501
     */
    public function deleteOrMergeGroup(MoodletxtAddressbookGroup $groupToNuke, 
            $deleteContacts = false, MoodletxtAddressbookGroup $mergeDestination = null) {
        
        $mergeDestinationId = ($mergeDestination !== null) ? $mergeDestination->getId() : 0;
        
        $this->deleteOrMergeGroupById(
            $groupToNuke->getAddressbookId(), 
            $groupToNuke->getId(), 
            $deleteContacts, 
            $mergeDestinationId
        );
        
    }
    
    /**
     * Delete's a user's addressbook group from the database,
     * optionally either nuking all the member contact records,
     * or moving them into another group
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookId ID of addressbook that contains group
     * @param int $groupIdToNuke ID of group to delete
     * @param boolean $deleteContacts Whether to delete member contacts
     * @param int $mergeDestinationId Optional ID of group to move member contacts into
     * @version 2012092501
     * @since 2012092501
     */
    public function deleteOrMergeGroupById($addressbookId, $groupIdToNuke, 
            $deleteContacts = false, $mergeDestinationId = 0) {
        
        global $DB;
        
        // Merge contacts into new group if requested
        if ($mergeDestinationId > 0) {
            
            $DB->set_field('block_moodletxt_ab_grpmem', 'groupid', 
                    $mergeDestinationId, array('groupid' => $groupIdToNuke));
            
        // Delete member contacts if requested
        } else if ($deleteContacts) {
            
            $contactIds = $DB->get_fieldset_select('block_moodletxt_ab_grpmem', 
                    'contact', 'groupid = :groupid', array('groupid' => $groupIdToNuke));
                        
            $this->deleteContactsById($addressbookId, $contactIds);
            
        }
        
        // If the contacts haven't been merged, 
        // we can nuke any entries in the link table
        if ($mergeDestinationId == 0)
            $DB->delete_records('block_moodletxt_ab_grpmem', array('groupid' => $groupIdToNuke));
        
        // Finally, delete the group record
        $DB->delete_records('block_moodletxt_ab_group', array(
            'addressbook' => $addressbookId, 
            'id' => $groupIdToNuke
        ));
        
    }

    /**
     * Deletes a user's addressbook from the database,
     * optionally merging its contacts and groups into 
     * another addressbook rather than deleting them as well.
     * @param MoodletxtAddressbook $addressbookToDelete Addressbook to delete
     * @param MoodletxtAddressbook $mergeDestination Addressbook to merge contacts/groups into
     * @version 2012092501
     * @since 2012090301
     */
    public function deleteOrMergeAddressbook(MoodletxtAddressbook $addressbookToDelete, MoodletxtAddressbook $mergeDestination = null) {

        $mergeDestinationId = ($mergeDestination !== null) ? $mergeDestination->getId() : 0;
        
        $this->deleteOrMergeAddressbookById($addressbookToDelete->getId(), $mergeDestinationId);
        
    }
    
    /**
     * Deletes a user's addressbook from the database,
     * optionally merging its contacts and groups into 
     * another addressbook rather than deleting them as well.
     * @global moodle_database $DB Moodle database manager
     * @param int $addressbookDeleteId ID of addressbook to nuke
     * @param int $mergeDestinationId ID of addressbook to merge contacts/groups into
     * @version 2012090301
     * @since 2012090301
     */
    public function deleteOrMergeAddressbookById($addressbookDeleteId, $mergeDestinationId = 0) {
        
        global $DB;
        
        // Merge contacts and groups to existing addressbook
        if ($mergeDestinationId > 0) {
            
            $DB->set_field('block_moodletxt_ab_entry', 'addressbook', 
                    $mergeDestinationId, array('addressbook' => $addressbookDeleteId));
            
            $DB->set_field('block_moodletxt_ab_group', 'addressbook', 
                    $mergeDestinationId, array('addressbook' => $addressbookDeleteId));
            
        
        // If no merge destination is provided, nuke the contacts and groups
        // along with the primary addressbook records
        } else {
            
            // Delete link records that link to the groups about to be nuked
            $groupIds = $DB->get_fieldset_select('block_moodletxt_ab_group', 'id', 
                    'addressbook = :addressbook', array('addressbook' => $addressbookDeleteId));
            
            if (count($groupIds) > 0) {
            
                list ($groupIn, $groupParams) = $DB->get_in_or_equal($groupIds, SQL_PARAMS_NAMED);

                $DB->delete_records_select('block_moodletxt_ab_grpmem', 'groupId ' . $groupIn, $groupParams);
                
            }
            
            
            // Delete link records that link to the contacts about to be nuked
            $contactIds = $DB->get_fieldset_select('block_moodletxt_ab_entry', 'id', 
                    'addressbook = :addressbook', array('addressbook' => $addressbookDeleteId));
            
            if (count($contactIds) > 0) {
            
                list ($contactIn, $contactParams) = $DB->get_in_or_equal($contactIds, SQL_PARAMS_NAMED);

                $DB->delete_records_select('block_moodletxt_in_ab', 'contact ' . $contactIn, $contactParams);
                $DB->delete_records_select('block_moodletxt_sent_ab', 'contact ' . $contactIn, $contactParams);
                
            }
            
            
            // Nuke contacts and records
            $DB->delete_records('block_moodletxt_ab_entry', array('addressbook' => $addressbookDeleteId));
            $DB->delete_records('block_moodletxt_ab_group', array('addressbook' => $addressbookDeleteId));
            
        }
        
        // Nuke primary records of addressbook
        $DB->delete_records('block_moodletxt_ab_u', array('addressbook' => $addressbookDeleteId));
        $DB->delete_records('block_moodletxt_ab', array('id' => $addressbookDeleteId));
        
    }
        
    /**
     * Converts a higher-level addressbook data object
     * into a standard object class for writing to the DB
     * @param MoodletxtAddressbook $addressbook Full-fat data object
     * @return object Object ready for DB
     * @version 2012090301
     * @since 2012090301
     */
    private function convertAddressbookBeanToStandardClass(MoodletxtAddressbook $addressbook) {
        
        $dbObj = new object();
        $dbObj->name  = $addressbook->getName();
        $dbObj->type  = $addressbook->getType();
        $dbObj->owner = $addressbook->getOwnerId();
        
        if ($addressbook->getId() > 0)
            $dbObj->id = $addressbook->getId();
        
        return $dbObj;
        
    }
    
    /**
     * Converts a higher-level contact object into a standard
     * object class for writing to the database
     * @param MoodletxtAddressbookRecipient $contact Full-fat data object
     * @return object Object ready for DB
     * @version 2012091301
     * @since 2012090501
     */
    private function convertContactBeanToStandardClass(MoodletxtAddressbookRecipient $contact) {
        
        $dbObj = new object();
        $dbObj->addressbook = $contact->getAddressbookId();
        $dbObj->firstname   = $contact->getFirstName();
        $dbObj->lastname    = $contact->getLastName();
        $dbObj->company     = $contact->getCompanyName();
        $dbObj->phoneno     = $contact->getRecipientNumber()->getPhoneNumber();
        
        if ($contact->getContactId() > 0)
            $dbObj->id = $contact->getContactId();
        
        return $dbObj;
        
    }
    
    /**
     * Converts a higher-level group object into a standard
     * object class for writing to the database
     * @param MoodletxtAddressbookGroup $group Full-fat data object
     * @return object Object ready for DB
     * @version 2012092401 
     * @since 2012092401
     */
    private function convertGroupBeanToStandardClass(MoodletxtAddressbookGroup $group) {
        
        $dbObj = new object();
        $dbObj->addressbook = $group->getAddressbookId();
        $dbObj->name        = $group->getName();
        $dbObj->description = ''; // Placeholder for now until groups are expanded on
        
        if ($group->getId() > 0)
            $dbObj->id = $group->getId();
        
        return $dbObj;
        
    }
    
}

?>