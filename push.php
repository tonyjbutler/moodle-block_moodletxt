<?php

/**
 * Push messaging endpoint for ConnectTxt. Inbound messages and status
 * updates can be automatically pushed here from your ConnectTxt account.
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
 * @version 2013052301
 * @since 2012101001
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib/MoodletxtEncryption.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/xml/MoodletxtXMLParser.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsSentMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsReceivedMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/inbound/MoodletxtInboundFilterManager.php');

// Read in POST variables
$inPushUser = required_param('u', PARAM_ALPHANUM);
$inPushPass = required_param('p', PARAM_ALPHANUM);
$inPayload  = required_param('x', PARAM_RAW);

// Assuming we have the right params, set up for parsing
$parser               = new MoodletxtXMLParser();
$decrypter            = new MoodletxtEncryption();
$sentMessagesDAO      = new TxttoolsSentMessageDAO();
$receivedMessagesDAO  = new TxttoolsReceivedMessageDAO();
$inboundFilterManager = new MoodletxtInboundFilterManager();

$key          = get_config('moodletxt', 'EK');
$pushUsername = get_config('moodletxt', 'Push_Username');
$pushPassword = $decrypter->decrypt($key, get_config('moodletxt', 'Push_Password'));

// Check credentials against those stored in Moodle
if ($inPushUser === $pushUsername &&
    $inPushPass === $pushPassword) {

    $parsedInboundMessages = array();
    $parsedStatusUpdates = array();
    
    try {
        $parsedObjects = $parser->parse($inPayload);
        
    } catch(Exception $ex) {

        // Invalid XML from remote system
        die();

    }

    if (is_array($parsedObjects)) {

        // Filter objects and save accordingly
        foreach($parsedObjects as $parsedObject) {

            if ($parsedObject instanceof MoodletxtInboundMessage)
                array_push($parsedInboundMessages, $parsedObject);

            else if ($parsedObject instanceof MoodletxtOutboundSMSStatus)
                array_push($parsedStatusUpdates, $parsedObject);

        }

        $sentMessagesDAO->saveSMSStatusUpdates($parsedStatusUpdates);

        $inboundMessages = $inboundFilterManager->filterMessages($parsedInboundMessages);
        $receivedMessagesDAO->saveInboundMessages($inboundMessages);            

    }
    
}

?>