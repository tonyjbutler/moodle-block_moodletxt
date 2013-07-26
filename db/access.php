<?php

/**
 * File defines new capabilities added to the system by moodletxt
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
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013070201
 * @since 2010081801
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

$capabilities = array(

    // User can add instances of the block
    'block/moodletxt:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'student'        => CAP_PROHIBIT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
    
    // User is allowed to create addressbooks
    'block/moodletxt:addressbooks' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    // User is allowed to use the moodletxt admin panel
    'block/moodletxt:adminsettings' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager' => CAP_ALLOW
        )
    ),

    // User is allowed to admin user filters on moodletxt
    'block/moodletxt:adminusers' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager' => CAP_ALLOW
        )
    ),

    // User can be a default inbox entry
    'block/moodletxt:defaultinbox' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    // User can create global address books
    'block/moodletxt:globaladdressbooks' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    // User can change their personal settings
    'block/moodletxt:personalsettings' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    // User can receive inbound messages
    'block/moodletxt:receivemessages' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    // User can send text messages
    'block/moodletxt:sendmessages' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    // User can view the send-message statistics page
    'block/moodletxt:viewstats' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PROHIBIT,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    )
    
)

?>