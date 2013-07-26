<?php

/**
 * File container for MoodletxtAJAXException class
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
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012090501
 * @since 2011061201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * If an error is produced during processing of an AJAX script,
 * this exception is thrown. It allows itself to be echoed to JSON
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012090501
 * @since 2011061201
 */
class MoodletxtAJAXException extends Exception {
    
    /**
     * Error code for when the JSON sent by the page is bad
     * @var int
     */
    public static $ERROR_CODE_BAD_JSON = 700;
    
    /**
     * Error code for when the page sends a non-existent account ID
     * @var int
     */
    public static $ERROR_CODE_BAD_ACCOUNT_ID = 701;
    
    /**
     * Error code for when a bad message ID is provided,
     * or the user does not own the message they're attempting to access/modify
     * @var int
     */
    public static $ERROR_CODE_BAD_MESSAGE_ID = 702;
    
    /**
     * Error code for when an invalid Moodle user ID is provided
     * @var int
     */
    public static $ERROR_CODE_BAD_USER_ID = 703;
    
    /**
     * Error code for when an invalid addressbook contact ID
     * @var int
     */
    public static $ERROR_CODE_BAD_CONTACT_ID = 704;
    
    /**
     * Error code for when a user tries to delete a tag that is not their own
     * @var int
     */
    public static $ERROR_NOT_TAG_OWNER = 705;
    
    /**
     * Error code for when a user tries to edit an addressbook that is not theirs
     * @var int
     */
    public static $ERROR_NOT_ADDRESSBOOK_OWNER = 706;
    
    /**
     * Boolean representing whether or not the calling page should cease requests
     * @var boolean
     */
    private $preventFurtherRequests;
    
    /**
     * Account ID that caused the exception to be thrown (if known)
     * @var int
     */
    private $accountId;
    
    /**
     * Extended exception constructor - takes additional AJAX-related kit
     * @param string $message Error message
     * @param int $code Error code
     * @param Exception $previous Previous exception
     * @param boolean $preventFurtherRequests Whether the calling page should stop requests
     * @param int $accountId Account ID that caused the error (if processing accounts)
     * @version 2011061201
     * @since 2011061201
     */
    public function __construct($message, $code, $previous = null, $preventFurtherRequests = false, $accountId = 0) {
        parent::__construct($message, $code, $previous);
        $this->preventFurtherRequests = $preventFurtherRequests;
        $this->accountId = $accountId;
    }

    /**
     * Outputs this exception as JSON to return to the page
     * @return string JSON version of error
     * @version 2011061201
     * @since 2011061201
     */
    public function toJSON() {
        $response['hasError'] = true;
        $response['errorCode'] = $this->getCode();
        $response['errorMessage'] = $this->getMessage();
        $response['makeNoFurtherRequests'] = $this->getPreventFurtherRequests();

        if ($this->getAccountId() > 0) {
            $response['accountID'] = $this->getAccountId();
        }
        
        return json_encode($response);
    }

    /**
     * Returns whether the calling page should cease all requests
     * @return boolean
     * @version 2011061201
     * @since 2011061201
     */
    public function getPreventFurtherRequests() {
        return $this->preventFurtherRequests;
    }

    /**
     * Returns the account ID that caused the exception to be thrown
     * @return int Account ID
     * @version 2011061201
     * @since 2011061201
     */
    public function getAccountId() {
        return $this->accountId;
    }
    
}

?>