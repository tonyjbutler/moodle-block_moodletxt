<?php

/**
 * File container for MoodletxtInboundMessageTag class
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
 * @version 2012051401
 * @since 2012042201
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Class represents a message tag within a user's inbox
 * @package uk.co.moodletxt.dao
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012051401
 * @since 2012042201
 */
class MoodletxtInboundMessageTag {

    /**
     * Tag's record ID
     * @var int
     */
    private $id;
    
    /**
     * The owner of this tag
     * @var MoodletxtBiteSizedUser
     */
    private $owner;
    
    /**
     * The name of the tag
     * @var string
     */
    private $name;
    
    /**
     * The 6-character custom colour code for the tag
     * @var string
     */
    private $colourCode;
    
    /**
     * Number of times this tag occurs in a user's inbox
     * @var int
     */
    private $tagCount;
    
    /**
     * Sets up the data bean with an initial data set
     * @param string $name Tag name
     * @param string $colourCode HTML colour code for background highlighting
     * @param int $id Tag record ID
     * @param MoodletxtBiteSizedUser $owner Moodle user who owns tag
     * @version 2012050401
     * @since 2012050401
     */
    public function __construct($name, $colourCode, $id = 0, MoodletxtBiteSizedUser $owner = null) {
        $this->setName($name);
        $this->setColourCode($colourCode);
        $this->setId($id);
        
        if ($owner != null)
            $this->setOwner($owner);
    }

    /**
     * Returns the database ID of this tag
     * @return int Tag ID
     * @version 2012050401
     * @since 2012050401
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the database record ID of this tag
     * @param int $id Tag record ID
     * @version 2012050401
     * @since 2012050401
     */
    public function setId($id) {
        
        if ($id > 0)
            $this->id = $id;
    }

    /**
     * Returns the Moodle user who owns this tag
     * @return MoodletxtBiteSizedUser Moodle user/tag owner
     * @version 2012050401
     * @since 2012050401
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * Sets the Moodle user that owns this tag
     * @param MoodletxtBiteSizedUser $owner Moodle user/tag owner
     * @version 2012050401
     * @since 2012050401
     */
    public function setOwner(MoodletxtBiteSizedUser $owner) {
        $this->owner = $owner;
    }

    /**
     * Returns the textual name of this tag
     * @return string Tag name
     * @version 2012050401
     * @since 2012050401
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the textual name of this tag
     * @param string $name Tag name
     * @version 2012050401
     * @since 2012050401
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Returns a CSS colour code to use when highlighting with this tag
     * @return string CSS colour code
     * @version 2012050401
     * @since 2012050401
     */
    public function getColourCode() {
        return $this->colourCode;
    }

    /**
     * Sets a CSS colour code to use when highlighting with this tag
     * @param string $colourCode CSS colour code
     * @version 2012050401
     * @since 2012050401
     */
    public function setColourCode($colourCode) {
        $this->colourCode = $colourCode;
    }
    
    /**
     * Returns the number of times this tag is used in the current inbox
     * @return int Number of occurences of tag
     * @version 2012051401
     * @since 2012051401
     */
    public function getTagCount() {
        return $this->tagCount;
    }

    /**
     * Sets the number of times this tag is used in the current inbox
     * @param int $tagCount Number of occurences of tag
     * @version 2012051401
     * @since 2012051401
     */
    public function setTagCount($tagCount) {
        $this->tagCount = $tagCount;
    }
    
}

?>