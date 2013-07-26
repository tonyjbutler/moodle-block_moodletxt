<?php

/**
 * Event handlers for the moodletxt+ messaging plugin, which depends on this
 * block plugin for its message sending
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
 * @version 2012062401
 * @since 2012062401
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsSentMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');

/**
 * Receives a message event object from moodletxt+
 * and sens an SMS to the destination user as per their
 * preferences
 * @param object $messageObject Message event object
 * @version 2012062401
 * @since 2012062401
 */
function send_moodletxt_plus_message($messageObject) {

    $accountDAO     = new TxttoolsAccountDAO();
    $userDAO        = new MoodletxtMoodleUserDAO();
    $messageDAO     = new TxttoolsSentMessageDAO();
    
    // moodletxt requires a ConnectTxt account to be specified
    // for this message to transition via
    $defaultAccountId  = (int) get_config('moodletxt', 'Event_Messaging_Account');
    
    if ($defaultAccountId > 0) {
        
        $connectTxtAccount = $accountDAO->getTxttoolsAccountById($defaultAccountId);

        // Check that the specified account has outbound enabled
        if ($connectTxtAccount->isOutboundEnabled()) {

            $sender = $userDAO->getUserById($messageObject->from_id);
            $recipient = $userDAO->getUserById($messageObject->to_id);
            
            $message = new MoodletxtOutboundMessage($connectTxtAccount, $sender, $messageObject->smallmessage, time(), 
                                MoodletxtOutboundMessage::$MESSAGE_CHARGE_TYPE_BULK);
            
            $message->addMessageRecipient($recipient);
            $message->setEventCreated(true);
            
            // Send message to ConnectTxt
            try {

                $connector = MoodletxtOutboundControllerFactory::getOutboundController(
                    MoodletxtOutboundControllerFactory::$CONTROLLER_TYPE_XML);
                
                $message->setSentSMSMessages($connector->sendMessage($message));
                $messageDAO->saveSentMessage($message);
            
            } catch(MoodletxtRemoteProcessingException $ex) {
                // Die silently, for now
            }
            
        }
        
    }
    
}

?>