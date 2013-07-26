<?php

/**
 * File container for MoodletxtInboxJSONHandler class
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
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013061701
 * @since 2012043101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/ajax/MoodletxtAJAXException.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsAccountDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtMoodleUserDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/dao/TxttoolsReceivedMessageDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/connect/MoodletxtOutboundControllerFactory.php');

/**
 * JSON/AJAX handler for handling calls made by the inbox page to
 * update/forward/delete inbound messages
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013061701
 * @since 2012042501
 */
class MoodletxtInboxJSONHandler {

    /**
     * Default values for response
     * @var array(string => mixed)
     */
    private $responseTemplate = array(
        'userId' => 0,
        'messages' => array(),
        'hasError' => false
    );

    /**
     * DAO object for loading filter info
     * @var TxttoolsReceivedMessageDAO
     */
    private $messageDAO;
    
    /**
     * DAO object for loading user info
     * @var MoodletxtMoodleUserDAO
     */
    private $userDAO;
    
    /**
     * Sets up the handler ready to suck on some JSON
     * @version 2012050301
     * @since 2012043101
     */
    public function __construct() {
        $this->messageDAO = new TxttoolsReceivedMessageDAO();
        $this->userDAO = new MoodletxtMoodleUserDAO();
    }

    /**
     * Receive JSON and decide what to do based on mode switch
     * @param string $json JSON to parse
     * @param int $userId Owner of received messages
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2013061701
     * @since 2012043101
     */
    public function processJSON($json, $userId) {
        
        $decoded = json_decode($json);
        
        // Check that JSON is valid
        if (! is_object($decoded) || ! isset($decoded->mode))
            throw new MoodletxtAJAXException(
                get_string('errorinvalidjson', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_JSON,
                null, false);
        
        $response = '';
        
        // What do we need to do with this?
        switch($decoded->mode) {

            case 'getNewMessages':
                $response = $this->getNewMessages(
                    clean_param($userId, PARAM_INT), 
                    clean_param($decoded->lastMessageTime, PARAM_INT)
                );
                break;
            
            case 'copyMessages':
                $response = $this->copyMessagesToUser(
                    clean_param($userId, PARAM_INT), 
                    clean_param_array($decoded->messageIds, PARAM_INT), 
                    clean_param($decoded->userId, PARAM_INT)
                );
                break;
            
            case 'moveMessages':
                $response = $this->moveMessagesToUser(
                    clean_param($userId, PARAM_INT), 
                    clean_param_array($decoded->messageIds, PARAM_INT), 
                    clean_param($decoded->userId, PARAM_INT)
                );
                break;
            
            case 'deleteMessages':
                $response = $this->deleteMessages(
                    clean_param($userId, PARAM_INT), 
                    clean_param_array($decoded->messagesToDelete, PARAM_INT)
                );
                break;
            
            case 'createTag':
                $response = $this->createTag(
                    clean_param($userId, PARAM_INT), 
                    clean_param($decoded->tagName, PARAM_TAG), 
                    preg_replace('/[^#A-Za-z]/', '', $decoded->tagColour)
                );
                break;
            
            case 'deleteTag':
                $response = $this->deleteTag(
                    clean_param($userId, PARAM_INT), 
                    clean_param($decoded->tagName, PARAM_TAG)
                );
                break;
            
            case 'addTagToMessage':
                $response = $this->addTagToMessage(
                    clean_param($userId, PARAM_INT), 
                    clean_param($decoded->messageId, PARAM_INT), 
                    clean_param($decoded->tagName, PARAM_TAG)
                );
                break;
            
            case 'removeTagFromMessage':
                $response = $this->removeTagFromMessage(
                    clean_param($userId, PARAM_INT), 
                    clean_param($decoded->messageId, PARAM_INT), 
                    clean_param($decoded->tagName, PARAM_TAG)
                );
                break;
            
            default:
                throw new MoodletxtAJAXException(
                    get_string('errorinvalidjson', 'block_moodletxt'),
                    MoodletxtAJAXException::$ERROR_CODE_BAD_JSON,
                    null, false);
        }
        
        return $response;
        
    }

    /**
     * Gets new messages from the server and returns them to the page
     * @param int $ownerId User ID to get messages for
     * @param int $lastMessageCheck Timestamp of last message check
     * @return string Encoded JSON response
     * @version 2012050301
     * @since 2012043101
     */
    private function getNewMessages($ownerId, $lastMessageCheck) {
        
        $messages = $this->messageDAO->getReceivedMessagesSinceTime(
                $ownerId, $lastMessageCheck, 0, 'timereceived DESC', true);
        
        // Copy template
        $response = $this->responseTemplate;
        $response['userId'] = $ownerId;
        
        foreach($messages as $message)
            $response['messages'][$message->getId()] = $this->buildMessageJSON($message);
        
        return json_encode($response);
        
    }
    
    /**
     * Copy messages from one user's inbox to another
     * @param int $sourceUserId ID of source user for the message(s)
     * @param array(int) $messageIds The ID(s) of message(s) to copy across
     * @param int $destinationUserId ID of user to send messages to
     * @return string Encoded JSON response
     * @throws MoodletxtAJAXException 
     * @version 2012051601
     * @since 2012050301
     */
    private function copyMessagesToUser($sourceUserId, array $messageIds, $destinationUserId) {
        
        try {
            
            $sourceUser = $this->userDAO->getUserById($sourceUserId);
            $destinationUser = $this->userDAO->getUserById($destinationUserId);
            
            // User is trying to pull a fast one - stop them
            if ($sourceUser === null || $destinationUser === null)
                throw new MoodletxtAJAXException(
                        get_string('errorinvaliduserid', 'block_moodletxt'),
                        MoodletxtAJAXException::$ERROR_CODE_BAD_USER_ID);
            
            foreach($messageIds as $messageId) {
            
                $message = $this->messageDAO->getMessageById($messageId, true, $sourceUser->getId());
                $message->addDestinationUserId($destinationUser->getId());
                $this->messageDAO->saveInboundMessage($message);
                
            }
            
        } catch (InvalidArgumentException $ex) {
            // Either the message no longer exists,
            // or the user is attempting to access a message they don't own
            throw new MoodletxtAJAXException(
                get_string('errorbadmessageid', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_MESSAGE_ID);
        }
        
        $response = $this->responseTemplate;
        $response['userId'] = $sourceUser->getId();

        return json_encode($response);
        
    }
    
    /**
     * Move messages from one user's inbox to another
     * @param int $sourceUserId ID of source user for the message(s)
     * @param array(int) $messageIds The ID(s) of message(s) to move across
     * @param int $destinationUserId ID of user to send messages to
     * @return string Encoded JSON response
     * @throws MoodletxtAJAXException 
     * @version 2012051601
     * @since 2012050301
     */
    private function moveMessagesToUser($sourceUserId, array $messageIds, $destinationUserId) {
        
        try {
        
            $sourceUser = $this->userDAO->getUserById($sourceUserId);
            $destinationUser = $this->userDAO->getUserById($destinationUserId);
            
            // User is trying to pull a fast one - stop them
            if ($sourceUser === null || $destinationUser === null)
                throw new MoodletxtAJAXException(
                        get_string('errorinvaliduserid', 'block_moodletxt'),
                        MoodletxtAJAXException::$ERROR_CODE_BAD_USER_ID);
            
            foreach($messageIds as $messageId) {
            
                $message = $this->messageDAO->getMessageById($messageId, true, $sourceUser->getId());
                $this->messageDAO->deleteMessage($message);

                $message->addDestinationUserId($destinationUser->getId());
                $this->messageDAO->saveInboundMessage($message);
                
            }
            
        } catch (InvalidArgumentException $ex) {
            // Either the message no longer exists,
            // or the user is attempting to access a message they don't own
            throw new MoodletxtAJAXException(
                get_string('errorbadmessageid', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_MESSAGE_ID);
        }
        
        $response = $this->responseTemplate;
        $response['userId'] = $sourceUser->getId();

        return json_encode($response);
        
    }
    
    /**
     * Deletes messages from a user's inbox
     * @param int $userId ID(s) of user(s) to delete message(s) from
     * @param array(int) $messageIds ID(s) of message(s) to delete
     * @return string Encoded JSON response
     * @throws MoodletxtAJAXException
     * @version 2012051401
     * @since 2012050301
     */
    private function deleteMessages($userId, array $messageIds) {
        
        try {
            
            $this->messageDAO->deleteMessagesByIds($messageIds, $userId);
            
        } catch (InvalidArgumentException $ex) {
            // Either the message no longer exists,
            // or the user is attempting to access a message they don't own
            throw new MoodletxtAJAXException(
                get_string('errorbadmessageid', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_MESSAGE_ID);
        }
        
        $response = $this->responseTemplate;
        $response['userId'] = $userId;

        return json_encode($response);
        
    }
    
    /**
     * Creates a tag within a user's inbox
     * @param int $userId ID of user to create tag for
     * @param string $tagName Name of tag to create
     * @param string $tagColour CSS/hex colour code for tag
     * @return string Encoded JSON response
     * @version 2012052201
     * @since 2012052201
     */
    private function createTag($userId, $tagName, $tagColour) {
        
        $this->messageDAO->createOrUpdateTag($userId, $tagName, $tagColour);
        
        $response = $this->responseTemplate;
        $response['userId'] = $userId;

        return json_encode($response);        
        
    }
    
    /**
     * Deletes a tag from a user's inbox
     * @param int $userId User ID to delete tag from
     * @param string $tagName Name of tag to delete
     * @return string Encoded JSON response
     * @version 2012052201
     * @since 2012052201
     */
    private function deleteTag($userId, $tagName) {

        try {
            $this->messageDAO->deleteTagByName($userId, $tagName);
            
        } catch (InvalidArgumentException $ex) {
            // Either the message no longer exists,
            // or the user is attempting to access a message they don't own
            throw new MoodletxtAJAXException(
                get_string('errornottagowner', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_MESSAGE_ID);
        }        
        
        $response = $this->responseTemplate;
        $response['userId'] = $userId;

        return json_encode($response);
                
    }
    
    /**
     * Adds a tag to a given user's inbox message
     * @param int $userId ID of user that owns tag/message
     * @param int $messageId ID of message to apply tag to
     * @param string $tagName Name of tag to apply
     * @return string Encoded JSON response
     * @version 2012052801
     * @since 2012052701
     */
    private function addTagToMessage($userId, $messageId, $tagName) {
        
        $tag = $this->messageDAO->getTagByName($userId, $tagName);
        $this->messageDAO->addTagToMessage($messageId, $tag->getId());
        
        $response = $this->responseTemplate;
        $response['userId'] = $userId;

        return json_encode($response);
        
    }

    /**
     * Removes a tag's association with a given inbox message
     * @param int $userId ID of user that owns inbox/message
     * @param int $messageId ID of message to remove tag from
     * @param string $tagName Name of tag to remove
     * @version 2012052801
     * @since 2012052701
     */
    private function removeTagFromMessage($userId, $messageId, $tagName) {
        
        $tag = $this->messageDAO->getTagByName($userId, $tagName);
        $this->messageDAO->removeTagFromMessage($messageId, $tag->getId());
        
        $response = $this->responseTemplate;
        $response['userId'] = $userId;

        return json_encode($response);
        
    }
    
    
    /**
     * Builds response JSON to send back to the webpage
     * @param MoodletxtInboundMessage $message Message to build JSON from
     * @return array JSON structure ready for encoding
     * @TODO Return destination accounts when stored by system
     * @version 2012050401
     * @since 2012050301
     */
    private function buildMessageJSON(MoodletxtInboundMessage $message) {
        
        $JSON = array(
            'source' => array(
                'firstName' => $message->getSourceFirstName(),
                'lastName' => $message->getSourceLastName(),
                'displayName' => $message->getSourceNameForDisplay(),
                'number' => $message->getSourceNumber()->getPhoneNumber()
            ),
//            'destination' => array(
//                'id' => $message->getDestinationAccountId(),
//                'username' => $message->getDestinationAccountUsername(),
//                'number' => $message->getDestinationNumber()->getPhoneNumber()
//            ),
            'ticket' => $message->getMessageTicket(),
            'messageText' => $message->getMessageText(),
            'timeReceived' => $message->getTimeReceived('%H:%M:%S,  %d %B %Y'),
            'hasBeenRead' => $message->getHasBeenRead()
        );        
        
        if ($message->hasOwner())
            $JSON['owner'] = array(
                'userId' => $message->getOwner()->getId(),
                'username' => $message->getOwner()->getUsername(),
                'firstName' => $message->getOwner()->getFirstName(),
                'lastName' => $message->getOwner()->getLastName(),
                'displayName' => $message->getOwner()->getFullNameForDisplay()
            );
        
        return $JSON;
        
    }
      
}

?>