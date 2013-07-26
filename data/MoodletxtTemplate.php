<?php

/**
 * File container for MoodletxtTemplate class
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
 * @see MoodletxtTemplate
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011072801
 * @since 2011072801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Data bean for a message template
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011072801
 * @since 2011072801
 */
class MoodletxtTemplate {

    /**
     * Database record ID of this template
     * @var int
     */
    private $id;
    
    /**
     * ID of Moodle user that owns this template
     * @var int
     */
    private $userId;
    
    /**
     * Message text of the template
     * @var string
     */
    private $text;
    
    /**
     * Sets up with initial data
     * @param int $userId Owner ID
     * @param string $text Message template text
     * @param int $id Template DB record ID
     * @version 2011072801
     * @since 2011072801
     */
    public function __construct($userId, $text, $id = 0) {
        $this->setId($id);
        $this->setUserId($userId);
        $this->setText($text);
    }
    
    /**
     * Returns the DB record ID of this template
     * @return int Database record ID
     * @version 2011072801
     * @since 2011072801
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the DB record ID of this template
     * @param int $id Database record ID
     * @version 2011072801
     * @since 2011072801
     */
    public function setId($id) {
        if ($id > 0)
            $this->id = $id;
    }

    /**
     * Returns the record ID of the Moodle
     * user who owns this template
     * @return int Moodle user ID
     * @version 2011072801
     * @since 2011072801
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Sets the record ID of the Moodle
     * user who owns this template
     * @param int $userId Moodle user ID
     * @version 2011072801
     * @since 2011072801
     */
    public function setUserId($userId) {
        if ($userId > 0)
            $this->userId = $userId;
    }

    /**
     * Returns the text of the template
     * @return string Template text
     * @version 2011072801
     * @since 2011072801
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Sets the text of the template
     * @param string $text Template text
     * @version 2011072801
     * @since 2011072801
     */
    public function setText($text) {
        $this->text = $text;
    }
    
}

?>