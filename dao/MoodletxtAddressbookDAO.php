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
 * @version 2012052301
 * @since 2011080501
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbookRecipient.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbookGroup.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAdditionalRecipient.php');

/**
 * Database access controller for sent SMS messages
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012050401
 * @since 2011080501
 */
class MoodletxtAddressbookDAO {    

    /**
     * Gets a number of addressbook contacts by their database record IDs
     * @param array(int) $contactIds IDs to get contacts for
     * @param string $indexField Field to use as return array key - 'id' or 'phone'
     * @global moodle_database $DB Moodle database manager
     * @version 2012041001
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
                $contact->lastname, $contact->company, $contact->id
            );
            
        }    
        
        return $returnedContacts;
        
    }
    
    /**
     * Finds the Moodle user or addressbook contact that owns a phone number
     * @param MoodletxtPhoneNumber $phoneNumber 
     * @param int[] $addressbookOwners IDs of users in whose addressbooks we should search
     * @return array Any user or addressbook sources found
     * @global moodle_database $DB Moodle database manager
     * @version 2012050401
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
                $matchedContact->lastname, $matchedContact->company, $matchedContact->id
            );
        
        return $associatedSources;
        
    }
    
    /**
     * Gets all the addressbook contacts owned by a given user
     * @param int $userId Moodle user ID
     * @param int $addressbookId ID of addressbook to pull from
     * @global moodle_database $DB Moodle database manager
     * @return MoodletxtAddressbookRecipient 
     * @version 2012041001
     * @since 2011102601
     */
    public function getAddressbookContactsForUser($userId, $addressbookId = 0) {
        
        global $DB;
        
        $foundContacts = array();
        
        $sql = 'SELECT contacts.*
                FROM {block_moodletxt_ab_entry} contacts
                INNER JOIN {block_moodletxt_ab} addressbook
                    ON contacts.addressbook = addressbook.id
                INNER JOIN {user} usertable
                    ON addressbook.owner = usertable.id
                WHERE usertable.id = :userid';
        
        if ($addressbookId > 0)
            $sql .= ' AND addressbook.id = :addressbookid';
        
        $sql .= ' ORDER BY contacts.lastname ASC';
        
        $foundContacts = $DB->get_records_sql($sql,
            array('userid' => $userId, 'addressbookid' => $addressbookId));
        
        $returnedContacts = array();
        
        foreach($foundContacts as $contact)
            $returnedContacts[$contact->id] = new MoodletxtAddressbookRecipient(
                new MoodletxtPhoneNumber($contact->phoneno), $contact->firstname, 
                $contact->lastname, $contact->company, $contact->id
            );
        
        return $returnedContacts;
        
    }

    /**
     * Gets all contacts in a given addressbook group
     * @param int $groupId Database ID of group to get members for
     * @param string $indexField Field to use as return array key - 'id' or 'phone'
     * @global moodle_database $DB Moodle database manager
     * @return MoodletxtAddressbookRecipient 
     * @version 2012041001
     * @since 2011102601
     */
    public function getAddressbookContactsInGroup($groupId, $indexField = 'id') {
        
        global $DB;
        
        $sql = 'SELECT contacts.*
                FROM {block_moodletxt_ab_entry} contacts
                INNER JOIN {block_moodletxt_ab_grpmem} link
                    ON contacts.id = link.contact
                INNER JOIN {block_moodletxt_ab_group} groups
                    ON link.group = groups.id
                WHERE groups.id = :groupid';
        
        $foundContacts = $DB->get_records_sql($sql, array('groupid' => $groupId));
        
        $returnedContacts = array();
        
        foreach($foundContacts as $contact) {
        
            $arrayIndex = ($indexField == 'phone') ? $contact->phoneno : $contact->id;
            $returnedContacts[$arrayIndex] = new MoodletxtAddressbookRecipient(
                new MoodletxtPhoneNumber($contact->phoneno), $contact->firstname, 
                $contact->lastname, $contact->company, $contact->id
            );
            
        }
        
        return $returnedContacts;
        
    }

    /**
     * Gets all addressbook groups owned by a given Moodle user
     * @param int $userId Moodle user ID
     * @param int $addressbookId ID of addressbook to limit search to
     * @param boolean $includeContactsInGroup True if group members should be retrieved
     * @global moodle_database $DB Moodle database manager
     * @return MoodletxtAddressbookGroup 
     * @version 2012041001
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
                $group->id, $group->name, $group->addressbook);
        
        if ($includeContactsInGroup)
            foreach($returnedGroups as $group)
                $group->setContacts($this->getAddressbookContactsInGroup($groupId));
        
        return $returnedGroups;
        
    }
        
}

?>