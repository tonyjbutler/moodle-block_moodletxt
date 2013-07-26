<?php

/**
 * File container for the TxttoolsAccount class
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
 * @see TxttoolsAccount
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012042301
 * @since 2010082001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtBiteSizedUser.php');

/**
 * Represents a txttools account within the system
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012042301
 * @since 2010082001
 */
class TxttoolsAccount {

    /**
     * Represents a billing type of invoiced
     * @var int
     */
    public static $BILLING_TYPE_INVOICED = 0;

    /**
     * Represents a billing type of prepaid
     * @var int
     */
    public static $BILLING_TYPE_PREPAID = 1;

    /**
     * DB Record ID for the account
     * @var int
     */
    private $id;

    /**
     * Username on txttools
     * @var string
     */
    private $username;

    /**
     * Encrypted password string
     * @var string
     */
    private $encryptedPassword;

    /**
     * Short description of the account
     * @var string
     */
    private $description;

    /**
     * Default user for receiving inbound messages
     * @var MoodletxtBiteSizedUser
     */
    private $defaultUser;

    /**
     * Number of credits used on account
     * @var int
     */
    private $creditsUsed;

    /**
     * Number of credits remaining on account
     * @var int
     */
    private $creditsRemaining;

    /**
     * Whether or not outbound access is enabled on this account
     * @var boolean
     */
    private $outboundEnabled;

    /**
     * Whether or not inbound access is enabled on this account
     * @var boolean
     */
    private $inboundEnabled;

    /**
     * Integer representing the billing type of this account (prepaid/invoiced/other)
     * @var int
     */
    private $billingType;

    /**
     * Unix timestamp representing last sync with txttools
     * @var int
     */
    private $lastUpdate;
    
    /**
     * Collection of Moodle users that outbound access
     * is restricted to on this account
     * @var MoodletxtBiteSizedUser[]
     */
    private $allowedUsers = array();
    
    /**
     * Collection of inbound filters operating on account
     * @var MoodletxtInboundFilter[]
     */
    private $inboundFilters = array();


    /**
     * Constructor - sets up account bean
     * @param string $username Username for the account
     * @param string $description Description of account
     * @param int $defaultUser Default user for inbound messages
     * @param int $creditsUsed Number of credits used
     * @param int $creditsRemaining Number of credits remaining
     * @param boolean $outboundEnabled Can account send messages?
     * @param boolean $inboundEnabled Can account receive messages?
     * @param int $lastUpdate Time of last sync with txtttools
     * @param int $billingType Account's billing type - prepaid/invoiced/other
     * @param int $id DB record ID for account
     * @version 2011033101
     * @since 2010082001
     */
    function __construct($username, $description, MoodletxtBiteSizedUser $defaultUser, $creditsUsed = 0, $creditsRemaining = 0,
            $outboundEnabled = true, $inboundEnabled = true, $billingType = 0, $lastUpdate = 0, $id = 0) {

        $this->setUsername($username);
        $this->setDescription($description);
        $this->setDefaultUser($defaultUser);
        $this->setCreditsUsed($creditsUsed);
        $this->setCreditsRemaining($creditsRemaining);
        $this->setOutboundEnabled($outboundEnabled);
        $this->setInboundEnabled($inboundEnabled);
        $this->setBillingType($billingType);
        $this->setLastUpdate($lastUpdate);
        $this->setId($id);

    }

    /**
     * Returns the DB record ID for this account
     * @return int DB record ID
     * @version 2010082001
     * @since 2010082001
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the DB record ID for this account
     * @param int $id DB record ID
     * @version 2010082001
     * @since 2010082001
     */
    public function setId($id) {
        if ($id > 0)
            $this->id = $id;
    }

    /**
     * Returns the username of this account
     * @return string Txttools account username
     * @version 2010082001
     * @since 2010082001
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set the txttools username for this account
     * @param string $username Txttools account username
     * @version 2010082001
     * @since 2010082001
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Returns the password for this account in encrypted form
     * @return string txttools account password (encrypted)
     * @version 2010082401
     * @since 2010082401
     */
    public function getEncryptedPassword() {
        return $this->encryptedPassword;
    }

    /**
     * Sets the password for this account in encrypted form
     * @param string $encryptedPassword txttools account password (encrypted)
     * @version 2010082401
     * @since 2010082401
     */
    public function setEncryptedPassword($encryptedPassword) {
        $this->encryptedPassword = $encryptedPassword;
    }

    /**
     * Returns a short description of the account
     * @return string Account description
     * @version 2010082001
     * @since 2010082001
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Sets a short description of the account
     * @param string $description  Account description
     * @version 2010082001
     * @since 2010082001
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Returns the user that receives any unfiltered messages on this account
     * @return MoodletxtBiteSizedUser Default user
     * @version 2012042301
     * @since 2012042301
     */
    public function getDefaultUser() {
        return $this->defaultUser;
    }

    /**
     * Sets the user that will receive any unfiltered messages on this account
     * @param MoodletxtBiteSizedUser $defaultUser Default user
     * @version 2012042301
     * @since 2012042301
     */
    public function setDefaultUser(MoodletxtBiteSizedUser $defaultUser) {
        $this->defaultUser = $defaultUser;
    }
        
    /**
     * Returns the number of message credits
     * used via this account
     * @return int Number of credits used
     * @version 2010082001
     * @since 2010082001
     */
    public function getCreditsUsed() {
        return $this->creditsUsed;
    }

    /**
     * Sets the number of message credits
     * used via this account
     * @param int $creditsUsed  Number of credits used
     * @version 2010082001
     * @since 2010082001
     */
    public function setCreditsUsed($creditsUsed) {
        $this->creditsUsed = $creditsUsed;
    }

    /**
     * Returns the number of message credits
     * remaining on this account
     * @return int Number of credits remaining
     * @version 2010082001
     * @since 2010082001
     */
    public function getCreditsRemaining() {
        return $this->creditsRemaining;
    }

    /**
     * Sets the number of message credits
     * remaining on this account
     * @param int $creditsRemaining Number of credits remaining
     * @version 2010082001
     * @since 2010082001
     */
    public function setCreditsRemaining($creditsRemaining) {
        $this->creditsRemaining = $creditsRemaining;
    }

    /**
     * Returns the time of the last sync
     * with txttools
     * @return int Unix timestamp
     * @version 2010082001
     * @since 2010082001
     */
    public function getLastUpdate() {
        return $this->lastUpdate;
    }
    
    /**
     * Returns the time of the last sync
     * with txttools, formatted for readability
     * @param string $format Userdate() format to use
     * @return string Formatted time/date
     * @version 2011061301
     * @since 2011061301
     */
    public function getLastUpdateFormatted($format = "%H:%M:%S, %d %B %Y") {
        return userdate($this->getLastUpdate(), $format);
    }

    /**
     * Sets the time of the last sync
     * with txttools
     * @param int $lastUpdate Unix timestamp
     * @version 2010082001
     * @since 2010082001
     */
    public function setLastUpdate($lastUpdate) {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * Returns whether outbound access is enabled
     * @return boolean Outbound enabled?
     * @version 2011060901
     * @since 2011060901
     */
    public function isOutboundEnabled() {
        return $this->outboundEnabled;
    }

    /**
     * Sets whether outbound access is enabled
     * @param boolean $outboundEnabled Outbound enabled?
     * @version 2010082001
     * @since 2010082001
     */
    public function setOutboundEnabled($outboundEnabled) {
        $this->outboundEnabled = $outboundEnabled;
    }

    /**
     * Gets whether inbound access is enabled
     * @return boolean Inbound enabled?
     * @version 2011060901
     * @since 2011060901
     */
    public function isInboundEnabled() {
        return $this->inboundEnabled;
    }

    /**
     * Sets whether inbound access is enabled
     * @param boolean $inboundEnabled Inbound enabled?
     * @version 2010082001
     * @since 2010082001
     */
    public function setInboundEnabled($inboundEnabled) {
        $this->inboundEnabled = $inboundEnabled;
    }

    /**
     * Returns the billing type of this account
     * @return int Billing type
     * @version 2011033101
     * @since 2011033101
     */
    public function getBillingType() {
        return $this->billingType;
    }

    /**
     * Sets the billing type of this account
     * @param int $billingType Billing type
     * @version 2011033101
     * @since 2011033101
     */
    public function setBillingType($billingType) {
        if ($billingType == self::$BILLING_TYPE_PREPAID)
            $this->billingType = $billingType;
        else
            $this->billingType = self::$BILLING_TYPE_INVOICED;
    }
    
    /**
     * Returns all users with outbound access to this account
     * @return array(MoodletxtBiteSizedUser) Set of allowed users
     * @version 2011061701
     * @since 2011061701
     */
    public function getAllowedUsers() {
        return $this->allowedUsers;
    }

    /**
     * Set the users that are allowed outbound access on this account
     * @param array(MoodletxtBiteSizedUser) $allowedUsers Users to give access to
     * @version 2011061701
     * @since 2011061701
     */
    public function setAllowedUsers($allowedUsers) {
        $this->allowedUsers = $allowedUsers;
    }

    /**
     * Grants a new user outbound access to the account
     * @param MoodletxtBiteSizedUser $allowedUser User to give access to
     * @version 2011061701
     * @since 2011061701
     */
    public function addAllowedUser(MoodletxtBiteSizedUser $allowedUser) {
        array_push($this->allowedUsers, $allowedUser);
    }

    /**
     * Removes all users that currently have outbound access on this account
     * @version 2011061701
     * @since 2011061701
     */
    public function clearAllowedUsers() {
        $this->allowedUsers = array();
    }
    
    /**
     * Returns all inbound filters active on the account
     * @return array(MoodletxtInboundFilter) Filter collection
     * @version 2011070401
     * @since 2011070401
     */
    public function getInboundFilters() {
        return $this->inboundFilters;
    }

    /**
     * Sets the inbound filters active on the account
     * @param array(MoodletxtInboundFilter) $inboundFilters Filter collection
     * @version 2011070401
     * @since 2011070401
     */
    public function setInboundFilters($inboundFilters) {
        $this->inboundFilters = $inboundFilters;
    }
    
    /**
     * Adds a single inbound filter to the account's active list
     * @param MoodletxtInboundFilter $inboundFilter 
     * @version 2011070401
     * @since 2011070401
     */
    public function addInboundFilter(MoodletxtInboundFilter $inboundFilter) {
        array_push($this->inboundFilters, $inboundFilter);
    }
    
    /**
     * Kills off all inbound filters active on the account
     * @version 2011070401
     * @since 2011070401
     */
    public function clearInboundFilters() {
        $this->inboundFilters = array();
    }

}

?>