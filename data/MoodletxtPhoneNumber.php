<?php

/**
 * File container for MoodletxtPhoneNumber class and related exceptions
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
 * @see MoodletxtPhoneNumber
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012060101
 * @since 2011040701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Data bean to represent a phone number and provide unified validation.
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012060101
 * @since 2011040701
 */
class MoodletxtPhoneNumber {

    /**
     * The phone number this bean represents
     * @var string
     */
    private $phoneNumber = '';

    /**
     * Sets up the phone number bean
     * @param string $phoneNumber Phone number string
     * @version 2011040701
     * @since 2011040701
     */
    function __construct($phoneNumber) {

        $this->setPhoneNumber($phoneNumber);

    }

    /**
     * Set the phone number stored
     * @param string $phoneNumber Phone number string
     * @throws InvalidPhoneNumberException
     * @version 2011040701
     * @since 2011040701
     */
    public function setPhoneNumber($phoneNumber) {

        // Strip unwanted characters
        $phoneNumber = preg_replace('/[^+0-9]/', '', $phoneNumber);

        if (self::validatePhoneNumber($phoneNumber))
            $this->phoneNumber = $phoneNumber;
        else 
            throw new InvalidPhoneNumberException('Phone number ' . $phoneNumber . 'is invalid. ' .
                    'Please ensure the number you are using is in international format.');
            
    }

    /**
     * Get the stored phone number
     * @return string Phone number string
     * @version 2011040701
     * @since 2011040701
     */
    public function getPhoneNumber() {

        return $this->phoneNumber;

    }

    /**
     * Validate a phone number.
     * Validates an international phone number, and returns success/failure.
     * @param string $phoneNumber Phone number string
     * @return boolean Success
     * @static
     * @version 2012060101
     * @since 2011040701
     */
    public static function validatePhoneNumber($phoneNumber) {

        if (preg_match('/^\+?\d{5,20}$/', $phoneNumber) > 0)
            return true;
        else
            return false;

    }
    
    /**
     * Overrides the standard toString method in order
     * to make the object easily printable
     * @return string Phone number
     * @version 2011062701
     * @since 2011062701
     */
    public function toString() {
        return $this->getPhoneNumber();
    }

}

/**
 * Exception thrown when a phone number is not accepted
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011040701
 * @since 2011040701
 */
class InvalidPhoneNumberException extends Exception {

    /**
     * Standard exception constructor - calls
     * superclass constructor with error message/code
     * @param string $message Error message
     * @param int $code Error code
     * @version 2011040701
     * @since 2011040701
     */
    function __construct($message = null, $code = 0) {

        parent::__construct($message, $code);

    }

}

?>