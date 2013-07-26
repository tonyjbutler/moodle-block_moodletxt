<?php

/**
 * File container for the QuickFormRendererWithSlides class
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
 * @see QuickFormRendererWithSlides
 * @package uk.co.moodletxt.forms.renderers
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011102101
 * @since 2011101801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

global $CFG;  // Required due to scope

require_once($CFG->libdir . '/formslib.php');

/**
 * Renderer to support forms that contain content slides
 * Written in style with parent objects (field names) to avoid confusion
 * 
 * @see QuickFormSlide
 * @see MoodleQuickFormWithSlides
 * @package uk.co.moodletxt.forms.renderers
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011102101
 * @since 2011101801
 */
class QuickFormRendererWithSlides extends MoodleQuickForm_Renderer {
   
    /**
     * The HTML template used to render the beginning of a slide wrapper.
     * (This is the container that slide panels are held within.)
     * @var string
     */
    protected $_openSlideWrapperTemplate = "\n\t<div id=\"slideWrapper\">\n\t\t<div id=\"slidePlate\">";
    
    /**
     * The HTML template used to render the end of a slide wrapper.
     * (This is the container that slide panels are held within.)
     * @var string
     */
    protected $_closeSlideWrapperTemplate = "\n\t\t</div>\n\t</div>";
    
    /**
     * The HTML template used to render the beginning of a content slide
     * @var string
     */
    protected $_openSlideTemplate = "\n\t\t\t<div id=\"{id}\" class=\"slide\">\n\t\t<h2>{header}</h2>";
    
    /**
     * The HTML template used to render the end of a content slide
     * @var string
     */
    protected $_closeSlideTemplate = "\n\t\t\t</div>";
    
    /**
     * Boolean holds whether a slide is currently open on the form.
     * If another slide is opened, the current one must be closed.
     * @var boolean
     */
    protected $_slideOpen = false;
    
    /**
     * Holds names of all form elements that mark the end of a slide.
     * Slides are rendered to close before these elements when encountered.
     * @var array(string)
     */
    protected $_closeSlideElements = array();
    
    /**
     * Sets up the render and instantiates any instance-specific templates
     * @version 2011102101
     * @since 2011101801
     */
    public function __construct() {
        parent::MoodleQuickForm_Renderer();
        $this->_elementTemplates['nolabel'] = "\n\t\t".
        '<div class="fitem {advanced}<!-- BEGIN required --> required<!-- END required --><!-- BEGIN error --> error<!-- END error --> fitem_{type}">
            <!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->
            {element}
        </div>';

    }

    /**
     * Renders a slide out to the screen
     * @param QuickFormSlide $slide Slide to render
     * @version 2011101801
     * @since 2011101801
     */
    public function renderSlide($slide) {

        // Fieldsets cannot span slides, obviously
        while ($this->_fieldsetsOpen > 0) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }
        
        // Close any previously opened slide
        if ($this->_slideOpen) {
            $this->_html .= $this->_closeSlideTemplate;
            $this->_slideOpen = false;
        }
        
        $slideHtml = $this->_openSlideTemplate;
        $slideHtml = str_replace('{id}', $slide->getName(), $slideHtml);
        $slideHtml = str_replace('{header}', $slide->toHtml(), $slideHtml);
        
        $this->_html .= $slideHtml;
        $this->_slideOpen = true;
        
    }
    
    /**
     * Returns the HTML template for rendering the end of a slide
     * @return string HTML template
     * @version 2011101801
     * @since 2011101801
     */
    public function getCloseSlideHtml() {
        return $this->_closeSlideTemplate;
    }
    
    /**
     * Marks the given element(s) as the point at which a slide ends
     * (The slide ends before the named element)
     * @param string|array $element Element(s) slides close before
     * @version 2011101901
     * @since 2011101901
     */
    public function addCloseSlideElements($element) {
        if (is_array($element))
            $this->_closeSlideElements = array_merge($this->_closeSlideElements,
                                                       $element);
        else
            $this->_closeSlideElements[] = $element;
    }
    
    /**
     * Renders a single element ready for output
     * @param HTML_QuickForm_element $element Element to render
     * @param boolean $required Whether this element should be marked as required
     * @param string $error Error message to display for the element
     * @version 2011101901
     * @since 2011101901
     */
    public function renderElement(&$element, $required, $error) {
        if (in_array($element->getName(), $this->_closeSlideElements) &&
            $this->_slideOpen) {
            
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_slideOpen = false;
        }
        
        parent::renderElement($element, $required, $error);
    }

    /**
     * Renders the beginning of the form, ready for output
     * @param HTML_QuickForm $form Form to render
     * @version 2011101901
     * @since 2011101901
     */
    public function startForm(&$form) {        
        parent::startForm($form);
        $this->_html .= $this->_openSlideWrapperTemplate . $this->_html;
    }
    
    /**
     * Renders the end of the form, ready for output
     * @param HTML_QuickForm $form Form to render
     * @version 2011101901
     * @since 2011101901
     */
    public function finishForm(&$form) {
        parent::finishForm($form);
        
        if ($this->_slideOpen)
            $this->_html .= $this->_closeSlideTemplate;
        
        $this->_html .= $this->_closeSlideWrapperTemplate;
    }
}

?>