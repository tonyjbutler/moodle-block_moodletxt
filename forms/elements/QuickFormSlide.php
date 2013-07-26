<?php

/**
 * File container for QuickFormSlide class
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
 * @version 2012110501
 * @since 2011101801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

global $CFG;

require_once($CFG->libdir . '/pear/HTML/QuickForm/static.php');

/**
 * Pseudo-element representing slides on a jQuery
 * slidey form thingy, like the compose page
 * @package uk.co.moodletxt.forms.elements
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012110501
 * @since 2011101801
 */
class QuickFormSlide extends HTML_QuickForm_static {

    /**
     * Class constructor
     * 
     * @version 2011101801
     * @since 2011101801
     */
    public function __construct($slideName = null, $headerText = null) {
        $this->HTML_QuickForm_static($slideName, null, $headerText);
        $this->_type = 'slide';
    }

    /**
     * Let's hold PEAR's hand with this handy dandy
     * fake constructor - redirects from the PHP4 method to PHP5
     * @version 2011101801
     * @since 2011101801
     */
    public function QuickFormSlide($slideName = null, $headerText = null) {
        $this->__construct($slideName, $headerText);
    }
    
    /**
     * Accepts a renderer
     * @param object An HTML_QuickForm_Renderer object
     * @param boolean Whether the element is required
     * @param string Error message associated with the element
     * @version 2012110501
     * @since 2011101801
     */
    public function accept(&$renderer, $required = false, $error = null) {
        $renderer->renderSlide($this, $required, $error);
    }

    /**
     * Returns the slide's associated title text
     * @return string Slide text
     * @version 2011101801
     * @since 2011101801
     */
    public function getText() {
        return $this->_text;
    }
    
}
?>
