<?php

/**
 * File container for MoodletxtOutboundControllerFactory class
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
 * @package uk.co.moodletxt.connect
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011061301
 * @since 2011061301
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundController.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtOutboundXMLController.php');

/**
 * Returns outbound controller objects on request
 * @todo When the txttools APIs are updated, upgrade this class for failover provision
 * @package uk.co.moodletxt.connect
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011061301
 * @since 2011061301
 */
class MoodletxtOutboundControllerFactory {

    /**
     * Represents a JSON-based connection to txttools
     * @var type string
     */
    public static $CONTROLLER_TYPE_JSON = 'json';
    
    /**
     * Represents a REST-based connection to txttools
     * @var type string
     */
    public static $CONTROLLER_TYPE_REST = 'rest';
    
    /**
     * Represents a SOAP-based connection to txttools
     * @var type string
     */
    public static $CONTROLLER_TYPE_SOAP = 'soap';
    
    /**
     * Represents an XML-based connection to txttools
     * @var type string
     */    
    public static $CONTROLLER_TYPE_XML  = 'xml';
    
    /**
     * Generates an outbound controller of the requested type
     * @param type $controllerType Requested controller type
     * @return MoodletxtOutboundController Selected controller
     * @throws InvalidArgumentException
     * @version 2011061301
     * @since 2011061301
     */
    public static final function getOutboundController($controllerType = 'xml') {
        
        $controller = null;
        
        switch($controllerType) {
            
            case self::$CONTROLLER_TYPE_JSON:
                break; // JSON is a stub for when we re-do the API packages on the site
            
            case self::$CONTROLLER_TYPE_REST:
                break; // REST is a stub for when we re-do the API packages on the site
            
            case self::$CONTROLLER_TYPE_SOAP:
                break; // SOAP is a stub for when we re-do the API packages on the site
            
            case self::$CONTROLLER_TYPE_XML:
                return new MoodletxtOutboundXMLController();
                break;
            
            default:
                throw new InvalidArgumentException('Invalid controller type specified.');
        }
        
    }
    
}

?>