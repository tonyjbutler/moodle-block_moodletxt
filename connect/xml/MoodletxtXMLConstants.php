<?php

/**
 * File container for MoodletxtXMLConstants class
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
 * @see MoodletxtXMLConstants
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013011001
 * @since 2010090101
 */

defined('MOODLE_INTERNAL') || die('File cannot be accessed directly.');

/**
 * Shrink-wrap class that holds XML tag definitions for use with the connector.
 * @package uk.co.moodletxt.connect.xml
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2013011001
 * @since 2010090101
 */
class MoodletxtXMLConstants {

    /**
     * The number of message/update requests that should be placed in one batch.
     * @var int
     */
   public static $MESSAGES_PER_BATCH = 50;

    /**
     * Option value used when getting unread messages from txttools
     * @var string
     */
    public static $INBOUND_FETCH_UNREAD = 'UNREAD';

    /**
     * Option value used when getting read and unread messages from txttools
     * @var string
     */
    public static $INBOUND_FETCH_ALL = 'ALL';


    public static $REQUEST_ROOT             = '<Request>';
    public static $_REQUEST_ROOT            = '</Request>';

    public static $REQUEST_AUTHENTICATION_BLOCK     = '<Authentication>';
    public static $_REQUEST_AUTHENTICATION_BLOCK    = '</Authentication>';
    public static $REQUEST_AUTHENTICATION_USERNAME  = '<Username><![CDATA[';
    public static $_REQUEST_AUTHENTICATION_USERNAME = ']]></Username>';
    public static $REQUEST_AUTHENTICATION_PASSWORD  = '<Password><![CDATA[';
    public static $_REQUEST_AUTHENTICATION_PASSWORD = ']]></Password>';

    public static $REQUEST_MESSAGE_BLOCK            = '<Message>';
    public static $_REQUEST_MESSAGE_BLOCK           = '</Message>';
    public static $REQUEST_MESSAGE_TEXT             = '<MessageText><![CDATA[';
    public static $_REQUEST_MESSAGE_TEXT            = ']]></MessageText>';
    public static $REQUEST_MESSAGE_PHONE            = '<Phone><![CDATA[';
    public static $_REQUEST_MESSAGE_PHONE           = ']]></Phone>';
    public static $REQUEST_MESSAGE_TYPE             = '<Type>';
    public static $_REQUEST_MESSAGE_TYPE            = '</Type>';
    public static $REQUEST_MESSAGE_SCHEDULE_DATE    = '<ScheduleTimeUTCSecs>';
    public static $_REQUEST_MESSAGE_SCHEDULE_DATE   = '</ScheduleTimeUTCSecs>';
    public static $REQUEST_MESSAGE_SUPPRESS_UNICODE = '<SuppressUnicode><![CDATA[true]]></SuppressUnicode>';
    public static $REQUEST_MESSAGE_UNIQUE_ID        = '<UniqueID><![CDATA[';
    public static $_REQUEST_MESSAGE_UNIQUE_ID       = ']]></UniqueID>';

    public static $REQUEST_STATUS_BLOCK             = '<RequestStatus>';
    public static $_REQUEST_STATUS_BLOCK            = '</RequestStatus>';
    public static $REQUEST_STATUS_TICKET            = '<Ticket>';
    public static $_REQUEST_STATUS_TICKET           = '</Ticket>';

    public static $REQUEST_INBOUND_BLOCK            = '<RetrieveInbound>';
    public static $_REQUEST_INBOUND_BLOCK           = '</RetrieveInbound>';
    public static $REQUEST_INBOUND_TYPE             = '<RetrieveType><![CDATA[';
    public static $_REQUEST_INBOUND_TYPE            = ']]></RetrieveType>';
    public static $REQUEST_INBOUND_NUMBER           = '<RetrieveNumber>';
    public static $_REQUEST_INBOUND_NUMBER          = '</RetrieveNumber>';
    public static $REQUEST_INBOUND_SINCE            = '<RetrieveSince>';
    public static $_REQUEST_INBOUND_SINCE           = '</RetrieveSince>';

    public static $REQUEST_ACCOUNT_DETAILS          = '<AccountDetails>';
    public static $_REQUEST_ACCOUNT_DETAILS         = '</AccountDetails>';
    public static $REQUEST_GET_ACCOUNT_DETAILS      = '<GetAccountDetails><![CDATA[TRUE]]></GetAccountDetails>';

    public static $RESPONSE_ROOT                    = 'Response';

    public static $RESPONSE_ERROR_BLOCK             = 'Error';
    public static $RESPONSE_ERROR_CODE              = 'ErrorCode';
    public static $RESPONSE_ERROR_MESSAGE           = 'ErrorMessage';

    public static $RESPONSE_STATUS_BLOCK            = 'MessageStatus';
    public static $RESPONSE_STATUS_MESSAGE_TEXT     = 'MessageText';
    public static $RESPONSE_STATUS_PHONE            = 'Phone';
    public static $RESPONSE_STATUS_TICKET           = 'Ticket';
    public static $RESPONSE_STATUS_UNIQUE_ID        = 'UniqueID';
    public static $RESPONSE_STATUS_CODE             = 'Status';
    public static $RESPONSE_STATUS_MESSAGE          = 'StatusMessage';

    public static $RESPONSE_INBOUND_BLOCK           = 'InboundMessage';
    public static $RESPONSE_INBOUND_MESSAGE_TEXT    = 'MessageText';
    public static $RESPONSE_INBOUND_PHONE           = 'Phone';
    public static $RESPONSE_INBOUND_DELIVERY_DATE   = 'Date';
    public static $RESPONSE_INBOUND_TICKET          = 'Ticket';
    public static $RESPONSE_INBOUND_DESTINATION     = 'Destination';
    public static $RESPONSE_INBOUND_DESTINATION_ACC = 'DestinationAccount';

    public static $RESPONSE_ACCOUNT_BLOCK           = 'AccountDetail';
    public static $RESPONSE_ACCOUNT_MESSAGES_USED   = 'MessagesUsed';
    public static $RESPONSE_ACCOUNT_MESSAGES_REMAIN = 'MessagesRemaining';
    public static $RESPONSE_ACCOUNT_BILLING_TYPE    = 'AccountType';

}

?>