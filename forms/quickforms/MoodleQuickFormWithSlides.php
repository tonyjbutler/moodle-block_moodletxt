<?php

/**
 * File container for MoodleQuickFormWithSlides class
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
 * @see MoodleQuickFormWithSlides
 * @package uk.co.moodletxt.forms.quickforms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012031701
 * @since 2011101901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

global $CFG; // Required due to scope

require_once($CFG->libdir . '/formslib.php');

/**
 * MoodleQuickForm extension to support sliding panels.
 * Useful for wizards and itemised content presentations.
 * 
 * @package uk.co.moodletxt.forms.quickforms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012031701
 * @since 2011101901
 */
class MoodleQuickFormWithSlides extends MoodleQuickForm {

    /**
     * Initialises the form and its attributes
     * @param string $formName Name of the form
     * @param string $method Submission method, GET or POST
     * @param string $action Destination for form submission
     * @param string $target Form's target
     * @param string $attributes Custom HTML attributes ('attr' => 'value')
     * @version 2012031701
     * @since 2011101901
     */
    public function __construct($formName, $method, $action, $target='', array $attributes = array()){
        parent::MoodleQuickForm($formName, $method, $action, $target, $attributes);
    }

    /**
     * Creates a marker within the form order to close
     * an open slide before the given element name.
     * Any slides not closed by the end of the form definition
     * are closed automatically by the QuickForm library.
     * @see QuickFormSlide
     * @param string $elementName Name of the element following the slide
     * @version 2011101901
     * @since 2011101901
     */
    public function closeSlideBefore($elementName) {
        $renderer =& $this->defaultRenderer();
        $renderer->addCloseSlideElements($elementName);
    }
    
}

?>
