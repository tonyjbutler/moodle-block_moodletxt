<?php


/**
 * File container for MoodletxtAddressbook class
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
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012090401
 * @since 2012080701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Data object representing a user's addressbook
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012090401
 * @since 2012080701
 */
class MoodletxtAddressbook {
    
    /**
     * Flag representing a global addressbook
     * @var string
     */
    public static $ADDRESSBOOK_TYPE_GLOBAL = 'global';
    
    /**
     * Flag representing a private addressbook
     * @var string 
     */
    public static $ADDRESSBOOK_TYPE_PRIVATE = 'private';

    /**
     * The ID of the addressbook in the DB (if known)
     * @var int
     */
    private $id;
    
    /**
     * The ID of the Moodle user that owns the addressbook
     * @var int
     */
    private $ownerId;
    
    /**
     * The name of the addressbook
     * @var string
     */
    private $name;
    
    /**
     * The type of addressbook (global/private)
     * @var string
     */
    private $type;
    
    /**
     * Holds the contacts contained within this addressbook
     * @var MoodletxtAddressbookRecipient[]
     */
    private $contacts = array();
    
    /**
     * Holds the groups contained within this addressbook
     * @var MoodletxtAddressbookGroup[]
     */
    private $groups = array();
    
    /**
     * Initialises an addressbook with initial data
     * @param int $ownerId ID of the book's creator/owner
     * @param string $name Name of the addressbook
     * @param string $type Type of addressbook (see static fields)
     * @param int $id Database ID of the addressbook (if known)
     * @see MoodletxtAddressbook::$ADDRESSBOOK_TYPE_GLOBAL
     * @see MoodletxtAddressbook::$ADDRESSBOOK_TYPE_PRIVATE
     * @version 2012090301
     * @since 2012080701
     */
    public function __construct($ownerId, $name, $type, $id = 0) {
        $this->setId($id);
        $this->setOwnerId($ownerId);
        $this->setName($name);
        $this->setType($type);
    }
    
    /**
     * Returns the ID of this addressbook within the database
     * @return int Addressbook ID
     * @version 2012080701
     * @since 2012080701
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the database ID of this addressbook
     * @param int $id Addressbook ID
     * @version 2012080701
     * @since 2012080701
     */
    public function setId($id) {
        if (is_int($id))
            $this->id = $id;
    }

    /**
     * Returns the ID of the Moodle user who owns this addressbook
     * @return int Moodle user ID
     * @version 2012080701
     * @since 2012080701
     */
    public function getOwnerId() {
        return $this->ownerId;
    }
    
    /**
     * Sets the ID of the Moodle user who owns this addressbook
     * @param int $ownerId Moodle user ID
     * @version 2012080701
     * @since 2012080701
     */
    public function setOwnerId($ownerId) {
        if (is_int($ownerId))
            $this->ownerId = $ownerId;
    }

    /**
     * Sets the ID of the Moodle user
     * who owns this addressbook
     * @param MoodletxtBiteSizedUser $user User to set as owner
     * @version 2012080701
     * @since 2012080701
     */
    public function setOwner(MoodletxtBiteSizedUser $user) {
        $this->ownerId = $user->getId();
    }

    /**
     * Returns the name of this addressbook
     * @return string Addressbook name
     * @version 2012080701
     * @since 2012080701
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the name of this addressbook
     * @param string $name Addressbook name
     * @version 2012080701
     * @since 2012080701
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Returns what type of addressbook this is
     * (private, global, etc)
     * @return string Addressbook type
     * @see MoodletxtAddressbook::$ADDRESSBOOK_TYPE_GLOBAL
     * @see MoodletxtAddressbook::$ADDRESSBOOK_TYPE_PRIVATE
     * @version 2012080701
     * @since 2012080701
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Sets what type of addressbook this is
     * @param string $type Addressbook type
     * @see MoodletxtAddressbook::$ADDRESSBOOK_TYPE_GLOBAL
     * @see MoodletxtAddressbook::$ADDRESSBOOK_TYPE_PRIVATE
     * @version 2012080701
     * @since 2012080701
     */
    public function setType($type) {
        
        if ($type == self::$ADDRESSBOOK_TYPE_GLOBAL ||
            $type == self::$ADDRESSBOOK_TYPE_PRIVATE)
            $this->type = $type;
        
    }

    /**
     * Returns the set of contacts contained within
     * this addressbook
     * @return MoodletxtAddressbookRecipient[] Addressbook contacts
     * @version 2012080701
     * @since 2012080701
     */
    public function getContacts() {
        return $this->contacts;
    }
    
    /**
     * Returns the number of contacts this addressbook contains
     * @return int Number of contacts found
     * @version 2012090401
     * @since 2012090401
     */
    public function numberOfContacts() {
        return count($this->contacts);
    }

    /**
     * Sets the contacts that are contained
     * within this addressbook
     * @param MoodletxtAddressbookRecipient[] $contacts Addressbook contacts
     * @version 2012080701
     * @since 2012080701
     */
    public function setContacts(array $contacts) {
        $this->contacts = $contacts;
    }
    
    /**
     * Adds a single contact to this addressbook
     * @param MoodletxtAddressbookRecipient $contact Contact to add
     * @version 2012080701
     * @since 2012080701
     */
    public function addContact(MoodletxtAddressbookRecipient $contact) {
        $this->contacts[$contact->getContactId()] = $contact;
    }

    /**
     * Returns a set of all the groups contained
     * within this addressbook
     * @return MoodletxtAddressbookGroup[] Addressbook groups
     * @version 2012080701
     * @since 2012080701
     */
    public function getGroups() {
        return $this->groups;
    }
    
    /**
     * Returns the number of groups that this addressbook contains
     * @return int Number of groups found
     * @version 2012090401
     * @since 2012090401
     */
    public function numberOfGroups() {
        return count($this->groups);
    }

    /**
     * Sets the groups that are contained
     * within this addressbook
     * @param MoodletxtAddressbookGroup[] $groups Addressbook groups
     * @version 2012080701
     * @since 2012080701
     */
    public function setGroups(array $groups) {
        $this->groups = $groups;
    }
    
    /**
     * Adds a single group to this addressbook
     * @param MoodletxtAddressbookGroup $group Group to add
     * @version 2012080701
     * @since 2012080701
     */
    public function addGroup(MoodletxtAddressbookGroup $group) {
        $this->groups[$group->getId()] = $group;
    }
    
}

?>
