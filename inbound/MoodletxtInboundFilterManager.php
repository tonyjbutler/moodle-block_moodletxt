<?php

/**
 * File container for MoodletxtInboundFilterManager class
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
 * @see MoodletxtInboundFilterManager
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012082901
 * @since 2012041701
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtInboundFilterDAO.php');

/**
 * Class designed to abstract the inbound filtering process into a single code set.
 * Pulls filters from the database and matches them against message objects handed to it.
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012082901
 * @since 2012041701
 */
class MoodletxtInboundFilterManager {

    /**
     * String reference for default inbox routing
     * @var string
     */
    public static $FILTER_TYPE_DEFAULT = "defaultInbox";

    /**
     * DAO object for grabbing ConnectTxt account at startup (with filters)
     * @ver TxttoolsAccountDAO 
     */
    private $txttoolsAccountDAO;
    
    /**
     * DAO object for grabbing filters at startup
     * @var MoodletxtInboundFilterDAO
     */
    private $inboundFilterDAO;
    
    /**
     * Holds filters indexed by account ID and username (via internal reference)
     *
     * Indexed like-a so:
     *
     * Account username
     *     ↓
     * Account ID
     *     ↓
     *     → Default Inbox
     *     ↓
     *     → Keywords → Filters → Destination Folders
     *     ↓
     *     → Source Phone Numbers → Filters → Destination Folders
     */
    private $filterSet = array();
    
    /**
     * Initialises the filter manager
     * @version 2012041701
     * @since 2012041701
     */
    public function __construct() {

        $this->setTxttoolsAccountDAO(new TxttoolsAccountDAO());
        $this->setInboundFilterDAO(new MoodletxtInboundFilterDAO());
        $this->setupFilters();
        
    }

    /**
     * Sets up the inbound filter set ready for use
     * @version 2012082901
     * @since 2012041701
     */
    private function setupFilters() {
        
        // Hello me! This is a quick note to remind you for the billionth time:
        // usernames are compared in lower case and keywords in upper case.
        // Can you please stop mixing those up now? It's your own convention!
        
        $accounts = $this->getTxttoolsAccountDAO()->getAllTxttoolsAccounts(false, true, false, true);

        foreach($accounts as $account) {

            // Set up placeholders in filter set array
            $this->filterSet[$account->getId()] = array();
            $this->filterSet[strtolower($account->getUsername())] = &$this->filterSet[$account->getId()];

            $this->filterSet[$account->getId()][self::$FILTER_TYPE_DEFAULT] = $account->getDefaultUser()->getId();
            $this->filterSet[$account->getId()][MoodletxtInboundFilter::$FILTER_TYPE_KEYWORD] = array();
            $this->filterSet[$account->getId()][MoodletxtInboundFilter::$FILTER_TYPE_PHONE_NUMBER] = array();

            // Iterate over filters and populate arrays
            foreach ($account->getInboundFilters() as $filter) {

                // Initialise array to hold destination users if it does not already exist at this point
                if (! isset($this->filterSet[$account->getId()][$filter->getFilterType()][$filter->getOperand()]))
                    $this->filterSet[$account->getId()][$filter->getFilterType()][strtoupper($filter->getOperand())] = array();

                foreach($filter->getDestinationUsers() as $destinationUser)
                    array_push($this->filterSet[$account->getId()][$filter->getFilterType()][strtoupper($filter->getOperand())], $destinationUser->getId());

            }
            
        }
        
    }

    /**
     * Takes an array of messages and applies inbound filters to them
     * @param MoodletxtInboundMessage[] $messageSet Messages to filter
     * @return MoodletxtInboundMessage[] Filtered message set
     * @version 2012082901
     * @since 2012041701
     */
    public function filterMessages(array $messageSet) {

        // Iterate over messages and filter
        foreach ($messageSet as $message) {

            if (! $message instanceof MoodletxtInboundMessage)
                continue;
            
            $keywordex = explode(' ', trim($message->getMessageText()));
            $keyword = strtoupper($keywordex[0]);  // Keywords are always compared in upper case
            $sourceNumber = $message->getSourceNumber()->getPhoneNumber();
            $message->setDestinationAccountUsername(strtolower($message->getDestinationAccountUsername())); // Usernames are always compared lower case

            // Get ID/username of the txttools account this message came in on
            $accountIdent = ($message->getDestinationAccountId() > 0)
                ? $message->getDestinationAccountId()
                : $message->getDestinationAccountUsername();

            // Do keyword filtering
            if (isset($this->filterSet[$accountIdent][MoodletxtInboundFilter::$FILTER_TYPE_KEYWORD][$keyword]))
                $message->addDestinationUserIds($this->filterSet[$accountIdent][MoodletxtInboundFilter::$FILTER_TYPE_KEYWORD][$keyword]);

            // Do source number filtering
            if (isset($this->filterSet[$accountIdent][MoodletxtInboundFilter::$FILTER_TYPE_PHONE_NUMBER][$sourceNumber]))
                $message->addDestinationUserIds($this->filterSet[$accountIdent][MoodletxtInboundFilter::$FILTER_TYPE_PHONE_NUMBER][$sourceNumber]);

            // If no filters have been matched, send to default inbox
            if ($message->getDestinationUserCount() == 0)
                $message->addDestinationUserId($this->filterSet[$accountIdent][self::$FILTER_TYPE_DEFAULT]);

        }

        return $messageSet;

    }
    
    /**
     * Returns the DAO this filter manager is using to fetch
     * ConnectTxt account information from the database
     * @return TxttoolsAccountDAO ConnectTxt account DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function getTxttoolsAccountDAO() {
        return $this->txttoolsAccountDAO;
    }

    /**
     * Sets the DAO this filter manager should use to fetch 
     * ConnectTxt account information from the database
     * @param TxttoolsAccountDAO $txttoolsAccountDAO ConnectTxt account DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function setTxttoolsAccountDAO(TxttoolsAccountDAO $txttoolsAccountDAO) {
        $this->txttoolsAccountDAO = $txttoolsAccountDAO;
    }

    /**
     * Returns the DAO this filter manager is using to pull filter
     * structure information from the database
     * @return MoodletxtInboundFilterDAO Filter DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function getInboundFilterDAO() {
        return $this->inboundFilterDAO;
    }

    /**
     * Sets the DAO this filter manager should use to fetch
     * filter structure info from the database
     * @param MoodletxtInboundFilterDAO $inboundFilterDAO Filter DAO
     * @version 2012041701
     * @since 2012041701
     */
    public function setInboundFilterDAO(MoodletxtInboundFilterDAO $inboundFilterDAO) {
        $this->inboundFilterDAO = $inboundFilterDAO;
    }

}

?>