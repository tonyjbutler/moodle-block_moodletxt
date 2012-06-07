---------------------------------
MOODLETXT SMS PLUGIN FOR TXTTOOLS

Author: Greg J Preece
Company: Blackboard ConnectTxt
Country: England
Contact: txttoolssupport@blackboard.com
Version: 3.0 Beta 2
Release Date: 1st June 2012
---------------------------------


--WHAT IS IT?--

moodletxt is a block that allows teachers and administrators to send SMS/text 
messages to their students directly from the Moodle system. It is available 
free of charge to all txttools.co.uk customers, and trial accounts can be 
provided on request. The module supports both inbound and outbound messaging, 
allowing teachers to text their students and receive replies without ever 
leaving Moodle. It also supports text message status updates, user-personalised 
messages, message templates and signatures, and more.


--INSTALLATION--

Installation is the same as any other block: simply drop the moodletxt folder
into your Moodle installation's /blocks directory, then log into Moodle as
an administrator and click the Notifications link on the Site Administration
menu. The automatic installation/upgrade scripts will do the rest.

When upgrading, it is recommended to remove the old moodletxt block completely
and replace it with a fresh copy from the new installer, before running the
upgrade script. All data is held within the database, so you will not lose 
anything by doing this, and it helps prevent conflicts between versions.


--STAYING UP TO DATE--

As well as the Moodle plugin repository, news and updates on the plugin can
be found on our website, at the following address:

http://www.txttools.co.uk/preloginjsp/txttools/plugins.jsp

An RSS feed is available at:

http://www.txttools.co.uk/preloginjsp/moodletxt/rss.xml

(Please note that these URLs will soon change. New URLs will be posted
at txttools.co.uk when the change occurs.)


--NEW IN BETA 2--

* Fixed issues with unhandled exceptions when a Moodle user has an
  invalid or blank phone number in their profile

* Fixed issue on control panel with error reporting when adding ConnectTxt
  accounts that already exist within the system


--NEW IN BETA 1--

* Full re-write to support Moodle 2.0 and PHP 5 completely

* Deep integration with Moodle 2.0 output/form/table libraries

* Data export on all relevant tables (sent messages, inbox, etc)

* Ditched inbox folders in favour of tags, which are many-to-many, rather
  than a message only being contained in one folder


--KNOWN BETA ISSUES--

* Address books are disabled in this beta, but will be re-introduced later
  (No data is lost when upgrading to the beta

* User stats are disabled in this beta, but will be re-introduced later


--STILL TO COME IN FINAL RELEASE--

* Re-introduction of address books and user stats, along with upgraded stats
  page featuring a larger range of metrics and visual graphing

* Per-page help bar, including textual and video documentation, as well as a
  live chat link to our customer support team

* Integration with Moodle 2.0's messaging hooks system, so messages sent from
  anywhere within Moodle can go via moodletxt's SMS system

* Support for Moodle's automatic backup/restore system


--LICENCE--

moodletxt is distributed as GPLv3 software, and is provided free of charge without warranty. 
A full copy of this licence can be found @
http://www.gnu.org/licenses/gpl.html
In addition to this licence, as described in section 7, we add the following terms:
  - Derivative works must preserve original authorship attribution (@author tags and other such notices)
  - Derivative works do not have permission to use the trade and service names 
    "txttools", "moodletxt", "Blackboard", "Blackboard Connect" or "Cy-nap"
  - Derivative works must be have their differences from the original material noted,
    and must not be misrepresentative of the origin of this material, or of the original service

Anyone using, extending or modifying moodletxt indemnifies the original authors against any contractual
or legal liability arising from their use of this code.