<?php

/**
 * File container for QuickFormRecipientMultiselect class
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
 * @version 2013052301
 * @since 2012100301
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

global $CFG;

require_once($CFG->dirroot . '/blocks/moodletxt/forms/elements/lib/HTML_QuickForm_advmultiselect.php');
require_once($CFG->dirroot . '/blocks/moodletxt/util/MoodletxtStringHelper.php');

/**
 * Overrides the internal validation of the advanced multiselect element,
 * for the specific case of the message composition form, where additional
 * recipients can be added to the message dynamically. These additional
 * recipients (and only these) should be allowed through validation.
 * @TODO This entire class is one of several workarounds for QFAMs multiselect and its various quirks. It should be replaced in 3.1 with something better!
 * @package uk.co.moodletxt.forms.elements
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012100301
 * @since 2012100301
 */
class QuickFormRecipientMultiselect extends HTML_QuickForm_advmultiselect {

    /**
     * Initialises form element with loaded options
     * @param string $elementName Element name
     * @param string $elementLabel Text for element's <label> tag
     * @param array $options Initially selected options within the list
     * @param array(string => string) $attributes HTML attributes for the element
     * @param int $sort Sort direction for select boxes (null for unsorted)
     * @version 2012100301
     * @since 2012100301
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, $sort = SORT_ASC) {
        parent::HTML_QuickForm_advmultiselect($elementName, $elementLabel, $options, $attributes, $sort);
    }
    
    /**
     * Let's hold PEAR's hand with this handy dandy
     * fake constructor - redirects from the PHP4 method to PHP5
     * @param string $elementName Element name
     * @param string $elementLabel Text for element's <label> tag
     * @param array $options Initially selected options within the list
     * @param array(string => string) $attributes HTML attributes for the element
     * @param int $sort Sort direction for select boxes (null for unsorted)
     * @version 2012100301
     * @since 2012100301
     */
    public function QuickFormRecipientMultiselect($elementName=null, $elementLabel=null, $options=null, $attributes=null, $sort = SORT_ASC) {
        $this->__construct($elementName, $elementLabel, $options, $attributes, $sort);
    }
    
    /**
     * Override of standard Moodle function - allows for this
     * element to have additional recipients selected that were not 
     * in the form when it first loaded.
     * @param array $submitValues Data submitted to form
     * @param boolean $assoc Whether to return the value(s) as associative array
     * @return mixed The selected and valid value(s) of the element
     * @version 2012100301
     * @since 2012100301
     */
    public function exportValue(&$submitValues, $assoc = false) {
        
        $value = $this->_findValue($submitValues);
        
        // Get values of element to iterate over
        if (is_null($value))
            $value = $this->getValue();
        
        else if(!is_array($value))
            $value = array($value);
        
        $cleanValues = null;
        
        // Values should only be allowed into the form data
        // if they are part of the initial value set,
        // or if they begin with the fragment "add#", which
        // denotes an additional recipient being dynamically added
        // to the composition form
        if (is_array($value) && ! empty($this->_options)) {
            
            foreach ($value as $v) {
                for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++) {
                    
                    // Passed values are compound - look for recipient type
                    $explodedValue = explode("#", $v);
                    
                    if ($v == $this->_options[$i]['attr']['value'] ||
                        $explodedValue[0] === 'add') {
                        $cleanValues[] = $v;
                        
                        // Dynamically added contacts must be persisted
                        // to the internal options array. After this point,
                        // the form will consider them to be valid and treat them
                        // as any other option in the form. Hurrah!
                        if ($explodedValue[0] === 'add') {
                            $this->_options[] = array(
                                'text' => MoodletxtStringHelper::formatNameForDisplay(
                                    $explodedValue[3], 
                                    $explodedValue[2], 
                                    null, null, null,
                                    $explodedValue[1]
                                ),
                                'attr' => array(
                                    'value' => $v
                                )
                            );
                        }
                        
                        break;
                    }
                    
                }
            }
            
        } else {
            $cleanValues = $value;
        }
        
        if (is_array($cleanValues) && !$this->getMultiple())
            return $this->_prepareValue($cleanValues[0], $assoc);
        
        else
            return $this->_prepareValue($cleanValues, $assoc);
        
    }  
        
}

?>