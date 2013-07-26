<?php

/**
 * File container for the MoodletxtCronHandler class
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
 * @see MoodletxtCronHandler
 * @package uk.co.moodletxt.events
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012041801
 * @since 2012041701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');
require_once($CFG->dirroot . '/blocks/moodletxt/inbound/MoodletxtInboundFilterManager.php');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsSentMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsReceivedMessageDAO.php');

/**
 * Performs maintenance functions for moodletxt.
 * @package uk.co.moodletxt.events
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012041801
 * @since 2012041701
 */
class MoodletxtCronHandler {

    /**
     * DAO for getting txttools account info
     * @var TxttoolsAccountDAO
     */
    private $txttoolsAccountDAO;
    
    /**
     * DAO for getting sent messages to update
     * @var TxttoolsSentMessageDAO
     */
    private $sentMessageDAO;
    
    /**
     * DAO for saving received messages
     * @var TxttoolsReceivedMessageDAO 
     */
    private $receivedMessageDAO;
    
    /**
     * XML controller for connections to txttools
     * @var MoodletxtOutboundController
     */
    private $outboundController;

    /**
     * Takes care of filtering inbound messages
     * @var MoodletxtInboundFilterManager
     */
    private $filterManager;

    /**
     * Constructor sets up required objects
     * for processing
     * @version 2012041801
     * @since 2012041701
     */
    public function __construct() {

        $this->setOutboundController(
            MoodletxtOutboundControllerFactory::getOutboundController(
                MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML
            )
        );
        
        $this->setFilterManager(new MoodletxtInboundFilterManager());
        $this->setSentMessageDAO(new TxttoolsSentMessageDAO());
        $this->setReceivedMessageDAO(new TxttoolsReceivedMessageDAO());
        $this->setTxttoolsAccountDAO(new TxttoolsAccountDAO());
        
    }

    /**
     * Runs the cron tasks required for
     * system mtainenance
     * @return boolean Success
     * @version 2010070712
     * @since 2010070712
     */
    public function doCron() {

        // You do cron cron cron, you do cron cron...

        $this->getStatusUpdates();
        $this->getInboundMessages();
        $this->updateAccountDetails();

        return true;

    }

    /**
     * Updates status info within the system for any sent
     * messages that have not reached a final status
     * @version 2012041701
     * @since 2012041701
     */
    private function getStatusUpdates() {

        // Get txttools account links
        $outboundAccounts = $this->getTxttoolsAccountDAO()->getAllTxttoolsAccounts();

        foreach($outboundAccounts as $account) {

            $messagesNeedingUpdate = $this->getSentMessageDAO()->getAllNonFinalisedSMSMessagesForAccount($account);
            
            if (count($messagesNeedingUpdate) > 0) {
                $updatedMessages = $this->getOutboundController()->getSMSStatusUpdates($messagesNeedingUpdate, $account);
                $this->getSentMessageDAO()->saveMessagesSentViaSMS($updatedMessages);
            }
            
        }

    }

    /**
     * Retrieves any new inbound messages from
     * the txttools server and filters them
     * @version 2012041701
     * @since 2012041701
     */
    private function getInboundMessages() {

        // Get txttools account links - only ones enabled for inbound
        $inboundAccounts = $this->getTxttoolsAccountDAO()->getAllTxttoolsAccounts(false, false, false, true);

        $inboundMessages = $this->getOutboundController()->getInboundMessages($inboundAccounts);

        if (count($inboundMessages) > 0) {
            
            $inboundMessages = $this->getFilterManager()->filterMessages($inboundMessages);
         
            $this->getReceivedMessageDAO()->saveInboundMessages($inboundMessages);

        }
        
    }

    /**
     * Retrieves updated account credit information
     * for all txttools accounts stored in the system
     * @version 2012041801
     * @since 2012041801
     */
    private function updateAccountDetails() {

        $txttoolsAccounts = $this->getTxttoolsAccountDAO()->getAllTxttoolsAccounts();
        
        foreach($txttoolsAccounts as $account) {

            $account = $this->getOutboundController()->updateAccountInfo($account);
            $this->getTxttoolsAccountDAO()->saveTxttoolsAccount($account);

        }

    }
    
    /**
     * Returns the outbound controller used by this cron handler to 
     * communicate with the messaging platform
     * @return MoodletxtOutboundController Outbound controller object
     * @version 2012041701
     * @since 2012041701
     */
    public function getOutboundController() {
        return $this->outboundController;
    }

    /**
     * Sets the outbound controller this cron handler will use
     * to communicate with the messaging platform
     * @param MoodletxtOutboundController $outboundController Controller for communication
     * @version 2012041701
     * @since 2012041701
     */
    public function setOutboundController(MoodletxtOutboundController $outboundController) {
        $this->outboundController = $outboundController;
    }

    /**
     * Returns the filter manager this cron handler should use to
     * filter inbound messages to their correct destination inboxes
     * @return MoodletxtInboundFilterManager Filter manager
     * @version 2012041701
     * @since 2012041701
     */
    public function getFilterManager() {
        return $this->filterManager;
    }

    /**
     * Sets the filter manager that this cron handler should use to filter
     * incoming messages to their respective inboxes
     * @param MoodletxtInboundFilterManager $filterManager Filter manager
     * @version 2012041701
     * @since 2012041701
     */
    public function setFilterManager(MoodletxtInboundFilterManager $filterManager) {
        $this->filterManager = $filterManager;
    }

    /**
     * Returns a DAO object for accessing ConnectTxt accounts within the database
     * @return TxttoolsAccountDAO ConnectTxt account DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function getTxttoolsAccountDAO() {
        return $this->txttoolsAccountDAO;
    }

    /**
     * Sets the DAO object this cron handler should use to retrieve ConnectTxt
     * account details from the database
     * @param TxttoolsAccountDAO $txttoolsAccountDAO ConnectTxt account DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function setTxttoolsAccountDAO(TxttoolsAccountDAO $txttoolsAccountDAO) {
        $this->txttoolsAccountDAO = $txttoolsAccountDAO;
    }
    
    /**
     * Returns the DAO object this cron handler is using to fetch and save
     * sent messages from/to the database
     * @return TxttoolsSentMessageDAO Sent message DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function getSentMessageDAO() {
        return $this->sentMessageDAO;
    }

    /**
     * Sets the DAO object this cron handler should use to communicate with
     * the database for retrieving and saving sent messages/status updates
     * @param TxttoolsSentMessageDAO $sentMessageDAO 
     * @version 2012041701
     * @since 2012041701
     */
    public function setSentMessageDAO(TxttoolsSentMessageDAO $sentMessageDAO) {
        $this->sentMessageDAO = $sentMessageDAO;
    }
    
    /**
     * Returns the DAO object this cron handler is using to communicate
     * with the database to retrieve and save inbound messages
     * @return TxttoolsReceivedMessageDAO Inbound message DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function getReceivedMessageDAO() {
        return $this->receivedMessageDAO;
    }

    /**
     * Sets the DAO object this cron handler should use to communicate with the 
     * database for retrieving and saving inbound messages
     * @param TxttoolsReceivedMessageDAO $receivedMessageDAO Inbound message DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function setReceivedMessageDAO(TxttoolsReceivedMessageDAO $receivedMessageDAO) {
        $this->receivedMessageDAO = $receivedMessageDAO;
    }

}

?>