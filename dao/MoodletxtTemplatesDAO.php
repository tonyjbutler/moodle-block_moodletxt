<?php

/**
 * File container for MoodletxtTemplatesDAO class
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
 * @see MoodletxtTemplatesDAO
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012041001
 * @since 2011072801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtTemplate.php');

/**
 * Data access object for user messaging templates
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012041001
 * @since 2011072801
 */
class MoodletxtTemplatesDAO {

    /**
     * Table mapping for user templates
     * @todo Use this for reflection-based conversion between DB and beans
     * @var array(string => string)
     */
    static $TEMPLATES_TABLE = array(
        'name'      => 'block_moodletxt_templ',
        'fields'    => array(
            'id'                => 'id',
            'userId'            => 'userid',
            'text'              => 'template'
        )
    );

    /**
     * Returns all saved message templates for the given user
     * @global moodle_database $DB Moodle database manager
     * @param int $userId Moodle user ID
     * @return array(MoodletxtTemplate) Set of templates found
     * @version 2011072801
     * @since 2011072801
     */
    public function getAllTemplatesForUserId($userId) {
        
        global $DB;
        
        $returnArray = array();
        $templateRecords = $DB->get_records(self::$TEMPLATES_TABLE['name'], array('userid' => $userId));
        
        foreach($templateRecords as $templateRecord)
            array_push($returnArray, $this->convertStandardClassToBean($templateRecord));
        
        return $returnArray;
        
    }

    /**
     * Returns a specific template by its database record ID
     * @global moodle_database $DB Moodle database manager
     * @param int $templateId Template record ID
     * @param int $ownerId User ID of template owner
     * @return MoodletxtTemplate Retrieved template
     * @version 2011072801
     * @since 2011072801
     */
    public function getTemplateById($templateId, $ownerId) {
        
        global $DB;
        
        $templateRecord = $DB->get_record(self::$TEMPLATES_TABLE['name'], array('id' => $templateId, 'userid' => $ownerId));
        return $this->convertStandardClassToBean($templateRecord);
        
    }
    
    /**
     * Saves a template to the database
     * @global moodle_database $DB Moodle database manager
     * @param MoodletxtTemplate $template Template to save
     * @return int|boolean ID of new record, boolean otherwise
     * @version 2011080201
     * @since 2011080201
     */
    public function saveTemplate(MoodletxtTemplate $template) {
        
        global $DB;

        $templateRecord = $this->convertBeanToStandardClass($template);
        
        if ($template->getId() > 0)
            return $DB->update_record(self::$TEMPLATES_TABLE['name'], $templateRecord);
        else
            return $DB->insert_record(self::$TEMPLATES_TABLE['name'], $templateRecord);
        
    }

    /**
     * Deletes a template from the database
     * @global moodle_database $DB Moodle database manager
     * @param int $templateId ID of template to delete
     * @param int $userId Owner of template
     * @return boolean Success
     * @version 2011080201
     * @since 2011080201
     */
    public function deleteTemplate($templateId, $userId) {
        
        global $DB;
        
        return $DB->delete_records(self::$TEMPLATES_TABLE['name'], array('id' => $templateId, 'userid' => $userId));
        
    }
    
    /**
     * Converts a data object to a stdClass object for
     * insertion into the database using Moodle's DB API
     * @param MoodletxtTemplate $bean Template to convery
     * @return stdClass Standard object ready for insertion
     * @version 2011072801
     * @since 2011072801
     */
    private function convertBeanToStandardClass(MoodletxtTemplate $bean) {
        
        $dbObject = new stdClass();
        $dbObject->userid = $bean->getUserId();
        $dbObject->template = $bean->getText();
        
        if ($bean->getId() > 0)
            $dbObject->id = $bean->getId();
        
        return $dbObject;
        
    }

    /**
     * Converts an stdClass object retrieved from the Moodle
     * database API into a useful template object
     * @param stdClass $stdObject Basic object from DB
     * @return MoodletxtTemplate Built-up object for use
     * @version 2011072801
     * @since 2011072801
     */
    private function convertStandardClassToBean($stdObject) {
        
        return new MoodletxtTemplate($stdObject->userid, $stdObject->template, $stdObject->id);
        
    }
    
}

?>
