<?php

/**
 * File container for MoodletxtMoodleUserDAO class
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
 * @version 2013070201
 * @since 2011062001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtTemplatesDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtBiteSizedUser.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtUserGroup.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtUserConfig.php');

/**
 * Database access controller for sent SMS messages
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013070201
 * @since 2011062001
 */
class MoodletxtMoodleUserDAO {    

    /**
     * DAO used to get/save user templates
     * @var MoodletxtTemplatesDAO
     */
    private $templateDAO;
    
    /**
     * Sets up the DAO object for use
     * @version 2011080201
     * @since 2011080201
     */
    public function __construct() {
        $this->templateDAO = new MoodletxtTemplatesDAO();
    }
    
    /**
     * Returns a set of all the Moodle users registered on the system
     * Use with caution - this could be hooooge!
     * @global moodle_database $DB Moodle database manager
     * @return MoodletxtBiteSizedUser[] Full user list
     * @version 2012071201
     * @since 2012071201
     */
    public function getAllUsers() {
        
        global $DB;
        
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name
        
        $returnArray = array();
        $userRecords = $DB->get_records_select('user', 'deleted = 0 AND id > 1', array(), 'lastname ASC, firstname ASC');
        
        foreach($userRecords as $recordId => $record) {
            $nibbler = new MoodletxtBiteSizedUser($record->id, $record->username, $record->firstname, $record->lastname);
            
            try {
                $nibbler->setRecipientNumber(new MoodletxtPhoneNumber($record->$numberField));
            } catch (InvalidPhoneNumberException $ex) {
                // User record will be OK without this
            }
            
            array_push($returnArray, $nibbler);
            unset($userRecords[$recordId]); // Saves on memory when converting one set of objects to another
        }

        return $returnArray;
    }
    
    /**
     * Searches through users to find those matching given text
     * @todo Tokenise search operand to search on multiple words
     * @global moodle_database $DB Moodle database manager
     * @param string $searchFragment Text to search for
     * @return array Set of matching users
     * @version 2012042301
     * @since 2011062301
     */
    public function searchUsersByNameAndUsername($searchFragment) {
        
        global $DB;
        
        // Drop in LIKE control characters - search for any position in string
        $searchFragment = '%' . $searchFragment . '%';
        
        //$tokeniser = strtok($searchFragment, ' -');
        
        $sql = 'SELECT id, username, firstname, lastname 
            FROM {user}
            WHERE deleted = 0 AND id > 1 AND (' .
            $DB->sql_like('firstname', ':firstname', false, false) . ' OR ' . 
            $DB->sql_like('lastname',  ':lastname',  false, false) . ' OR ' . 
            $DB->sql_like('username',  ':username',  false, false) . 
            ') ORDER BY lastname ASC';
        
        $returnArray = array();
        $records = $DB->get_records_sql($sql, array('firstname' => $searchFragment, 'lastname' => $searchFragment, 'username' => $searchFragment));

        foreach ($records as $record) {
            $nibbler = new MoodletxtBiteSizedUser($record->id, $record->username, $record->firstname, $record->lastname);
            array_push($returnArray, $nibbler);
        }

        return $returnArray;
        
    }
    
    /**
     * Grabs a single Moodle user from the DB by its record ID
     * @global moodle_database $DB Moodle database manager
     * @param int $userId User record ID
     * @param boolean $includeConfig 
     * @return MoodletxtBiteSizedUser Moodle user
     * @version 2012060101
     * @since 2011062701
     */    
    public function getUserById($userId, $includeConfig = false, $includeTemplates = false) {
        
        global $DB;
        
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name
        $fields = 'id, username, firstname, lastname, ' . $numberField;
        
        $moodleUser = $DB->get_record('user', array('id' => $userId), $fields);
        
        if (! is_object($moodleUser))
            throw new InvalidArgumentException(get_string('errorinvalidaccountid', 'block_moodletxt'));
        
        $userObject = new MoodletxtBiteSizedUser($moodleUser->id, $moodleUser->username, $moodleUser->firstname, $moodleUser->lastname);
        
        try {
            $phoneObject = new MoodletxtPhoneNumber($moodleUser->$numberField);
            $userObject->setRecipientNumber($phoneObject);
        } catch(InvalidPhoneNumberException $ex) {
            // Profile's phone number is invalid - not necessary to throw further
        }
        
        // Include optionals if requested
        if ($includeConfig)
            $userObject->setConfig($this->getUserConfig($userObject->getId()));
        
        if ($includeTemplates)
            $userObject->setTemplates($this->templateDAO->getAllTemplatesForUserId($userObject->getId()));
        
        return $userObject;
        
    }
    
    /**
     * Grabs a collection of Moodle users by record ID.
     * Unlike the method above for grabbing a single user,
     * this method will only return basic info. Loading full data
     * for all users would cause a massive performance hit.
     * @global moodle_database $DB Moodle database manager
     * @param array(int) $userIds Database IDs of users to fetch
     * @param string $indexField Field to use as return array key - 'id' or 'phone'
     * @return MoodletxtBiteSizedUser[] Users found in DB
     * @version 2012060101
     * @since 2012031201
     */
    public function getUsersById(array $userIds, $indexField = 'id') {
        
        global $DB;

        $userObjects = array();
        
        if (count($userIds) > 0) {
        
            list ($inOrEqual, $params) = $DB->get_in_or_equal($userIds, SQL_PARAMS_NAMED);

            $numberSource = get_config('moodletxt', 'Phone_Number_Source');
            $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name

            $sql = 'SELECT id, username, firstname, lastname, ' . $numberField . ' 
                FROM {user}
                WHERE id ' . $inOrEqual;

            $userRecords = $DB->get_records_sql($sql, $params);

            foreach($userRecords as $moodleUser) {
                $arrayIndex = ($indexField == 'phone') ? $moodleUser->$numberField : $moodleUser->id;
                $userObjects[$arrayIndex] = new MoodletxtBiteSizedUser($moodleUser->id, $moodleUser->username, $moodleUser->firstname, $moodleUser->lastname);

                try {
                    $phoneObject = new MoodletxtPhoneNumber($moodleUser->$numberField);
                    $userObjects[$arrayIndex]->setRecipientNumber($phoneObject);
                } catch(InvalidPhoneNumberException $ex) {
                    // Profile's phone number is invalid - not necessary to throw further
                }                
                
            }
            
        }
            
        return $userObjects;
        
    }
        
    /**
     * Returns all user config options for the given user ID
     * @global moodle_database $DB Moodle database manager
     * @param int $userId ID of user to search against
     * @return MoodletxtUserConfig Config container
     * @version 2012041001
     * @since 2011080201
     */
    public function getUserConfig($userId) {
        
        global $DB;
        
        $userConfigObject = new MoodletxtUserConfig($userId, array());
        
        $configRecords = $DB->get_records('block_moodletxt_uconfig', array('userid' => $userId));
        
        foreach($configRecords as $configRecord)
            $userConfigObject->setUserConfig($configRecord->setting, $configRecord->value);
        
        return $userConfigObject;
        
    }
    
    /**
     * Saves given user configuration data to the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtUserConfig $userConfigObject User config container
     * @return boolean Success
     * @version 2012041001
     * @since 2011080201
     */
    public function saveUserConfig(MoodletxtUserConfig $userConfigObject) {
        
        global $DB;
        
        $success = true;
        
        foreach($userConfigObject->getAllUserConfig() as $setting => $value) {
            
            if ($success) {
            
                // Check for existing record
                $configRecord = $DB->get_record('block_moodletxt_uconfig', array(
                    'userid' => $userConfigObject->getUserId(),
                    'setting' => $setting
                ));

                // Record already exists
                if (is_object($configRecord)) {

                    $configRecord->value = $value;
                    $success = $success && $DB->update_record('block_moodletxt_uconfig', $configRecord);

                // New insert
                } else {

                    $insertObject = new stdClass();
                    $insertObject->userid = $userConfigObject->getUserId();
                    $insertObject->setting = $setting;
                    $insertObject->value = $value;

                    $success = $success && $DB->insert_record('block_moodletxt_uconfig', $insertObject);

                }
                
            }
            
        }
        
        return $success;
        
    }
 
    /**
     * Returns a set of every enrolled user
     * on a given course. 
     * 
     * Could be improved in futureversions by giving the admin 
     * a choice of which roles appear and using get_role_users
     * to pull them, or by giving potential recipient roles
     * a new capability, which could be used with this methodology
     * @param int $courseId Course ID
     * @return MoodletxtBiteSizedUser[] Users on course
     * @version 2013070201
     * @since 2011102501
     */
    public function getUsersOnCourse($courseId) {
        
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name
        
        $context = context_course::instance($courseId);
        $userSet = get_enrolled_users($context, '', 0, 'u.id, u.username, u.firstname, u.lastname, u.' . $numberField);
        
        $userObjects = array();
        
        foreach($userSet as $user) {
            
            $userObjects[$user->id] = new MoodletxtBiteSizedUser($user->id, 
                $user->username, $user->firstname, $user->lastname);
        
            try {
                $phoneObject = new MoodletxtPhoneNumber($user->$numberField);
                $userObjects[$user->id]->setRecipientNumber($phoneObject);
            } catch(InvalidPhoneNumberException $ex) {
                // Profile's phone number is invalid - not necessary to throw further
            }
            
        }
                
        return $userObjects;
        
    }
    
    /**
     * Returns a set of every user contained within a Moodle group
     * @param int $groupId ID of group to query
     * @param string $indexField Field to use as return array key - 'id' or 'phone'
     * @return MoodletxtBiteSizedUser[] Users in group
     * @version 2012060101
     * @since 2011102501
     */
    public function getUsersInGroup($groupId, $indexField = 'id') {
        
        $numberSource = get_config('moodletxt', 'Phone_Number_Source');
        $numberField = ($numberSource == 'phone1') ? 'phone1' : 'phone2'; // Protects against bad DB values and abstracts field name        
        
        $groupMembers = groups_get_members($groupId, 'u.id, u.username, u.firstname, u.lastname, u.' . $numberField);
        
        $userObjects = array();
        
        foreach($groupMembers as $user) {
         
            $arrayIndex = ($indexField == 'phone') ? $user->$numberField : $user->id;
            $userObjects[$arrayIndex] = new MoodletxtBiteSizedUser($user->id, 
                $user->username, $user->firstname, $user->lastname);
            
            try {
                $phoneObject = new MoodletxtPhoneNumber($user->$numberField);
                $userObjects[$arrayIndex]->setRecipientNumber($phoneObject);
            } catch(InvalidPhoneNumberException $ex) {
                // Profile's phone number is invalid - not necessary to throw further
            }
            
        }
        
        return $userObjects;            
        
    }

    /**
     * Returns all user groups contained within a given course
     * @param int $courseId ID of course to query
     * @param int $availableToUser Optional ID of user to restrict group access to
     * @param boolean $includeUsersInResult Whether to include group members in the result
     * @return MoodletxtUserGroup[] Set of groups
     * @return MoodletxtUserGroup 
     * @version 2013070201
     * @since 2011102501
     */
    public function getUserGroupsOnCourse($courseId, $availableToUser = 0, $includeUsersInResult = false) {
        
        $context = context_course::instance($courseId);
        $groupList = groups_get_all_groups($courseId, $availableToUser);
        
        $groupObjects = array();
        
        foreach($groupList as $group)
            $groupObjects[$group->id] = new MoodletxtUserGroup($group->id, $group->name, $context);
        
        if ($includeUsersInResult)
            foreach($groupObjects as $group)
                $group->setUsers($this->getUsersInGroup($group->getId()));
        
        return $groupObjects;
        
    }
    
}

?>