<?php

/**
 * File container for the MoodletxtBiteSizedUser class
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
 * @see MoodletxtBiteSizedUser
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011062101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtRecipient.php');
require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtPhoneNumber.php');

/**
 * Cut-down version of a Moodle user record for
 * use in dealing with txttools accounts
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2011061501
 */
class MoodletxtBiteSizedUser extends MoodletxtRecipient {
    
    /**
     * ID number of the Moodle user
     * @var int
     */
    private $id;
    
    /**
     * Username of the Moodle user
     * @var string
     */
    private $username;
            
    /**
     * User configuration data in wrapper
     * @var MoodletxtUserConfig
     */
    private $config;
    
    /**
     * Array of templates owned by the user
     * @var MoodletxtTemplate[]
     */
    private $templates;
    
    /**
     * Sets up the data container with Moodle user's details
     * @param int $id User ID
     * @param string $username Moodle username
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param MoodletxtPhoneNumber Phone number
     * @version 2012042301
     * @since 2011061501
     */
    public function __construct($id, $username, $firstName, $lastName, MoodletxtPhoneNumber $phoneNumber = null) {
        
        parent::__construct($phoneNumber, $firstName, $lastName);
        
        $this->setId($id);
        $this->setUsername($username);
                
    }
    
    /**
     * Returns the ID number of the Moodle user
     * @return int User ID
     * @version 2011061501
     * @since 2011061501
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the ID number of the Moodle user
     * @param int $id User ID
     * @version 2011061501
     * @since 2011061501
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Returns the username of the Moodle user
     * @return string Username
     * @version 2011061501
     * @since 2011061501
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Sets the username of the Moodle user
     * @param string $username Username
     * @version 2011061501
     * @since 2011061501
     */
    public function setUsername($username) {
        $this->username = $username;
    }
    
    /**
     * Returns the user's config object
     * @return MoodletxtUserConfig
     * @version 2011080201
     * @since 2011080201
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Sets up the user's config object
     * @param MoodletxtUserConfig $config User config object
     * @version 2011080201
     * @since 2011080201
     */
    public function setConfig(MoodletxtUserConfig $config) {
        $this->config = $config;
    }

    /**
     * Returns all the user's owned templates
     * @return MoodletxtTemplate[]
     * @version 2011080201
     * @since 2011080201
     */
    public function getTemplates() {
        return $this->templates;
    }

    /**
     * Sets the user's owned templates
     * @param MoodletxtTemplate[] $templates User's templates
     * @version 2011080201
     * @since 2011080201
     */
    public function setTemplates(array $templates) {
        $this->templates = $templates;
    }

    /**
     * Adds a new template to this user's set
     * @param MoodletxtTemplate $template Template to add
     * @version 2011080201
     * @since 2011080201
     */
    public function addTemplate(MoodletxtTemplate $template) {
        array_push($this->templates, $template);
    }
    
    /**
     * Returns the recipients full name, formatted for screen display
     * @return string Recipient's full name, display formatted
     * @version 2013052301
     * @since 2012031401
     */
    public function getFullNameForDisplay($linkifyUsername = false) {
        $userId = ($linkifyUsername) ? $this->getId() : 0;
        
        return MoodletxtStringHelper::formatNameForDisplay(
                $this->getFirstName(), $this->getLastName(), $this->getUsername(), null, $userId);
    }    
    
}

?>