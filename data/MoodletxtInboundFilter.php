<?php

/**
 * File container for the MoodletxtInboundFilter class
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
 * @see MoodletxtInboundFilter
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012042301
 * @since 2011070401
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Represents a filter for sorting inbound messages on an account
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012042301
 * @since 2011070401
 */
class MoodletxtInboundFilter {
    
    /**
     * Represents a keyword filter type within the system
     * @var string
     */
    public static $FILTER_TYPE_KEYWORD = 'keyword';
    
    /**
     * Represents a phone number filter type within the system
     * @var string
     */
    public static $FILTER_TYPE_PHONE_NUMBER = 'phoneno'; // Legacy DB values - do not change!
    
    /**
     * The filter's database record ID
     * @var int
     */
    private $id;
    
    /**
     * The ID of the txttools account this filter is active on
     * @var int
     */
    private $accountId;
    
    /**
     * What kind of filter this is - keyword, source, etc
     * @var string
     */
    private $filterType;
    
    /**
     * The filter's operand, i.e., what it is searching for
     * @var string
     */
    private $operand;
        
    /**
     * Set of destination users for messages hit by this filter
     * @var type 
     */
    private $destinationUsers = array();
        
    /**
     * Special-case operand type for phone number filters
     * Allows for specialised operations on phone number
     * @var MoodletxtPhoneNumber
     */
    private $phoneNumberOperand;

    /**
     * Initialises bean with given data
     * @param int $accountId ID of txttools account this filter is attached to
     * @param string $filterType Type of filter - keyword, phone number, etc
     * @param string $operand Value to search for within messages
     * @param int $id Record ID of this filter if one exists
     * @version 2011070501
     * @since 2011070401
     */
    public function __construct($accountId, $filterType, $operand, $id = 0) {
        
        $this->setAccountId($accountId);
        $this->setFilterType($filterType);        
        $this->setOperand($operand);
        $this->setId($id);
    }

    /**
     * Returns the record ID of this filter, if one exists
     * @return int Record ID
     * @version 2011070401
     * @since 2011070401
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the record ID of this filter
     * @param int $id Record ID
     * @version 2011070401
     * @since 2011070401
     */
    public function setId($id) {
        if ($id > 0)
            $this->id = $id;
    }

    /**
     * Returns the ID of the txttools account 
     * this filter is attached to
     * @return int Account ID
     * @version 2011070401
     * @since 2011070401
     */
    public function getAccountId() {
        return $this->accountId;
    }

    /**
     * Sets the ID of the account this filter
     * is attached to
     * @param int $accountId Account ID
     * @version 2011070401
     * @since 2011070401
     */
    public function setAccountId($accountId) {
        if ($accountId > 0)
            $this->accountId = $accountId;
    }

    /**
     * Returns the type of filter this is:
     * keyword, phone number, etc
     * @return string Filter type
     * @version 2011070401
     * @since 2011070401
     */
    public function getFilterType() {
        return $this->filterType;
    }

    /**
     * Sets the type of filter this is:
     * keyword, phone number, etc
     * @param string $filterType Filter type
     * @throws InvalidArgumentException
     * @version 2011070401
     * @since 2011070401
     */
    public function setFilterType($filterType) {
        
        $filterType = strtolower($filterType);
        
        if ($filterType == self::$FILTER_TYPE_KEYWORD ||
            $filterType == self::$FILTER_TYPE_PHONE_NUMBER)
            $this->filterType = $filterType;
        else
            throw new InvalidArgumentException('Bad filter type - see public static fields in MoodletxtInboundFilter');
        
    }

    /**
     * Returns the operand of this filter - the
     * value being searched for
     * @return string Operand
     * @version 2012041701
     * @since 2011070401
     */
    public function getOperand() {
        if ($this->getFilterType() == self::$FILTER_TYPE_PHONE_NUMBER)
            return $this->getPhoneNumberOperand()->getPhoneNumber();
        else
            return $this->operand;
    }

    /**
     * Sets the operand of this filter - the
     * value being searched for
     * @param string $operand Operand
     * @version 2012041701
     * @since 2011070401
     */
    public function setOperand($operand) {
        if ($this->getFilterType() == self::$FILTER_TYPE_PHONE_NUMBER)
            $this->setPhoneNumberOperand(new MoodletxtPhoneNumber($operand));
        else
            $this->operand = $operand;
    }

    /**
     * Returns the phone number operand 
     * for source-based filters. This is a different
     * data type to all other filters.
     * @return MoodletxtPhoneNumber Phone number object
     * @version 2011070401
     * @since 2011070401
     */
    private function getPhoneNumberOperand() {
        return $this->phoneNumberOperand;
    }

    /**
     * Sets the phone number to be used in source-based
     * filters. This is a special case phone number class
     * @param MoodletxtPhoneNumber $phoneNumberOperand Phone number
     * @version 2011070401
     * @since 2011070401
     */
    private function setPhoneNumberOperand(MoodletxtPhoneNumber $phoneNumberOperand) {
        $this->phoneNumberOperand = $phoneNumberOperand;
    }
    
    /**
     * Returns an array of the users this filter forwards messages to
     * @return MoodletxtBiteSizedUser[] Destination users
     * @version 2012042301
     * @since 2012042301
     */
    public function getDestinationUsers() {
        return $this->destinationUsers;
    }

    /**
     * Sets the users this filter will forward messages to
     * @param MoodletxtBiteSizedUser[] $destinationUsers Destination users
     * @version 2012042301
     * @since 2012042301
     */
    public function setDestinationUsers($destinationUsers) {
        $this->destinationUsers = $destinationUsers;
    }

    /**
     * Adds a Moodle user to the list of users that will receive messages from this filter
     * @param MoodletxtBiteSizedUser $user Moodle user to add
     * @version 2012042301
     * @since 2012042301
     */
    public function addDestinationUser(MoodletxtBiteSizedUser $user) {
        $this->destinationUsers[$user->getId()] = $user;
    }

    /**
     * Clears the set of Moodle users that this filter forwards to
     * @version 2012042301
     * @since 2012042301
     */
    public function clearDestinationUsers() {
        $this->destinationUsers = array();
    }
    
}

?>