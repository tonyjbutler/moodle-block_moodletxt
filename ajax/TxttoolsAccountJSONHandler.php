<?php

/**
 * File container for TxttoolsAccountJSONHandler class
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
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012042301
 * @since 2011061201
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
 * @version 2012042301
 * @since 2011061201
 */
class TxttoolsAccountJSONHandler {

    /**
     * Represents outbound connections from an account
     * when updating its settings
     * @var string
     */
    public static $DIRECTION_IDENTIFIER_OUTBOUND = 'outbound';
    
    /**
     * Represents inbound connections from an account
     * when updating its settings
     * @var string
     */
    public static $DIRECTION_IDENTIFIER_INBOUND  = 'inbound';
    
    /**
     * Default values for response
     * @var array(string => mixed)
     */
    private $responseTemplate = array(
        'accountID' => 0,
        'creditsUsed' => 0,
        'creditsRemaining' => 0,
        'updateTimeString' => '',
        'allowOutbound' => false,
        'allowInbound' => false,
        'billingType' => 0,
        'allowedUsers' => array(),
        'inboundFilters' => array(),

        'hasError' => false,
        'errorCode' => 0,
        'errorMessage' => '',
        'makeNoFurtherRequests' => false
    );
    
    /**
     * Outbound connector for updating accounts from txttools
     * @var MoodletxtOutboundController
     */
    private $connector;
    
    /**
     * Data access object for loading/saving txttools accounts
     * @var TxttoolsAccountDAO
     */
    private $accountDAO;
    
    /**
     * Data access object for loading Moodle user data
     * @var MoodletxtMoodleUserDAO
     */
    private $userDAO;
    
    /**
     * Sets up the handler ready to suck on some JSON
     * @version 2011071901
     * @since 2011061301
     */
    public function __construct() {
        $this->accountDAO = new TxttoolsAccountDAO();
        $this->userDAO = new MoodletxtMoodleUserDAO();
        $this->connector = MoodletxtOutboundControllerFactory::getOutboundController(
            MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML
        );
    }

    /**
     * Receive JSON and decide what to do based on mode switch
     * @param string $json JSON to parse
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2011062701
     * @since 2011061301
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

            case 'getAccountDetails':
                $response = $this->getAccountDetails((int) $decoded->accountId);
                break;
            
            case 'updateAccountRestrictions':
                $response = $this->updateAccountRestrictions($decoded);
                break;
            
            case 'updateAccountFromTxttools':
                $response = $this->updateAccountFromTxttools((int) $decoded->accountId);
                break;
            
            case 'setInboundAccess':
                $response = $this->setAccountAccess(
                    (int) $decoded->accountId, 
                    self::$DIRECTION_IDENTIFIER_INBOUND,
                    $decoded->allowInbound);
                break;
            
            case 'setOutboundAccess':
                $response = $this->setAccountAccess(
                    (int) $decoded->accountId,
                    self::$DIRECTION_IDENTIFIER_OUTBOUND,
                    $decoded->allowOutbound);
                break;
            
            case 'toggleOutboundAccess':
                $response = $this->toggleAccountAccess(
                    (int) $decoded->accountId, 
                    self::$DIRECTION_IDENTIFIER_OUTBOUND);
                break;
            
            case 'toggleInboundAccess':
                $response = $this->toggleAccountAccess(
                    (int) $decoded->accountId,
                    self::$DIRECTION_IDENTIFIER_INBOUND);
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
     * Gets full details of a txttools account
     * @param int $accountId ID of account to fetch
     * @return string JSON response
     * @version 2011071101
     * @since 2011062301
     */
    private function getAccountDetails($accountId) {
        
        $txttoolsAccount = $this->accountDAO->getTxttoolsAccountById($accountId, true, true);
        
        // Account must exist within system
        if (!is_object($txttoolsAccount))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidaccountid', 'block_moodletxt'), 
                MoodletxtAJAXException::$ERROR_CODE_BAD_ACCOUNT_ID,
                null, false);

        return $this->buildResponse($txttoolsAccount);
        
    }
    
    /**
     * Updates a single account's details from txttools
     * @param int $accountId ID of account to check
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2011062701
     * @since 2011061301
     */
    private function updateAccountFromTxttools($accountId) {
        
        $txttoolsAccount = $this->accountDAO->getTxttoolsAccountById($accountId);
        
        // Account must exist within system
        if (!is_object($txttoolsAccount))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidaccountid', 'block_moodletxt'), 
                MoodletxtAJAXException::$ERROR_CODE_BAD_ACCOUNT_ID,
                null, false);
        
        // Update from txttools server
        try {
            $this->connector->updateAccountInfo($txttoolsAccount);
            $this->accountDAO->saveTxttoolsAccount($txttoolsAccount);
            return $this->buildResponse($txttoolsAccount);
            
        } catch (MoodletxtRemoteProcessingException $ex) {
            throw new MoodletxtAJAXException(
                get_string('errorconn' . $ex->getCode(), 'block_moodletxt'), 
                $ex->getCode(),
                null, true);
            
        } catch (Exception $ex) {
            throw new MoodletxtAJAXException(
                get_string('errorconndefault', 'block_moodletxt'), 
                $ex->getCode(),
                null, true);
        }
    }
    
    /**
     * Updates an acount's list of allowed users
     * @param object $decodedJson Parsed JSON
     * @return string JSON response
     * @version 2011062301
     * @since 2011062301
     */
    private function updateAccountRestrictions($decodedJson) {
        
        $txttoolsAccount = $this->accountDAO->getTxttoolsAccountById((int) $decodedJson->accountId);
        
        // Account must exist within system
        if (!is_object($txttoolsAccount))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidaccountid', 'block_moodletxt'), 
                MoodletxtAJAXException::$ERROR_CODE_BAD_ACCOUNT_ID,
                null, false);
        
        // Easiest way of doing this, rather than checking
        // which links already exist and modifying, is to clear
        // all existing links and save the given set
        $txttoolsAccount->clearAllowedUsers();
        
        if (is_array($decodedJson->allowedUsers)) {
            foreach ($decodedJson->allowedUsers as $allowedUser) {
                try {
                    $userObj = $this->userDAO->getUserById($allowedUser);
                    $txttoolsAccount->addAllowedUser($userObj);
                } catch (InvalidArgumentException $ex) {
                    // Bad user ID - ignore and continue
                    continue;
                }
            }
        }
        
        $this->accountDAO->saveTxttoolsAccount($txttoolsAccount);
        return $this->buildResponse($txttoolsAccount);
        
    }

    /**
     * Globally allow/deny access to an account, either in or outbound
     * @param int $accountId ID of account to modify
     * @param string $direction Specify inbound/outbound access to update
     * @param boolean $allow Whether to allow access
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2011062701
     * @since 2011061301
     */
    private function setAccountAccess($accountId, $direction = 'outbound', $allow = false) {
        
        $txttoolsAccount = $this->accountDAO->getTxttoolsAccountById($accountId);
        
        // Account must exist within system
        if (!is_object($txttoolsAccount))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidaccountid', 'block_moodletxt'), 
                MoodletxtAJAXException::$ERROR_CODE_BAD_ACCOUNT_ID,
                null, false);

        // Update account settings within DB
        if ($direction == self::$DIRECTION_IDENTIFIER_OUTBOUND)
            $txttoolsAccount->setOutboundEnabled($allow);
        
        else if ($direction == self::$DIRECTION_IDENTIFIER_INBOUND)
            $txttoolsAccount->setInboundEnabled($allow);
        
        $this->accountDAO->saveTxttoolsAccount($txttoolsAccount);
        return $this->buildResponse($txttoolsAccount);
        
    }

    /**
     * Globally toggle access to an account, either in or outbound
     * @param int $accountId ID of account to modify
     * @param string $direction Specify inbound/outbound access to update
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2011062701
     * @since 2011061301
     */
    private function toggleAccountAccess($accountId, $direction = 'outbound') {
        
        $txttoolsAccount = $this->accountDAO->getTxttoolsAccountById($accountId);
        
        // Account must exist within system
        if (!is_object($txttoolsAccount))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidaccountid', 'block_moodletxt'), 
                MoodletxtAJAXException::$ERROR_CODE_BAD_ACCOUNT_ID,
                null, false);

        // Update account settings within DB
        if ($direction == self::$DIRECTION_IDENTIFIER_OUTBOUND)
            $txttoolsAccount->setOutboundEnabled(! $txttoolsAccount->isOutboundEnabled());
        
        else if ($direction == self::$DIRECTION_IDENTIFIER_INBOUND)
            $txttoolsAccount->setInboundEnabled(! $txttoolsAccount->isInboundEnabled());
        
        $this->accountDAO->saveTxttoolsAccount($txttoolsAccount);
        return $this->buildResponse($txttoolsAccount);
        
    }
    
    /**
     * Build JSON response structure for an updated account
     * @param TxttoolsAccount $txttoolsAccount Account to build from
     * @return string Constructed JSON
     * @version 2012042301
     * @since 2011061301
     */
    private function buildResponse(TxttoolsAccount $txttoolsAccount) {
        
        // Copy template down
        $response = $this->responseTemplate;
        
        $response['accountID'] = $txttoolsAccount->getId();
        $response['creditsUsed'] = $txttoolsAccount->getCreditsUsed();
        $response['creditsRemaining'] = $txttoolsAccount->getCreditsRemaining();
        $response['updateTimeString'] = $txttoolsAccount->getLastUpdateFormatted();
        $response['allowOutbound'] = ($txttoolsAccount->isOutboundEnabled()) ? 1 : 0;
        $response['allowInbound'] = ($txttoolsAccount->isInboundEnabled()) ? 1 : 0;
        $response['billingType'] = $txttoolsAccount->getBillingType();

        // Include account restrictions if specified
        foreach($txttoolsAccount->getAllowedUsers() as $allowedUser) {
            
            $response['allowedUsers'][$allowedUser->getId()] = array(
                'firstName' => $allowedUser->getFirstName(),
                'lastName' => $allowedUser->getLastName(),
                'username' => $allowedUser->getUsername()
            );
            
        }
        
        // Include inbound filters if specified
        foreach($txttoolsAccount->getInboundFilters() as $inboundFilter) {
            
            $response['inboundFilters'][$inboundFilter->getId()] = array(
                'type' => $inboundFilter->getFilterType(),
                'operand' => (string) $inboundFilter->getOperand(),
                'inboxes' => array()
            );
            
            foreach($inboundFilter->getDestinationUsers() as $biteSizedUser) {
                
                // No I'm not kidding. Fortunately these won't be included often
                $response['inboundFilters'][$inboundFilter->getId()]['users'][$biteSizedUser->getId()] = array(
                    'firstName' => $biteSizedUser->getFirstName(),
                    'lastName' => $biteSizedUser->getLastName(),
                    'username' => $biteSizedUser->getUsername()
                );
            }
            
        }
        
        return json_encode($response);
        
    }
    
}

?>