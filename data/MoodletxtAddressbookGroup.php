<?php

/**
 * File container for MoodletxtAddressbookGroup class
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
 * @see MoodletxtAddressbookGroup
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012031401
 * @since 2011102601
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtAddressbookRecipient.php');

/**
 * Data bean representing a group of addressbook contacts
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012031401
 * @since 2011102601
 */
class MoodletxtAddressbookGroup {

    /**
     * Database record ID of the group
     * @var int
     */
    private $id = 0;
    
    /**
     * The name of the group, as defined by the user
     * @var string
     */
    private $name = '';

    /**
     * ID of the addressbook this group is contained within
     * @var int
     */
    private $addressbookId = 0;
    
    /**
     * Array of contacts within the group
     * @var array(MoodletxtAddressbookRecipient)
     * @see MoodletxtAddressbookRecipient
     */
    private $contacts = array();
    
    /**
     * Instantiates the group with its current data set
     * @param int $id Group record ID
     * @param string $name Group name
     * @param int $addressbookId ID of containing addressbook
     * @param array(MoodletxtAddressbookRecipient) $contacts Group member contacts
     * @version 2011102601
     * @since 2011102601
     */
    public function __construct($id, $name, $addressbookId = 0, array $contacts = array()) {
        $this->setId($id);
        $this->setName($name);
        $this->setAddressbookId($addressbookId);
        $this->setContacts($contacts);
    }
    
    /**
     * Returns the record ID of this group
     * @return int Group record ID
     * @version 2011102601
     * @since 2011102601
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the record ID of this group
     * @param int $id Group record ID
     * @version 2011102601
     * @since 2011102601
     */
    public function setId($id) {
        if (is_int($id) && $id > 0)
            $this->id = $id;
    }

    /**
     * Returns the name of the group
     * @return string Group name
     * @version 2011102601
     * @since 2011102601
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the name of this group
     * @param string $name Group name
     * @version 2011102601
     * @since 2011102601
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Returns the ID of the addressbook this group belongs to
     * @return int Addressbook ID
     * @version 2011102601
     * @since 2011102601
     */
    public function getAddressbookId() {
        return $this->addressbookId;
    }

    /**
     * Sets the ID of the addressbook this group belongs to
     * @param int $addressbookId Addressbook ID
     * @version 2011102601
     * @since 2011102601
     */
    public function setAddressbookId($addressbookId) {
        if (is_int($addressbookId) && $addressbookId > 0)
            $this->addressbookId = $addressbookId;
    }
    
    /**
     * Returns a collection of contacts contained within this group
     * @return array(MoodletxtAddressbookRecipient) Group contacts
     * @version 2011102601
     * @since 2011102601
     */
    public function getContacts() {
        return $this->contacts;
    }

    /**
     * Sets the contacts contained within this group
     * @param mixed $contacts One or more addressbook contacts
     * @version 2012031401
     * @since 2011102601
     */
    public function setContacts($contacts) {
        if (is_array($contacts))
            $this->contacts = $contacts;
        
        else if ($contacts instanceof MoodletxtAddressbookRecipient)
            $this->contacts = array($contacts->getId() => $contacts);
    }
    
    /**
     * Adds a contact to the group
     * @param MoodletxtAddressbookRecipient $contact Contact to add
     * @version 2011102601
     * @since 2011102601
     */
    public function addContact(MoodletxtAddressbookRecipient $contact) {
        $this->contacts[$contact->getId()] = $contact;
    }
    
}


   
