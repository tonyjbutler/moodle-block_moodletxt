<?php

/**
 * File container for QuickFormSelectDynamic class
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
 * @version 2011071401
 * @since 2011071401
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

global $CFG;

require_once($CFG->libdir . '/pear/HTML/QuickForm/select.php');
require_once($CFG->dirroot . '/blocks/moodletxt/renderer.php');

/**
 * Overrides the PEAR select element for selectboxes that are dynamically
 * populated via AJAX on the calling page. Basically this override
 * prevents the element checking returned values against the inital list,
 * as the initial list is likely to be blank.
 * @package uk.co.moodletxt.forms.elements
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011071401
 * @since 2011071401
 */
class QuickFormSelectDynamic extends HTML_QuickForm_select {

    /**
     * Initialises form element with loaded options
     * @param string $elementName Element name
     * @param string $elementLabel Text for element's <label> tag
     * @param array $options Initially selected options within the list
     * @param array(string => string) $attributes HTML attributes for the element
     * @version 2011071401
     * @since 2011071401
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        parent::HTML_QuickForm_select($elementName, $elementLabel, $options, $attributes);
    }
    
    /**
     * Let's hold PEAR's hand with this handy dandy
     * fake constructor - redirects from the PHP4 method to PHP5
     * @param string $elementName Element name
     * @param string $elementLabel Text for element's <label> tag
     * @param array $options Initially selected options within the list
     * @param array(string => string) $attributes HTML attributes for the element
     * @version 2011071401
     * @since 2011071401
     */
    public function QuickFormSelectDynamic($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        $this->__construct($elementName, $elementLabel, $options, $attributes);
    }
    
    /**
     * Override of standard Moodle function - allows for this
     * element to have options selected that were not in the form
     * when it first loaded, as these select boxes have their
     * contents loaded via AJAX
     * @param array $submitValues Data submitted to form
     * @param boolean $assoc Whether to return the value(s) as associative array
     * @return mixed The selected and valid value(s) of the element
     * @version 2011071401
     * @since 2011071401
     */
    public function exportValue(&$submitValues, $assoc = false) {

        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        }
        $value = (array)$value;

        $cleaned = array();
        foreach ($value as $v)
            $cleaned[] = (string) $v;

        if (empty($cleaned))
            return $this->_prepareValue(null, $assoc);
        
        if ($this->getMultiple())
            return $this->_prepareValue($cleaned, $assoc);
        else
            return $this->_prepareValue($cleaned[0], $assoc);
        
    }    
        
}

?>