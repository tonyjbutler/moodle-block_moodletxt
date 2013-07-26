<?php

/**
 * File container for block_moodletxt_edit_form class
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
 * @see block_moodletxt_edit_form
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011072201
 * @since 2011072201
 */

/**
 * Instance configuration form extension for the moodletxt block
 * @package uk.co.moodletxt
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2011072201
 * @since 2011072201
 */
class block_moodletxt_edit_form extends block_edit_form {
    
    /**
     * Extends the standard instance config form with custom
     * fields for moodletxt specifically
     * @param MoodleQuickForm $form Form to extend
     * @version 2011072201
     * @since 2011072201
     */
    protected function specific_definition($form) {
 
        // Section header title according to language file.
        $form->addElement('header', 'configheader', get_string('headerinstanceconfig', 'block_moodletxt'));
 
        // The title of the block
        $form->addElement('text', 'config_title', get_string('labelblocktitle', 'block_moodletxt'));
        $form->setDefault('config_title', get_string('blocktitle', 'block_moodletxt'));
        $form->setType('config_title', PARAM_MULTILANG);
        
    }    
    
}

?>
