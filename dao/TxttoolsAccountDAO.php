<?php

/**
 * File container for TxttoolsAccountDAO class
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
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012110501
 * @since 2011042601
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtInboundFilterDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/TxttoolsAccount.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtBiteSizedUser.php');

/**
 * Database access controller for txttools account details
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012110501
 * @since 2011042601
 */
class TxttoolsAccountDAO {
    
    /**
     * DAO object for fetching and saving filter info
     * @var MoodletxtInboundFilterDAO
     */
    private $filterDAO;

    /**
     * Sets up DAO object with necessary dependencies
     * @version 2011070801
     * @since 2011070801
     */
    public function __construct() {
        $this->filterDAO = new MoodletxtInboundFilterDAO();
    }
    
    /**
     * Loads access restrictions onto the account object
     * @global moodle_database $DB Moodle database controller 
     * @param TxttoolsAccount $txttoolsAccount Account to fill
     * @return TxttoolsAccount Modified account object
     * @version 2012042301
     * @since 2011061701
     */
    private function getAllowedUsersForAccount(TxttoolsAccount $txttoolsAccount) {
        
        global $DB;
        
        $sql = 
        'SELECT u.id, u.username, u.firstname, u.lastname FROM {user} u
        INNER JOIN {block_moodletxt_restrict} l
            ON u.id = l.moodleuser
        INNER JOIN {block_moodletxt_accounts} a
            ON l.txttoolsaccount = a.id
        WHERE a.id = :accountid';

        $allowedUsers = $DB->get_records_sql($sql, array('accountid' => $txttoolsAccount->getId()));

        foreach($allowedUsers as $allowedUser) {
            $txttoolsAccount->addAllowedUser(new MoodletxtBiteSizedUser(
                $allowedUser->id, $allowedUser->username,
                $allowedUser->firstname, $allowedUser->lastname
            ));
        }
        
        return $txttoolsAccount;
        
    }
    
    /**
     * Returns all ConnectTxt accounts that the given user has access to
     * @global moodle_database $DB Moodle database controller 
     * @param int $userId The ID of the user to check access for
     * @param boolean $restrictOutbound Whether to check outbound restrictions
     * @param boolean $restrictInbound Whether to check inbound restrictions
     * @return TxttoolsAccount[] All accounts found
     * @version 2012110501
     * @since 2012072201
     */
    public function getAccessibleAccountsForUserId($userId, $restrictOutbound = true, $restrictInbound = true) {
        
        global $DB;

        $restrictions = '';
        
        if ($restrictOutbound)
            $restrictions .= ' AND accounts.outboundenabled = 1';
        
        if ($restrictInbound)
            $restrictions .= ' AND accounts.inboundenabled = 1';
                
        // Oracle doesn't like aliasing these select-from-union
        // queries, which is made more annoying by it *requiring*
        // select-from-union so that the results can be sorted,
        // as Derby uses sorting to perform a union in the first place.
        // Hurt your head yet?
        $queryAlias = ($DB->get_dbfamily() != 'oracle') ? ' AS icanseeyou' : '';
        
        $sql = 'SELECT * FROM (
            
                    SELECT accounts.*, usertable.firstname, usertable.lastname, 
                    usertable.username AS defaultusername
                    FROM {block_moodletxt_accounts} accounts
                    INNER JOIN {user} usertable
                        ON accounts.defaultuser = usertable.id
                    INNER JOIN {block_moodletxt_restrict} restrictions
                        ON accounts.id = restrictions.txttoolsaccount
                    WHERE restrictions.moodleuser = :userid

                    ' . $restrictions . '

                    UNION

                    SELECT accounts.*, usertable.firstname, usertable.lastname,
                    usertable.username AS defaultusername
                    FROM {block_moodletxt_accounts} accounts
                    INNER JOIN {user} usertable
                        on accounts.defaultuser = usertable.id
                    LEFT JOIN {block_moodletxt_restrict} restrictions
                        ON accounts.id = restrictions.txttoolsaccount
                    WHERE restrictions.moodleuser IS NULL '

                    . $restrictions . '
                
                )' . $queryAlias . ' ORDER BY username ASC';
        
        $returnArray = array();
        $rawRecords = $DB->get_records_sql($sql, array('userid' => $userId));
        
        foreach($rawRecords as $rawRecord) {
            $txttoolsAccount = $this->convertStandardClassToBean($rawRecord);                    
            $returnArray[$txttoolsAccount->getId()] = $txttoolsAccount;
        }
        
        return $returnArray;
        
    }
    
    /**
     * Fetches a txttools account by DB record ID
     * @global moodle_database $DB Moodle database controller 
     * @param int $accountId DB record ID
     * @param boolean $includeRestrictions Turn this off if restrictions aren't needed to speed up fetch
     * @param boolean $includeFilters Turn this on if filters are needed - slows down fetch
     * @return TxttoolsAccount txttools account
     * @version 2012042301
     * @since 2011042601
     */
    public function getTxttoolsAccountById($accountId, $includeRestrictions = true, $includeFilters = false) {

        global $DB;
        
        $sql = 'SELECT accounts.*, usertable.firstname, usertable.lastname, 
                usertable.username AS defaultusername
                FROM {block_moodletxt_accounts} accounts
                INNER JOIN {user} usertable
                    ON accounts.defaultuser = usertable.id
                WHERE accounts.id = :accountid';
        
        $txttoolsAccount = $this->convertStandardClassToBean(
            $DB->get_record_sql($sql, array('accountid' => $accountId))
        );
        
        if ($includeRestrictions)
            $txttoolsAccount = $this->getAllowedUsersForAccount($txttoolsAccount);

        if ($includeFilters)
            $txttoolsAccount = $this->filterDAO->getFiltersForAccount($txttoolsAccount);
                    
        return $txttoolsAccount;
        
    }
    
    /**
     * Fetches a txttools account by username
     * @global moodle_database $DB Moodle database controller 
     * @param string $txttoolsUsername Username of account
     * @param boolean $includeRestrictions Turn this off if restrictions aren't needed to speed up fetch
     * @param boolean $includeFilters Turn this on if filters are needed - slows down fetch
     * @return TxttoolsAccount txttools account
     * @version 2012042301
     * @since 2011042601
     */
    public function getTxttoolsAccountByUsername($txttoolsUsername, $includeRestrictions = true, $includeFilters = false) {

        global $DB;

        $sql = 'SELECT accounts.*, usertable.firstname, usertable.lastname, 
                usertable.username AS defaultusername
                FROM {block_moodletxt_accounts} accounts
                INNER JOIN {user} usertable
                    ON accounts.defaultuser = usertable.id
                WHERE accounts.username = :username';
        
        $txttoolsAccount = $this->convertStandardClassToBean(
            $DB->get_record_sql($sql, array('username' => $txttoolsUsername))
        );
        
        if ($includeRestrictions)
            $txttoolsAccount = $this->getAllowedUsersForAccount($txttoolsAccount);

        if ($includeFilters)
            $txttoolsAccount = $this->filterDAO->getFiltersForAccount($txttoolsAccount);
                    
        return $txttoolsAccount;

    }

    /**
     * Gets all txttools accounts stored within moodletxt
     * @global type $DB Moodle database manager
     * @param boolean $includeRestrictions Turn this off if restrictions aren't needed to speed up fetch
     * @param boolean $includeFilters Turn this off if filters aren't needed to speed up fetch
     * @return TxttoolsAccount[] All accounts found
     * @version 2012073101
     * @since 2011060801
     */
    public function getAllTxttoolsAccounts($includeRestrictions = true, $includeFilters = true, 
            $checkActiveOutbound = false, $checkActiveInbound = false) {
        
        global $DB;
        
        $params = array();
        
        $sql = 'SELECT accounts.*, usertable.firstname, usertable.lastname, 
                usertable.username AS defaultusername
                FROM {block_moodletxt_accounts} accounts
                INNER JOIN {user} usertable
                    ON accounts.defaultuser = usertable.id';
        
        if ($checkActiveOutbound) {
            $sql .= (strpos($sql, 'WHERE') > 0) ? ' AND' : ' WHERE';
            $sql .= ' accounts.outboundenabled = 1';
        }
        
        if ($checkActiveInbound) {
            $sql .= (strpos($sql, 'WHERE') > 0) ? ' AND' : ' WHERE';
            $sql .= ' accounts.inboundenabled = 1';
        }
        
        $sql .= ' ORDER BY accounts.username ASC';
        
        $returnArray = array();
        $rawRecords = $DB->get_records_sql($sql, $params);
        
        foreach($rawRecords as $rawRecord) {
            $txttoolsAccount = $this->convertStandardClassToBean($rawRecord);
            
            if ($includeRestrictions)
                $txttoolsAccount = $this->getAllowedUsersForAccount($txttoolsAccount);
            
            if ($includeFilters)
                $txttoolsAccount = $this->filterDAO->getFiltersForAccount($txttoolsAccount);
                    
            $returnArray[$txttoolsAccount->getId()] = $txttoolsAccount;
        }
        
        return $returnArray;
    }

    /**
     * Saves a txttools account to the database
     * @global moodle_database $DB Moodle database controller 
     * @param TxttoolsAccount $txttoolsAccount txttools account
     * @param boolean $updateRestrictions Whether included restrictions need saving
     * @version 2012042301
     * @since 2011042601
     */
    public function saveTxttoolsAccount(TxttoolsAccount $txttoolsAccount, $updateRestrictions = false) {

        global $DB;

        $insertClass = new object();
        $action = 'insert';

        // Check for existing account
        if ($txttoolsAccount->getId() > 0) {

            // Account already exists - update DB values with new ones
            $insertClass = $this->convertBeanToStandardClass(
                    $txttoolsAccount,
                    $DB->get_record('block_moodletxt_accounts', array('id' => $txttoolsAccount->getId()))
            );

            $action = 'update';

        } else {
            $insertClass = $this->convertBeanToStandardClass($txttoolsAccount);
        }

        // Do database update/insert
        if ($action == 'update')
            $DB->update_record('block_moodletxt_accounts', $insertClass);
        else
            $DB->insert_record('block_moodletxt_accounts', $insertClass);
        
        if ($updateRestrictions) {
        
            // Clear existing allowed users and set new ones
            $DB->delete_records('block_moodletxt_restrict', array('txttoolsaccount' => $txttoolsAccount->getId()));

            foreach($txttoolsAccount->getAllowedUsers() as $allowedUser) {
                $insertClass = new object();
                $insertClass->moodleuser = $allowedUser->getId();
                $insertClass->txttoolsaccount = $txttoolsAccount->getId();
                $DB->insert_record('block_moodletxt_restrict', $insertClass);
            }
            
        }

    }

    /**
     * Returns a count of the number of txttools accounts
     * stored within moodletxt
     * @global moodle_database $DB Moodle database controller 
     * @return int Number of accounts stored
     * @version 2012052301
     * @since 2011042601
     */
    public function countTxttoolsRecords() {
        global $DB;

        return $DB->count_records('block_moodletxt_accounts');
    }
    
    /**
     * Checks whether a given account ID is valid
     * @global moodle_database $DB Moodle database controller 
     * @param int $accountId ID to check
     * @return boolean True if account exists
     * @version 2012052301
     * @since 20111071201
     */
    public function accountIdExists($accountId) {
        global $DB;
        
        return ($DB->count_records('block_moodletxt_accounts', array('id' => $accountId)) > 0);
    }
    
    /**
     * Checks whether a given account username already exists within the DB
     * @global moodle_database $DB Moodle database controller 
     * @param string $accountName Txttools account username
     * @return boolean True if account exists
     * @version 2012060101
     * @since 2012060101
     */
    public function accountNameExists($accountName) {
        global $DB;
        
        return ($DB->count_records('block_moodletxt_accounts', array('username' => $accountName)) > 0);
    }
    
    /**
     * Converts a fully fledged data bean into a basic
     * object for the Moodle DB layer
     * @param TxttoolsAccount $txttoolsAccount Account to convert
     * @param object $existingRecord Existing DB record
     * @return object Converted object
     * @version 2012042301
     * @since 2011042601
     */
    private function convertBeanToStandardClass(TxttoolsAccount $txttoolsAccount, $existingRecord = null) {

        if ($existingRecord == null)
            $existingRecord = new object();

        // Map fields in
        $existingRecord->username          = $txttoolsAccount->getUsername();
        $existingRecord->password          = $txttoolsAccount->getEncryptedPassword();
        $existingRecord->description       = $txttoolsAccount->getDescription();
        $existingRecord->defaultuser       = $txttoolsAccount->getDefaultUser()->getId();
        $existingRecord->creditsused       = $txttoolsAccount->getCreditsUsed();
        $existingRecord->creditsremaining  = $txttoolsAccount->getCreditsRemaining();
        $existingRecord->outboundenabled   = ($txttoolsAccount->isOutboundEnabled()) ? 1 : 0;
        $existingRecord->inboundenabled    = ($txttoolsAccount->isInboundEnabled()) ? 1 : 0;
        $existingRecord->accounttype       = $txttoolsAccount->getBillingType();
        $existingRecord->lastupdate        = $txttoolsAccount->getLastUpdate();

        return $existingRecord;
        
    }

    /**
     * Converts a basic object returned from the
     * Moodle DB layer into a full data type
     * @param object $standardClass DB record object
     * @return txttoolsAccount Converted acount
     * @version 2012100801
     * @since 2011042601
     */
    private function convertStandardClassToBean($standardClass) {

        $defaultUser = new MoodletxtBiteSizedUser(
                $standardClass->defaultuser, 
                $standardClass->defaultusername, 
                $standardClass->firstname, 
                $standardClass->lastname
        );
        
        $txttoolsAccount = new TxttoolsAccount(
                $standardClass->username,
                $standardClass->description,
                $defaultUser,
                $standardClass->creditsused,
                $standardClass->creditsremaining,
                $standardClass->outboundenabled,
                $standardClass->inboundenabled,
                $standardClass->accounttype,
                $standardClass->lastupdate,
                $standardClass->id
        );
        $txttoolsAccount->setEncryptedPassword($standardClass->password);

        return $txttoolsAccount;

    }
    
}

?>