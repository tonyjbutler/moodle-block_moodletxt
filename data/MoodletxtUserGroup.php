<?php

/**
 * File container for MoodletxtUserGroup class
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
 * @see MoodletxtUserGroup
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052101
 * @since 2011102501
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtBiteSizedUser.php');

/**
 * Data object representing a group of Moodle users within the system
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052101
 * @since 2011102501
 */
class MoodletxtUserGroup {

    /**
     * The DB record ID of this user group
     * @var int
     */
    private $id = 0;
    
    /**
     * The name of this user group
     * @var string
     */
    private $name = '';

    /**
     * The Moodle context level at which this group is defined
     * @var type 
     */
    private $context;

    /**
     * An array of the users contained within this group
     * @var array(MoodletxtBiteSizedUser) Group members
     */
    private $users = array();
    
    /**
     * Initialises the Moodle user group with its current data
     * @param int $id Group ID
     * @param string $name Group name
     * @param object $context The Moodle context in which this group is defined
     * @param array(MoodletxtBiteSizedUser) $users Set of group members
     * @version 2011102501
     * @since 2011102501
     */
    public function __construct($id, $name, $context = null, array $users = array()) {
        $this->setId($id);
        $this->setName($name);
        $this->setContext($context);
        $this->setUsers($users);
    }
    
    /**
     * Returns the database ID of this user group
     * @return int User group ID
     * @version 2011102501
     * @since 2011102501
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the database ID of this user group
     * @param int $id User group ID
     * @version 2013052101
     * @since 2011102501
     */
    public function setId($id) {
        $id = (int) $id;
        
        if ($id > 0)
            $this->id = $id;
    }

    /**
     * Returns the name of this group
     * @return string Group name
     * @version 2011102501
     * @since 2011102501
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the name of this group
     * @param string $name Group name
     * @version 2011102501
     * @since 2011102501
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Returns the context object this group is associated with
     * @return object Moodle context object
     * @version 2011102501
     * @since 2011102501
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * Sets the Moodle context this group was created in
     * @param object $context Moodle context object
     * @version 2011102501
     * @since 2011102501
     */
    public function setContext($context) {
        if ($context != null)
            $this->context = $context;
    }

    /**
     * Returns a set of all Moodle users that are members of this group
     * @return array(MoodletxtBiteSizedUser) Group members
     * @version 2011102501
     * @since 2011102501
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * Sets the Moodle users that are members of this group
     * @see MoodletxtBiteSizedUser
     * @param mixed $users One or more MoodletxtBiteSizedUser objects
     * @version 2011102501
     * @since 2011102501
     */
    public function setUsers($users) {
        if (is_array($users))
            $this->users = $users;
        else
            $this->users = array($users->getId() => $users);
    }
    
    /**
     * Adds a Moodle user to the group
     * @param MoodletxtBiteSizedUser $user User to add
     * @version 2011102501
     * @since 2011102501
     */
    public function addUser(MoodletxtBiteSizedUser $user) {
        $this->users[$user->getId()] = $user;
    }
    
}


   
