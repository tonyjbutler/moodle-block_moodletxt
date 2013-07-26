<?php

/**
 * File container for MoodletxtFilterJSONHandler class
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
 * @since 2011071101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/ajax/MoodletxtAJAXException.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');

/**
 * JSON handler for updating txttools accounts directly from
 * the admin page. AJAX calls made by the page are processed here
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013061701
 * @since 2011071101
 */
class MoodletxtFilterJSONHandler {

    /**
     * Default values for response
     * @var array(string => mixed)
     */
    private $responseTemplate = array(
        'filterId' => 0,
        'accountId' => 0,
        'type' => '',
        'operand' => '',
        'inboxes' => array(),

        'hasError' => false,
        'errorCode' => 0,
        'errorMessage' => '',
        'makeNoFurtherRequests' => false
    );

    /**
     * DAO object for loading filter info
     * @var MoodletxtInboundFilterDAO
     */
    private $filterDAO;
    
    /**
     * Sets up the handler ready to suck on some JSON
     * @version 2011071101
     * @since 2011071101
     */
    public function __construct() {
        $this->filterDAO = new MoodletxtInboundFilterDAO();
    }

    /**
     * Receive JSON and decide what to do based on mode switch
     * @param string $json JSON to parse
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2013061701
     * @since 2011071101
     */
    public function processJSON($json) {
        
        $decoded = json_decode($json);
        
        // Check that JSON is valid
        if (! is_object($decoded) || ! isset($decoded->mode))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidjson', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_JSON,
                null, false);
        
        $response = '';
        
        // What do we need to do with this?
        switch($decoded->mode) {

            case 'getFilterDetails':
                $response = $this->getFilterDetails(
                    clean_param($decoded->filterId, PARAM_INT));
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
     * Gets full details of an inbound filter
     * @param int $filterId ID of filter to fetch
     * @return string JSON response
     * @version 2011071101
     * @since 2011071101
     */
    private function getFilterDetails($filterId) {

        $inboundFilter = $this->filterDAO->getFilterById($filterId);
        
        // Account must exist within system
        if (!is_object($inboundFilter))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidaccountid', 'block_moodletxt'), 
                MoodletxtAJAXException::$ERROR_CODE_BAD_ACCOUNT_ID,
                null, false);

        return $this->buildResponse($inboundFilter);
        
    }
    
    
    /**
     * Build JSON response structure for a filter
     * @param MoodletxtInboundFilter $filter Filter to build from
     * @return string Constructed JSON
     * @version 2012042301
     * @since 2011071101
     */
    private function buildResponse(MoodletxtInboundFilter $filter) {
        
        // Copy template down
        $response = $this->responseTemplate;

        $response['filterId'] = $filter->getId();
        $response['accountId'] = $filter->getAccountId();
        $response['type'] = $filter->getFilterType();
        $response['operand'] = $filter->getOperand();
        
        foreach($filter->getDestinationUsers() as $biteSizedUser) {

            $response['users'][$biteSizedUser->getId()] = array(
                'userId' => $biteSizedUser->getId(),
                'firstName' => $biteSizedUser->getFirstName(),
                'lastName' => $biteSizedUser->getLastName(),
                'username' => $biteSizedUser->getUsername()
            );
            
        }
        
        return json_encode($response);
        
    }
    
}

?>