<?php

/**
 * File container for MoodletxtStatusIconFactory class
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
 * @see MoodletxtStatusIconFactory
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2012040301
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/renderer.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtOutboundSMSStatus.php');

/**
 * Utility class to wrap generation of status "light" icons, dependent
 * on the indicated status of a message
 * @package uk.co.moodletxt.util
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2012040301
 */
class MoodletxtStatusIconFactory {

    /**
     * Creates an appropriate status icon for a message based on its status code,
     * with localised alt and title tags included
     * @param int $statusCode Status code of message
     * @return moodletxt_icon Status icon for message
     * @version 2012040301
     * @since 2012040301
     */
    public static final function generateStatusIconForCode($statusCode) {

        $statusCode = (int) $statusCode;
        $statusIcon = null;
        
        switch($statusCode) {

            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_AT_NETWORK:
            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_INSUFFICIENT_CREDITS:
            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_UNKNOWN_ERROR:

                $statusIcon = new moodletxt_icon(moodletxt_icon::$ICON_STATUS_FAILED, 
                        get_string('altstatusfailed', 'block_moodletxt'),
                        array('title' => get_string('labelstatusfailed', 'block_moodletxt')));
                break;

            case MoodletxtOutboundSMSStatus::$STATUS_TRANSIT_MESSAGE_QUEUED:
            case MoodletxtOutboundSMSStatus::$STATUS_TRANSIT_DELIVERED_TO_AGGREGATOR:
            case MoodletxtOutboundSMSStatus::$STATUS_TRANSIT_DELIVERED_TO_NETWORK:
            case MoodletxtOutboundSMSStatus::$STATUS_FAILED_WILL_RETRY:

                $statusIcon = new moodletxt_icon(moodletxt_icon::$ICON_STATUS_TRANSIT,
                        get_string('altstatustransit', 'block_moodletxt'),
                        array('title' => get_string('labelstatustransit', 'block_moodletxt')));
                break;

            case MoodletxtOutboundSMSStatus::$STATUS_DELIVERED_TO_HANDSET:

                $statusIcon = new moodletxt_icon(moodletxt_icon::$ICON_STATUS_DELIVERED,
                        get_string('altstatusdelivered', 'block_moodletxt'),
                        array('title' => get_string('labelstatusdelivered', 'block_moodletxt')));
                break;            
            
            default:
                
                $statusIcon = new moodletxt_icon(moodletxt_icon::$ICON_STATUS_TRANSIT,
                        get_string('altstatustransit', 'block_moodletxt'),
                        array('title' => get_string('labelstatustransit', 'block_moodletxt')));
                
        }
        
        return $statusIcon;
        
    }
    
}

?>