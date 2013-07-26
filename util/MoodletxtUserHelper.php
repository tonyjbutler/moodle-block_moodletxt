<?php

/**
 * File container for MoodletxtUserHelper class
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
 * @see MoodletxtUserHelper
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011080401
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Utility class that extends the existing Moodle authentication
 * functions to allow multiple valid capabilities to be required easily
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011080401
 */
class MoodletxtUserHelper {

    /**
     * Dictates that a user must have one of the given capabilities to view the current page
     * @param array $capabilities An array of capability names.
     * @param object $context The context to check the capability in. 
     * @param integer $userid A user id. By default (null) checks the permissions of the current user.
     * @param bool $doanything If false, ignore effect of admin role assignment
     * @param string $errorstring The error string to to user. Defaults to 'nopermissions'.
     * @param string $stringfile The language file to load the error string from. Defaults to 'error'.
     * @return void Terminates with an error if the user does not have the given capability.
     * @version 2011080401
     * @since 2011080401
     */
    public static function require_any_capability($capabilities, $context, $userid = null, $doanything = true,
        $errormessage = 'nopermissions', $stringfile = '') {
        
        if (!is_array($capabilities))
            $capabilities = array($capabilities);

        if (! has_any_capability($capabilities, $context, $userid, $doanything))
            throw new required_capability_exception($context, $capabilities[0], $errormessage, $stringfile); // Just use the top one
                    
    }

    /**
     * Dictates that a user must have all of the given capabilities to view the current page
     * @param array $capabilities An array of capability names.
     * @param object $context The context to check the capability in.
     * @param integer $userid A user id. By default (null) checks the permissions of the current user.
     * @param bool $doanything If false, ignore effect of admin role assignment
     * @param string $errorstring The error string to to user. Defaults to 'nopermissions'.
     * @param string $stringfile The language file to load the error string from. Defaults to 'error'.
     * @return void Terminates with an error if the user does not have the given capability.
     * @version 2011080401
     * @since 2011080401
     */
    public static function require_all_capabilities($capabilities, $context, $userid = null, $doanything = true,
        $errormessage = 'nopermissions', $stringfile = '') {

        if (!is_array($capabilities))
            $capabilities = array($capabilities);
              
        // Done this way rather than using has_all_capabilities so that we can capture
        // which capability is missing and chuck it as an exception
        foreach($capabilities as $capability)
            if (! has_capability($capability, $context, $userid, $doanything))
                throw new required_capability_exception($context, $capability, $errormessage, $stringfile);
            
    }
    
}

?>