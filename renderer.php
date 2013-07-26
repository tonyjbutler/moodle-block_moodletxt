<?php

/**
 * File container for moodletxt custom renderer and widgets
 * IMPORTANT: Use Moodle's naming conventions in renderer and
 * associated classes, as reflection is used to process
 * widgets during rendering.
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
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @todo Possibly split widget classes off into their own subdir
 * @todo (Long term) When PHP 5.4 is generally used, traits could be useful in widgets
 * @version 2012100501
 * @since 2011060901
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Custom renderer class for moodletxt plugin
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012052801
 * @since 2011060901
 */
class block_moodletxt_renderer extends plugin_renderer_base {    

    /**
     * Renders a provided icon to the page
     * @param moodletxt_icon $icon Icon to render
     * @return string The generated output code
     * @version 2011061001
     * @since 2011060901
     */
    protected function render_moodletxt_icon (moodletxt_icon $icon) {
        $attributes = $icon->get_attributes();
        $attributes['src'] = $icon->get_icon();
        return html_writer::empty_tag('img', $attributes);
    }
    
    /**
     * Renders a provided progress bar (from the jQuery UI library)
     * @param moodletxt_ui_progress_bar $bar Progress bar to render
     * @return string The generated output code
     * @version 2011061001
     * @since 2011061001
     */
    protected function render_moodletxt_ui_progress_bar(moodletxt_ui_progress_bar $bar) {
        
        // Build from inside out
        $textbox = html_writer::tag('div', '', $bar->get_textbox_attributes());
        $progressBar = html_writer::tag('div', $textbox, $bar->get_bar_attributes());
        return $progressBar;
        
    }

    /**
     * Renders a dialog box (from the jQuery UI library)
     * @param moodletxt_ui_dialog $dialog Dialog widget to render
     * @return string Generated output code
     * @version 2011061001
     * @since 2011061001
     */
    protected function render_moodletxt_ui_dialog(moodletxt_ui_dialog $dialog) {
        
        $dialogBox = html_writer::tag('div', $dialog->getContent(), $dialog->getAttributes());
        return $dialogBox;
        
    }
    
    /**
     * Renders a slide form navigator set
     * @param moodletxt_slide_form_navigator $navigator Navigator to render
     * @return moodletxt_slide_form_navigator Rendered navigator
     * @version 2011102001
     * @since 2011101901
     */
    protected function render_moodletxt_slide_form_navigator(moodletxt_slide_form_navigator $navigator) {
        
        $listItems = '';
        
        foreach($navigator->getNavItems() as $navId => $navName)
            $listItems .= html_writer::tag('li', $navName, array('id' => $navId));
        
        $list = html_writer::tag('ul', $listItems);
        $navigator = html_writer::tag('div', $list, $navigator->getAttributes());
        
        return $navigator;
        
    }
    
    /**
     * Renders a message tag link to the screen
     * @param moodletxt_message_tag_link $tag The tag link to render
     * @return string Rendered HTML for the tag link widget
     * @version 2012052201
     * @since 2012051401
     */
    protected function render_moodletxt_message_tag_link(moodletxt_message_tag_link $tag) {
        
        $htmlAttributes = $tag->get_attributes();
        
        $htmlAttributes['class'] = $tag->get_attribute('class') . ' mtxtTag';    
        $htmlAttributes['style'] = $tag->get_attribute('style') . ' background-color:' . $tag->get_colour() . ';';
        
        // Work out size according to weight
        $htmlAttributes['style'] .= ' font-size:' . (50 + floor($tag->get_weight() / 2)) . '%;';
        
        $tagLink = html_writer::tag('a', $tag->get_name(), array('href' => '#' . $tag->get_safe_name()));
        $tagSpan = html_writer::tag('span', $tagLink, $htmlAttributes);
        
        return $tagSpan;
        
    }

    /**
     * Renders a message tag cloud 
     * @param moodletxt_message_tag_cloud $cloud The tag cloud to render 
     * @return string Rendered HTML for the tag cloud
     * @version 2012052801
     * @since 2012051401
     */
    protected function render_moodletxt_message_tag_cloud(moodletxt_message_tag_cloud $cloud) {
        
        $htmlAttributes = $cloud->get_attributes();        
        $htmlAttributes['class'] = $cloud->get_attribute('class') . ' mtxtTagCloud';
        
        $tagHTML = '';
        foreach($cloud->get_tag_widgets() as $widget) {
            $tagHTML .= $this->render_moodletxt_message_tag_link($widget);
        }
        
        $tagIcon = $this->render_moodletxt_icon(new moodletxt_icon(moodletxt_icon::$ICON_TAG, $cloud->get_title()));
        
        $header = html_writer::tag('h2', $tagIcon . $cloud->get_title());
        $header .= html_writer::tag('p', get_string('labeltagdraganddrop', 'block_moodletxt'));
        $para = html_writer::tag('p', $tagHTML, array('class' => 'tags'));
        
        $inputBox = html_writer::empty_tag('input', array('type' => 'text', 'width' => '20', 'name' => 'cloudNewTag'));
        $newTagContent = get_string('labelnewtag', 'block_moodletxt') . $inputBox .$this->render_moodletxt_icon(
                new moodletxt_icon(moodletxt_icon::$ICON_ADD, get_string('altaddtag', 'block_moodletxt'), array('class' => 'mdltxtAddTagButton')));
        $newTagDiv = html_writer::tag('div', $newTagContent, array('id' => 'cloudNewTagForm'));
        
        return html_writer::tag('div', $header . $para . $newTagDiv, $htmlAttributes);
        
    }
    
}

/**
 * Widget implementation for icons within moodletxt
 * (According to pix_icon's docs it cannot support blocks)
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012100501
 * @since 2011060901
 */
class moodletxt_icon implements renderable {
    
    /**
     * Path to the loading icon shown during AJAX transactions
     * @var string
     */
    public static $AJAX_LOADING = 'pix/icons/ajax-loader.gif';
    
    /**
     * Path to the icon shown when account access is denied
     * @var string
     */
    public static $ICON_ACCESS_DENIED = 'pix/icons/access_denied.png';
    
    /**
     * Path to the icon shown for editing account access
     * @var string
     */
    public static $ICON_ACCESS_EDIT = 'pix/icons/access_edit.png';
    
    /**
     * Path to the "add" icon used when adding elements to forms
     * @var string
     */
    public static $ICON_ADD = 'pix/icons/add.png';
    
    /**
     * Path to the icon shown in menus/navigation to represent the addressbook
     * @var string
     */
    public static $ICON_ADDRESSBOOK = 'pix/icons/addressbook.png';
    
    /**
     * Path to the icon shown when inbound access is allowed
     * @var string
     */
    public static $ICON_ALLOW_INBOUND = 'pix/icons/allow_inbound.png';
    
    /**
     * Path to the icon shown when outbound access is allowed
     * @var string
     */
    public static $ICON_ALLOW_OUTBOUND = 'pix/icons/allow_outbound.png';
    
    /**
     * Path to the "delete" icon used when removing elements from forms
     * @var string
     */
    public static $ICON_DELETE = 'pix/icons/delete.png';
    
    /**
     * Path to the generic "edit" icon used for a general-purpose edit button
     * @var string
     */
    public static $ICON_EDIT = 'pix/icons/edit.png';
    
    /**
     * Path to the icon shown in menus/navigation to represent the compose page
     * @var string
     */
    public static $ICON_MESSAGE_COMPOSE = 'pix/icons/message_compose.png';
    
    /**
     * Path to the icon shown for the delete action on a message
     * @var string
     */
    public static $ICON_MESSAGE_DELETE = 'pix/icons/message_delete.png';
    
    /**
     * Path to the icon shown for the reply action on a message
     * @var string
     */
    public static $ICON_MESSAGE_REPLY = 'pix/icons/message_reply.png';
    
    /**
     * Path to the icon shown in menus/navigations to represent the inbox page
     * @var string
     */
    public static $ICON_MESSAGES_INBOX = 'pix/icons/messages_inbox.png';
    
    /**
     * Path to the icon shown in menus/navigation to represent the sent messages page
     * @var string
     */
    public static $ICON_MESSAGES_SENT = 'pix/icons/messages_sent.png';
    
    /**
     * Path to the icon shown in menus/navigation to represent the statistics page
     * @var string
     */
    public static $ICON_STATS = 'pix/icons/stats.png';
    
    /**
     * Path to the icon shown in menus/navigation to represent the user settings page
     * @var string
     */
    public static $ICON_PREFERENCES = 'pix/icons/preferences.png';
    
    /**
     * Path to the icon shown in menus/navigation to represent the settings page
     * @var string
     */
    public static $ICON_SETTINGS = 'pix/icons/settings.png';
    
    /**
     * Path to the icon shown when a task has successfully completed
     * @var string
     */
    public static $ICON_SUCCESS = 'pix/icons/ok.png';
    
    /**
     * Path to the icon shown when there is a warning or error to display
     * @var string
     */
    public static $ICON_WARNING = 'pix/icons/warning.png';
    
    /**
     * Path to the icon representing a message that has failed to be delivered
     * @var string
     */
    public static $ICON_STATUS_FAILED = 'pix/icons/status_failed.gif';
    
    /**
     * Path to the icon representing a message currently in transit
     * @var string
     */
    public static $ICON_STATUS_TRANSIT = 'pix/icons/status_transit.gif';
    
    /**
     * Path to the icon representing a message that has been delivered
     * @var string
     */
    public static $ICON_STATUS_DELIVERED = 'pix/icons/status_delivered.gif';
    
    /**
     * Path to the icon representing a message tag
     * @var string
     */
    public static $ICON_TAG = 'pix/icons/tag.png';
    
    /**
     * Holds which of the pre-defined icons to use
     * @var string
     */
    private $icon;
    
    /**
     * XHTML attributes to add to the icon
     * @var array(string => string)
     */
    private $attributes;
    
    /**
     * Sets up the icon widget 
     * @param string $icon Icon from predefined paths
     * @param string $alt Alternate text for icon
     * @param array(string => string) $attributes Other XHTML attributes
     * @version 2011061001
     * @since 2011060901
     */
    function __construct($icon, $alt, $attributes = array()) {
        
        $this->set_icon($icon);
        $this->set_attributes($attributes);
        
        if (! $this->attribute_exists('alt'))
            $this->set_attribute ('alt', $alt);
        
        if (! $this->attribute_exists('title'))
            $this->set_attribute ('title', $this->get_attribute ('alt'));
        
        if (! $this->attribute_exists('width'))
            $this->set_attribute ('width', '16');
        
        if (! $this->attribute_exists('height'))
            $this->set_attribute ('height', '16');
        
    }
    
    /**
     * Set the icon image to use
     * @param string $icon Icon from predefined paths
     * @version 2011061001
     * @since 2011060901
     */
    public function set_icon($icon) {
        $this->icon = $icon;
    }

    /**
     * Returns the path of the icon image to use
     * @global object $CFG Moodle config object
     * @return string Icon path
     * @version 2011071901
     * @since 2011060901
     */
    public function get_icon() {
        global $CFG;
        return $CFG->wwwroot . '/blocks/moodletxt/' . $this->icon;
    }

    /**
     * Sets the XHTML attributes for this icon widget
     * @param array(string => string) $attributes XHTML attributes
     * @version 2011061001
     * @since 2011060901
     */
    public function set_attributes($attributes = array()) {
        if (is_array($attributes))
            $this->attributes = $attributes;
    }

    /**
     * Returns the XHTML attributes for this icon widget
     * @return array(string => string)
     * @version 2011061001
     * @since 2011060901
     */
    public function get_attributes() {
        return $this->attributes;
    }

    /**
     * Sets an individual XHTML attribute for the widget
     * @param string $key Attribute to set
     * @param string $val Value of attribute
     * @version 2011061001
     * @since 2011060901
     */
    public function set_attribute($key, $val) {
        $this->attributes[$key] = $val;
    }

    /**
     * Gets a single attribute from the icon's set
     * @param string $key Attribute to fetch
     * @return string Value of attribute
     * @version 2011070501
     * @since 2011060901
     */
    public function get_attribute($key) {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : '';
    }

    /**
     * Checks whether the named attribute exists within this widget
     * @param string $key Attribute to check for
     * @return boolean Attribute exists?
     * @version 2011061001
     * @since 2011060901
     */
    public function attribute_exists($key) {
        // Putting this in shorthand causes a fatal error
        // No, I don't know how
        if (isset($this->attributes[$key]))
            return true;
        else
            return false;
    }
    
}

/**
 * Widget implementation for a message tag link
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012052201
 * @since 2012051401
 */
class moodletxt_message_tag_link implements renderable {
    
    /**
     * Custom HTML attributes to apply to the widget
     * @var array(string => string)
     */
    private $attributes = array();
    
    /**
     * Name of the tag
     * @var string
     */
    private $name = '';
    
    /**
     * CSS-safe version of the tag's name
     * @var string
     */
    private $safe_name = '';
    
    /**
     * CSS colour code for highlighting the tag/associated messages
     * @var string
     */
    private $colour = '#ffffff';
    
    /**
     * Tag's weighting within a tag list
     * based on how many messages are applied against this tag
     * @var int
     */
    private $weight;
    
    /**
     * Sets up the widget with an initial data set
     * @param string $name The tag's name
     * @param string $safeName CSS-safe version of the tag's name
     * @param int $weight Tag's list weighting
     * @param string $bgColour Tag highlighting CSS colour value
     * @param array $attributes Custom HTML attributes
     * @version 2012052201
     * @since 2012051401
     */
    public function __construct($name, $safeName, $weight, $bgColour = '#ffffff', array $attributes = array()) {
        $this->set_attributes($attributes);
        $this->set_name($name);
        $this->set_safe_name($safeName);
        $this->set_weight($weight);
        $this->set_colour($bgColour);
    }

    /**
     * Returns the custom HTML attributes for this widget
     * @return array(string => string) HTML attributes
     * @version 2012051401
     * @since 2012051401
     */
    public function get_attributes() {
        return $this->attributes;
    }

    /**
     * Sets the custom HTML attributes for this widget
     * @param array(string => string) $attributes HTML attributes
     * @version 2012051401
     * @since 2012051401
     */
    public function set_attributes(array $attributes) {
        $this->attributes = $attributes;
    }

    /**
     * Returns the name of this tag
     * @return Tag name
     * @version 2012051401
     * @since 2012051401
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Sets the name of this tag
     * @param string $name Tag name
     * @version 2012051401
     * @since 2012051401
     */
    public function set_name($name) {
        $this->name = $name;
    }
    
    /**
     * Returns a CSS identifier safe version of the tag's name
     * @return string CSS-safe tag name
     * @version 2012052201
     * @since 2012052201
     */
    public function get_safe_name() {
        return $this->safe_name;
    }

    /**
     * Sets a CSS identifier safe version of the tag's name
     * @param string $safe_name CSS-safe tag name
     * @version 2012052201
     * @since 2012052201
     */
    public function set_safe_name($safe_name) {
        $this->safe_name = $safe_name;
    }
        
    /**
     * Returns the CSS colour code for highlighting this tag
     * @return string CSS hex colour code
     * @version 2012051401
     * @since 2012051401
     */
    public function get_colour() {
        return $this->colour;
    }

    /**
     * Sets the CSS colour code for highlighting this tag
     * @param string $colour CSS hex colour code
     * @version 2012051401
     * @since 2012051401
     */
    public function set_colour($colour) {
        $this->colour = $colour;
    }

    /**
     * Returns the list weighting of this tag
     * @return int List weighting
     * @version 2012051501
     * @since 2012051401
     */
    public function get_weight() {
        return $this->weight;
    }

    /**
     * Sets the list weighting of this tag
     * @param int $weight List weighting
     * @version 2012051501
     * @since 2012051401
     */
    public function set_weight($weight) {
        $this->weight = $weight;
    }
    
    /**
     * Sets an individual XHTML attribute for the widget
     * @param string $key Attribute to set
     * @param string $val Value of attribute
     * @version 2012051401
     * @since 2012051401
     */
    public function set_attribute($key, $val) {
        $this->attributes[$key] = $val;
    }

    /**
     * Gets a single attribute from the icon's set
     * @param string $key Attribute to fetch
     * @return string Value of attribute
     * @version 2012051401
     * @since 2012051401
     */
    public function get_attribute($key) {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : '';
    }

    /**
     * Checks whether the named attribute exists within this widget
     * @param string $key Attribute to check for
     * @return boolean Attribute exists?
     * @version 2012051401
     * @since 2012051401
     */
    public function attribute_exists($key) {
        // Putting this in shorthand causes a fatal error
        // No, I don't know how
        if (isset($this->attributes[$key]))
            return true;
        else
            return false;
    }
    
}

/**
 * Widget implementation for a message tag cloud
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012051401
 * @since 2012051401 
 */
class moodletxt_message_tag_cloud implements renderable {

    /**
     * Title of the tag cloud
     * @var string
     */
    private $title;
    
    /**
     * Child tag widgets to be rendered inside this cloud
     * @var moodletxt_message_tag_link[]
     */
    private $tag_widgets = array();
    
    /**
     * Custom HTML attributes for the tag cloud
     * @var array(string => string)
     */
    private $attributes = array();
    
    /**
     * Sets up the widget with an inital set of properties
     * @param string $title Cloud title
     * @param moodletxt_message_tag_link[] $tagWidgets Tag links contained within cloud
     * @param array(string => string) $attributes Custom HTML attributes
     * @version 2012051401
     * @since 2012051401 
     */
    public function __construct($title, array $tagWidgets = array(), array $attributes = array()) {
        $this->set_attributes($attributes);
        $this->set_title($title);
        $this->set_tag_widgets($tagWidgets);
    }
    
    /**
     * Returns the title of this tag cloud
     * @return string Cloud title
     * @version 2012051401
     * @since 2012051401 
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Sets the title of this tag cloud
     * @param string $title Cloud title
     * @version 2012051401
     * @since 2012051401 
     */
    public function set_title($title) {
        $this->title = $title;
    }
    
    /**
     * Returns the set of tag link widgets contained within this cloud
     * @return moodletxt_message_tag_link[] Tag link widgets
     * @version 2012051401
     * @since 2012051401 
     */
    public function get_tag_widgets() {
        return $this->tag_widgets;
    }

    /**
     * Sets the tag link widgets that will be contained within this cloud
     * @param moodletxt_message_tag_link[] $tag_widgets Tag link widgets
     * @version 2012051401
     * @since 2012051401 
     */
    public function set_tag_widgets(array $tag_widgets) {
        $this->tag_widgets = $tag_widgets;
    }

    /**
     * Add a single tag link widget to the tag cloud
     * @param moodletxt_message_tag_link $widget Tag link widget
     * @version 2012051401
     * @since 2012051401 
     */
    public function add_tag_widget(moodletxt_message_tag_link $widget) {
        $this->tag_widgets[] = $widget;
    }
    
    /**
     * Returns a set of custom HTML attributes for this widget
     * @return array(string => string) HTML attributes
     * @version 2012051401
     * @since 2012051401 
     */
    public function get_attributes() {
        return $this->attributes;
    }

    /**
     * Sets the custom HTML attributes for this widget
     * @param array(string => string) $attributes HTML attributes
     * @version 2012051401
     * @since 2012051401 
     */
    public function set_attributes(array $attributes) {
        $this->attributes = $attributes;
    }
    
    /**
     * Sets an individual XHTML attribute for the widget
     * @param string $key Attribute to set
     * @param string $val Value of attribute
     * @version 2012051401
     * @since 2012051401
     */
    public function set_attribute($key, $val) {
        $this->attributes[$key] = $val;
    }

    /**
     * Gets a single attribute from the icon's set
     * @param string $key Attribute to fetch
     * @return string Value of attribute
     * @version 2012051401
     * @since 2012051401
     */
    public function get_attribute($key) {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : '';
    }

    /**
     * Checks whether the named attribute exists within this widget
     * @param string $key Attribute to check for
     * @return boolean Attribute exists?
     * @version 2012051401
     * @since 2012051401
     */
    public function attribute_exists($key) {
        // Putting this in shorthand causes a fatal error
        // No, I don't know how
        if (isset($this->attributes[$key]))
            return true;
        else
            return false;
    }
    
}

/**
 * Widget implementation for the jQuery UI progress bar
 * utilised by moodletxt. Abstracted as a custom widget
 * to ensure the structure is the same across all installations
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011061001
 * @since 2011061001
 */
class moodletxt_ui_progress_bar implements renderable {

    /**
     * XHTML attributes for the outer box of the progress bar
     * @var array(string => string)
     */
    private $bar_attributes = array();
    
    /**
     * XHTML attributes for the inner progress textbox on the bar
     * @var array(string => string)
     */
    private $textbox_attributes = array();
    
    /**
     * Sets up the progress bar widget
     * @param string $bar_id DOM ID for the progress bar
     * @param string $textbox_id DOM ID for the internal textbox
     * @param string $bar_classes Style classes for the progress bar
     * @param string $textbox_classes Style classes for the internal textbox
     * @param string $bar_attributes XHTML attributes for the progress bar
     * @param string $textbox_attributes XHTML attributes for the internal textbox
     * @version 2011061001
     * @since 2011061001
     */
    function __construct($bar_id, $textbox_id, $bar_classes = '', $textbox_classes = '', 
            $bar_attributes = array(), $textbox_attributes = array()) {
        
        $this->set_bar_attributes($bar_attributes);
        $this->set_bar_id($bar_id);
        $this->set_bar_classes($bar_classes);

        $this->set_textbox_attributes($textbox_attributes);
        $this->set_textbox_id($textbox_id);
        $this->set_textbox_classes($textbox_classes);
        
    }

    /**
     * Returns the DOM ID of the outer progress bar 
     * @return string DOM ID
     * @version 2011061501
     * @since 2011061001
     */
    public function get_bar_id() {
        return $this->get_bar_attribute('id');
    }

    /**
     * Sets the DOM ID of the outer progress bar
     * @param string $bar_id DOM ID
     * @version 2011061501
     * @since 2011061001
     */
    public function set_bar_id($bar_id) {
        $this->set_bar_attribute('id', $bar_id);
    }

    /**
     * Returns the DOM ID of the internal textbox
     * @return string DOM ID
     * @version 2011061501
     * @since 2011061001
     */
    public function get_textbox_id() {
        return $this->get_textbox_attribute('id');
    }

    /**
     * Sets the DOM ID of the internal textbox
     * @param string $textbox_id DOM ID
     * @version 2011061501
     * @since 2011061001
     */
    public function set_textbox_id($textbox_id) {
        $this->set_textbox_attribute('id', $textbox_id);
    }

    /**
     * Returns the CSS classes for the progress bar
     * @return string CSS class string
     * @version 2011061501
     * @since 2011061001
     */
    public function get_bar_classes() {
        return $this->get_bar_attribute('class');
    }

    /**
     * Sets the CSS classes for the progress bar
     * @param string $bar_classes CSS class string
     * @version 2011061501
     * @since 2011061001
     */
    public function set_bar_classes($bar_classes) {
        $this->set_bar_attribute('class', 'ui-progressbar ' . $bar_classes);
    }

    /**
     * Returns the CSS classes for the internal textbox
     * @return string CSS class string
     * @version 2011061501
     * @since 2011061001
     */
    public function get_textbox_classes() {
        return $this->get_textbox_attribute('class');
    }

    /**
     * Sets the CSS classes for the internal textbox
     * @param string $textbox_classes CSS class string
     * @version 2011061501
     * @since 2011061001
     */
    public function set_textbox_classes($textbox_classes) {
        $this->set_textbox_attribute('class', 'ui-progressbar-textvalue ' . $textbox_classes);
    }

    /**
     * Returns the XHTML attribute set for the progress bar
     * @return array(string => string) XHTML attribute set
     * @version 2011061001
     * @since 2011061001
     */
    public function get_bar_attributes() {
        return $this->bar_attributes;
    }

    /**
     * Sets the XHTML attribute set for the progress bar
     * @param array(string => string) $attributes XHTML attribute set
     * @version 2011061001
     * @since 2011061001
     */
    public function set_bar_attributes($attributes = array()) {
        $this->bar_attributes = $attributes;
    }

    /**
     * Returns the value of a single XHTML attribute for the progress bar
     * @param string $key XHTML attribute to fetch
     * @return string Attribute value
     * @version 2011061001
     * @since 2011061001
     */
    public function get_bar_attribute($key) {
        return $this->bar_attributes[$key];
    }

    /**
     * Sets a single XHTML attribute for the progress bar
     * @param string $key XHTML attribute
     * @param string $val Attribute value
     * @version 2011061001
     * @since 2011061001
     */
    public function set_bar_attribute($key, $val) {
        $this->bar_attributes[$key] = $val;
    }

    /**
     * Check that a given XHTML attribute exists on the progress bar
     * @param string $key XHTML attribute to check
     * @return boolean Attribute exists?
     * @version 2011061001
     * @since 2011061001
     */
    public function bar_attribute_exists($key) {
        // Done long-winded to prevent rendering glitch
        if (isset($this->bar_attributes[$key]))
            return true;
        else
            return false;
    }

    /**
     * Returns the XHTML attributes for the internal textbox
     * @return array(string => string) XHTML attribute set
     * @version 2011061001
     * @since 2011061001
     */
    public function get_textbox_attributes() {
        return $this->textbox_attributes;
    }

    /**
     * Sets the XHTML attributes for the internal textbox
     * @param array(string => string) $attributes XHTML attribute set
     * @version 2011061001
     * @since 2011061001
     */
    public function set_textbox_attributes($attributes = array()) {
        $this->textbox_attributes = $attributes;
    }

    /**
     * Gets a specified XHTML attribute from the internal textbox
     * @param string $key XHTML attribute identifier
     * @return string Attribute value
     * @version 2011061001
     * @since 2011061001
     */
    public function get_textbox_attribute($key) {
        return $this->textbox_attributes[$key];
    }

    /**
     * Sets a specified XHTML attribute for the internal textbox
     * @param string $key XHTML attribute identifier
     * @param string $val Attribute value
     * @version 2011061001
     * @since 2011061001
     */
    public function set_textbox_attribute($key, $val) {
        $this->textbox_attributes[$key] = $val;
    }

    /**
     * Checks that a given XHTML attribute exists for the internal textbox
     * @param string $key XHTML attribute identifier
     * @return string Attribute exists?
     * @version 2011061001
     * @since 2011061001
     */
    public function textbox_attribute_exists($key) {
        // Done long-winded to avoid rendering glitch
        if (isset($this->textbox_attributes[$key]))
            return true;
        else
            return false;
    }
    
}

/**
 * Widget implementation for the jQuery UI dialog box
 * utilised by moodletxt. Abstracted as a custom widget
 * to ensure the structure is the same across all installations
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011061001
 * @since 2011061001
 */
class moodletxt_ui_dialog implements renderable {
    
    /**
     * XHTML attributes for the dialog box
     * @var array(string => string)
     */
    private $attributes;

    /**
     * Pre-rendered content to include within the dialog
     * @var string
     */
    private $content;
    
    /**
     * Sets up the dialog box
     * @param string $id DOM ID of dialog box
     * @param string $content HTML content for dialog
     * @param string $title Title of the dialog box
     * @param string $classes CSS class string
     * @param array(string => string) $attributes HTML attributes for dialog box
     * @version 2011061001
     * @since 2011061001
     */
    public function __construct($id, $content, $title = '', $classes = '', $attributes = array()) {

        $this->setAttributes($attributes);
        $this->setId($id);
        $this->setContent($content);
        $this->setTitle($title);
        $this->setClasses($classes);
        
    }
    
    /**
     * Returns HTML attributes for the dialog box
     * @return array(string => string) HTML attributes for dialog box
     * @version 2011061001
     * @since 2011061001
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Sets HTML attributes for the dialog box
     * @param array(string => string) $attributes HTML attributes for dialog box
     * @version 2011061001
     * @since 2011061001
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
    }

    /**
     * Sets an individual HTML attribute for the dialog box
     * @param string $key Name of attribute
     * @param string $val Value of attribute
     * @version 2011061001
     * @since 2011061001
     */
    public function setAttribute($key, $val) {
        $this->attributes[$key] = $val;
    }
    
    /**
     * Returns an individual HTML attribute from the dialog box
     * @param string $key Name of attribute
     * @return string Value of attribute
     * @version 2011061001
     * @since 2011061001
     */
    public function getAttribute($key) {
        return $this->attributes[$key];
    }

    /**
     * Returns the HTML content of the dialog box
     * @return string HTML string
     * @version 2011061001
     * @since 2011061001
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Sets the HTML content of the dialog box
     * @param string $content HTML string
     * @version 2011061001
     * @since 2011061001
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * Sets the CSS class string for the dialog box
     * @param string $classString CSS classes
     * @version 2011061001
     * @since 2011061001
     */
    public function setClasses($classString) {
        $this->setAttribute('class', $classString);
    }
    
    /**
     * Returns the CSS class string for the dialog box
     * @return string CSS classes
     * @version 2011061001
     * @since 2011061001
     */
    public function getClasses() {
        return $this->getAttribute('class');
    }

    /**
     * Sets the title of the dialog box
     * @param string $title Dialog box title
     * @version 2011061001
     * @since 2011061001
     */
    public function setTitle($title) {
        $this->setAttribute('title', $title);
    }

    /**
     * Returns the title of the dialog box
     * @return string Dialog box title
     * @version 2011061001
     * @since 2011061001
     */
    public function getTitle() {
        return $this->getAttribute('title');
    }
    
    /**
     * Sets the DOM ID of the dialog box
     * @param string $id DOM ID
     * @version 2011061001
     * @since 2011061001
     */
    public function setId($id) {
        $this->setAttribute('id', $id);
    }
    
    /**
     * Returns the DOM ID of the dialog box
     * @return string DOM ID
     * @version 2011061001
     * @since 2011061001
     */
    public function getId() {
        return $this->getAttribute('id');
    }
    
}

/**
 * Widget implementation for the navigation section of a
 * sliding content form
 * @see MoodleQuickFormWihSlides
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011101901
 * @since 2011101901
 */
class moodletxt_slide_form_navigator implements renderable {
    
    /**
     * HTML attributes
     * @var array
     */
    private $attributes = array();
    
    /**
     * Holds items of navigation
     * @var array
     */
    private $navItems = array();
    
    /**
     * Sets up the navigator 
     * @param string $id Element ID of the navigator
     * @param array $navItems Set of navigations items (id => content)
     * @param string $classes CSS class string
     * @param array $attributes HTML attributes (attr => value)
     * @version 2011101901
     * @since 2011101901
     */
    public function __construct($id, array $navItems, $classes = '', array $attributes = array()) {
        $this->setAttributes($attributes); // Set attributes first to prevent overwrite
        $this->setId($id);
        $this->setClasses($classes);
        $this->setNavItems($navItems);
    }
    
    /**
     * Returns HTML attributes for the navigator
     * @return array(string => string) HTML attributes for navigator
     * @version 2011101901
     * @since 2011101901
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Sets HTML attributes for the navigator
     * @param array(string => string) $attributes HTML attributes for navigator
     * @version 2011101901
     * @since 2011101901
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
    }

    /**
     * Sets an individual HTML attribute for the navigator
     * @param string $key Name of attribute
     * @param string $val Value of attribute
     * @version 2011101901
     * @since 2011101901
     */
    public function setAttribute($key, $val) {
        $this->attributes[$key] = $val;
    }
    
    /**
     * Returns an individual HTML attribute from the navigator
     * @param string $key Name of attribute
     * @return string Value of attribute
     * @version 2011101901
     * @since 2011101901
     */
    public function getAttribute($key) {
        return $this->attributes[$key];
    }

    /**
     * Returns the individual items within the navigator
     * @return array Navigation items
     * @version 2011101901
     * @since 2011101901
     */
    public function getNavItems() {
        return $this->navItems;
    }
    
    /**
     * Sets the individual items within the navigator
     * @param array $navItems Navigation items
     * @version 2011101901
     * @since 2011101901
     */
    public function setNavItems($navItems) {
        $this->navItems = $navItems;
    }
    
    /**
     * Sets the CSS class string for the navigator
     * @param string $classString CSS classes
     * @version 2011101901
     * @since 2011101901
     */
    public function setClasses($classString) {
        $this->setAttribute('class', $classString);
    }
    
    /**
     * Returns the CSS class string for the navigator
     * @return string CSS classes
     * @version 2011101901
     * @since 2011101901
     */
    public function getClasses() {
        return $this->getAttribute('class');
    }
    
    /**
     * Sets the DOM ID of the navigator
     * @param string $id DOM ID
     * @version 2011101901
     * @since 2011101901
     */
    public function setId($id) {
        $this->setAttribute('id', $id);
    }
    
    /**
     * Returns the DOM ID of the navigator
     * @return string DOM ID
     * @version 2011101901
     * @since 2011101901
     */
    public function getId() {
        return $this->getAttribute('id');
    }
    
}

?>