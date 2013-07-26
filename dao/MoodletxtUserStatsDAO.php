<?php

/**
 * File container for MoodletxtUserStatsDAO class
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
 * @since 2012101101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * DAO object for incrementing and retrieving user statistics
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012101601
 * @since 2012101101
 */
class MoodletxtUserStatsDAO {

    /**
     * Increment's a user's outbound message stats for a given day
     * @TODO Expand these stats in 3.1
     * @param TxttoolsAccount $txttoolsAccount Account message was sent through
     * @param object $user Moodle user object
     * @param int $numberSent Number of messages sent
     * @version 2012101101
     * @since 2012101101
     */
    public function incrementUserOutboundStats(TxttoolsAccount $txttoolsAccount, object $user, $numberSent) {
        
        $this->incrementUserOutboundStatsById($txttoolsAccount->getId(), $user->id, $numberSent);
        
    }
    
    /**
     * Increment's a user's outbound message stats for a given day
     * @TODO Expand these stats in 3.1
     * @TODO Stop using DATETIME for the date field. It's not recommended by Moodle
     * @global moodle_database $DB Moodle database manager
     * @param int $txttoolsAccountId ID of account message was sent through
     * @param int $userId ID of Moodle user that sent message
     * @param int $numberSent Number of messages sent
     * @version 2012101601
     * @since 2012101101
     */
    public function incrementUserOutboundStatsById($txttoolsAccountId, $userId, $numberSent) {

        global $DB;

        // This field is a date in Oracle, and a datetime in everything else. Go figure.
        $statsDate = ($DB->get_dbfamily() == 'oracle') ? date('d-M-y') : date('Y-m-d') . ' 00:00:00';
        
        // Update daily user stats
        $todaysStats = $DB->get_record('block_moodletxt_stats', array(
            'txttoolsaccount' => $txttoolsAccountId,
            'userid' => $userId,
            'date_entered' => $statsDate
        ));

        if (is_object($todaysStats)) {

            $todaysStats->numbersent = ((int) $todaysStats->numbersent) + $numberSent;

            $DB->update_record('block_moodletxt_stats', $todaysStats);

        } else {

            $todaysStats = new object();
            $todaysStats->txttoolsaccount = $txttoolsAccountId;
            $todaysStats->userid = $userId;
            $todaysStats->date_entered = $statsDate;
            $todaysStats->numbersent = $numberSent;

            $DB->insert_record('block_moodletxt_stats', $todaysStats);

        }
        
        
    }
    
}

?>