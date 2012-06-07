<?php

/**
 * File container for MoodletxtAdditionalRecipient class
 * 
 * moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 * In addition to this licence, as described in section 7, we add the following terms:
 *   - Derivative works must preserve original authorship attribution (@author tags and other such notices)
 *   - Derivative works do not have permission to use the trade and service names 
 *     "txttools", "moodletxt", "Blackboard", "Blackboard Connect" or "Cy-nap"
 *   - Derivative works must be have their differences from the original material noted,
 *     and must not be misrepresentative of the origin of this material, or of the original service
 * 
 * Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
 * or legal liability arising from their use of this code.
 * 
 * @see MoodletxtAdditionalRecipient
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011041401
 * @since 2010082001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/data/MoodletxtRecipient.php');

/**
 * Data bean for a recipient added on-the-fly
 * @package uk.co.moodletxt.data
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011040801
 * @since 2010082001
 */
class MoodletxtAdditionalRecipient extends MoodletxtRecipient {

    /**
     * Constructor - initialises data bean
     * @param MoodletxtPhoneNumber $recipientNumber Recipient's mobile phone number
     * @param string $firstName Recipient's first name (Recommended)
     * @param string $lastName Recipient's last name (Recommended)
     * @version 2011040801
     * @since 2010082001
     */
    public function __construct(MoodletxtPhoneNumber $recipientNumber, $firstName = '', $lastName = '') {
        
        parent::__construct($recipientNumber, $firstName, $lastName);
                
    }
        
}

?>