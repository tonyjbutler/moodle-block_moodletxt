<?php


/**
 * File container for admin_setting_password_unmask_encrypted class
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
 * @package uk.co.moodletxt.settings
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2012101001
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/lib/MoodletxtEncryption.php');

/**
 * Moodle admin extension to provide encryption/decryption
 * of a setting's value within the database
 * @package uk.co.moodletxt.settings
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013052301
 * @since 2012101001
 */
class admin_setting_password_unmask_encrypted extends admin_setting_configpasswordunmask {

    /**
     * Retrieves the setting's plaintext value from
     * the database, for editing
     * @return mixed Returns value if successful, otherwise null
     * @version 2013052301
     * @since 2012101001
     */
    public function get_setting() {
        
        // Decrypt value to return
        $encrypter = new MoodletxtEncryption();
        $key = get_config('moodletxt', 'EK');
        
        $encrypted = $this->config_read($this->name);
        return ($encrypted !== null) ? $encrypter->decrypt($key, $encrypted) : '';
    }

    /**
     * Takes the inputted form value, encrypts it,
     * and saves it back to the database
     * @param mixed $data Data to write to the DB
     * @return boolean Success
     * @version 2013052301
     * @since 2012101001
     */
    public function write_setting($data) {
        
        // If data value is an integer...
        if ($this->paramtype === PARAM_INT and $data === '') {
            // Do not complain if '' used instead of 0
            $data = 0;
        }
        
        // If data value is a string...
        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }
        
        // Encrypt value before write
        $encrypter = new MoodletxtEncryption();
        $key = get_config('moodletxt', 'EK');
        $encrypted = $encrypter->encrypt($key, $data);
        
        return ($this->config_write($this->name, $encrypted) ? '' : get_string('errorsetting', 'admin'));
    }
    
    
}

?>
