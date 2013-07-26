<?php

/**
 * File container for MoodletxtAddressbookRecipient class
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
 * @see MoodletxtAddressbookRecipient
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2010082001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtRecipient.php');

/**
 * Data bean for a recipient who has an entry in the address book
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2010082001
 */
class MoodletxtAddressbookRecipient extends MoodletxtRecipient {

    /**
     * DB record ID for the contact
     * @var int
     */
    private $id;

    /**
     * ID of the addressbook this contact is found in
     * @var int
     */
    private $addressbookId;
    
    /**
     * Name of the company the recipient represents
     * @var string
     */
    private $companyName = '';
    
    /**
     * Addressbook groups that this recipient belongs to
     * NOTE: If this is null, the DAO will ignore groups
     * when saving. If this is an array, empty or otherwise,
     * the DAO will synchronise the database to match
     * this list of groups for the addressbook recipient
     * @var MoodletxtAddressbookGroup[]
     */
    private $groups;
    
    /**
     * Constructor - initialises data bean
     * @param MoodletxtPhoneNumber $recipientNumber Recipient's mobile phone number
     * @param string $firstName Recipient's first name (Recommended)
     * @param string $lastName Recipient's last name (Recommended)
     * @param string $companyName Name of the company the recipient represents (Optional)
     * @param int $id DB record ID of the recipient (Optional)
     * @param int $addressbookId ID of the addressbook this contact belongs to (Optional)
     * @version 2012092601
     * @since 2010082001
     */
    public function __construct(MoodletxtPhoneNumber $recipientNumber, $firstName = '',
            $lastName = '', $companyName = '', $id = 0, $addressbookId = 0) {

        // Call super-constructor with common info
        parent::__construct($recipientNumber, $firstName, $lastName);

        // Set bean properties
        $this->setCompanyName($companyName);
        $this->setId($id);
        $this->setAddressbookId($addressbookId);
        
    }

    /**
     * Set the record ID of this recipient, if known
     * @param int $id Recipient's DB record ID
     * @version 2010090201
     * @since 2010082001
     */
    public function setId($id) {
        $id = (int) $id;

        if ($id > 0)
            $this->id = $id;
        
    }

    /**
     * Set the name of the company this recipient represents
     * @param string $name Company name
     * @version 2013050801
     * @since 2010082001
     */
    public function setCompanyName($companyName) {
        $this->companyName = $companyName;
    }

    /**
     * Returns the DB record ID of this recipient, if known
     * @return int Recipient ID
     * @version 2012052301
     * @since 2010082001
     */
    public function getContactId() {
        return $this->id;
    }    

    /**
     * Returns the name of the company this recipient represents
     * @return string Company name
     * @version 2010082001
     * @since 2010082001
     */
    public function getCompanyName() {
        return $this->companyName;
    }

    /**
     * Returns the recipient's full name, as computed from other values stored
     * @return string Recipient's full name
     * @version 2010082001
     * @since 2010082001
     */
    public function getFullName() {
        
        if ($this->getFirstName() != '' || $this->getLastName() != '')
            return $this->getFirstName() . ' ' . $this->getLastName();
        else if ($this->getCompany() != '')
            return $this->getCompany();
        else
            return '';
        
    }

    /**
     * Returns the recipients full name, formatted for screen display
     * @return string Recipient's full name, display formatted
     * @version 2013052301
     * @since 2010082001
     */
    public function getFullNameForDisplay() {

        return MoodletxtStringHelper::formatNameForDisplay(
                $this->getFirstName(), $this->getLastName(), 
                null, $this->getCompanyName());
        
    }
    
    /**
     * Returns a set of all the groups that this
     * contact is a member of
     * @return MoodletxtAddressbookGroup[]
     * @version 2012091201
     * @since 2012091201
     */
    public function getGroups() {
        return $this->groups;
    }

    /**
     * Sets the groups that this contact
     * is a member of
     * @param MoodletxtAddressbookGroup[] $groups Contact's groups
     * @version 2012091201
     * @since 2012091201
     */
    public function setGroups(array $groups) {
        $this->groups = $groups;
    }

    /**
     * Adds a single contact that the contact is a member of
     * @param MoodletxtAddressbookGroup $group Contact's new group
     * @version 2012092401
     * @since 2012091201
     */
    public function addGroup(MoodletxtAddressbookGroup $group) {
        
        if (! is_array($this->groups))
            $this->groups = array();
        
        $this->groups[$group->getId()] = $group;
    }

    /**
     * Returns whether this contact is a member of any groups
     * @return boolean True if this contact is associated with groups
     * @version 2012091301
     * @since 2012091301
     */
    public function hasGroups() {
        return ($this->groups != null && is_array($this->groups));
    }

    /**
     * Returns the number of groups that this contact
     * is a member of
     * @return int Number of groups that the contact is in
     * @version 2012091301
     * @since 2012091301
     */
    public function groupCount() {
        if ($this->groups == null)
            return 0;
        else
            return count($this->groups);
    }
    
    /**
     * Disassociates this contact from any groups
     * it is currently a member of
     * @version 2012091301
     * @since 2012091301
     */
    public function clearGroups() {
        $this->groups = null;
    }
    
    /**
     * Returns the ID of the addressbook that
     * contains this contact
     * @return int Addressbook DB ID
     * @version 2012091301
     * @since 2012091301
     */
    public function getAddressbookId() {
        return $this->addressbookId;
    }

    /**
     * Sets the ID of the addressbook that
     * contains this contact
     * @param int $addressbookId Addressbook DB ID
     * @version 2012092601
     * @since 2012091301
     */
    public function setAddressbookId($addressbookId) {
        $addressbookId = (int) $addressbookId;
        
        if ($addressbookId > 0)
            $this->addressbookId = $addressbookId;
    }
    
}

?>