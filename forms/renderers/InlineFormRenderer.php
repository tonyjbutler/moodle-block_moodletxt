<?php

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/lib/pear/HTML/QuickForm/Renderer/Default.php');
require_once($CFG->dirroot . '/lib/pear/HTML/QuickForm/Renderer/Tableless.php');

/**
 * A renderer for MoodleQuickForm that only uses XHTML and CSS and no
 * table tags, extends PEAR class HTML_QuickForm_Renderer_Tableless
 *
 * Stylesheet is part of standard theme and should be automatically included.
 * 
 * Modified by Greg J Preece for inline rendering
 *
 * @package   moodlecore
 * @copyright Jamie Pratt <me@jamiep.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version 2013010901
 * @since 2012031701
 */
class InlineFormRenderer extends HTML_QuickForm_Renderer_Tableless {

    /**
    * Element template array
    * @var      array
    * @access   private
    */
    var $_elementTemplates;
    /**
    * Template used when opening a hidden fieldset
    * (i.e. a fieldset that is opened when there is no header element)
    * @var      string
    * @access   private
    */
    var $_openHiddenFieldsetTemplate = "\n\t<fieldset class=\"mtxtFieldsetHidden mtxtInline\"><ul class=\"mtxtInlineFormElements\">";
   /**
    * Header Template string
    * @var      string
    * @access   private
    */
    var $_headerTemplate =
       "\n\t\t<legend class=\"ftoggler\">{header}</legend>\n\t\t<div class=\"advancedbutton\">{advancedimg}{button}</div><div class=\"fcontainer clearfix\">\n\t\t";

   /**
    * Template used when opening a fieldset
    * @var      string
    * @access   private
    */
    var $_openFieldsetTemplate = "\n\t<fieldset class=\"clearfix mtxtInline\" {id}><ul class=\"mtxtInlineFormElements\">";

    /**
    * Template used when closing a fieldset
    * @var      string
    * @access   private
    */
    var $_closeFieldsetTemplate = "\n\t\t</ul></fieldset>";

   /**
    * Required Note template string
    * @var      string
    * @access   private
    */
    var $_requiredNoteTemplate =
        "\n\t\t<div class=\"fdescription required\">{requiredNote}</div>";

    var $_advancedElements = array();

    /**
     * Whether to display advanced elements (on page load)
     *
     * @var integer 1 means show 0 means hide
     */
    var $_showAdvanced;

    /**
     * Initialises templates for the various element types
     * that can be rendered by this class
     * @version 2013010901
     * @since 2012031701
     */
    function __construct(){
        
        $this->_elementTemplates = array(
            
        'default' => "\n\t\t".'
<li>
    <label>{label}{help}<!-- BEGIN required -->{req}<!-- END required --></label>
    <!-- BEGIN error --><span class="error">{error}</span><!-- END error -->
    {element}
</li>',
            
        'actionbuttons'=>"\n\t\t".'
<div class="fitem {advanced}">
    <div class="fitemtitle">
        <div class="fstaticlabel">
            <label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} {help}</label>
        </div>
    </div>
    <div class="felement fstatic <!-- BEGIN error --> error<!-- END error -->">
        <!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->
        {element}&nbsp;
    </div>
</div>',        

        'fieldset'=>"\n\t\t".'
<li>
    <label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} {help}</label>
    <!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->
    {element}
</li>',

        'static'=>"\n\t\t".'
<div class="fitem {advanced}">
    <div class="fitemtitle">
        <div class="fstaticlabel">
            <label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} {help}</label>
        </div>
    </div>
    <div class="felement fstatic <!-- BEGIN error --> error<!-- END error -->">
        <!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->
        {element}&nbsp;
    </div>
</div>',

        'submit'=>"\n\t\t".'
<div class="fitem {advanced}">
    <div class="fitemtitle">
        <div class="fstaticlabel">
            <label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} {help}</label>
        </div>
    </div>
    <div class="felement fstatic <!-- BEGIN error --> error<!-- END error -->">
        <!-- BEGIN error --><span class="error">{error}</span><br /><!-- END error -->
        {element}&nbsp;
    </div>
</div>',

        'warning'=>"\n\t\t".'<div class="fitem {advanced}">{element}</div>',

        'nodisplay'=>'');

        parent::HTML_QuickForm_Renderer_Tableless();
    }

    /**
     * @param array $elements
     */
    function setAdvancedElements($elements){
        $this->_advancedElements = $elements;
    }

    /**
     * What to do when starting the form
     *
     * @param object $form MoodleQuickForm
     */
    function startForm(&$form){
        $this->_reqHTML = $form->getReqHTML();
        $this->_elementTemplates = str_replace('{req}', $this->_reqHTML, $this->_elementTemplates);
        $this->_advancedHTML = $form->getAdvancedHTML();
        $this->_showAdvanced = $form->getShowAdvanced();
        parent::startForm($form);
        if ($form->isFrozen()){
            $this->_formTemplate = "\n<div class=\"mform frozen\">\n{content}\n</div>";
        } else {
            $this->_formTemplate = "\n<form{attributes}>\n\t{hidden}\n{content}\n</form>";
            $this->_hiddenHtml .= $form->_pageparams;
        }


    }

    /**
     * @param object $group Passed by reference
     * @param mixed $required
     * @param mixed $error
     */
    function startGroup(&$group, $required, $error){
        if (method_exists($group, 'getElementTemplateType')){
            $html = $this->_elementTemplates[$group->getElementTemplateType()];
        }else{
            $html = $this->_elementTemplates['default'];

        }
        if ($this->_showAdvanced){
            $advclass = ' advanced';
        } else {
            $advclass = ' advanced hide';
        }
        if (isset($this->_advancedElements[$group->getName()])){
            $html =str_replace(' {advanced}', $advclass, $html);
            $html =str_replace('{advancedimg}', $this->_advancedHTML, $html);
        } else {
            $html =str_replace(' {advanced}', '', $html);
            $html =str_replace('{advancedimg}', '', $html);
        }
        if (method_exists($group, 'getHelpButton')){
            $html =str_replace('{help}', $group->getHelpButton(), $html);
        }else{
            $html =str_replace('{help}', '', $html);
        }
        $html =str_replace('{name}', $group->getName(), $html);
        $html =str_replace('{type}', 'fgroup', $html);

        $this->_templates[$group->getName()]=$html;
        // Fix for bug in tableless quickforms that didn't allow you to stop a
        // fieldset before a group of elements.
        // if the element name indicates the end of a fieldset, close the fieldset
        if (   in_array($group->getName(), $this->_stopFieldsetElements)
            && $this->_fieldsetsOpen > 0
           ) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }
        parent::startGroup($group, $required, $error);
    }
    /**
     * @param object $element
     * @param mixed $required
     * @param mixed $error
     */
    function renderElement(&$element, $required, $error){
        //manipulate id of all elements before rendering
        if (!is_null($element->getAttribute('id'))) {
            $id = $element->getAttribute('id');
        } else {
            $id = $element->getName();
        }
        //strip qf_ prefix and replace '[' with '_' and strip ']'
        $id = preg_replace(array('/^qf_|\]/', '/\[/'), array('', '_'), $id);
        if (strpos($id, 'id_') !== 0){
            $element->updateAttributes(array('id'=>'id_'.$id));
        }

        //adding stuff to place holders in template
        //check if this is a group element first
        if (($this->_inGroup) and !empty($this->_groupElementTemplate)) {
            // so it gets substitutions for *each* element
            $html = $this->_groupElementTemplate;
        }
        elseif (method_exists($element, 'getElementTemplateType')){
            $html = $this->_elementTemplates[$element->getElementTemplateType()];
        }else{
            $html = $this->_elementTemplates['default'];
        }
        if ($this->_showAdvanced){
            $advclass = ' advanced';
        } else {
            $advclass = ' advanced hide';
        }
        if (isset($this->_advancedElements[$element->getName()])){
            $html =str_replace(' {advanced}', $advclass, $html);
        } else {
            $html =str_replace(' {advanced}', '', $html);
        }
        if (isset($this->_advancedElements[$element->getName()])||$element->getName() == 'mform_showadvanced'){
            $html =str_replace('{advancedimg}', $this->_advancedHTML, $html);
        } else {
            $html =str_replace('{advancedimg}', '', $html);
        }
        $html =str_replace('{type}', 'f'.$element->getType(), $html);
        $html =str_replace('{name}', $element->getName(), $html);
        if (method_exists($element, 'getHelpButton')){
            $html = str_replace('{help}', $element->getHelpButton(), $html);
        }else{
            $html = str_replace('{help}', '', $html);

        }
        if (($this->_inGroup) and !empty($this->_groupElementTemplate)) {
            $this->_groupElementTemplate = $html;
        }
        elseif (!isset($this->_templates[$element->getName()])) {
            $this->_templates[$element->getName()] = $html;
        }

        parent::renderElement($element, $required, $error);
    }

    /**
     * @global moodle_page $PAGE
     * @param object $form Passed by reference
     */
    function finishForm(&$form){
        global $PAGE;
        if ($form->isFrozen()){
            $this->_hiddenHtml = '';
        }
        parent::finishForm($form);
        if (!$form->isFrozen()) {
            $args = $form->getLockOptionObject();
            if (count($args[1]) > 0) {
                $PAGE->requires->js_init_call('M.form.initFormDependencies', $args, false, moodleform::get_js_module());
            }
        }
    }
   /**
    * Called when visiting a header element
    *
    * @param    object  $header   An HTML_QuickForm_header element being visited
    * @access   public
    * @return   void
    * @global moodle_page $PAGE
    */
    function renderHeader(&$header) {
        global $PAGE;

        $name = $header->getName();

        $id = empty($name) ? '' : ' id="' . $name . '"';
        $id = preg_replace(array('/\]/', '/\[/'), array('', '_'), $id);
        if (is_null($header->_text)) {
            $header_html = '';
        } elseif (!empty($name) && isset($this->_templates[$name])) {
            $header_html = str_replace('{header}', $header->toHtml(), $this->_templates[$name]);
        } else {
            $header_html = str_replace('{header}', $header->toHtml(), $this->_headerTemplate);
        }

        if (isset($this->_advancedElements[$name])){
            $header_html =str_replace('{advancedimg}', $this->_advancedHTML, $header_html);
            $elementName='mform_showadvanced';
            if ($this->_showAdvanced==0){
                $buttonlabel = get_string('showadvanced', 'form');
            } else {
                $buttonlabel = get_string('hideadvanced', 'form');
            }
            $button = '<input name="'.$elementName.'" class="showadvancedbtn" value="'.$buttonlabel.'" type="submit" />';
            $PAGE->requires->js_init_call('M.form.initShowAdvanced', array(), false, moodleform::get_js_module());
            $header_html = str_replace('{button}', $button, $header_html);
        } else {
            $header_html =str_replace('{advancedimg}', '', $header_html);
            $header_html = str_replace('{button}', '', $header_html);
        }

        if ($this->_fieldsetsOpen > 0) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }

        $openFieldsetTemplate = str_replace('{id}', $id, $this->_openFieldsetTemplate);
        if ($this->_showAdvanced){
            $advclass = ' class="advanced"';
        } else {
            $advclass = ' class="advanced hide"';
        }
        if (isset($this->_advancedElements[$name])){
            $openFieldsetTemplate = str_replace('{advancedclass}', $advclass, $openFieldsetTemplate);
        } else {
            $openFieldsetTemplate = str_replace('{advancedclass}', '', $openFieldsetTemplate);
        }
        $this->_html .= $openFieldsetTemplate . $header_html;
        $this->_fieldsetsOpen++;
    } // end func renderHeader

    function getStopFieldsetElements(){
        return $this->_stopFieldsetElements;
    }
}

?>