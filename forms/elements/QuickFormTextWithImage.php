<?php

/**
 * File container for QuickFormTextWithImage class
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
 * @package uk.co.moodletxt.forms.elements
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011070601
 * @since 2011070501
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

global $CFG;

require_once($CFG->libdir . '/pear/HTML/QuickForm/text.php');
require_once($CFG->dirroot . '/blocks/moodletxt/renderer.php');

/**
 * Overridden PEAR text element with optional image tacked on.
 * Used in filter management form
 * @package uk.co.moodletxt.forms.elements
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011070601
 * @since 2011070501
 */
class QuickFormTextWithImage extends HTML_QuickForm_text {

    /**
     * Holds the icon to be displayed with the form element
     * @var moodletxt_icon
     */
    private $icon;
    
    /**
     * Initialises form element with loaded options
     * @param string $elementName Element name
     * @param string $elementLabel Text for element's <label> tag
     * @param array(string => string) $attributes HTML attributes for the element
     * @param moodletxt_icon $icon Icon to be displayed with element
     * @version 2011070501
     * @since 2011070501
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, moodletxt_icon $icon=null) {
        parent::HTML_QuickForm_text($elementName, $elementLabel, $attributes);
        
        if ($icon != null)
            $this->setIcon($icon);
    }
    
    /**
     * Let's hold PEAR's hand with this PHP4 to PHP5 bridge
     * @param string $elementName Element name
     * @param string $elementLabel Text for element's <label> tag
     * @param array(string => string) $attributes HTML attributes for the element
     * @param moodletxt_icon $icon Icon to be displayed with element
     * @version 2011070501
     * @since 2011070501
     */
    public function QuickFormTextWithImage($elementName=null, $elementLabel=null, $attributes=null, moodletxt_icon $icon=null) {
        $this->__construct($elementName, $elementLabel, $attributes, $icon);
    }
    
    /**
     * Generates the HTML for this element
     * @global object $PAGE Moodle page object for fetching renderer
     * @return string Generated HTML 
     * @version 2011070501
     * @since 2011070501
     */
    public function toHtml() {
        global $PAGE;
        $output = $PAGE->get_renderer('block_moodletxt');
        
        $renderedElement = parent::toHtml();
        $renderedElement .= $output->render($this->getIcon());
        
        return $renderedElement;
    }
    
    /**
     * Returns the icon associated with the form element
     * @return moodletxt_icon Icon object
     * @version 2011070501
     * @since 2011070501
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * Sets the icon associated with the form element
     * @param moodletxt_icon $icon Icon object
     * @version 2011070501
     * @since 2011070501
     */
    public function setIcon(moodletxt_icon $icon) {
        $icon->set_attribute('class', 'mdltxtBaselineIcon ' . $icon->get_attribute('class'));
        $this->icon = $icon;
    }
    
}

?>