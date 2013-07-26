<?php

/**
 * File container for MoodletxtCharsetJSONHandler class
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
 * @version 2013061701
 * @since 2011102401
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/ajax/MoodletxtAJAXException.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtCharsetHelper.php');

/**
 * JSON handler for checking whether given text can fit within a
 * specified character set. Used generally for detecting whether outbound
 * SMS messages will fit within GSM 03.38, as most other systems will
 * support UTF-8 at this point.
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013061701
 * @since 2011102401
 */
class MoodletxtCharsetJSONHandler {

    /**
     * Default values for response
     * @var array(string => mixed)
     */
    private $responseTemplate = array(
        'charset' => 'GSM',
        'matches' => false,

        'hasError' => false,
        'errorCode' => 0,
        'errorMessage' => '',
        'makeNoFurtherRequests' => false
    );

    /**
     * Receive JSON and decide what to do based on mode switch
     * @param string $json JSON to parse
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2013061701
     * @since 2011102401
     */
    public function processJSON($json) {
        
        $decoded = json_decode($json);
        
        // Check that JSON is valid
        if (! is_object($decoded) || ! isset($decoded->charset))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidjson', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_JSON,
                null, false);
        
        $response = '';
        
        // What do we need to do with this?
        switch(strtolower($decoded->charset)) {

            case 'gsm':
                $response = $this->checkGSM(
                    clean_param($decoded->text, PARAM_TEXT));
                break;            
                        
            default:
                throw new MoodletxtAJAXException(
                    get_string('errorinvalidjson', 'block_moodletxt'),
                    MoodletxtAJAXException::$ERROR_CODE_BAD_JSON,
                    null, false);
        }
        
        return $response;
        
    }

    /**
     * Checks whether the given text matches the GSM 03.38 charset
     * @param string $textToCheck Text to check
     * @return string Encoded JSON response
     * @version 2013052301
     * @since 2011102401
     */
    private function checkGSM($textToCheck) {
        $response = $this->responseTemplate; // Copy template
        $response['charset'] = 'GSM';
        $response['matches'] = MoodletxtCharsetHelper::is_GSM($textToCheck);
        
        return json_encode($response);
    }
        
}

?>