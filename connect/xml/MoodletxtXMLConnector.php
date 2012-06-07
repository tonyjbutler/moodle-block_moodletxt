<?php

/**
 * File container for the MoodletxtXMLConnector class and related exceptions
 * 
 * moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "txttools", "moodletxt", "Blackboard", "Blackboard Connect" or "Cy-nap"
 *   - Derivative works must be have their differences from the original material noted,
 *     and must not be misrepresentative of the origin of this material, or of the original service
 * 
 * Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
 * or legal liability arising from their use of this code.
 * 
 * @see MoodletxtXMLConnector
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012052801
 * @since 2011032901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Sends and receives data to/from the txttools website
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012052801
 * @since 2011032901
 */
class MoodletxtXMLConnector {

    /**
     * Holds which protocol to use - SSL/HHTP
     * @var int
     */
    private $protocol;

    /**
     * User agent for PHP API
     * @var string
     */
    private $USER_AGENT = 'moodletxt 3.0-beta1';

    /**
     * Sets up the connector and selects protocol
     * @param int $protocol
     * @version 2011032901
     * @since 2011032901
     */
    function __construct($protocol) {

        $this->protocol = $protocol;

        // Set up user agent according to platform
        if (array_key_exists('SERVER_SOFTWARE', $_SERVER))
            $this->USER_AGENT .= ' SRV(' . $_SERVER['SERVER_SOFTWARE'] . ')';

        $this->USER_AGENT .= ' PHP(' . phpversion() . ')';

    }

    /**
     * Sends requests to txttools.
     * Takes an array of requests and shoves them to txttools, then grabs the responses and passes them back up to the calling object
     * @param array $requestArray Requests to send
     * @return array Unparsed response from txttools
     * @throws SocketException, HTTPException
     * @version 2011041301
     * @since 2011032901
     */
    public function sendData($requestArray) {

        // If the parameter is not formatted as an array, make it one
        if (! is_array($requestArray))
            $requestArray = array($requestArray);

        // Check for protocol
        if ($this->protocol == MoodletxtOutboundController::$CONNECTION_TYPE_SSL) {

            $port = 443;
            $prefix = 'ssl://';

        } else {

            $port = 80;
            $prefix = '';

        }

        $host = 'www.txttools.co.uk';
        $path = '/connectors/XML/xml.jsp';

        $responseArray = array();

        foreach($requestArray as $request) {

            // Build URL-encoded string from XML request
            $poststring = "XMLPost=" . urlencode($request);

            // Build connection string
            $request  = "POST " . $path . " HTTP/1.0\r\n";
            $request .= "Host: " . $host . "\r\n";
            $request .= "User-Agent: " . $this->USER_AGENT . "\r\n";
            $request .= "Content-type: application/x-www-form-urlencoded\r\n";
            $request .= "Content-length: " . strlen($poststring) . "\r\n";
            $request .= "Connection: close\r\n\r\n";
            $request .= $poststring . "\r\n";

//            error_log(get_string('logxmlblocksent', 'block_moodletxt') . "\r\n\r\n" . $request);

            // Open socket
            $fp = fsockopen($prefix . $host, $port, $errorNo, $errorStr, $timeout = 30);

            if (! $fp) {

                throw new SocketException();

            } else {

                // Send request to server
                fputs($fp, $request);

                // Get server response
                $response = '';

                while (!feof($fp)) {

                    $response .= @fgets($fp, 128); // Bug in PHP SSL handling causes problems here - suppress

                }

                fclose($fp);

                // Check that XML has been returned
                $XMLproc = '<?xml';

                $checkForXML = strpos($response, $XMLproc);

                if ($checkForXML === false) {

                    // If no XML is received, check for HTTP error codes
                    // Uses only the txttools server, so safe to assume Apache,HTTP 1.1, Linux
                    $responseCode = substr($response, 9, 3);

                    throw new HTTPException('HTTP error encountered when sending.', $responseCode);


                } else {

                    $response = substr($response, $checkForXML);

                }
              
            }

//            error_log(get_string('logxmlresponse', 'block_moodletxt') . "\r\n\r\n" . $response);

            // Push response from website onto response array
            array_push($responseArray, $response);
            
        }

        // Give responses back to parsers
        return $responseArray;

    }

}

/**
 * Exception thrown when HTTP response codes are returned from a connection
 * @author Greg J Preece <support@txttools.co.uk>
 * @package uk.co.moodletxt.connect.xml
 * @copyright Copyright &copy; 2011 txttools Ltd. All rights reserved.
 * @version 2011032901
 * @since 2011032901
 */
class HTTPException extends Exception {

    /**
     * Standard exception constructor - calls superclass constructor with error message/code
     * @param string $message
     * @param int $code
     * @version 2011032901
     * @since 2011032901
     */
    function __construct($message = null, $code = 0) {

        parent::__construct($message, $code);

    }

}

/**
 * Exception thrown when a connection socket
 * cannot be opened, or fails
 * @author Greg J Preece <support@txttools.co.uk>
 * @package uk.co.moodletxt.connect.xml
 * @copyright Copyright &copy; 2011 txttools Ltd. All rights reserved.
 * @version 2011032901
 * @since 2011032901
 */
class SocketException extends Exception {

    /**
     * Standard exception constructor - calls
     * superclass constructor with error message/code
     * @param string $message
     * @param int $code
     * @version 2011032901
     * @since 2011032901
     */
    function __construct($message = null, $code = 0) {

        parent::__construct($message, $code);

    }

}

?>