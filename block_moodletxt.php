<?php

/**
 * File container for block_moodletxt class
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
 * @see block_moodletxt
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013053101
 * @since 2010081801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsReceivedMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/events/MoodletxtCronHandler.php');

/**
 * Main moodletxt block class for display on course pages
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013053101
 * @since 2010081801
 */
class block_moodletxt extends block_list {

    /**
     * Data access object for Moodle users
     * @var MoodletxtMoodleUserDAO
     */
    private $userDAO;
    
    /**
     * Data access object for received messages
     * @var TxttoolsReceivedMessageDAO
     */
    private $receivedMessagesDAO;
    
    /**
     * Class initialiser to set up the display block
     *
     * @version 2011071901
     * @since 2010081801
     */
    public function init() {
        $this->title = get_string('blocktitle', 'block_moodletxt');
        $this->blocktitle = get_string('blocktitle', 'block_moodletxt');
        
        $this->userDAO = new MoodletxtMoodleUserDAO();
        $this->receivedMessagesDAO = new TxttoolsReceivedMessageDAO();
    }

    /**
     * Fetches content for the block when displayed
     * @global Object $CFG Moodle config object
     * @global Object $USER Moodle user object
     * @return string Block content
     * @version 2012071701
     * @since 2010081801
     */
    public function get_content() {

        global $CFG, $USER;

        // If content has already been created, return that
        if ($this->content !== NULL)
            return $this->content;

        // Get renderer
        $output = $this->page->get_renderer('block_moodletxt');
        
        // Get some user details
        $user = $this->userDAO->getUserById($USER->id);
        
        // Initialise content class
        $this->content = new stdClass;

        // Check that specialization has been done
        $this->specialization();

        $userIsAdmin = false;
        $userCanReceive = false;

        // Check for admin
        $userIsAdmin = (has_capability('block/moodletxt:adminsettings', $this->context, $USER->id) ||
                        has_capability('block/moodletxt:adminusers', $this->context, $USER->id));

        // Check that user has send access
        $checkSend = has_capability('block/moodletxt:sendmessages', $this->context, $USER->id);
        
        // Check user is allowed to set their signature/templates up
        $checkPrefs = has_capability('block/moodletxt:personalsettings', $this->context, $USER->id);

        $unreadFrag = '';

        if (has_capability('block/moodletxt:receivemessages', $this->context, $USER->id)) {
            $userCanReceive = true;

            $unreadMessages = $this->receivedMessagesDAO->countMessagesInUsersInbox($USER->id, true);

            if ($unreadMessages > 0)
                $unreadFrag = html_writer::tag('b', '(' . $unreadMessages . ')');

        }

        // Initialise content object
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // Add links to block dependent on user permissions/access level
        if ($checkSend) {
        
            $icon = new moodletxt_icon(moodletxt_icon::$ICON_MESSAGE_COMPOSE, get_string('altcompose', 'block_moodletxt'), array('title' => get_string('imgtitlecompose', 'block_moodletxt')));
            array_push($this->content->items, html_writer::tag('a', get_string('blocklinksend', 'block_moodletxt'), array('href' => $CFG->wwwroot . '/blocks/moodletxt/send.php?course=' . $this->page->course->id . '&instance=' . $this->instance->id)));
            array_push($this->content->icons, $output->render($icon));
            
            $icon = new moodletxt_icon(moodletxt_icon::$ICON_MESSAGES_SENT, get_string('altsentmessages', 'block_moodletxt'), array('title' => get_string('imgtitlesentmessages', 'block_moodletxt')));
            array_push($this->content->items, html_writer::tag('a', get_string('blocklinksent', 'block_moodletxt'), array('href' => $CFG->wwwroot . '/blocks/moodletxt/sent.php?course=' . $this->page->course->id . '&instance=' . $this->instance->id)));
            array_push($this->content->icons, $output->render($icon));
            
            $icon = new moodletxt_icon(moodletxt_icon::$ICON_ADDRESSBOOK, get_string('altaddressbook', 'block_moodletxt'), array('title' => get_string('imgtitleaddressbook', 'block_moodletxt')));
            array_push($this->content->items, html_writer::tag('a', get_string('blocklinkaddressbook', 'block_moodletxt'), array('href' => $CFG->wwwroot . '/blocks/moodletxt/addressbooks.php?course=' . $this->page->course->id . '&instance=' . $this->instance->id)));
            array_push($this->content->icons, $output->render($icon));
            
        }

        if ($userCanReceive) {
            
            $icon = new moodletxt_icon(moodletxt_icon::$ICON_MESSAGES_INBOX, get_string('altinbox', 'block_moodletxt'), array('title' => get_string('imgtitleinbox', 'block_moodletxt')));
            array_push($this->content->items, html_writer::tag('a', get_string('blocklinkinbox', 'block_moodletxt') . $unreadFrag, array('href' => $CFG->wwwroot . '/blocks/moodletxt/received.php?course=' . $this->page->course->id . '&instance=' . $this->instance->id)));
            array_push($this->content->icons, $output->render($icon));            
            
        }
        
        if ($checkPrefs) {
            
            $icon = new moodletxt_icon(moodletxt_icon::$ICON_PREFERENCES, get_string('altpreferences', 'block_moodletxt'), array('title' => get_string('imgtitlepreferences', 'block_moodletxt')));
            array_push($this->content->items, html_writer::tag('a', get_string('blocklinkpreferences', 'block_moodletxt'), array('href' => $CFG->wwwroot . '/blocks/moodletxt/preferences.php?course=' . $this->page->course->id . '&instance=' . $this->instance->id)));
            array_push($this->content->icons, $output->render($icon));                        
            
        }
        
        if ($userIsAdmin) {
            
//            $icon = new moodletxt_icon(moodletxt_icon::$ICON_STATS, get_string('altstats', 'block_moodletxt'), array('title' => get_string('imgtitlestats', 'block_moodletxt')));
//            array_push($this->content->items, html_writer::tag('a', get_string('blocklinkstats', 'block_moodletxt'), array('href' => $CFG->wwwroot . '/blocks/moodletxt/userstats.php?course=' . $this->page->course->id . '&instance=' . $this->instance->id)));
//            array_push($this->content->icons, $output->render($icon));                        
            
            $icon = new moodletxt_icon(moodletxt_icon::$ICON_SETTINGS, get_string('altsettings', 'block_moodletxt'), array('title' => get_string('imgtitlesettings', 'block_moodletxt')));
            array_push($this->content->items, html_writer::tag('a', get_string('blocklinksettings', 'block_moodletxt'), array('href' => $CFG->wwwroot . '/admin/settings.php?section=blocksettingmoodletxt')));
            array_push($this->content->icons, $output->render($icon));                        
                        
        }
        
        // If some form of content has been added, set up block
        if (count($this->content->items) > 0) {

            $this->content->footer = get_string('blockfooter', 'block_moodletxt');

            // Check whether config info has been previously defined
            if (! isset($this->config->title) || empty($this->config->title)) {

                // Set up default configuration
                $this->title = get_string('blocktitle', 'block_moodletxt');

            } else {

                // Use user configuration
                $this->title = $this->config->title;

            }

        }

        return $this->content;

    }

    /**
     * Returns whether multiple moodletxt blocks
     * are allowed within the same course.
     * In the case of moodletxt, no.
     * @return boolean Multiple blocks?
     * @version 2010081801
     * @since 2010081801
     */
    public function instance_allow_multiple() {

        return false;

    }

    /**
     * Returns whether the block has a global config
     * file or not. moodletxt has one.
     * @return boolean Has global config?
     * @version 2010081801
     * @since 2010081801
     */
    public function has_config() {

        return true;

    }

    /**
     * Returns whether or not the block's
     * header should be hidden. In this case, no.
     * @return boolean Hide header?
     * @version 2010081801
     * @since 2010081801
     */
    public function hide_header() {

        return false;

    }

    /**
     * Performs any specialist initialisation
     * required by the block. moodletxt requires
     * session variables to be initialised
     * @version 2011041401
     * @since 2010081801
     */
    public function specialization() {

        if ($this->page->course)
            $_SESSION['moodletxt_last_course'] = $this->page->course->id;

    }

    /**
     * Returns a list of formats, and whether the block
     * should be displayed within them. moodletxt should
     * only be displayed within courses.
     * @return array(string => boolean) List of formats
     * @version 2010081801
     * @since 2010081801
     */
    public function applicable_formats() {
        return array(
            'course-view' => true,
            'all' => false
        );
    }

    /**
     * Responds to calls from the Moodle cron
     * maintenance script. Used in moodletxt for
     * automatic fetching of data from txttools
     * and any necessary database cleanup.
     * @return boolean Success
     * @version 2013053101
     * @since 2010081801
     */
    public function cron() {

        try {
        
            $cronhandler = new MoodletxtCronHandler();
            return $cronhandler->doCron();
            
        } catch (Exception $ex) {
            error_log("Fatal error when processing MoodleTxt cron job:\n" . 
                    $ex->getMessage() . "\n" . $ex->getTraceAsString());
            
            return false;
        }

    }

}

?>