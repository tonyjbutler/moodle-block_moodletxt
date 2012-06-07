//<!--
//<![CDATA[

/**
 * jQuery scripting for the received messages page
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
 * @author Greg J Preece <txttoolssupport@blackboard.com>
 * @copyright Copyright &copy; 2012 Blackboard Connect. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public Licence v3 (See code header for additional terms)
 * @version 2012052901
 * @since 2011080501
 */

var COURSE_ID = 0;
var INSTANCE_ID = 0;

var $IMAGE_LOADING;

/**
 * Sets up any dynamic elements the script needs
 * to add to the page later
 * @version 2012052801
 * @since 2012052801
 */
function setupElements() {
    $IMAGE_LOADING = $('<img />')
        .attr('src', 'pix/icons/ajax-loader.gif')
        .attr('width', '16')
        .attr('height', '16')
        .css('vertical-align', 'bottom')
        .attr('alt', M.str.block_moodletxt.fragloading)
        .attr('title', M.str.block_moodletxt.fragloading);    
}

/**
 * Update the message table to show only the messages that
 * match the selected message tags
 * @version 2012052201
 * @since 2012052201
 */
function updateTagView() {
    
    var activeTags = $('.mtxtTag.selected');
    
    if (activeTags.length == 0) {
        
        $('table#receivedMessagesList tr').filter(':gt(0)').show()
        
    } else {
    
        $('table#receivedMessagesList tr').filter(':gt(0)').hide();

        activeTags.each(function() {
            var link = $(this).children('a').filter(':first').attr('href');
            $(link.replace('#', '.')).show();
        });
        
    }
    
}

/**
 * Toggle a given tray on or off the screen
 * @param id Tray's DOM ID
 * @version 2012052501
 * @since 2012052501
 */
function toggleTray(id) {
    
    if ($('div#' + id).data('shown') == 'true') {
        hideTray(id);
    } else {
        showTray(id);
    }
    
}

/**
 * Slides a specified tray onto the screen
 * @param id Tray's DOM ID
 * @version 2012052501
 * @since 2012052501
 */
function showTray(id) {
            
    $('div#' + id).data('shown', 'true');

    $('div#' + id).animate({
        bottom   : '0px'
    }, 
    {
        duration : 300,
        easing   : 'swing'
    });
        
}

/**
 * Slides a specified tray off the screen
 * @param id Tray's DOM ID
 * @version 2012052501
 * @since 2012052501
 */
function hideTray(id) {
    
    $('div#' + id).data('shown', 'false');

    $('div#' + id).animate({
        bottom    : '-50px'
    }, 
    {
        duration : 300,
        easing   : 'swing'
    });
        
}

/**
 * Enables dragging for the element(s) provided,
 * and enables the trash tray during dragging
 * @param elements Elements to make draggable
 * @version 2012052701
 * @since 2012052701
 */
function makeDraggable(elements) {
    
    $(elements).draggable({
        appendTo:   'body',
        helper  :   'clone',
        start   :   function(event, ui) {
            showTray('trashPopup');
        },
        stop    :   function(event, ui) {
            hideTray('trashPopup');
        },
        zIndex  :   6
    });
    
}

/**
 * Copies or moves the selected messages on the page
 * to another user's inbox via an AJAX call
 * @param action Whether to copy or move the messages
 * @param destinationUser The ID of the user to send the messages to
 * @version 2012052801
 * @since 2012051601
 */
function copyOrMoveMessages(action, destinationUser) {
    
    var messageIds = getCheckedMessageIds();

    if (messageIds.length == 0) {

        alert(M.str.block_moodletxt.alertnomessagesselected);
        resetControls();
        
    } else {
        
        $('select#id_accounts').parent().append($IMAGE_LOADING.clone());
        
        var requestMode = (action == 'copy') ? 'copyMessages' : 'moveMessages';
        
        var requestJSON = $.toJSON({
            mode : requestMode,
            messageIds : messageIds,
            userId : destinationUser
        });
        
        $.post (
            'received_process.php',
            {
                course : COURSE_ID,
                instance : INSTANCE_ID,
                json : requestJSON
            },
            function(data) {
                $('select#id_accounts').siblings(':not(select)').remove();
                
                if (data.hasError == false) {
                    if (action == 'move') {
                        eraseMessages(messageIds);
                    } else {
                        clearMessages(messageIds);
                    }    
                    resetControls();
                }
            }
        );
        
    }
    
}

/**
 * Deletes the selected messages from the message table
 * and the user's inbox via an AJAX call
 * @version 2012052801
 * @since 2012051601
 */
function deleteMessages() {
    
    var messageIds = getCheckedMessageIds();
    
    if (messageIds.length == 0) {
        
        alert(M.str.block_moodletxt.alertnomessagesselected);
        resetControls();
        
    } else if (confirm(M.str.block_moodletxt.alertconfirmdeletemessages)) {
        
        $('select#id_accounts').parent().append($IMAGE_LOADING.clone());
        
        var requestJSON = $.toJSON({
            mode : 'deleteMessages',
            messagesToDelete : messageIds
        });
        
        $.post(
            'received_process.php',
            {
                course : COURSE_ID,
                instance : INSTANCE_ID,
                json : requestJSON
            },
            function(data) {
                $('select#id_accounts').siblings(':not(select)').remove();
                
                if (data.hasError == false) {
                    eraseMessages(messageIds);
                    resetControls();
                }
            }
        );
    }
    
}

/**
 * Get the IDs of the messages in the inbox table that the
 * user has selected via checkbox
 * @return array Message IDs
 * @version 2012051601
 * @since 2012051601
 */
function getCheckedMessageIds() {
    
    var messageIds = [];
    $('input[name=messageids\\[\\]]').filter(':checked').each(function() {
        messageIds.push($(this).val());
    });
    
    return messageIds;
        
}

/**
 * Erases the specified messages from the inbox table and
 * deletes them altogether from the DOM
 * @param messageIds The IDs of the messages to delete
 * @version 2012051601
 * @since 2012051601
 */
function eraseMessages(messageIds) {
    
    var rowsToErase = [];
    
    $('input[name=messageids\\[\\]]').each(function() {
        if ($.inArray($(this).val(), messageIds) > -1) {
            rowsToErase.push($(this).parent().parent());
        }
    });
    
    // Loop twice - once to fast-hide from the user,
    // and once to actually remove the messages from the DOM
    $.each(rowsToErase, function(index, value) {
        $(value).slideUp();
    });
    
    $.each(rowsToErase, function(index, value) {
        $(value).remove();
    });
    
}

/**
 * Deselects the specified messages from the messages table
 * @param messageIds IDs of messages to deselect
 * @version 2012051601
 * @since 2012051601
 */
function clearMessages(messageIds) {

    $.each(messageIds, function(index, value) {    
        $('input[name=messageids\\[\\]]').filter('[value=' + value + ']').prop('checked', false);
    })


}

/**
 * Resets the "with selected" form controls
 * @version 2012052801
 * @since 2012051601
 */
function resetControls() {
    $('select[name=action]').val('');
    $('select[name=accounts]').val('').attr('disabled', 'disabled');
}

/**
 * Receives the Moodle course ID from YUI
 * @param YUI YUI object, which we'll ignore
 * @param courseId Moodle course ID
 * @version 2012051601
 * @since 2012051601
 */
function receiveCourseId(YUI, courseId) {
    COURSE_ID = courseId;
}

/**
 * Receives the Moodle instance ID from YUI
 * @param YUI YUI object, ignored
 * @param instanceId Moodle instance ID
 * @version 2012051601
 * @since 2012051601
 */
function receiveInstanceId(YUI, instanceId) {
    INSTANCE_ID = instanceId;
}

$(document).ready(function() {
   
    setupElements();
   
    // Enable tag links in the cloud to hide/show messages
    $('.mtxtTagCloud .mtxtTag a').click(function(event) {
        $(this).parent().toggleClass('selected');
        updateTagView();
    });
    
    
    makeDraggable($('.mtxtTag'));
    
    makeDraggable($('.mtxtAppliedTag'));
    
    /*
     * Drop handler for table rows
     */
    $('#receivedMessagesList tr').slice(0).droppable({
        hoverClass  :   'droppableHighlight',
        drop        :   function(event, ui) {
            
            // Make accessible within closure scope
            var $row = $(this);
            var $draggable = $(ui.draggable);
            
            // If this is anything other than a tag from the tray
            // or tag list, then ignore it
            if (! $draggable.hasClass('mtxtTag')) {
                return;
            }
            
            $row.children('.tagDrop').append($IMAGE_LOADING.clone());
            
            var requestJSON = $.toJSON({
                mode : 'addTagToMessage',
                messageId : $row.find('input').val(),
                tagName : $draggable.text()
            });
            
            var found = false;

            // Checking to see if this tag already exists for the given message
            $row.find('.mtxtAppliedTag').each(function() {
                if ($(this).text().trim() == $draggable.text().trim()) {
                    found = true;
                }
            });

            if (! found) {
                
                $.post(
                    'received_process.php',
                    {
                        course : COURSE_ID,
                        instance : INSTANCE_ID,
                        json : requestJSON
                    },
                    function(data) {
                        // Remove loading icon
                        $row.children('.tagDrop').children('img:last').remove();
                        
                        // Create a tag entry within the table and add its class to the row
                        var tag = $('<span>').text($draggable.text()).addClass('mtxtAppliedTag');
                        makeDraggable(tag); // New elements need binds attached separately
                        $row.children('.tagDrop').append(tag).append(' ');
                        $row.addClass($draggable.children('a').filter(':first').attr('href').replace('#', '')); // Fleetwood Mac, baby
                    }
                );
            }

        }
    });
    
    /*
     * Drop handler for trash tray
     */
    $('#trashPopup').droppable({
        hoverClass  :   'droppableHighlight',
        drop        :   function(event, ui) {
            hideTray('trashPopup');
            
            var $draggable = $(ui.draggable);
            var tagId   = $draggable.parent().parent().find('input').val(); // Fails silently if not an applied tag
            var tagLink = $draggable.children('a').filter(':first').attr('href'); // Fails silently if not a tray tag
            var tagText = $draggable.text();
            var requestJSON;
            
            $draggable.parent().prepend($IMAGE_LOADING.clone());
            
            if ($draggable.hasClass('mtxtAppliedTag')) {
                // Don't convert to JSON string at this point
                // This makes requestJSON available in closure scope
                requestJSON = {
                    mode : 'removeTagFromMessage',
                    messageId : tagId,
                    tagName : tagText
                };
            } else {
                requestJSON = {
                    mode : 'deleteTag',
                    tagName : tagText
                };
            }
            
            $.post(
                'received_process.php',
                {
                    course : COURSE_ID,
                    instance : INSTANCE_ID,
                    json : $.toJSON(requestJSON)
                },
                function(data) {
                    $draggable.siblings('img:first').remove();
                    
                    if (requestJSON.mode == 'removeTagFromMessage') {
                        $draggable.remove();                
                    } else {
                        $('.mtxtTag').has('a[href=' + tagLink + ']').remove();
                        $('.mtxtAppliedTag').filter(function() { 
                            return $(this).text() == tagText; 
                        }).remove();
                    }
                }
            )            
        }
    });
    
    /*
     * AJAX form handler for creating new tags
     */
    $('.addTagButton').click(function(event) {
        $(this).parent().append($IMAGE_LOADING.clone());
        
        var $inputBox = $(this).siblings('input[type=text]');
        
        // @todo Add tag colours in final release
        var requestJSON = {
            mode : 'createTag',
            tagName : $inputBox.val(),
            tagColour : '#ffffff'
        };
        
        $.post(
            'received_process.php',
            {
                course : COURSE_ID,
                instance : INSTANCE_ID,
                json : $.toJSON(requestJSON)
            },
            function(data) {
                $inputBox.siblings('img:last').remove();
                $inputBox.val('');
                
                if (data.hasError == false) {
                    $tagLink = $('<a>').attr('href', '#' + requestJSON.tagName.replace(' ', '')).text(requestJSON.tagName);
                    $newTag = $('<span>').addClass('mtxtTag').append($tagLink);
                    makeDraggable($newTag);
                    $newTag2 = $newTag.clone(); // Do not clone events relating to draggable. Position is affected
                    makeDraggable($newTag2);
                    
                    $('div#tagListScroller').append($newTag);
                    $('.mtxtTagCloud .tags').append($newTag2);
                }
            }
        );
    });
    
    $('input[name=selectAllMessages]').change(function(event) {
        if ($(this).is(':checked')) {
            $('input[name=messageids\\[\\]]').attr('checked', 'checked');
        } else {
            $('input[name=messageids\\[\\]]').removeAttr('checked');
        }
    });
   
    $('img.mtxtMessageDeleteButton').click(function(event) {
        $('input[name=messageids\\[\\]]').removeAttr('checked');
        $(this).parent().parent().find('input').filter(':first').attr('checked', 'checked');
        $('select[name=action]').val('killmaimburn').change();
    });
   
    $('img.mtxtMessageTagButton').click(function(event) {
        toggleTray('tagPopup');
    });
   
    $('select[name=action]').change(function(event) {
        switch ($(this).val()) {
           
            case 'killmaimburn':
                $('select[name=accounts]').attr('disabled', 'disabled').val('');
                deleteMessages();
                break;
                
            case 'copy':
            case 'move':
                $('select[name=accounts]').removeAttr('disabled');
                break;
               
            case '':
            default:
                // Lock second drop-down
                $('select[name=accounts]').attr('disabled', 'disabled').val('');
           
        }
    });
   
    $('select[name=accounts]').change(function(event) {
        
        if ($(this).val() == 0) {
            // Do nothing - no-one selected
           
        } else if ($('select[name=action]').val() == 'copy' ||
                  $('select[name=action]').val() == 'move') {
           
           copyOrMoveMessages($('select[name=action]').val(), $('select[name=accounts]').val());

        }
       
    });
    
});

//]]>
//-->