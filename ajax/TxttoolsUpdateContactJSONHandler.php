<?php

/**
 * File container for TxttoolsUpdateJSONHandler class
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
 * @version 2013061801
 * @since 2012090501
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

require_once($CFG->dirroot . '/blocks/moodletxt/dao/MoodletxtAddressbookDAO.php');
require_once($CFG->dirroot . '/blocks/moodletxt/ajax/MoodletxtAJAXException.php');

/**
 * JSON handler for updating addressbook contacts directly from
 * the addressbook view page
 * @package uk.co.moodletxt.ajax
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013061801
 * @since 2012090501
 */
class TxttoolsUpdateContactJSONHandler {
    
    /**
     * Default values for response
     * @var array(string => mixed)
     */
    private $responseTemplate = array(
        'contacts' => array(),
        'hasError' => false,
        'errorCode' => 0,
        'errorMessage' => '',
        'makeNoFurtherRequests' => false
    );

    /**
     * Data access object for editing contact data
     * @var MoodletxtAddressbookDAO
     */
    private $addressbookDAO;
        
    /**
     * Sets up the handler ready to suck on some JSON
     * @version 2012090501
     * @since 2012090501
     */
    public function __construct() {
        $this->addressbookDAO = new MoodletxtAddressbookDAO();
    }

    /**
     * Receive JSON and decide what to do based on mode switch
     * @param string $json JSON to parse
     * @return string JSON response
     * @throws MoodletxtAJAXException
     * @version 2013061801
     * @since 2012090501
     */
    public function processJSON($json) {
        
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

            case 'updateContact':
                $response = $this->updateContact(
                    clean_param($decoded->addressbookId, PARAM_INT), 
                    clean_param($decoded->contact->id, PARAM_INT),
                    strip_tags(clean_param($decoded->contact->firstName, PARAM_TEXT)),
                    strip_tags(clean_param($decoded->contact->lastName, PARAM_TEXT)),
                    strip_tags(clean_param($decoded->contact->company, PARAM_TEXT)),
                    preg_replace('/[^+0-9()\-]/', '', $decoded->contact->phoneNo)
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
     * Updates a contact's details in the DB according to the
     * values passed via AJAX
     * @global object $USER Moodle user object (session persistent)
     * @param int $addressbookId ID of addressbook to update
     * @param int $contactId ID of contact to update
     * @param string $contactFirstName Contact's first name
     * @param string $contactLastName Contact's last name
     * @param string $contactCompany Contact's employer
     * @param string $contactPhoneNumber Contact's phone number
     * @return string JSON-encoded response to request
     * @throws MoodletxtAJAXException
     * @version 2012061801
     * @since 2012090501
     */
    private function updateContact($addressbookId, $contactId, $contactFirstName, 
            $contactLastName, $contactCompany, $contactPhoneNumber) {
        
        global $USER;
        
        // Check that user owns DB
        if (! $this->addressbookDAO->checkAddressbookOwnership($addressbookId, $USER->id))
            throw new MoodletxtAJAXException(
                get_string('errorbooknotowned', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_NOT_ADDRESSBOOK_OWNER,
                null, false);
        
        // Get record from DB and update it
        try {
            $contact = $this->addressbookDAO->getAddressbookContactById($addressbookId, $contactId);
            
            $contact->setFirstName($contactFirstName);
            $contact->setLastName($contactLastName);
            $contact->setCompanyName($contactCompany);
            $contact->setRecipientNumber(new MoodletxtPhoneNumber($contactPhoneNumber));
            
            $this->addressbookDAO->saveContact($contact);
            
        } catch(InvalidArgumentException $ex) {
            throw new MoodletxtAJAXException(
                get_string('errorbadcontactid', 'block_moodletxt'),
                MoodletxtAJAXException::$ERROR_CODE_BAD_CONTACT_ID,
                null, false
            );
        }
        
        return $this->buildResponse($contact);
        
    }
    
    
    /**
     * Build JSON response structure for an updated contact
     * @param MoodletxtAddressbookRecipient $contact Updated contact
     * @return string Constructed JSON
     * @version 2012090501
     * @since 2012090501
     */
    private function buildResponse(MoodletxtAddressbookRecipient $contact) {
        
        // Copy template down
        $response = $this->responseTemplate;

        $contactArray = array(
            'contactId' => $contact->getContactId(),
            'firstName' => $contact->getFirstName(),
            'lastName'  => $contact->getLastName(),
            'company'   => $contact->getCompanyName(),
            'phoneNo'   => $contact->getRecipientNumber()->getPhoneNumber()
        );
        
        array_push($response['contacts'], $contactArray);
        
        return json_encode($response);
        
    }
    
}

?>