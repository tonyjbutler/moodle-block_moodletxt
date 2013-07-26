<?php

/**
 * File container for MoodletxtBrowserHelper class
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
 * @see MoodletxtBrowserHelper
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011101901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Provides helper methods for working with web browsers
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011101901
 */
class MoodletxtBrowserHelper {

    /**
     * Check to see if the user is using a given Internet Exploder version
     * @param int $version Version of IE to look for
     * @return boolean If user is using the version given
     * @version 2011101901
     * @since 2011101901
     */
    public static function isExploder($version = 6) {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ' . $version . '.') !== FALSE);
    }
}

?>
