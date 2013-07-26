<?php

/**
 * File container for MoodletxtRecipient class
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
 * @see MoodletxtRecipient
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2010082001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');

/**
 * Parent data bean for message recipients - all
 * representation of Moodle users, address book contacts,
 * etc inherit from this class.
 *
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2010082001
 */
abstract class MoodletxtRecipient {

    /**
     * Holds the destination phone number
     * @var MoodletxtPhoneNumber
     */
    protected $recipientNumber;

    /**
     * Holds the recipient's first name
     * @var string
     */
    protected $firstName;

    /**
     * Holds the recipient's last name
     * @var string
     */
    protected $lastName;

    /**
     * Initialises the data bean
     * @param MoodletxtPhoneNumber $recipientNumber Recipient's phone number
     * @param string $firstName Recipient's first name
     * @param string $lastName Recipient's last name
     * @version 2012031401
     * @since 2010082001
     */
    public function __construct(MoodletxtPhoneNumber $recipientNumber = null, $firstName = '', $lastName = '') {

        if ($recipientNumber != null)
            $this->setRecipientNumber($recipientNumber);
        
        $this->setFirstName($firstName);
        $this->setLastName($lastName);

    }

    /**
     * Performs checks on a passed destination number and sets it
     * @param MoodletxtPhoneNumber $recipientNumber Recipient's phone number
     * @version 2012031401
     * @since 2010090201
     */
    public function setRecipientNumber(MoodletxtPhoneNumber $recipientNumber) {
        
        $this->recipientNumber = $recipientNumber;
        
    }

    /**
     * Sets the first name of this recipient
     * @param string $firstName Recipient's first name
     * @version 2013050801
     * @since 2010082001
     */
    public function setFirstName($firstName = '') {
        $this->firstName = $firstName;
    }

    /**
     * Sets the last name of this recipient
     * @param string $lastName Recipient's last name
     * @version 2013050801
     * @since 2010082001
     */
    public function setLastName($lastName = '') {
        $this->lastName = $lastName;
    }

    /**
     * Gets the phone number of this recipient
     * @return MoodletxtPhoneNumber Recipient's phone number
     * @version 2011040801
     * @since 2010090201
     */
    public function getRecipientNumber() {

        return $this->recipientNumber;

    }

    /**
     * Gets the first name of this recipient
     * @return string Recipient's first name
     * @version 2010082001
     * @since 2010082001
     */
    public function getFirstName() {

        return $this->firstName;

    }

    /**
     * Gets the last name of this recipient
     * @return string Recipient's last name
     * @version 2010082001
     * @since 2010082001
     */
    public function getLastName() {

        return $this->lastName;

    }

    /**
     * Uses the first and last name fields to generate
     * a full name for this recipient, to be used
     * in message tags
     * @return string Recipient's full name
     * @version 2010082001
     * @since 2010082001
     */
    public function getFullName() {

        return $this->getFirstName() . ' ' . $this->getLastName();

    }

    /**
     * Returns a full-name string to be used
     * in page table displays
     * @return string Recipient's full name, display formatted
     * @version 2013052301
     * @since 2010082001
     */
    public function getFullNameForDisplay() {

        return MoodletxtStringHelper::formatNameForDisplay(
                $this->getFirstName(), $this->getLastName());

    }
    
    /**
     * Returns whether the recipient has a valid phone number applied
     * to it. If no number is present here, then the user or contact
     * either has no phone number in their profile, or the number in
     * their profile is invalid and thus not applied
     * @return boolean Whether user has a number
     * @version 2012060101
     * @since 2012060101
     */
    public function hasPhoneNumber() {
        return ($this->recipientNumber != null);
    }
        

}

?>