<?php

/**
 * File container for MoodletxtStringHelper class
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
 * @see MoodletxtStringHelper
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011011401
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundSMSStatus.php');

/**
 * Utility class containing common string formatting/manipulation tasks
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011011401
 */
class MoodletxtStringHelper {

    /**
     * Method to ensure that all names within the module
     * are outputted in the same manner
     * @global object $CFG Moodle config object
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param string $username Username (where applicable)
     * @param string $company Name of company/institution (where applicable)
     * @param int $userId If user ID is present, usernames will become profile links
     * @param string $phoneNumber Phone number (where applicable)
     * @return string Formatted display string
     * @version 2012100401
     * @since 2011011401
     */
    public static final function formatNameForDisplay($firstname, $lastname, 
            $username = null, $company = null, $userId = 0, $phoneNumber = null) {
        
        global $CFG;

        if ($firstname == '')
            $firstname = get_string('fragunknownname', 'block_moodletxt');
        
        if ($lastname == '')
            $lastname = get_string('fragunknownname', 'block_moodletxt');        
        
        $displayName = $lastname . ', ' . $firstname;
        
        if ($company !== null)
            $displayName .= ' (' . $company . ')';
        
        if ($phoneNumber !== null)
            $displayName .= ' (' . $phoneNumber . ')';
        
        if ($username !== null) {
            
            if ($userId > 0)
                $displayName .= ' ' . html_writer::tag('a', '(' . $username . ')',
                        array('href' => $CFG->wwwroot . '/user/view.php?id=' . $userId));
            else
                $displayName .= ' (' . $username . ')';
            
        }

        return $displayName;

    }
    
    /**
     * Method to ensure that all account names within the
     * module are outputted in the same manner
     * @param string $username Txttools username
     * @param type $description Account description
     * @return string Formatted display string
     * @version 2011102401
     * @since 2011102401
     */
    public static final function formatAccountForDisplay($username, $description = null) {
        
        if ($description !== null)
            $username .= ' (' . $description . ')';
        
        return $username;
        
    }

    /**
     * Connection-independent method for merging tags into
     * outbound messages.
     * @param string $messageText
     * @param MoodletxtRecipient $recipient Message recipient
     * @return string Tagged message for single recipient
     * @version 2011040801
     * @since 2011040801
     */
    public static final function mergeTagsIntoMessageText($messageText, MoodletxtRecipient $recipient) {

        // Swap in name binds
        $messageText = str_replace('%FIRSTNAME%', $recipient->getFirstName(), $messageText);
        $messageText = str_replace('%SURNAME%', $recipient->getLastName(), $messageText);
        $messageText = str_replace('%FULLNAME%', $recipient->getFullName(), $messageText);

        return $messageText;

    }

    /**
     * Function to determine whether a string represents an integer.
     * Based on a suggestion in the PHP manual by "mark at codedesigner dot nl" 2008-06-26.
     *
     * @param mixed $invar The variable to be checked.
     * @return bool Whether or not the string represents an integer value.
     * @version 2012031401
     * @since 2012031401
     */
    public static final function isIntegerValue ($invar) {        
        return is_int($invar) || preg_match('@^[-]?[0-9]+$@',$invar) === 1;
    }
    
    /**
     * Returns a localised language string for the given message status code
     * @param int $statusCode Message status code
     * @return string Localised description of message status
     * @version 2012040301
     * @since 2012040301
     */
    public static final function getLanguageStringForStatusCode($statusCode) {
        
        $statusCode = (int) $statusCode;
        $responseString = '';
        
        switch($statusCode) {
            
            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_AT_NETWORK:
            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_INSUFFICIENT_CREDITS:
            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_UNKNOWN_ERROR:

                $responseString = get_string('labelstatusfailed', 'block_moodletxt');
                break;

            case MoodletxtOutboundSMSStatus::$STATUS_TRANSIT_MESSAGE_QUEUED:
            case MoodletxtOutboundSMSStatus::$STATUS_TRANSIT_DELIVERED_TO_AGGREGATOR:
            case MoodletxtOutboundSMSStatus::$STATUS_TRANSIT_DELIVERED_TO_NETWORK:
            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_WILL_RETRY:

                $responseString = get_string('labelstatustransit', 'block_moodletxt');
                break;

            case MoodletxtOutboundSMSStatus::$STATUS_DELIVERED_TO_HANDSET:

                $responseString = get_string('labelstatusdelivered', 'block_moodletxt');
                break;
            
        }
        
        return $responseString;
        
    }
    
    /**
     * Returns a localised language string for a connector error code
     * These should only be displayed to users with the relevant privileges
     * @param int $errorCode Error code requiring label
     * @return string Localised description of error code
     * @version 2013032601
     * @since 2012040301
     */
    public static final function getLanguageStringForErrorCode($errorCode) {
        
        return get_string('errorconn' . (int) $errorCode, 'block_moodletxt');
                
    }
    
    /**
     * Returns a localised error description for a remote processing exception
     * @param MoodletxtRemoteProcessingException $ex Exception requiring descriptor
     * @return string Localised description of error
     * @version 2012040301
     * @since 2012040301
     */
    public static final function getLanguageStringForRemoteProcessingException(MoodletxtRemoteProcessingException $ex) {
        
        return self::getLanguageStringForErrorCode($ex->getCode());
        
    }
    
    /**
     * Takes an input string and converts it to a valid CSS identifier
     * @param string $inputString String to convert
     * @return string Generated CSS identifier
     * @version 2012051501
     * @since 2012051501
     */
    public static final function convertToValidCSSIdentifier($inputString) {

        return preg_replace('/[^a-z0-9]+/i', '-', $inputString);
        
    }
    
}

?>