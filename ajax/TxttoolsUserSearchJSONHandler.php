<?php

/**
 * File container for TxttoolsUserSearchJSONHandler class
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
 * @version 2013061801
 * @since 2011062001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/ajax/MoodletxtAJAXException.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');

/**
 * JSON handler for various forms of Moodle user search,
 * used in autocompleters and the like
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013061801
 * @since 2011062001
 */
class TxttoolsUserSearchJSONHandler {

    /**
     * Default values for response
     * @var array(string => mixed)
     */
    private $responseTemplate = array(
        'users' => array(),

        'hasError' => false,
        'errorCode' => 0,
        'errorMessage' => '',
        'makeNoFurtherRequests' => false
    );
    
    
    /**
     * Data access object for querying Moodle users
     * @var TxttoolsAccountDAO
     */
    private $dao;
    
    /**
     * Sets up the handler ready to suck on some JSON
     * @version 2011071901
     * @since 2011062001
     */
    public function __construct() {
        $this->dao = new MoodletxtMoodleUserDAO();
    }

    /**
     * Receive JSON and decide what to do based on mode switch
     * @param string $json JSON to parse
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2013061801
     * @since 2011062001
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

            case 'searchByKeyword':
                $response = $this->searchByKeyword(
                    strip_tags(clean_param($decoded->operand, PARAM_TEXT)));
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
     * Returns any Moodle users that match a given keyword
     * @param string $keyword Keyword to search for
     * @return string JSON response
     * @version 2011062701
     * @since 2011062301
     */
    private function searchByKeyword($keyword) {
        
        $foundUsers = $this->dao->searchUsersByNameAndUsername($keyword);
        return $this->buildResponse($foundUsers);
        
    }

    /**
     * Build JSON response structure for an updated account
     * @param MoodletxtBiteSizedUser[] $moodleUsers List of users to return
     * @return string Constructed JSON
     * @version 2012052301
     * @since 2011062001
     */
    private function buildResponse($moodleUsers) {
        
        // Copy template down
        $response = $this->responseTemplate;
        
        foreach($moodleUsers as $moodleUser) {
            
            $response['users'][$moodleUser->getId()] = array(
                'firstName' => $moodleUser->getFirstName(),
                'lastName' => $moodleUser->getLastName(),
                'username' => $moodleUser->getUsername()
            );
            
        }
                
        return json_encode($response);
        
    }
    
}

?>