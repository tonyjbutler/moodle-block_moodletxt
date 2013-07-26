<?php

/**
 * Endpoint for JSON calls from filter settings page
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
 * @version 2013070201
 * @since 2011071101
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/moodletxt/ajax/MoodletxtFilterJSONHandler.php');

// User requires same permissions as calling page
require_login();
require_capability('block/moodletxt:adminsettings', context_system::instance());

// Prevent page code being echoed by simply not including any of it!

$json = stripslashes(required_param('json', PARAM_RAW));
$handler = new MoodletxtFilterJSONHandler();

// Set valid JSON MIME header for response
header('Content-Type: application/json; charset=utf-8');

try {
    $response = $handler->processJSON($json);
    echo($response);
} catch(MoodletxtAJAXException $ex) {
    echo($ex->toJSON());
}

?>