<!--
//<![CDATA[

/**
 * Common JS library file for moodletxt
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
 * @package uk.co.moodletxt.js
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012030701
 * @since 2011061001
 */

// Character limit of a GSM message - changes to 70 if the user enables unicode sending
var MESSAGE_CHARACTER_LIMIT_GSM = 160;
var MESSAGE_CHARACTER_LIMIT_UNICODE = 70;
var MESSAGE_CHARACTER_LIMIT = MESSAGE_CHARACTER_LIMIT_GSM;

var TIME_BETWEEN_CHECKS = 1000;

var keyupTimer;  // Was tempted to call this the ey-up timer.  Oh aye, lad.
var checkTimeHasElapsed = true;

/**
 * Extends the basic string object to provide a trim() function. Cool!
 * @link http://javascript.crockford.com/remedial.html
 * @version 2011061001
 * @since 2011061001
 */
String.prototype.trim = function () {
    return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
};

/**
 * Allows us to insert given text into a textarea
 * at the caret's current position
 * @link http://technology.hostei.com/?p=3
 * @version 2011061001
 * @since 2011061001
 */
$.fn.insertAtCaret = function (tagName) {
    return this.each(function(){
        if (document.selection) {
            //IE support
            this.focus();
            sel = document.selection.createRange();
            sel.text = tagName;
            this.focus();
        } else if (this.selectionStart || this.selectionStart == '0') {
            //MOZILLA/NETSCAPE support
            startPos = this.selectionStart;
            endPos = this.selectionEnd;
            scrollTop = this.scrollTop;
            this.value = this.value.substring(0, startPos) + tagName + this.value.substring(endPos,this.value.length);
            this.focus();
            this.selectionStart = startPos + tagName.length;
            this.selectionEnd = startPos + tagName.length;
            this.scrollTop = scrollTop;
        } else {
            this.value += tagName;
            this.focus();
        }
    });
};


/**
 * Validate input in phone fields. Returning false prevents bad character being echoed to screen.
 * 48 - 57 are numbers
 * 43 is the + sign
 * 8 is backspace
 * 0 is for system keys
 * @param event The JS event being triggered
 * @return bool
 * @version 2011061001
 * @since 2011061001
 */
function validatePhoneInput(event) {

    /*
     * 48 - 57 are numbers
     * 43 is the + sign
     * 8 is backspace
     * 0 is for system keys
     */

    if ((event.which >= 48 && event.which <= 57) ||
        event.which == 43 ||
        event.which == 8 ||
        event.which == 0) {

        return true;

    } else {

        return false;

    }

}

/**
 * Validate input in name fields. Returning false prevents bad character being echoed to screen.
 * 48 - 57 are numbers
 * 43 is the + sign
 * 8 is backspace
 * 0 is for system keys
 * 65-90 and 97-122 are upper and lower case letters
 * @param event The JS event being triggered
 * @return bool
 * @version 2011061001
 * @since 2011061001
 */
function validateNameInput(event) {

    if ((event.which >= 97 && event.which <= 122) ||
        (event.which >= 65 && event.which <= 90) ||
        event.which == 45 ||
        event.which == 32 ||
        event.which == 8 ||
        event.which == 0) {

        return true;

    } else {

        return false;

    }

}

/**
 * Pads out a given field with the specified character
 * @param input String to be padded
 * @param count Length of string after padding
 * @param character Character to pad with - defaults to 0
 * @param direction Place pad characters to the right or left of the input
 * @return string Padded string
 * @version 2011061001
 * @since 2011061001
 */
function pad(input, count, character, direction) {

    // Defaults
    if (character == null) {
        character = '0';
    }
    
    if (direction != 'right') {
        direction = 'left';
    }

    // Processing
    var padded = input + '';

    while(padded.length < count) {
        if (direction == 'left') {
            padded = character + padded;
        } else {
            padded = padded + character;
        }
    }

    return padded;

}

/**
 * Given inputs, builds a standardised string for displaying 
 * names of contacts/users across moodletxt
 * @param firstName First name of user/contact
 * @param lastName Last name of user/contact
 * @param username Moodle username if this is a user (Optional)
 * @return string Display string
 * @version 2011062001
 * @since 2011062001
 */
function nameDisplayString(firstName, lastName, username) {
    
    var displayString = lastName + ', ' + firstName;
    displayString += (username != null && username != '') ? ' (' + username + ')' : '';
    
    return displayString;
    
}


/**
 * Check to see if the message box onscreen contains
 * non-GSM characters, and alert if it does
 * @param $messageElement Message box object
 * @param optionSet JS object representing options
 * @version 2012030701
 * @since 2011061001
 */
function isUnicode($messageElement, optionSet) {

    var defaultOptions = {
        unicodeMessageSelector  :   '#unicodeMessage',          // Element selector of hidden container(s) that hold unicode warning
        unicodeSuppressSelector :   '#unicodeSuppressedMessage',// Element selector of hidden container(s) that hold unicode suppression warnings
        unicodeSuppressSwitch   :   false,                      // Whether or not unicode suppression is switched on
        CPMElement              :   '#charactersPerMessage'     // Element ID of "characters per message" counter
    };

    var options = $.extend(defaultOptions, optionSet);

    // Prevent other checks taking place while this one is running
    checkTimeHasElapsed = false;

    // Make sure parameters are jQuery
    $messageElement     = $($messageElement);
    $alertElement       = $(options.unicodeMessageSelector);
    $suppressElement    = $(options.unicodeSuppressSelector);
    $CPMElement         = $(options.CPMElement);

    if ($messageElement.val().length > 0) {

        var requestJSON = $.toJSON({
            charset :   'GSM',
            text    :   $messageElement.val()
        });

        previousBorderWidth = $messageElement.css('border-width');

        // Query servlet with message text
        $.getJSON('checkcharset.php',
            { json : requestJSON },
            function(json) {

                // If unicode was detected, warn the user
                if (json.matches == false) {

                    MESSAGE_CHARACTER_LIMIT = MESSAGE_CHARACTER_LIMIT_UNICODE;

                    // Highlight "characters per message" and show text alert
                    // (If jQuery elements are not specified this will fail silently)
                    if (options.unicodeSuppressSwitch) {
                        $suppressElement.slideDown();
                    } else {
                        $suppressElement.slideUp();
                        
                        if ($alertElement.is(':hidden')) {
                            
                            $alertElement.slideDown();
                        
                            // Animate unicode warning to attract attention
                            $alertElement.effect('highlight', {color : '#AA0000'}, 2000);                        
                            
                        }
                    }

                } else {

                    // Message is GSM 03.38 compatible - form should be unaffected
                    MESSAGE_CHARACTER_LIMIT = MESSAGE_CHARACTER_LIMIT_GSM;

                    $alertElement.slideUp();
                    $suppressElement.slideUp();
                    
                }

                $CPMElement.text(MESSAGE_CHARACTER_LIMIT);

            }

        );

    }

    $CPMElement.text(MESSAGE_CHARACTER_LIMIT);

    // Prevent any further unicode checks occurring within the next second (+keyup latency)
    // Prevents hammering on the website/database
    setTimeout(function() {checkTimeHasElapsed = true;}, TIME_BETWEEN_CHECKS);

}

/**
 * Function to update the message length/messages used counter
 * displayed on the page for the user's reference
 * @param $messageObject jQuery object for message box
 * @param actionSet Parameter set for operations
 * @version 2012030801
 * @since 2012030801
 */
function updateMessageCounter($messageObject, actionSet) {
    
    // Default parameters
    var defaultActionSet = {
        characterCounterId      :   '#id_charsUsed',               // Element ID of character counter
        confirmCounterId        :   '#id_confirmCharsUsed'         // Element ID of character counter on confirm page
    };
    
    $messageObject = $($messageObject);
    actionSet = $.extend(defaultActionSet, actionSet);
    var messageLength = $messageObject.val().length;

    $(actionSet.characterCounterId).val(messageLength + ' / ' + Math.ceil(messageLength / MESSAGE_CHARACTER_LIMIT));
    $(actionSet.confirmCounterId).val(messageLength + ' / ' + Math.ceil(messageLength / MESSAGE_CHARACTER_LIMIT));
    
}

/**
 * Function to update message boxes within
 * the module as text is entered into them
 * @param $messageObject jQuery object for message box
 * @param actionSet Parameter set for operations
 * @version 2012030801
 * @since 2011061001
 */
function updateMessageBox($messageObject, actionSet) {

    // Default parameters
    var defaultActionSet = {
        checkMessageChars       :   true,                           // Update "characters remaining"
        checkForUnicode         :   true,                           // Check text box for unicode content
        characterCountElements  :   {
            characterCounterId      :   '#id_charsUsed',            // Element ID of character counter
            confirmCounterId        :   '#id_confirmCharsUsed'      // Element ID of character counter on confirm page
       },
       unicodeDetectElements    :   {
            unicodeMessageSelector  :   '#unicodeMessage',          // Element selector of hidden container(s) that hold unicode warning
            unicodeSuppressSelector :   '#unicodeSuppressedMessage',// Element selector of hidden container(s) that hold unicode suppression warnings
            unicodeSuppressSwitch   :   false,                      // Whether or not unicode suppression is switched on
            CPMElement              :   '#charactersPerMessage'     // Element ID of "characters per message" counter
       }
    };


    $messageObject = $($messageObject);
    actionSet = $.extend(defaultActionSet, actionSet);
    var messageLength = $messageObject.val().length;

    // Can only check for unicode if enough time has elapsed since the last one
    if (actionSet.checkForUnicode && checkTimeHasElapsed) {

        // Unicode check fires 500ms after the user stops typing
        clearTimeout(keyupTimer);
        keyupTimer = setTimeout(function() {
            isUnicode($messageObject, actionSet.unicodeDetectElements)
        }, 500);

    }

    if (actionSet.checkMessageChars) {

        $(actionSet.characterCountElements.characterCounterId).val(messageLength + ' / ' + Math.ceil(messageLength / MESSAGE_CHARACTER_LIMIT));
        $(actionSet.characterCountElements.confirmCounterId).val(messageLength + ' / ' + Math.ceil(messageLength / MESSAGE_CHARACTER_LIMIT));

    }

}


//]]>
//-->