<?php

/**
 * File container for the MoodletxtRemoteProcessingException class
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
 * @version 2011042801
 * @since 2011042801
 */

/**
 * Exception is thrown when processing errors are returned from txttools server
 * @package uk.co.moodletxt.connect
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011042801
 * @since 2011042801
 */
class MoodletxtRemoteProcessingException extends Exception {

    /**
     * Do-nothing constructor - calls superclass constructor with passed parameters
     * @param string $errorMessage The error message
     * @param int $errorCode System code for this error
     * @version 2011042801
     * @since 2011042801
     */
    function __construct($errorMessage, $errorCode = 0) {

        parent::__construct($errorMessage, $errorCode);

    }

}

?>