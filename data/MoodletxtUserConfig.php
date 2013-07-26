<?php

/**
 * File container for MoodletxtUserConfig class
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
 * @see MoodletxtUserConfig
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013071001
 * @since 2011072801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Wrapper class holds all a given user's preferences/config data
 * (Templates are stored separately.)
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013071001
 * @since 2011072801
 */
class MoodletxtUserConfig {

    /**
     * ID of the Moodle user who owns this config
     * @var int
     */
    private $userId;
    
    /**
     * Configuration options for the Moodle user
     * @var array(string => mixed)
     */
    private $userConfig = array();

    /**
     * Sets up the config object with initial data
     * @param int $userId Moodle user ID
     * @param array(string => mixed) $configSet Initial config data
     * @version 2011072801
     * @since 2011072801
     */
    public function __construct($userId, $configSet) {
        $this->setUserId($userId);
        $this->setAllUserConfig($configSet);
    }

    /**
     * Returns all configuration data stored for
     * the Moodle user
     * @return array(string => mixed) Config data
     * @version 2011072801
     * @since 2011072801
     */
    public function getAllUserConfig() {
        return $this->userConfig;
    }

    /**
     * Stores a full set of configuration data
     * for the Moodle user, overwriting anything currently held.
     * YOU PROBABLY DON'T WANT THIS METHOD!
     * @param array(string => mixed) $userConfig New config data
     * @version 2011072801
     * @since 2011072801
     */
    public function setAllUserConfig($userConfig) {
        $this->userConfig = $userConfig;
    }

    /**
     * Returns the value of a given config option
     * @param string $configName Config option
     * @return mixed Value of option
     * @version 2013071001
     * @since 2011072801
     */
    public function getUserConfig($configName) {
        if (array_key_exists($configName, $this->userConfig))
            return($this->userConfig[$configName]);
        else
            return '';
    }
    
    /**
     * Sets the value of a given config option
     * @param string $configName Config option
     * @param mixed $configValue Value of option
     * @version 2011072801
     * @since 2011072801
     */
    public function setUserConfig($configName, $configValue) {
        $this->userConfig[$configName] = $configValue;
    }

    /**
     * Returns the ID of the user who owns this config set
     * @return int Moodle user ID
     * @version 2011072801
     * @since 2011072801
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Sets the ID of the user who owns this config set
     * @param int $userId Moodle user ID
     * @version 2011072801
     * @since 2011072801
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }
    
}

?>