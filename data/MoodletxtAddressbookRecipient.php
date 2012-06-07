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
 *     "txttools", "moodletxt", "Blackboard", "Blackboard Connect" or "Cy-nap"
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
 * @version 2012052301
 * @since 2010082001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/util/StringHelper.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtRecipient.php');

/**
 * Data bean for a recipient who has an entry in the address book
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012052301
 * @since 2010082001
 */
class MoodletxtAddressbookRecipient extends MoodletxtRecipient {

    /**
     * DB record ID for the contact
     * @var int
     */
    private $id;

    /**
     * Name of the company the recipient represents
     * @var string
     */
    private $companyName;

    /**
     * Constructor - initialises data bean
     * @param MoodletxtPhoneNumber $recipientNumber Recipient's mobile phone number
     * @param string $firstName Recipient's first name (Recommended)
     * @param string $lastName Recipient's last name (Recommended)
     * @param string $companyName Name of the company the recipient represents (Optional)
     * @param int $id DB record ID of the recipient (Optional)
     * @version 2011040801
     * @since 2010082001
     */
    public function __construct(MoodletxtPhoneNumber $recipientNumber, $firstName = '', $lastName = '', $companyName = '', $id = 0) {

        // Call super-constructor with common info
        parent::__construct($recipientNumber, $firstName, $lastName);

        // Set bean properties
        $this->setCompanyName($companyName);
        $this->setId($id);
        
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
     * @version 2010090201
     * @since 2010082001
     */
    public function setCompanyName($companyName) {
        
        if ($companyName != '')
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
     * @version 2012032101
     * @since 2010082001
     */
    public function getFullNameForDisplay() {

        return StringHelper::formatNameForDisplay(
                $this->getFirstName(), $this->getLastName(), 
                null, $this->getCompanyName());
        
    }
    
}

?>