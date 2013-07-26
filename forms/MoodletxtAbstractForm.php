<?php

/**
 * File container for MoodletxtAbstractForm class
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
 * @package uk.co.moodletxt.forms
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092101
 * @since 2012092101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->libdir . '/formslib.php');

/**
 * Abstract extension of Moodle's base form class.
 * Adds a number of useful utility methods to aid in form processing.
 * @package uk.co.moodletxt.forms
 * @see moodleform
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012092101
 * @since 2012092101
 */
abstract class MoodletxtAbstractForm extends moodleform {
    
    /**
     * Exposes individual elements from inside the wrapped form.
     * This is done so that element contents/values can be edited
     * during form processing after submission, generally to
     * add newly created entries to a list.
     * @param string $elementName Name of element to retrieve
     * @return HTML_QuickForm_element Requested element
     * @version 2012092101
     * @since 2012092101
     */
    public function get_element($elementName) {
        return $this->_form->getElement($elementName);
    }

    /**
     * Utility method to wipe submitted data after it is
     * processed. This allows the form to return to the same page
     * without the form being repopulated with old data
     * @version 2012092101
     * @since 2012092101
     */
    public function clearSubmittedValues() {
        $this->_form->updateSubmission(array(), array());
    }    
    
    /**
     * Method used to render the form as a string,
     * rather than immediately dumping to screen.
     * @return string Rendered HTML
     * @version 2012092101
     * @since 2012092101
     */
    public function toHtml() {
        return $this->_form->toHtml();
    }
    
}

?>