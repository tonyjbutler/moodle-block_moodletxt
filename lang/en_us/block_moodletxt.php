<?php

/**
 * English language file for moodletxt
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
 * @since 2006081012
 */

// Common

$string['cancel']                       = 'Cancel';
$string['loadtoken']                    = 'Loading...';
$string['nexttoken']                    = 'Next';
$string['ok']                           = 'OK';
$string['previoustoken']                = 'Previous';

$string['billingtypeinvoiced']          = 'Invoiced';
$string['billingtypeprepaid']           = 'Pre-paid';

// Add contact page

$string['addcontactbutton']             = 'Add contact and add another';
$string['addcontactbuttonreturn']       = 'Add contact and return to address book';
$string['addcontactlblcompany']         = 'Company Name:';
$string['addcontactlblfirstname']       = 'First Name:';
$string['addcontactlbllastname']        = 'Last Name:';
$string['addcontactlblphoneno']         = 'Phone No:';
$string['addcontactheader']             = 'Add a Contact';
$string['addcontactheadergroups']       = 'Group Membership';
$string['addcontactpara1']              = 'To add this contact to one or more groups in your address book, select the groups from the available list on the left and transfer them to the selected list on the right.';
$string['addcontactsuccessful']         = 'Contact added successfully.';
$string['addcontacttitle']              = 'Add a Contact in ';

// Address book page

$string['addressadd']                   = 'Add a new address book';
$string['addressbookadded']             = 'The address book was added successfully.';
$string['addressbookdeleted']           = 'The address book was deleted successfully.';
$string['addressbookupdated']           = 'The address book was updated successfully.';
$string['addressbutaddbook']            = 'Add address book';
$string['addressbutdelbook']            = 'Delete address book';
$string['addressdelete']                = 'Delete an existing address book';
$string['addressexisting']              = 'Your existing address books';
$string['addressfragnodest']            = 'Nowhere - delete the contacts';
$string['addressfragtypeglobal']        = 'Global';
$string['addressfragtypeprivate']       = 'Private';
$string['addressheader']                = 'Your Address Books';
$string['addresslblnewname']            = 'Address book name:';
$string['addresslblkill']               = 'Select address book to delete:';
$string['addresslblmove']               = 'Move contacts in this book to:';
$string['addresslbltype']               = 'Address book type:';
$string['addresstitle']                 = 'Address Books for ';

// Admin page

$string['adminaccountadded']            = 'Account was added successfully.';
$string['adminbutaddaccount']           = 'Add Account';
$string['adminbutaddfolder']            = 'Add Folder';
$string['adminbutadduserfilter']        = 'Add Filter';
$string['adminbutdelfolder']            = 'Delete Folder';
$string['adminbutdeluserfilter']        = 'Remove Filter';
$string['adminbutremovefilterusers']    = 'Remove selected users from filter';
$string['adminbutsavefilterusers']      = 'Save the filter';
$string['adminbutupdatepass']           = 'Update Account';
$string['adminbutupdatesettings']       = 'Update Settings';
$string['admindescdefaultname']         = 'The default name moodletxt should assign to message recipients that it cannot identify.';
$string['admindescdefaultprefix']       = 'The international prefix for phone numbers within your country.';
$string['admindescdefnatprefix']        = 'The default prefix for phone numbers within your country.';
$string['admindescdisablewarn']         = 'Should moodletxt display warnings when SSL connections are not enabled?';
$string['admindescjqueryinclude']       = 'Controls whether or not the jQuery Javascript framework will be loaded by moodletxt when required. If you already include the jQuery library as part of your Moodle theme, you should disable this include.';
$string['admindescjqueryuiinclude']     = 'Controls whether or not the jQuery User Interface extension will be loaded by moodletxt when required. If you already include jQuery UI as part of your Moodle theme, you should disable this include.';
$string['admindescmiscsettings']        = 'These are the various system settings that don\'t fit under any other heading, such as update notifications, interface tweaks, etc.';
$string['admindescphonesource']         = 'Which of Moodle\'s profile fields moodletxt should use as a number source.';
$string['admindescprotocol']            = 'Which protocol moodletxt should use for connections to ConnectTxt. Only use HTTP if SSL is not available';
$string['admindescproxyaddress']        = 'The address of your proxy server.';
$string['admindescproxypassword']       = 'The password moodletxt should use to connect to your proxy server (if required).';
$string['admindescproxyport']           = 'The port your proxy server listens on.';
$string['admindescproxysettings']       = 'If you want moodletxt to connect via a proxy server, enter its details here. Please note that moodletxt currently only supports proxy servers using BASIC authentication, or no authentication.';
$string['admindescproxyusername']       = 'The username moodletxt should use to connect to your proxy server (if required).';
$string['admindescrecipsettings']       = 'These settings change where/how moodletxt sources recipient phone numbers, along with how recipient data is handled.';
$string['admindescrssexpire']           = 'How long a new RSS update should be displayed for on the admin page after being published.';
$string['admindescrssupdate']           = 'How often moodletxt should check for new updates to the moodletxt RSS feed.';
$string['admindescsendreceive']         = 'These settings are used to control the sending and receiving of messages within the block, and how updates/inbound messages are received.';
$string['admindescsetautoinbound']      = 'Should moodletxt connect to fetch inbound messages when their inbox page is opened?';
$string['admindescsetautoupdate']       = 'Should moodletxt connect to fetch status updates when the message details page is opened?';
$string['admindescsetxmluser']          = 'The username ConnectTxt should use when sending messages/status updates to you via XML push. This is not your ConnectTxt username.';
$string['admindescsetxmlpass']          = 'The password ConnectTxt should use when sending messages/status updates to you via XML push. This is not your ConnectTxt password.';
$string['admindescshowinbound']         = 'Controls whether or not the source phone number/student name should be shown with inbound messages on the inbox page. If you are using moodletxt in classroom situations, you may wish to hide inbound sources so that messages displayed are anonymous. Please note that as of moodletxt 3.0, this can be overridden by individuals users, so your selection here will become the default setting.';
$string['adminerrorpara1']              = 'The following errors were encountered when trying to send the text message:';
$string['adminerrorpara2']              = 'Please try again later.  If the problem persists, please contact the system administrator.';
$string['adminheaderaccess']            = 'User Access';
$string['adminheaderaccountslist']      = 'ConnectTxt Account List';
$string['adminheaderaccounts']          = 'ConnectTxt Accounts';
$string['adminheaderinfo']              = 'Useful Information';
$string['adminheaderlinks']             = 'Account and Filter Settings';
$string['adminheadermiscsettings']      = 'Miscellaneous Settings';
$string['adminheaderproxysettings']     = 'Proxy Settings';
$string['adminheaderrecipsettings']     = 'Recipient Settings';
$string['adminheaderrssupdate']         = 'Latest moodletxt News';
$string['adminheadersendreceive']       = 'Send/Receive Settings';
$string['adminheadersslfailed']         = 'Warning: Could not establish secure connection';
$string['adminheadersslwarning']        = 'Warning: Secure transmissions not enabled';
$string['adminheading']                 = 'Change Admin Options';
$string['admininfocontacttel']          = 'Telephone:';
$string['admininfocontactfax']          = 'Fax:';
$string['admininfocontactemail']        = 'E-mail:';
$string['admininfocontactweb']          = 'Visit bbconnecttxt.com';
$string['admininfoheader1']             = 'Automatically Updating Status Information';
$string['admininfoheader2']             = 'ConnectTxt contact information';
$string['admininfopara1']               = 'If your Moodle system can accept incoming connections from outside your institution on port 443 (HTTPS),then by providing ConnectTxt with some basic connection info, you can have status updates and inbound messages automatically sent to your moodletxt installation.  This will provide you with instantly up-to-date information, without requiring you to fetch it manually.';
$string['admininfopara2']               = 'To use XML push facilities, you need to create a username and password for the ConnectTxt system to use when connecting. Use the form at the top right of the page to store these details, then contact ConnectTxt support with the username, password, and the address shown below.  Once this is done you can disable automatic fetching in the system settings - again, using the form at the top right.';
$string['admininfopara3']               = 'If your system cannot accept SSL connections on port 443, but can accept standard HTTP connections on port 80, you can still use XML push.  Simply use the alternate address shown below.';
$string['admininfopara4']               = 'If your Moodle system cannot accept incoming outside connections, but is capable of running scheduled tasks (also known as "cron jobs"), then by setting the file shown below to run at regular intervals, you can keep your status information up-to-date for immediate availability.';
$string['admininfopara5']               = 'If you have any questions regarding the ConnectTxt system, or need help using the moodletxt system, feel free to contact the ConnectTxt support team via any of the methods shown below.';
$string['adminintroheader1']            = 'No ConnectTxt Accounts Found';
$string['adminintroheader2']            = 'Users with existing ConnectTxt accounts';
$string['adminintroheader3']            = 'Users without existing ConnectTxt accounts';
$string['adminintropara1']              = 'You are most likely seeing this message because you have just installed moodletxt.  moodletxt requires at least one bbconnecttxt.com account to be registered on the moodletxt system in order to run.';
$string['adminintropara2']              = 'Users with existing ConnectTxt accounts should first make sure that any account they wish to use with the moodletxt system has been enabled.  You can do this by contacting ConnectTxt at <a href="mailto:txttoolssupport@blackboard.com">txttoolssupport@blackboard.com</a>, or by phone on +44 (0) 113 234 2111.  Activation takes just a few minutes.';
$string['adminintropara3']              = 'Once your account(s) have been activated, please enter the details of the first account you wish to use with moodletxt into the form below.  This will activate your moodletxt installation, and you will then be able to use the system.';
$string['adminintropara4']              = 'If you do not already have a ConnectTxt account registered, you can obtain one by contacting ConnectTxt at <a href="mailto:txttoolssales@blackboard.com">txttoolssales@blackboard.com</a>, or by phone on +44 (0) 113 234 2111.  <b>Remember to mention that you will be using the account with moodletxt, so that your account can be activated for use with it.</b>';
$string['adminintropara5']              = 'Once you have obtained a ConnectTxt account, please enter its details into the form below.  This will activate moodletxt, and you will be immediately able to use the system.';
$string['adminlabelaccdesc']            = 'Account Description:';
$string['adminlabelaccinbox']           = 'Default Inbox:';
$string['adminlabelaccusername']        = 'Account Username:';
$string['adminlabelaccpassword']        = 'Account Password:';
$string['adminlabelaccpassword2']       = 'Confirm Password:';
$string['adminlabeladdaccount']         = 'Add a New Account';
$string['adminlabelchangepass']         = 'Change Password:';
$string['adminlabelcoursename']         = 'Course name:';
$string['adminlabelcreatenew']          = 'Create new';
$string['adminlabeldefnatprefix']       = 'National Prefix:';
$string['adminlabeldefaultname']        = 'Default Recipient Name:';
$string['adminlabeldefaultprefix']      = 'Default International Prefix:';
$string['adminlabeldisablewarn']        = 'Display SSL security warnings.';
$string['adminlabelexistingacc']        = 'Available ConnectTxt accounts:';
$string['adminlabelexistingfilters']    = 'Existing filters';
$string['adminlabelfilteracc']          = 'Select an account to add filter to:';
$string['adminlabelfilterkeyword']      = 'Keyword:';
$string['adminlabelfilterphone']        = 'Phone no:';
$string['adminlabelfilteraddusers']     = 'Add users to filter';
$string['adminlabelfilterusersearch']   = 'Enter user name here...';
$string['adminlabelfilterskeyword']     = 'Existing keyword filters';
$string['adminlabelfiltersphone']       = 'Existing phone number filters';
$string['adminlabelhideproxy']          = 'Hide proxy settings';
$string['adminlabeljqueryinclude']      = 'Include main jQuery package';
$string['adminlabeljqueryuiinclude']    = 'Include jQuery UI library';
$string['adminlabelphonesource']        = 'Take phone numbers from:';
$string['adminlabelprotocol']           = 'Connection Protocol:';
$string['adminlabelproxyaddress']       = 'Proxy Address:';
$string['adminlabelproxypassword']      = 'Proxy Password:';
$string['adminlabelproxyport']          = 'Proxy Port:';
$string['adminlabelproxyusername']      = 'Proxy Username:';
$string['adminlabelrssupdate']          = 'RSS Update Interval:';
$string['adminlabelrssexpire']          = 'RSS items expire after:';
$string['adminlabelselectacc']          = 'Select a ConnectTxt account...';
$string['adminlabelsetautoinbound']     = 'Automatically get inbound messages on Inbox page';
$string['adminlabelsetautoupdate']      = 'Automatically get status updates on "View Message" page';
$string['adminlabelsetxmluser']         = 'XML Push Username:';
$string['adminlabelsetxmlpass']         = 'XML Push Password:';
$string['adminlabelshowinbound']        = 'Show source names/numbers in inbox (Default)';
$string['adminlinkaccounts']            = 'ConnectTxt Accounts';
$string['adminlinkfilters']             = 'Inbound Message Filters';
$string['adminnoaccount']               = 'No accounts found to display';
$string['adminnoticefilterdeleted']     = 'Removed filter from system.';
$string['adminnoticefilterupdated']     = 'Filter updated successfully.';
$string['adminselectphone1']            = 'Phone 1';
$string['adminselectphone2']            = 'Phone 2';
$string['adminselectprotocolssl']       = 'SSL - secure encrypted';
$string['adminselectprotocolhttp']      = 'HTTP - standard unencrypted';
$string['adminselectrssdaily']          = 'Daily';
$string['adminselectrssexday']          = 'After 24 Hours';
$string['adminselectrssexweek']         = 'After 7 Days';
$string['adminselectrssexmonth']        = 'After 28 Days';
$string['adminselectrsshourly']         = 'Hourly';
$string['adminselectrssmonthly']        = 'Every 28 Days';
$string['adminselectrssweekly']         = 'Weekly';
$string['adminsettingsupdated']         = 'Settings updated successfully.';
$string['adminsslfailedpara1']          = 'While testing your system for compatibility, moodletxt was unable to establish a secure connection with the ConnectTxt server.  This may be due to a network problem, or your server may not be configured for use with Secure Socket Layer (SSL) connections.';
$string['adminsslfailedpara2']          = 'As a result, moodletxt has been set to use standard, unencrypted HTTP connections for sending messages.  If this change has been made in error, or you later change your server configuration to use SSL, then you may use the moodletxt admin panel to re-enable secure connections.';
$string['adminsslwarnpara1']            = 'moodletxt is currently using standard unencrypted transmissions to send text messages.  It is recommended that you set up your server to allow encrypted Secure Socket Layer (SSL) connections, for greater security.';
$string['adminsslwarnpara2']            = 'If your server is now capable of SSL connections, you can re-enable SSL using the System Settings panel at the bottom right of this page.  Alternatively, if SSL compatibility is not possible on your server, you can use the same panel to disable this warning message.';
$string['admintitleaccountlist']        = 'Account List';

// Account listing page

$string['adminaccountbutupdateall']     = 'Update Credit Info For All Accounts';
$string['adminaccountconfirmupdate']    = 'moodletxt will now connect to the ConnectTxt server and poll each registered account for updated data. If you have a large number of ConnectTxt accounts registered in moodletxt then this can take a significant amount of time. Do you wish to continue?';
$string['adminaccountfragloading']      = 'Updating account...';
$string['adminaccountfragneverupdated'] = 'Never Updated';
$string['adminaccountintropara1']       = 'This page displays details of all the ConnectTxt accounts registered within the system.  To enable or disable inbound/outbound access on a given account, find the account\'s entry in the table below, and click the appropriate icon.  This will toggle access as required.';
$string['adminaccountintropara2']       = 'Clicking the "Update Credit Info" button will synchronise credit information for the accounts listed with the ConnectTxt server.  Please note that this task is normally taken care of at regular intervals by the Moodle system, and as such is not normally necessary';
$string['adminaccountinvoicedaccount']  = 'Invoiced account';
$string['adminaccountprocessedfrag']    = 'Processed';
$string['adminaccountupdatefailed']     = 'Update error';
$string['adminaccountupdatesuccess']    = 'Updated OK';

// JavaScript alerts
$string['alertconfirmdeletemessages']   = 'Are you sure you wish to delete the selected message(s)?';
$string['alertconfirmdeletetemplate']   = 'Are you sure you wish to delete this template?';
$string['alertnomessagesselected']      = 'You have not selected any messages.';
$string['alertnotemplateselected']      = 'Please select a template.';

// Alt texts

$string['altaccessdenied']              = 'Access Denied';
$string['altaccessedit']                = 'Edit Access';
$string['altaccessinbound']             = 'Inbound Active';
$string['altaccessoutbound']            = 'Outbound Active';
$string['altaddtag']                    = 'Add Tag';
$string['altdelete']                    = 'Delete';
$string['altinbox']                     = 'Inbox';
$string['altaddfilter']                 = 'Add Filter';
$string['altaddressbook']               = 'Addressbook(s)';
$string['altcancelfilter']              = 'Cancel Filter';
$string['altcompose']                   = 'Compose Message';
$string['altpreferences']               = 'Preferences';
$string['altreply']                     = 'Reply';
$string['altsentmessages']              = 'Sent Messages';
$string['altsettings']                  = 'Settings';
$string['altstats']                     = 'Usage Stats';
$string['altstatusdelivered']           = 'Message Delivered';
$string['altstatusfailed']              = 'Delivery Failed';
$string['altstatustransit']             = 'Message in Transit';

// Block Strings

$string['blocklinkaddressbook']         = 'Address Book';
$string['blocklinkinbox']               = 'Received Messages';
$string['blocklinkpreferences']         = 'Preferences';
$string['blocklinksend']                = 'Compose a Message';
$string['blocklinksent']                = 'Sent Messages';
$string['blocklinksettings']            = 'Change Admin Options';
$string['blocklinkstats']               = 'View user stats';
$string['blockname']                    = 'moodletxt';
$string['blocktitle']                   = 'moodletxt 3.0-beta1';
$string['blockfooter']                  = 'Powered by <a href="http://www.bbconnecttxt.com">ConnectTxt</a>';
$string['pluginname']                   = 'moodletxt';

// Buttons

$string['buttonadd']                    = 'Add';
$string['buttonremove']                 = 'Remove';
$string['buttonremoveusers']            = 'Remove User(s)';
$string['buttonsave']                   = 'Save';
$string['buttonsendmessage']            = 'Send Message';
$string['buttontagfirstname']           = 'First Name';
$string['buttontaglastname']            = 'Last Name';
$string['buttontagfullname']            = 'Full Name';
$string['buttontemplatedelete']         = 'Delete Template';
$string['buttontemplateedit']           = 'Edit Template';
$string['buttonupdate']                 = 'Update';

// Instance config page

$string['configtitle']                  = 'Enter a title for the block:';

// Used in config settings

$string['configdefaultrecipient']       = 'Moodler';
$string['configdefaultsource']          = 'Moodler';

// Address book edit page

$string['editbookaddlink']              = 'Add a contact';
$string['editbookbutcheckall']          = 'Check all';
$string['editbookbutuncheckall']        = 'Uncheck all';
$string['editbookbutcancel']            = 'Cancel';
$string['editbookbutsave']              = 'Save';
$string['editbookbutupdate']            = 'Update';
$string['editbookcontactsperpage']      = 'contacts per page';
$string['editbookdelnotselected']       = 'Delete All But Selected Contacts';
$string['editbookdelselected']          = 'Delete Selected Contacts';
$string['editbookdoubleclick']          = 'Double click on a contact to edit it.';
$string['editbookgroupslink']           = 'Manage groups';
$string['editbookheader']               = 'Editing Address Book';
$string['editbooklblname']              = 'Address book name:';
$string['editbooklbltable']             = 'Double click on a contact entry to edit it.';
$string['editbooklbltype']              = 'Type:';
$string['editbookselectedaction']       = 'With selected...';
$string['editbooktableheader1']         = 'Last Name';
$string['editbooktableheader2']         = 'First Name';
$string['editbooktableheader3']         = 'Company Name';
$string['editbooktableheader4']         = 'Phone Number';
$string['editbooktitle']                = 'Editing address book:';

// Error messages

$string['erroraccountexists']           = 'The account name entered already exists.';
$string['erroraccountinsertfailed']     = 'There was an error when attempting to add the account.  Please try again later.';
$string['erroraddcontactfailed']        = 'Unable to add contact to the database. Please try again later. If the problem persists, contact your system administrator.';
$string['errorbadbookid']               = 'The address book selected was invalid, or you do not own the address book you are attempting to edit.';
$string['errorbadcontactid']            = 'The contact you are trying to update could not be found in the database.';
$string['errorbadcourseid']             = 'Course ID was incorrect';
$string['errorbadinstanceid']           = 'No block instance ID found - please access the page via the moodletxt block.';
$string['errorbadmessageid']            = 'The message ID given does not exist, or does not belong to the user ID found. Please select a valid message to view.';
$string['errorbookaddfailed']           = 'Failed to add the address book.  Please try again later.';
$string['errorbooknameexists']          = 'The address book name already exists';
$string['errorbooknamelength']          = 'The address book name is too long.  Names cannot be longer than 50 characters.';
$string['errorbooknotdeleted']          = 'There was an error when deleting the address book.  If selected, contacts and groups have already been moved to the target address book.  Please contact your system administrator for further assistance.';
$string['errorbooknotowned']            = 'Form hacking detected - you are attempting to delete an address book you do not own.';
$string['errorconn401']                 = 'Moodletxt was not granted access to the bbconnecttxt.com system. Reason unknown. This is not a user authentication error. HTTP error code: ';
$string['errorconn404']                 = 'Moodletxt could not find the XML connector on the ConnectTxt server. This may be due to a temporary loss of service, or your Moodletxt version may be out of date.  Please contact ConnectTxt support for more information.';
$string['errorconn500']                 = 'There was an internal error on the ConnectTxt server that prevented the XML request from being processed.  This error should be temporary.  Please contact ConnectTxt support for more information.';
$string['errorconn503']                 = 'The ConnectTxt XML service is currently unavailable.  Please try again later.  If the problem persists, please contact ConnectTxt support.';
$string['errorconn601']                 = 'The username and password for your ConnectTxt account were rejected by the ConnectTxt server. Please check that your ConnectTxt username and password have been saved correctly within moodletxt.';
$string['errorconn602']                 = 'One or more fields specified when connecting to the ConnectTxt server had no value set. This may be a result of modifying moodletxt, or your moodletxt version may be out of date. Please contact ConnectTxt if you require assistnace.';
$string['errorconn603']                 = 'An error was encountered during transmission to ConnectTxt.  Please retry shortly. If the problem persists, please contact ConnectTxt for assistance.';
$string['errorconn604']                 = 'The XML sent to ConnectTxt could not be parsed correctly. This may be a result of modifying moodletxt, or your moodletxt version may be out of date. Please contact ConnectTxt if you require assistance.';
$string['errorconn606']                 = 'ConnectTxt encountered an error when attempting to process your request. Please retry shortly.  If the problem persists, please contact ConnectTxt for assistance.';
$string['errorconn607']                 = 'An invalid message charge type was sent when communicating with the ConnectTxt server. This may be a result of modifying moodletxt, or your moodletxt version may be out of date. Please contact ConnectTxt if you require assistnace.';
$string['errorconn608']                 = 'An invalid XML header was sent when communicating with the ConnectTxt server. This may be a result of modifying moodletxt, or your moodletxt version may be out of date. Please contact ConnectTxt if you require assistnace.';
$string['errorconn700']                 = 'The JSON sent through to moodletxt was not defined, or not of a valid structure. Please correct your code.';
$string['errorconn701']                 = 'The account ID sent through via JSON was not set or was invalid. Please only send valid account IDs.';
$string['errorconnadmin']               = 'The following errors occurred during communication with the ConnectTxt server:';
$string['errorconndefault']             = 'An error occurred during communication with the ConnectTxt SMS system. If this persists, please contact your administrator for assistance.';
$string['errorconnnosocket']            = 'XML connector error: Could not open socket to ConnectTxt. Please ensure your moodletxt installation has outbound access through your firewall.';
$string['errorconnrss401']              = 'Moodletxt was not granted access to the moodletxt.co.uk system. Reason unknown. This is not a user authentication error. HTTP error code: ';
$string['errorconnrss404']              = 'Moodletxt could not find the RSS feed on the moodletxt server. This may be due to a temporary loss of service, or your moodletxt version may be out of date.  Please contact ConnectTxt support for more information.';
$string['errorconnrss500']              = 'There was an internal error on the ConnectTxt server that prevented the feed request from being processed.  This error should be temporary.  Please contact ConnectTxt support for more information.';
$string['errorconnrss503']              = 'The ConnectTxt RSS service is currently unavailable.  Please try again later.  If the problem persists, please contact ConnectTxt support.';
$string['errorconnrssdefault']          = 'An error occurred when attempting to contact the moodletxt RSS service.  The HTTP response code was: ';
$string['errordestbooknotowned']        = 'Form hacking detected: You do not own the destination address book.  Please select a valid destination for contacts.';
$string['errordestbooksame']            = 'The destination address book for contacts is the same as the one being deleted.';
$string['errordestfoldersame']          = 'The destination folder for messages is the same as the folder being deleted.';
$string['errordestgroupinvalid']        = 'The destination group for contacts is invalid, or you do not own it.';
$string['errordestgroupsame']           = 'The destination group for contacts is the same as the one being deleted.';
$string['errordocbrown']                = 'The date/time entered for scheduling is in the past!  Doc Brown says "no!"';
$string['errorfilterexists']            = 'A filter of that type and value already exists within the database.';
$string['errorfilternoaccount']         = 'Please select a valid account on which to create/edit filters.';
$string['errorfilternotselected']       = 'Please select a valid filter to modify, or create a new one, before assigning users.';
$string['errorfilternousers']           = 'Please select users to add to the new filter.';
$string['errorfolderaddfailed']         = 'Folder could not be added.  Please try again later.';
$string['errorfolderexists']            = 'A folder with this name already exists.  Please enter a new name.';
$string['errorfoldernametoolong']       = 'Folder names cannot be more than 30 characters in length.';
$string['errorformhackaccount']         = 'Form hacking detected.  You are not authorised to use the ConnectTxt account selected.';
$string['errorformhacktemplate']        = 'The template ID entered was invalid.  Quit hacking the form.';
$string['errorglobalbooknotallowed']    = 'You do not have the necessary permissions to create global address books.';
$string['errorgroupnotadded']           = 'The group could not be added. Please try again later. If the problem persists, please contact your system administrator.';
$string['errorgroupsmakechoice']        = 'Please indicate what moodletxt should do with contacts within the group to be deleted.';
$string['errorinboxcantconnect']        = 'One or more of your ConnectTxt accounts could not connect to check for new messages.  Please ask an administrator to check your account details in the moodletxt control panel.';
$string['errorinvalidbookid']           = 'Form hacking detected. The address book ID is invalid.';
$string['errorinvalidbooktype']         = 'Form hacking detected.  The book type entered on the form was invalid.  Please select a valid book type.';
$string['errorinvalidchoice']           = 'You did not indicate what should happen to contacts within this address book. Please make a valid selection from the options shown.';
$string['errorinvaliddate']             = 'The date/time entered for scheduling is invalid.  Please enter a valid date.';
$string['errorinvalidaccount']          = 'No valid account was selected from the list.  Please select an account to modify.';
$string['errorinvalidaccountid']        = 'An invalid account ID was provided.';
$string['errorinvalidfolder']           = 'The folder ID selected was not valid. Either the folder selected for deletion does not exist inside your inbox, or you are attempting to delete a system folder. Please select a valid folder for deletion.';
$string['errorinvalidgroupid']          = 'Please select a valid group from the list provided.';
$string['errorinvalidjson']             = 'The JSON sent to the server was invalid or  did not contain a correct mode switch.';
$string['errorinvalidnumber']           = 'The phone number provided was invalid.';
$string['errorinvaliduserid']           = 'The Moodle user ID provided was invalid. Please provide the ID of a valid Moodle user account.';
$string['errorlabel']                   = 'The following errors were encountered:';
$string['errormessagetoolong']          = 'The message entered was too long.  The message must be 1600 characters or less to be sent.';
$string['errormovecontactsfailed']      = 'The system was unable to move contacts from one address book to the other.  Delete cancelled. Please contact your system administrator.';
$string['errormovegroupsfailed']        = 'The system was unable to move contact groups from one address book to the other.  Delete cancelled.  Individual contacts have already been moved.  Please contact your system administrator.';
$string['errornoaccountselected']       = 'No ConnectTxt account was selected to send the message from.';
$string['errornobookname']              = 'No address book name was entered';
$string['errornogroupname']             = 'You must enter a name for the new group';
$string['errornogroupselected']         = 'No group was selected to send to.  Please select a valid group from the list.';
$string['errornofirstname']             = 'No first name was entered.';
$string['errornofoldername']            = 'You must enter a name for the new folder.';
$string['errornolastname']              = 'No last name was entered.';
$string['errornomessage']               = 'No message was entered to send!';
$string['errornonameorcompany']         = 'You must enter either a name or company name for this contact.';
$string['errornonewpassword']           = 'No new password was entered.  Please enter a new password for the account.';
$string['errornonumber']                = 'You must enter a phone number to send to.';
$string['errornopassword']              = 'No password was entered for the ConnectTxt account.';
$string['errornopasswordmatch']         = 'The two passwords entered do not match.';
$string['errornopermission']            = 'You do not have permission to view this page.';
$string['errornopermissioncourse']      = 'You do not have permission to send text messages on this course.';
$string['errornopermissionmessage']     = 'You are not authorised to view this message.';
$string['errornorecipients']            = 'No recipients were found for this message.  It was not received.  The most likely reason is that there was an error when attempting to send the message.  Please contact your administrator for more information.';
$string['errornorecipientsselected']    = 'No recipients were selected to send to.  Please select an individual from the list given.';
$string['errornorecipienttype']         = 'No recipient type was selected.  Please indicate whether you wish to send to an individual or a group';
$string['errornoscheduling']            = 'No scheduling option was selected.  Please indicate when you want the message to be sent.';
$string['errornotemplate']              = 'You must enter a template';
$string['errornottagowner']             = 'You are attempting to delete a tag you do not own.';
$string['errornousername']              = 'No username was entered for the ConnectTxt account.';
$string['errornovalidnumbers']          = 'None of the contacts selected to send to have valid mobile phone numbers stored in their profiles.  Please add valid phone numbers to their profiles, or select new contacts to send to.';
$string['erroroperationinprogress']     = 'Another operation is currently in progress. Please wait.';
$string['errorpasswordtooshort']        = 'ConnectTxt passwords must be a minimum of 8 characters.';
$string['errorprefsupdatefail']         = 'Preferences could not be updated.  Please try again later.';
$string['errorsetinvalidnatprefix']     = 'National prefix entered was invalid.  National prefixes must contain only numbers.';
$string['errorsetinvalidprefix']        = 'Default International Prefix entered was invalid.  International prefixes must start with a + sign and contain only numbers after that.';
$string['errorsettingsupdatefail']      = 'System settings could not be updated.';
$string['errorsigtoolong']              = 'Your signature is too long.  Please amend it to be under 25 characters.';
$string['errortagnotfound']             = 'The specified tag was not found in the database.';
$string['errortemplatedeletefail']      = 'The template could not be deleted.  Please try again later.';
$string['errortemplateinsertfail']      = 'The template could not be added to the system. Please try again later.';
$string['errortemplateupdatefail']      = 'The template could not be updated.  Please try again later.';
$string['errorupdatecontactfailed']     = 'The contact could not be updated. Please try again later.';

// File export data
$string['exportsheetinbox']             = 'moodletxt_received_messages';
$string['exportsheetsent']              = 'moodletxt_sent_messages';
$string['exporttitleinbox']             = 'Messages Received in Moodletxt';
$string['exporttitlesent']              = 'Messages Sent from Moodletxt';

// Miscellaneous text fragments
$string['fragloading']                  = 'Loading...';
$string['fragunknown']                  = 'Unknown';
$string['fragunknownname']              = 'Unknown';

// Headers

$string['headeraccountrestrictionsfor'] = 'Outbound Access Restrictions for:';
$string['headeraddaccount']             = 'Adding a new ConnectTxt account to moodletxt';
$string['headercomposemessagebody']     = 'Compose Message';
$string['headerfilters']                = 'Manage Inbound Message Filters';
$string['headerinboundprefs']           = 'My Inbox Preferences';
$string['headerinstanceconfig']         = 'Instance Settings for moodletxt';
$string['headermessagedetails']         = 'Message Details';
$string['headermessageoptions']         = 'Message Options';
$string['headernewinstall']             = 'moodletxt - New Installation Detected';
$string['headerpreferences']            = 'My Preferences';
$string['headerreceivedmessages']       = 'Your Received Messages';
$string['headerselectrecipients']       = 'Select Recipients';
$string['headersend']                   = 'Send a Text Message';
$string['headersendreview']             = 'Review and Send';
$string['headersent']                   = 'Your Sent moodletxt Messages';
$string['headerstatus']                 = 'Sent Message Status';
$string['headerstatuskey']              = 'Status Key';
$string['headersignature']              = 'My Signature';
$string['headertags']                   = 'Your Tags';
$string['headertemplatesnew']           = 'Add New Template';
$string['headertemplatesedit']          = 'Edit Existing Template';
$string['headertemplatesexist']         = 'My Message Templates';

// Image title tags

$string['imgtitleaddressbook']          = 'View and edit your addressbook(s)';
$string['imgtitlecompose']              = 'Compose a new message and send it';
$string['imgtitleinbox']                = 'View all your received messages';
$string['imgtitlepreferences']          = 'Change your user-specific settings';
$string['imgtitlesentmessages']         = 'View your previously sent messages';
$string['imgtitlesettings']             = 'Administer the moodletxt block';
$string['imgtitlestats']                = 'View moodletxt usage statistics';

// Inbox page

$string['inboxfolderinbox']             = 'Inbox';
$string['inboxfoldertrash']             = 'Trash Can';

$string['inboxtableheaderticket']       = 'Ticket No';
$string['inboxtableheadermessage']      = 'Message Text';
$string['inboxtableheaderoptions']      = 'Options';
$string['inboxtableheaderphone']        = 'Phone Number';
$string['inboxtableheadername']         = 'Source Name';
$string['inboxtableheadertags']         = 'Tags';
$string['inboxtableheadertime']         = 'Time Received';

// Inbox folders page

$string['inboxfolderstitle']            = 'MoodletTxt Inbox folders for';
$string['inboxfoldersheader']           = 'Your Inbox Folders';
$string['inboxfoldersexisting']         = 'Your existing folders';
$string['inboxfoldersadded']            = 'Folder was added successfully.';
$string['inboxfoldersdeleted']          = 'Folder was deleted successfully.';
$string['inboxfoldersadd']              = 'Add a new folder';
$string['inboxfolderslblname']          = 'Folder name:';
$string['inboxfoldersdel']              = 'Delete an existing folder';
$string['inboxfolderslblkill']          = 'Select folder to delete:';
$string['inboxfolderslbldest']          = 'Move messages in this folder to:';

// Labels

$string['labelactions']                 = 'Actions:';
$string['labeladditionalname']          = 'Enter first name/last name:';
$string['labeladditionalnumber']        = 'Enter number here (eg +44123456789):';
$string['labeladdresscontacts']         = 'Address Book Contacts:';
$string['labeladdressgroups']           = 'Address Book Groups:';
$string['labeladdsignature']            = 'Add signature:';
$string['labelblocktitle']              = 'Block title';
$string['labelcharspermessage']         = 'Characters per message:';
$string['labelcharsremaining']          = 'Characters remaining:';
$string['labelcharsused']               = 'Characters/messages used:';
$string['labelfinalrecipients']         = 'Message Recipients';
$string['labelfolderjump']              = 'Jump to:';
$string['labelhideinboundsources']      = 'Hide sources on inbox page:';
$string['labelinboundliveinterval']     = 'How often to check with Moodle for new messages:';
$string['labelinterval1min']            = '1 minute';
$string['labelinterval2min']            = '2 minutes';
$string['labelinterval5min']            = '5 minutes';
$string['labelinterval10min']           = '10 minutes';
$string['labelinterval15min']           = '15 minutes';
$string['labelmergetags']               = 'Insert into the message:';
$string['labelmessageauthor']           = 'Message Author:';
$string['labelmessagetext']             = 'Message Text:';
$string['labelnewtag']                  = 'New Tag:';
$string['labelpotentialrecipients']     = 'Potential Recipients:';
$string['labelrecipients']              = 'Recipients:';
$string['labelrecipienttypeselect']     = 'Show:';
$string['labelrestrictedusers']         = 'Allowed Users:';
$string['labelscheduledfor']            = 'Scheduled For:';
$string['labelscheduletime']            = 'Schedule time';
$string['labelschedulenow']             = 'Send now';
$string['labelschedulelater']           = 'Schedule to send later';
$string['labelselectedrecipients']      = 'Selected Recipients';
$string['labelsignature']               = 'Signature:';
$string['labelsearchusers']             = 'Search Users:';
$string['labelstatusdelivered']         = 'Message delivery confirmed as received by the mobile phone.';
$string['labelstatusfailed']            = 'Message delivery has failed and will not be delivered to the mobile phone.';
$string['labelstatustransit']           = 'Message has been sent. No errors or handset delivery confirmation received.';
$string['labelsuppressunicodeno']       = 'Send full unicode messages';
$string['labelsuppressunicodenodesc']   = '(default - messages containing non-GSM characters are restricted to 70 characters)';
$string['labelsuppressunicodeyes']      = 'Restrict message to GSM character set only';
$string['labelsuppressunicodeyesdesc']  = '(all messages are 160 characters long)';
$string['labeltagdraganddrop']          = 'Drag and drop tags to and from messages.';
$string['labeltemplates']               = 'Message Templates:';
$string['labeltemplatesexist']          = 'Existing Templates';
$string['labeltemplatetext']            = 'Template Text:';
$string['labeltimesent']                = 'Time Sent:';
$string['labeltxttoolsaccount']         = 'ConnectTxt account:';
$string['labelusergroups']              = 'Moodle User Groups:';
$string['labeluserlist']                = 'Moodle Users:';

// Fieldset legends

// Links

$string['linkaddaccount']               = 'Add New Account';

// Logging strings

$string['logxmlblockcreated']           = 'Created XML request block:';
$string['logxmlblocksent']              = 'Sending packet to ConnectTxt through XML connector:';
$string['logxmlresponse']               = 'Raw response from ConnectTxt:';
$string['logxmlparsedobjects']          = 'Parsed inbound objects:';

// Manage Groups page

$string['mangroupsactionleave']         = 'Delete the group, leave contacts in address book';
$string['mangroupsactionmerge']         = 'Delete the group and merge its contacts into';
$string['mangroupsactionnuke']          = 'Delete the group and all contacts in it';
$string['mangroupsadded']               = 'Group added successfully.';
$string['mangroupsbutaddgroup']         = 'Add Group';
$string['mangroupsbutdelete']           = 'Delete Group';
$string['mangroupsbutton']              = 'Update Group';
$string['mangroupscontactsdeleted']     = 'Group and contacts deleted.';
$string['mangroupsdeleted']             = 'Group successfully deleted';
$string['mangroupsheader']              = 'Manage Groups';
$string['mangroupslbldelete']           = 'Group to delete:';
$string['mangroupslblgrouplist']        = 'Select Group:';
$string['mangroupslblnewdesc']          = 'Description:';
$string['mangroupslblnewname']          = 'Group Name:';
$string['mangroupslblpotential']        = 'Potential group members:';
$string['mangroupslblselected']         = 'Selected group members:';
$string['mangroupslegaddgroup']         = 'Add a Group';
$string['mangroupslegdelete']           = 'Delete a Group';
$string['mangroupslegupdate']           = 'Update Group Members';
$string['mangroupsmerged']              = 'Group successfully merged.';
$string['mangroupstitle']               = 'Manage Groups in ';
$string['mangroupsupdated']             = 'Group updated.';

// Capability Names

$string['moodletxt:addressbooks']       = 'Create Addressbooks';
$string['moodletxt:adminsettings']      = 'Administer System Settings';
$string['moodletxt:adminusers']         = 'View/Change User Settings';
$string['moodletxt:defaultinbox']       = 'Receive Unfiltered Messages';
$string['moodletxt:globaladdressbooks'] = 'Create Global Addressbooks';
$string['moodletxt:personalsettings']   = 'Can create templates/change own settings';
$string['moodletxt:receivemessages']    = 'Receive Inbound Messages';
$string['moodletxt:sendmessages']       = "Send Messages";
$string['moodletxt:viewstats']          = "View user statistics";

// Navigation linkage
$string['navaccounts']                  = 'ConnectTxt Accounts';
$string['navfilters']                   = 'Inbound Filters';
$string['navmoodletxt']                 = 'moodletxt';
$string['navnewaccount']                = 'Adding a New Account';
$string['navnewinstall']                = 'New Installation';
$string['navpreferences']               = 'My Preferences';
$string['navreceivedmessages']          = 'Received Messages';
$string['navsend']                      = 'Send a Message';
$string['navsent']                      = 'Sent Messages';
$string['navsliderecipients']           = 'Step 1: Select recipients';
$string['navslidemessage']              = 'Step 2: Compose message';
$string['navslidemessageopts']          = 'Step 3: Message Options';
$string['navslidereview']               = 'Step 4: Review &amp; Send';

// Page notifications
$string['notifyprefsupdated']           = 'Preferences saved to system.';
$string['notifytemplateadded']          = 'The template was added to the system.';
$string['notifytemplatedeleted']        = 'The template was deleted successfully.';
$string['notifytemplateupdated']        = 'The template was successfully updated.';

// Select options

$string['optionchoosetemplate']         = 'Choose a template...';
$string['optioncopy']                   = 'Copy to...';
$string['optiondelete']                 = 'Delete';
$string['optionfolders']                = 'Your folders...';
$string['optionmove']                   = 'Move to...';
$string['optionwithselected']           = 'With selected...';

// XML parser

$string['parserinvalidlogin']           = 'The ConnectTxt account details used are invalid.  Either the username or password entered are invalid, or the account selected has not been enabled for use with moodletxt.  Please check your ConnectTxt account details and try again.  If you still encounter problems, please contact ConnectTxt to make sure your account has been activated.';
$string['parserinvalidxml']             = 'The connection to the ConnectTxt system was rejected because the data sent was invalid.  You may be using a version of moodletxt that is out of date.  Please contact bbconnecttxt.com for more information.';

// Redirects

$string['redirectaccountsfound']        = 'ConnectTxt accounts found, redirecting to account admin...';
$string['redirectmessagesent']          = 'Message sent. We are now taking you to the sent page. Hold tight!';
$string['redirectnoaccountsfound']      = 'No ConnectTxt accounts found. Taking you to the new installation page...';

// Send page

$string['sendconfirmcharacters']        = 'characters';
$string['sendconfirmmessage']           = 'Message:';
$string['sendconfirmschedule']          = 'The message will be sent';
$string['sendconfirmsms']               = 'SMS messages per contact';
$string['sendfragnext']                 = 'Next';
$string['sendfragprev']                 = 'Back';
$string['sendheading']                  = 'Send a Text Message';
$string['sendkeyuser']                  = 'Moodle user';
$string['sendkeyusergroup']             = 'Moodle user group';
$string['sendkeyab']                    = 'Address book contact';
$string['sendkeyabgroup']               = 'Address book group';
$string['sendkeyadd']                   = 'Additional number';
$string['sendlabelaccountdesc']         = 'Account description:';
$string['sendlabeladdsig']              = 'Add signature:';
$string['sendlabelcolourkey']           = 'Colour key:';
$string['sendlabelchooseaccount']       = 'Please choose one';
$string['sendlabelerrorsfound']         = 'Errors were found on the highlighted slides. Please correct these and try again.';
$string['sendlabelgroups']              = 'Group';
$string['sendlabelindividuals']         = 'Individual ';
$string['sendlabelmessage']             = 'Message:';
$string['sendlabelnotemplates']         = 'No templates found';
$string['sendlabelrecipienttype']       = 'Choose...';
$string['sendlabelservertime']          = 'Current server time is';
$string['sendlabeltemplates']           = 'Templates';
$string['sendlegendaccounts']           = 'ConnectTxt Accounts';
$string['sendlegendschedule']           = 'Scheduling';
$string['sendlegendunicode']            = 'Unicode Messaging Options';
$string['sendmultipleaccounts']         = 'You may have access to more than one ConnectTxt account on this course.  Please select which one to use when sending this text message.';
$string['sendtabs1']                    = 'Moodle Users';
$string['sendtabs2']                    = 'Address Books';
$string['sendtabs3']                    = 'Additional';
$string['sendtitle']                    = 'Send a message from';

// Sent page

$string['sentnoticefrag1']              = ' sent messages and ';
$string['sentnoticefrag2']              = ' recipients were found in the database.';
$string['sentnoticefrag3']              = ' of the messages sent originated from this account.';
$string['sentnoticestatslink']          = 'Go back to the stats page';

// Settings page

$string['settingsaddressbooklink']      = 'Go to your address books';
$string['settingsedittemplate']         = 'Update Template';
$string['settingsinboxlink']            = 'Go to your inbox';
$string['settingssendtextlink']         = 'Send a text message from';
$string['settingssentmessageslink']     = 'View your sent messages';
$string['settingssubmittemplate']       = 'Add Template';

// Status update info

$string['statuskeyfailedalt']           = 'Failed';
$string['statuskeyfailedtitle']         = 'Delivery has failed';
$string['statuskeysentalt']             = 'Sent';
$string['statuskeysenttitle']           = 'Message sent, but not yet confirmed';
$string['statuskeyreceivedalt']         = 'Delivered';
$string['statuskeyreceivedtitle']       = 'Message received';

// Stats page

$string['statsheader']                  = 'Viewing moodletxt User Statistics';
$string['statslabelmodebutton']         = 'Change mode';
$string['statslabelswitchmode']         = 'Statistics mode:';
$string['statsmodeallusers']            = 'Show number sent by user';
$string['statsmodealldates']            = 'Show number sent by date';
$string['statspara1']                   = 'This page allows you to view statstics on the number of messages sent through moodletxt.  You can view message statistics by user or by date - select your choice from the drop-down menu on the right.  You can also click on a particular user/date\'s message count to view all messages sent by that user/on that date.';
$string['statstableheaderdate']         = 'Date';
$string['statstableheadermessages']     = 'Number of messages sent';
$string['statstableheaderuser']         = 'User';
$string['statstitle']                   = 'moodletxt User Statistics';

// Page titles
$string['titleaccountrestrictions']     = 'Outbound Account Restrictions';
$string['titleaddaccount']              = 'Add a ConnectTxt account';
$string['titlefilters']                 = 'Manage Filters';
$string['titlenewinstall']              = 'moodletxt - New Installation';
$string['titlepreferences']             = 'moodletxt - My Preferences';
$string['titlereceivedmessages']        = 'moodletxt Inbox for';
$string['titlesend']                    = 'Sending a Text Message from';
$string['titlesent']                    = 'moodletxt History for';
$string['titlestatus']                  = 'Viewing a Message in';


// Table headers

$string['tableheaderaccounttype']       = 'Account Type';
$string['tableheaderallowinbound']      = 'Allow Inbound';
$string['tableheaderallowoutbound']     = 'Allow Outbound';
$string['tableheadercreditsleft']       = 'Credits Remaining';
$string['tableheadercreditsused']       = 'Credits Used';
$string['tableheaderdescription']       = 'Description';
$string['tableheaderdestination']       = 'Destination Number';
$string['tableheaderlastupdate']        = 'Last Update';
$string['tableheadermessages']          = 'Messages Sent';
$string['tableheadermessagetext']       = 'Message Text';
$string['tableheaderrecipient']         = 'Recipient';
$string['tableheaderstatus']            = 'Message Status';
$string['tableheadertimesent']          = 'Time Sent';
$string['tableheadertimeupdated']       = 'Time of Update';
$string['tableheadertxttoolsaccount']   = 'ConnectTxt Account';
$string['tableheaderuser']              = 'Moodle User';
$string['tableheaderusername']          = 'Username';


// View Message Page

$string['viewunknownlink']              = 'Unknown';

// Warnings

$string['warnunicode']                  = 'The message you are writing contains characters outside the default SMS alphabet. Messages sent using these characters can only be a maximum of 70 characters long, rather than the usual 160. The number of message credits used may be affected as a result. In addition, the receiving phone may not support these characters.  Please be certain you wish to send a unicode message.';
$string['warnunicodesuppressed']        = 'The message you are writing contains characters outside the default SMS alphabet, but unicode suppression is switched on. Any characters in this message that do not fall within the GSM character set may not appear correctly on the receiving phone. To correct this, edit the non-GSM characters out of your message, or turn off unicode suppression under message options.';

?>
