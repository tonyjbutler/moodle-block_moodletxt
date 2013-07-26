<?php

/**
 * File container for MoodletxtInboundFilterDAO class
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
 * @version 2012101601
 * @since 2011070401
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtInboundFilter.php');

/**
 * DAO layer object for loading and saving inbound filter records
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012101601
 * @since 2011070401
 */
class MoodletxtInboundFilterDAO {
    
    /**
     * SQL query used to pull back details of active inboxes
     * @var string
     */
    private static $FETCH_INBOXES_SQL = 
        'SELECT usertable.id, usertable.username, usertable.firstname, usertable.lastname
        FROM {user} usertable
        INNER JOIN {block_moodletxt_in_fil} filterlink
            ON filterlink.userid = usertable.id
        INNER JOIN {block_moodletxt_filter} filter
            ON filterlink.filter = filter.id
        WHERE filter.id = :filterid
        ORDER BY usertable.lastname ASC, usertable.firstname ASC';


    /**
     * Returns an individual filter matching the given ID, including
     * any links to Moodle user inboxes
     * @global moodle_database $DB Moodle database controller 
     * @param int $filterId Filter ID
     * @return MoodletxtInboundFilter Assembled filter
     * @version 2012052301
     * @since 2011071101
     */
    public function getFilterById($filterId) {
        
        global $DB;
        
        $filter = null;
        $filterRecord = $DB->get_record('block_moodletxt_filter', array('id' => $filterId));
        
        // If record exists, fetch links to inboxes
        if (is_object($filterRecord)) {

            $filter = new MoodletxtInboundFilter($filterRecord->account, $filterRecord->type, $filterRecord->value, $filterRecord->id);
            $users = $DB->get_records_sql(self::$FETCH_INBOXES_SQL, array('filterid' => $filter->getId()));

            // Add inbox details to bean
            foreach($users as $user)
                $filter->addDestinationUser(new MoodletxtBiteSizedUser($user->id, $user->username, $user->firstname, $user->lastname));

        }
        
        return $filter;
        
    }
    
    /**
     * Returns all filters on a given txttools account
     * @global moodle_database $DB Moodle database controller 
     * @param TxttoolsAccount $txttoolsAccount Account to search against
     * @return TxttoolsAccount Updated account object
     * @version 2012042301
     * @since 2011070401
     */
    public function getFiltersForAccount(TxttoolsAccount $txttoolsAccount) {
        
        global $DB;
        
        $filters = $DB->get_records('block_moodletxt_filter', array('account' => $txttoolsAccount->getId()));
        
        // Iterate over filters and build beans
        foreach($filters as $filter) {
            
            $filterObj = new MoodletxtInboundFilter($filter->account, $filter->type, $filter->value, $filter->id);
            $users = $DB->get_records_sql(self::$FETCH_INBOXES_SQL, array('filterid' => $filterObj->getId()));
            
            // Get links between filters and user inboxes
            foreach($users as $user)
                $filterObj->addDestinationUser(new MoodletxtBiteSizedUser($user->id, $user->username, $user->firstname, $user->lastname));
            
            $txttoolsAccount->addInboundFilter($filterObj);
            
        }
        
        return $txttoolsAccount;
        
    }
    
    /**
     * Check whether a given filter exists
     * @global moodle_database $DB Moodle database controller 
     * @param int $accountId ID of txttools account
     * @param string $filterType Type of filter (see static fields on bean)
     * @param string $filterValue Filter operand
     * @return boolean Whether or not the filter exists
     * @version 2012042301
     * @since 2011071201
     */
    public function filterExists($accountId, $filterType, $filterValue) {
        
        global $DB;
        
        return ($DB->count_records('block_moodletxt_filter', array('account' => $accountId, 'type' => $filterType, 'value' => $filterValue)) > 0);
        
    }
    
    /**
     * Saves a created or modified filter back to the database
     * @global moodle_database $DB Moodle database controller 
     * @param MoodletxtInboundfilter $filter Filter to save
     * @version 2012052301
     * @since 2011071301
     */
    public function saveFilter(MoodletxtInboundfilter $filter) {
        
        global $DB;
        
        // Filters with no users on are useless - nuke them
        if (count($filter->getDestinationUsers()) == 0)
            return $this->deleteFilter($filter);
        
        // Check what kind of operation needs performing and generate object
        $saveObject = new object();
        $action = ($filter->getId() > 0) ? 'update' : 'insert';
        $saveObject = $this->convertBeanToStandardClass($filter);
        
        // Save main filter record
        if ($action == 'update')
            $DB->update_record('block_moodletxt_filter', $saveObject);
        else
            $filter->setId($DB->insert_record('block_moodletxt_filter', $saveObject, true));
        
        // Clear existing filter links and apply those from object
        $DB->delete_records('block_moodletxt_in_fil', array('filter' => $filter->getId()));
        
        foreach(array_keys($filter->getDestinationUsers()) as $destinationUserId) {
            $insertObject = new object();
            $insertObject->filter = $filter->getId();
            $insertObject->userid = $destinationUserId;
            $DB->insert_record('block_moodletxt_in_fil', $insertObject);
        }
        
    }
    
    /**
     * Removes a filter and links from the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtInboundFilter $filter Filter to nuke
     * @version 2012042301
     * @since 2011071501
     */
    public function deleteFilter(MoodletxtInboundFilter $filter) {
        
        global $DB;
        
        $DB->delete_records('block_moodletxt_in_fil', array('filter' => $filter->getId()));
        $DB->delete_records('block_moodletxt_filter', array('id' => $filter->getId()));
        
    }
    
    /**
     * Converts a filter bean to a standard object for insertion
     * @param MoodletxtInboundFilter $filter Filter to convert
     * @return object Base-level object for insertion
     * @version 2012042301
     * @since 2001071301
     */
    private function convertBeanToStandardClass(MoodletxtInboundFilter $filter) {
        
        $standardObject = new object();
        $standardObject->account = $filter->getAccountId();
        $standardObject->type = $filter->getFilterType();
        $standardObject->value = (string) $filter->getOperand();
        
        if ($filter->getId() > 0)
            $standardObject->id = $filter->getId();
        
        return $standardObject;
        
    } 
    
    /**
     * Converts a database record into a full data bean
     * @param object $record Database record
     * @return MoodletxtInboundFilter Generated data bean
     * @version 2011071301
     * @since 2011071301
     */
    private function convertStandardClassToBean($record) {
        
        return new MoodletxtInboundFilter($record->account, $record->type, $record->value);
        
    }

}

?>