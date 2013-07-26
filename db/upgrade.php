<?php

/**
 * XMLDB upgrade file for moodletxt
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
 * @version 2013071001
 * @since 2008081212
 */

require_once($CFG->dirroot . '/blocks/moodletxt/lib/MoodletxtLegacyEncryption.php');
require_once($CFG->dirroot . '/blocks/moodletxt/lib/MoodletxtEncryption.php');

function xmldb_block_moodletxt_upgrade($oldversion = 0) {

    global $CFG, $DB;
    $dbman = $DB->get_manager(); // Get database manager

    $BLOCK_RECORD = $DB->get_record('block', array('name' => 'moodletxt'));
    
    /**
     * Any release before 2.4 is invalid. User must
     * be on at least moodletxt 2.4 before upgrading to 3.0.
     */
    if ($oldversion < 2011032901) {
        throw new upgrade_exception('moodletxt', 2012052901, 
            'Upgrading to moodletxt 3.0 from below moodletxt 2.4 is not supported. Please ensure you are running at least moodletxt 2.4 before upgrading.');
    }

    /**
     * moodletxt 2.4.1 - the inevitable round
     * of bugfixes from the big release in April
     * Mainly processing fixes. Corrected a bug in the admin panel,
     * some backporting issues, and added GPLv3 licencing headers.
     */
    if ($oldversion < 2011101101) {

        // This database field somehow snuck into the upgrade script
        // and started causing trouble, despite the fact that it's never used.
        // Ever. Not once.  So let's murder it to death!
        $table = new xmldb_table('block_mtxt_outbox');
        $field = new xmldb_field('suppresunicode', XMLDB_TYPE_INTEGER, '1', 
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null); // Spelling is correct...

        if ($dbman->field_exists($table, $field))
            $dbman->drop_field($table, $field);

        // Save point!
        upgrade_block_savepoint(true, 2011101101, 'moodletxt');

    }
    
    /**
     * moodletxt 2.4.2 - Changes required for plugin validation
     * in the new repository. I've got to rename all my tables!
     * Again! Be nice if I had any space left in 
     * 30 characters after the three mandatory prefixes, but whatever.
     * Apologies to Oracle users everywhere.
     */
    if ($oldversion < 2011101201) {
        
        $table = new xmldb_table('block_mtxt_ab');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_ab');

        $table = new xmldb_table('block_mtxt_ab_entry');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_ab_entry');

        $table = new xmldb_table('block_mtxt_ab_groups');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_ab_group');

        $table = new xmldb_table('block_mtxt_ab_users');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_ab_u');

        $table = new xmldb_table('block_mtxt_accounts');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_accounts');

        $table = new xmldb_table('block_mtxt_config');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_config');

        $table = new xmldb_table('block_mtxt_filter');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_filter');

        $table = new xmldb_table('block_mtxt_in_ab');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_in_ab');

        $table = new xmldb_table('block_mtxt_in_filter');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_in_fil');

        $table = new xmldb_table('block_mtxt_in_folders');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_in_fold');

        $table = new xmldb_table('block_mtxt_in_mess');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_in_mess');

        $table = new xmldb_table('block_mtxt_in_user');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_in_u');

        $table = new xmldb_table('block_mtxt_inbox');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_inbox');

        $table = new xmldb_table('block_mtxt_outbox');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_outbox');

        $table = new xmldb_table('block_mtxt_rss');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_rss');

        $table = new xmldb_table('block_mtxt_sent');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_sent');

        $table = new xmldb_table('block_mtxt_sent_ab');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_sent_ab');

        $table = new xmldb_table('block_mtxt_sent_user');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_sent_u');

        $table = new xmldb_table('block_mtxt_stats');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_stats');

        $table = new xmldb_table('block_mtxt_status');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_status');

        $table = new xmldb_table('block_mtxt_templates');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_templ');

        $table = new xmldb_table('block_mtxt_uconfig');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_uconfig');

        upgrade_block_savepoint(true, 2011101201, 'moodletxt');
    }
    
    if ($oldversion < 2011101202) {
        
        $table = new xmldb_table('block_mtxt_ab_grpmem');
        if ($dbman->table_exists($table))
            $dbman->rename_table($table, 'block_moodletxt_ab_gmem');
        
        upgrade_block_savepoint(true, 2011101202, 'moodletxt');
    }
        
    /**
     * 2012052301 is 2.4.3
     * Upgrade script patches from 2.4.2 - no DB upgrades 
     */
    if ($oldversion < 2012052301) {
        
        // Nothing to do
        upgrade_block_savepoint(true, 2012052301, 'moodletxt');
        
    }
    
    /**
     * moodletxt 3.0 beta 1
     * Full re-write to Moodle 2.x
     * New fancy inbox page with tags instead of folders
     */
    if ($oldversion < 2012052901) {

        $configTable = new xmldb_table('block_moodletxt_config');

        if ($dbman->table_exists($configTable)) {

            // Port all config to Moodle config table
            $configRecords = $DB->get_records('block_moodletxt_config');

            foreach($configRecords as $configRecord)
                set_config($configRecord->setting, $configRecord->value, 'moodletxt');

            // Drop old table
            $dbman->drop_table($configTable);
        }

        $passwordsAlreadyUpgraded = get_config('moodletxt', 'Passwords_Upgraded_3_0');

        if ($passwordsAlreadyUpgraded === false || $passwordsAlreadyUpgraded != 1) {

            // Upgrade all passwords to be compatible with new
            // encryption class.  Might as well improve the
            // encryption key while we're here
            $oldKey = get_config('moodletxt', 'EK');
            $newKey = substr(md5(mt_rand()), 0, 10);

            $legacyEncrypter = new MoodletxtLegacyEncryption();
            $newEncrypter = new MoodletxtEncryption();

            $accountRecords = $DB->get_records('block_moodletxt_accounts');

            foreach($accountRecords as $accountRecord){
                $rawPassword = $legacyEncrypter->decrypt($oldKey, $accountRecord->password);
                $accountRecord->password = $newEncrypter->encrypt($newKey, $rawPassword, 20);
                $DB->update_record('block_moodletxt_accounts', $accountRecord);
            }

            // Save new EK
            set_config('EK', $newKey, 'moodletxt');
            set_config('Passwords_Upgraded_3_0', 1, 'moodletxt');

        }
        
        // Kill off old ENUM fields - no longer supported in Moodle 2.0
        
        $table = new xmldb_table('block_moodletxt_filter');
        $field = new xmldb_field('type', XMLDB_TYPE_CHAR, '7', null, 
            XMLDB_NOTNULL, null, 'KEYWORD', 'account');

        if ($dbman->field_exists($table, $field) && 
            $dbman->check_constraint_exists($table, $field))
            $dbman->drop_enum_from_field($table, $field);

        
        $table = new xmldb_table('block_moodletxt_ab');
        $field = new xmldb_field('type', XMLDB_TYPE_CHAR, '7', null, 
            XMLDB_NOTNULL, null, 'global', 'name');

        if ($dbman->field_exists($table, $field) &&
            $dbman->check_constraint_exists($table, $field))
            $dbman->drop_enum_from_field($table, $field);
        
        
        
        // Split sent name on sent messages table into first and last names
        // This is for compatibility with the new recipient set within moodletxt
        
        $table = new xmldb_table('block_moodletxt_sent');
        $field = new xmldb_field('sendfirstname', XMLDB_TYPE_CHAR, '50', null, 
            XMLDB_NOTNULL, null, get_string('fragunknownname', 'block_moodletxt'), 'destination');

        if (! $dbman->field_exists($table, $field))
            $dbman->add_field($table, $field);

        $field = new xmldb_field('sendlastname', XMLDB_TYPE_CHAR, '50', null, 
            XMLDB_NOTNULL, null, get_string('fragunknownname', 'block_moodletxt'), 'sendfirstname');

        if (!$dbman->field_exists($table, $field))
            $dbman->add_field($table, $field);
        
        // Move records into new structure and drop the old field
        $field = new xmldb_field('sendname');
        
        if ($dbman->field_exists($table, $field)) {
        
            $sentMessages = $DB->get_records('block_moodletxt_sent');

            if (count($sentMessages) > 0) {

                foreach($sentMessages as $sentMessage) {
                    $nameFrags = explode(" ", $sentMessage->sendname);
                    $sentMessage->sendlastname = str_replace(",", " ", $nameFrags[0]);
                    $sentMessage->sendfirstname = $nameFrags[1];
                }

                $DB->update_record('block_moodletxt_sent', $sentMessage, true);

            }

            $dbman->drop_field($table, $field);
            
        }
        
        
        // Split source name on inbox messages table into first name and last name
        // Again, for compatibility with new recipient set

        $table = new xmldb_table('block_moodletxt_in_mess');
        $field = new xmldb_field('sourcefirstname', XMLDB_TYPE_CHAR, '50', null, 
            XMLDB_NOTNULL, null, get_string('fragunknownname', 'block_moodletxt'), 'sourcename');

        if (!$dbman->field_exists($table, $field))
            $dbman->add_field($table, $field);

        $field = new xmldb_field('sourcelastname', XMLDB_TYPE_CHAR, '50', null, 
            XMLDB_NOTNULL, null, get_string('fragunknownname', 'block_moodletxt'), 'sourcefirstname');

        if (!$dbman->field_exists($table, $field))
            $dbman->add_field($table, $field);

        // Move records into new structure and drop old field
        $field = new xmldb_field('sourcename');

        if ($dbman->field_exists($table, $field)) {

            $receivedMessages = $DB->get_records('block_moodletxt_in_mess');

            if (count($receivedMessages) > 0) {

                foreach($receivedMessages as $receivedMessage) {
                    $nameFrags = explode(" ", $receivedMessage->sourcename);
                    $receivedMessage->sourcelastname = str_replace(",", " ", $nameFrags[0]);
                    $receivedMessage->sourcefirstname = $nameFrags[1];
                }

                $DB->update_record('block_moodletxt_in_mess', $receivedMessage, true);

            }


            $dbman->drop_field($table, $field);

        }
        
        
        // Create new table to restrict outbound access on user accounts
        $table = new xmldb_table('block_moodletxt_restrict');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        $table->add_field('txttoolsaccount', XMLDB_TYPE_INTEGER, '10', 
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);

        $table->add_field('moodleuser', XMLDB_TYPE_INTEGER, '10', 
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk-txttoolsaccount', XMLDB_KEY_FOREIGN, array('txttoolsaccount'), 'block_moodletxt_accounts', array('id'));
        $table->add_key('fk-moodleuser', XMLDB_KEY_FOREIGN, array('moodleuser'), 'user', array('id'));

        if (!$dbman->table_exists($table))
            $dbman->create_table($table);
        
        
        /*
         * RE-WORKING INBOXES
         * Dropping folders and replacing them with tags.
         */
        
        // Add any new fields and keys to message table            
        $table = new xmldb_table('block_moodletxt_in_mess');

        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, null, 0, 'id');

        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // Grrr, why doesn't ADODB have a method for checking key existence??
            $key = new xmldb_key('fk-message-user', XMLDB_KEY_FOREIGN, 
                    array('userid'), 'user', array('id'));

            $dbman->add_key($table, $key);

        }

        // Add any new fields and keys to filter link table
        $table = new xmldb_table('block_moodletxt_in_fil');

        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, null, 0, 'filter');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            $key = new xmldb_key('fk-filter-user', XMLDB_KEY_FOREIGN, 
                    array('userid'), 'user', array('id'));

            $dbman->add_key($table, $key);            

        }

        // Adds any new fields and keys to accounts table
        $table = new xmldb_table('block_moodletxt_accounts');

        $field = new xmldb_field('defaultuser', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, null, 0, 'description');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // This key needs to be dropped earlier than we would normally drop it,
            // as fk-default-inbox and fk-default-user get converted to the same
            // key name at the database level!
            $key = new xmldb_key('fk-default-inbox', XMLDB_KEY_FOREIGN, 
                    array('defaultinbox'), 'block_moodletxt_inbox', array('id'));

            $dbman->drop_key($table, $key);

            $key = new xmldb_key('fk-default-user', XMLDB_KEY_FOREIGN, 
                    array('defaultuser'), 'user', array('id'));

            $dbman->add_key($table, $key);

        }
        
        
        
        // Create new table for inbox tags
        $table = new xmldb_table('block_moodletxt_tags');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, null, null);

        $table->add_field('name', XMLDB_TYPE_CHAR, '50', null, 
                XMLDB_NOTNULL, null, null);

        $table->add_field('colour', XMLDB_TYPE_CHAR, '7', null, 
                null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk-tag-owner', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        if (!$dbman->table_exists($table))
            $dbman->create_table($table);


        // Create new table to link tags to messages
        $table = new xmldb_table('block_moodletxt_in_tag');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        $table->add_field('message', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, null, null);

        $table->add_field('tag', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table))
            $dbman->create_table($table);

        
        
        // Move message data into new structure
        $sql = 'SELECT folders.*, usertable.id as userid
                FROM {block_moodletxt_in_fold} folders
                INNER JOIN {block_moodletxt_inbox} inbox
                    ON folders.inbox = inbox.id
                INNER JOIN {user} usertable
                    ON inbox.userid = usertable.id';

        $folderSet = $DB->get_records_sql($sql);

        foreach($folderSet as $folder) {
            $DB->set_field('block_moodletxt_in_mess', 
                'userid', $folder->userid, array('folderid' => $folder->id));
        }

        // Move filter and account data into new structure
        $sql = 'SELECT inbox.id as inboxid, usertable.id as userid
                FROM {block_moodletxt_inbox} inbox                
                INNER JOIN {user} usertable
                    ON inbox.userid = usertable.id';

        $inboxSet = $DB->get_records_sql($sql);

        foreach($inboxSet as $inbox) {
            $DB->set_field('block_moodletxt_in_fil',
                'userid', $inbox->userid, array('inbox' => $inbox->inboxid));

            $DB->set_field('block_moodletxt_accounts',
                'defaultuser', $inbox->userid, array('defaultinbox' => $inbox->inboxid));
        }

        // Change folders into tags - heavy update            
        foreach($folderSet as $folder) {

            // Write new tag record
            $tagObject = new object();
            $tagObject->userid = $folder->userid;
            $tagObject->name   = $folder->name;
            $tagObject->colour = '#ffffff'; // Default to white highlighting - non-disruptive

            $tagId = $DB->insert_record('block_moodletxt_tags', $tagObject);

            $messagesWithThisTag = $DB->get_records('block_moodletxt_in_mess', 
                    array('folderid' => $folder->id), '', 'id');

            foreach($messagesWithThisTag as $message) {

                // Write new link record
                $linkObject = new object();
                $linkObject->message = $message->id;
                $linkObject->tag = $tagId;

                $DB->insert_record('block_moodletxt_in_tag', $linkObject);

            }

        }
        
        
        // Drop old fields and keys from message table
        $table = new xmldb_table('block_moodletxt_in_mess');

        $key = new xmldb_key('fk-message-folder', XMLDB_KEY_FOREIGN, 
            array('folderid'), 'block_moodletxt_in_fold', array('id'));

        // ->drop_key() has no return value. Consistency!
        $dbman->drop_key($table, $key);

        $field = new xmldb_field('folderid');

        if ($dbman->field_exists($table, $field))
            $dbman->drop_field($table, $field);


        // Drop old fields and keys from filter link table
        $table = new xmldb_table('block_moodletxt_in_fil');

        $key = new xmldb_key('fk-filter-inbox', XMLDB_KEY_FOREIGN, 
                array('inbox'), 'moodletxt_inbox', array('id'));

        $dbman->drop_key($table, $key);

        $field = new xmldb_field('inbox');

        if ($dbman->field_exists($table, $field))
            $dbman->drop_field($table, $field);


        // Drop old fields and keys from user account table
        $table = new xmldb_table('block_moodletxt_accounts');

        // Foreign key for default inbox field has already been dropped above for bugfix
        
        $field = new xmldb_field('defaultinbox');

        if ($dbman->field_exists($table, $field))
            $dbman->drop_field($table, $field);


        // Drop inbox folder table
        $table = new xmldb_table('block_moodletxt_in_fold');

        if ($dbman->table_exists($table))
            $dbman->drop_table($table);


        // Drop inbox parent table
        $table = new xmldb_table('block_moodletxt_inbox');

        if ($dbman->table_exists($table))
            $dbman->drop_table($table);


        // We have to drop keys pointing at the messages table before we
        // can rename it, then re-apply the keys
        $table = new xmldb_table('block_moodletxt_in_ab');

        $key = new xmldb_key('fk-receivedmessage', XMLDB_KEY_FOREIGN, 
                array('receivedmessage'), 'block_moodletxt_in_mess', array('id'));

        $dbman->drop_key($table, $key);


        $table = new xmldb_table('block_moodletxt_in_u');

        $key = new xmldb_key('fk-receivedmessage', XMLDB_KEY_FOREIGN, 
                array('receivedmessage'), 'block_moodletxt_in_mess', array('id'));

        $dbman->drop_key($table, $key);


        // Rename messages table to be the inbox table
        $table = new xmldb_table('block_moodletxt_in_mess');

        $dbman->rename_table($table, 'block_moodletxt_inbox');


        // Re-apply foreign keys into renamed messages table
        $table = new xmldb_table('block_moodletxt_in_ab');

        $key = new xmldb_key('fk-receivedmessage', XMLDB_KEY_FOREIGN, 
                array('receivedmessage'), 'block_moodletxt_inbox', array('id'));

        $dbman->add_key($table, $key);


        $table = new xmldb_table('block_moodletxt_in_u');

        $key = new xmldb_key('fk-receivedmessage', XMLDB_KEY_FOREIGN, 
                array('receivedmessage'), 'block_moodletxt_inbox', array('id'));

        $dbman->add_key($table, $key);
            
        // Save point!
        upgrade_block_savepoint(true, 2012052901, 'moodletxt');

    }
    
    /**
     * moodletxt 3.0 beta 2
     * Fixing code errors with users missing phone numbers 
     */
    if ($oldversion < 2012060101) {
        
        upgrade_block_savepoint(true, 2012060101, 'moodletxt');
        
    }
    
    /**
     * moodletxt 3.0 final
     * Adding support for moodletxt+ and encrypted settings
     */
    if ($oldversion < 2012103001) {
        
        // Initialise event messaging settings if they don't exist
        if (get_config('moodletxt', 'Event_Messaging_Account') === false)
            set_config('Event_Messaging_Account', '0', 'moodletxt');

        // Add a flag to the outbox to show whether this is an event-generated message
        $table = new xmldb_table('block_moodletxt_outbox');
        $field = new xmldb_field('fromevent', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, 
                XMLDB_NOTNULL, null, '0', 'type');

        if (!$dbman->field_exists($table, $field))
            $dbman->add_field($table, $field);
        
        
        // In the betas, password values coming from settings.php were not encrypted.
        // This is now supported, so let's encrypt any existing passwords.
        $passwordsAlreadyUpgraded = get_config('moodletxt', 'Settings_Encrypted_3_0');

        if ($passwordsAlreadyUpgraded === false || $passwordsAlreadyUpgraded != 1) {
            
            $key           = get_config('moodletxt', 'EK');
            $pushPassword  = get_config('moodletxt', 'Push_Password');
            $proxyPassword = get_config('moodletxt', 'Proxy_Password');
            
            $encrypter = new MoodletxtEncryption();
            
            if ($pushPassword != '') {
                
                $pushPassword = $encrypter->encrypt($key, $pushPassword);
                set_config('Push_Password', $pushPassword, 'moodletxt');
                
            }
            
            if ($proxyPassword != '') {
                
                $proxyPassword = $encrypter->encrypt($key, $proxyPassword);
                set_config('Proxy_Password', $proxyPassword, 'moodletxt');
                
            }
         
            set_config('Settings_Encrypted_3_0', '1', 'moodletxt');
            
        }
        
        upgrade_block_savepoint(true, 2012103001, 'moodletxt');        
        
    }
    
    /**
     * 3.0.1 release
     * Patch related to running the block on MySQL 5.1 and above
     */
    if ($oldversion < 2012110501) {
        
        // Nothing to do - code patches only
        
        upgrade_block_savepoint(true, 2012110501, 'moodletxt');
    }
    
    /**
     * 3.0.2 release
     * Patched permissions error on sent page, updated CSS image links,
     * removed disabled accounts from filter management listing.
     */
    if ($oldversion < 2012112901) {
        
        // Nothing to do - code patches only
        
        upgrade_block_savepoint(true, 2012112901, 'moodletxt');
    }
    
    /**
     * 3.0.3 release
     * Patches for Moodle 2.4 compatibility (table display library).
     * Fixed error with scheduling on the send page.
     */
    if ($oldversion < 2013011001) {
        
        // Nothing to do - code patches only
        
        upgrade_block_savepoint(true, 2013011001, 'moodletxt');
    }

    /**
     * 3.0.4 release
     * Patching two Javascript issues:
     *  -Number of accounts incorrectly calculated in control panel 
     *   (table display library in 2.4)
     *  -Javascript caching broken by malformed CDATA comment in lib.js
     */
    if ($oldversion < 2013032101) {
        
        // Nothing to do - code patches only
        
        upgrade_block_savepoint(true, 2013032101, 'moodletxt');
    }
    
    /**
     * 3.0.5-rc1 release
     * Prepping Moodletxt for Moodlerooms distribution and updating
     * for better compatibility with Moodle 2.5
     * AJAX fixes on admin panel
     * Group display fix on compose page
     * Users can now blank names when editing addressbook contacts
     * Fixed rare installation issue with settings.php being called before run
     */
    if ($oldversion < 2013061901) {
        
        // Nothing to do - code patches only
        
        upgrade_block_savepoint(true, 2013061901, 'moodletxt');
    }
    
    /**
     * 3.0.5-rc2 release
     * Prepping Moodletxt for Moodlerooms distribution
     * Added missing 'addinstance' capability
     * Removed unused 'viewmoodletxtpluslogs' capability
     * Removed deprecated get_context_* calls in Moodle 2.2 and above
     */
    if ($oldversion < 2013070203) {
        
        // Nothing to do - code patches only
        
        upgrade_block_savepoint(true, 2013070203, 'moodletxt');
    }
    
    /**
     * 3.0.5 release
     * No changes since RC2
     */
    if ($oldversion < 2013071001) {
        
        // Nothing to do - code patches only
        
        upgrade_block_savepoint(true, 2013071001, 'moodletxt');
    }
    
    return true;
    
}

?>