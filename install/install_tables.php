<?php
/**
 * Install database tables for Hotaru CMS.
 * 
 * Steps through the set-up process, creating database tables and registering 
 * the Admin user. Note: You must delete this file after installation as it 
 * poses a serious security risk if left.
 *
 * PHP version 5
 *
 * LICENSE: Hotaru CMS is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version. 
 *
 * Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. 
 *
 * You should have received a copy of the GNU General Public License along 
 * with Hotaru CMS. If not, see http://www.gnu.org/licenses/.
 * 
 * @category  Content Management System
 * @package   HotaruCMS
 * @author    Nick Ramsay <admin@hotarucms.org>
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

/**
 * Create database tables
 *
 * @param string $table_name
 *
 * Note: Deletes the table if it already exists, then makes it again
 */
function create_table($table_name)
{
    global $db, $lang, $h;
    
    $sql = 'DROP TABLE IF EXISTS `' . DB_PREFIX . $table_name . '`;';
    $db->query($sql);

    
    // BLOCKED TABLE - blocked IPs, users, email types, etc...
    
    if ($table_name == "blocked") {
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `blocked_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `blocked_type` varchar(64) NULL,
          `blocked_value` text NULL,
          `blocked_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `blocked_updateby` int(20) NOT NULL DEFAULT 0,
          INDEX  (`blocked_type`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Blocked IPs, users, emails, etc';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
    }
    
    
    // CATEGORIES TABLE - categories
    
    if ($table_name == "categories") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `category_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `category_parent` int(11) NOT NULL DEFAULT '1',
          `category_name` varchar(64) NOT NULL DEFAULT '',
          `category_safe_name` varchar(64) NOT NULL DEFAULT '',
          `rgt` int(11) NOT NULL DEFAULT '0',
          `lft` int(11) NOT NULL DEFAULT '0',
          `category_order` int(11) NOT NULL DEFAULT '0',
          `category_desc` text NULL,
          `category_keywords` varchar(255) NOT NULL,
          `category_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `category_updateby` int(20) NOT NULL DEFAULT 0, 
          UNIQUE KEY `key` (`category_name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Categories';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
        
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (category_name, category_safe_name) VALUES (%s, %s)";
        $db->query($db->prepare($sql, urlencode('All'), urlencode('all')));
    }
        
        
    // COMMENTS TABLE - comments
    
    if ($table_name == "comments") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `comment_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `comment_archived` enum('Y','N') NOT NULL DEFAULT 'N',
          `comment_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `comment_post_id` int(20) NOT NULL DEFAULT '0',
          `comment_user_id` int(20) NOT NULL DEFAULT '0',
          `comment_parent` int(20) DEFAULT '0',
          `comment_date` timestamp NOT NULL,
          `comment_status` varchar(32) NOT NULL DEFAULT 'approved',
          `comment_content` text NOT NULL,
          `comment_votes` int(20) NOT NULL DEFAULT '0',
          `comment_subscribe` tinyint(1) NOT NULL DEFAULT '0',
          `comment_updateby` int(20) NOT NULL DEFAULT 0,
          FULLTEXT (`comment_content`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Post Comments';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    

    
    
    // MISCDATA TABLE - for storing default permissions, etc.
    
    if ($table_name == "miscdata") {
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `miscdata_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `miscdata_key` varchar(64) NOT NULL,
          `miscdata_value` text NOT NULL DEFAULT '',
          `miscdata_default` text NOT NULL DEFAULT '',
          `miscdata_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `miscdata_updateby` int(20) NOT NULL DEFAULT 0
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Miscellaneous Data';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
        
        // Add Hotaru version number to the database (referred to when upgrading)
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (miscdata_key, miscdata_value, miscdata_default) VALUES (%s, %s, %s)";
        $db->query($db->prepare($sql, 'hotaru_version', $h->version, $h->version));

        // Default permissions
        $perms['options']['can_access_admin'] = array('yes', 'no');
        $perms['can_access_admin']['admin'] = 'yes';
        $perms['can_access_admin']['supermod'] = 'yes';
        $perms['can_access_admin']['default'] = 'no';
        $perms = serialize($perms);
        
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (miscdata_key, miscdata_value, miscdata_default) VALUES (%s, %s, %s)";
        $db->query($db->prepare($sql, 'permissions', $perms, $perms));
    }
    
    

    
    
    // PLUGINS TABLE
    
    if ($table_name == "plugins") {
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `plugin_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `plugin_enabled` tinyint(1) NOT NULL DEFAULT '0',
          `plugin_name` varchar(64) NOT NULL DEFAULT '',
          `plugin_folder` varchar(64) NOT NULL DEFAULT '',
          `plugin_class` varchar(64) NOT NULL DEFAULT '',
          `plugin_extends` varchar(64) NOT NULL DEFAULT '',
          `plugin_type` varchar(32) NOT NULL DEFAULT '',
          `plugin_desc` varchar(255) NOT NULL DEFAULT '',
          `plugin_requires` varchar(255) NOT NULL DEFAULT '',
          `plugin_version` varchar(32) NOT NULL DEFAULT '0.0',
          `plugin_order` int(11) NOT NULL DEFAULT 0,
          `plugin_author` varchar(32) NOT NULL DEFAULT '',
          `plugin_authorurl` varchar(128) NOT NULL DEFAULT '',
          `plugin_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `plugin_updateby` int(20) NOT NULL DEFAULT 0,
          UNIQUE KEY `key` (`plugin_folder`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Application Plugins';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
    }
    
    // PLUGIN HOOKS TABLE
    
    if ($table_name == "pluginhooks") {
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `phook_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `plugin_folder` varchar(64) NOT NULL DEFAULT '',
          `plugin_hook` varchar(128) NOT NULL DEFAULT '',
          `plugin_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `plugin_updateby` int(20) NOT NULL DEFAULT 0,
          INDEX  (`plugin_folder`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Plugins Hooks';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
    }
    
    // PLUGIN SETTINGS TABLE
    
    if ($table_name == "pluginsettings") {
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `psetting_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `plugin_folder` varchar(64) NOT NULL,
          `plugin_setting` varchar(64) NULL,
          `plugin_value` text NULL,
          `plugin_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `plugin_updateby` int(20) NOT NULL DEFAULT 0,
          INDEX  (`plugin_folder`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Plugins Settings';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
    }
    
    
    // POSTS TABLE - stories/news
    
    if ($table_name == "posts") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `post_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `post_archived` enum('Y','N') NOT NULL DEFAULT 'N',
          `post_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `post_author` int(20) NOT NULL DEFAULT 0,
          `post_category` int(20) NOT NULL DEFAULT 1,
          `post_status` varchar(32) NOT NULL DEFAULT 'processing',
          `post_date` timestamp NOT NULL,
          `post_title` varchar(255) NULL, 
          `post_orig_url` varchar(255) NULL, 
          `post_domain` varchar(255) NULL, 
          `post_url` varchar(255) NULL, 
          `post_content` text NULL,
          `post_votes_up` smallint(11) NOT NULL DEFAULT '0',
          `post_votes_down` smallint(11) NOT NULL DEFAULT '0',
          `post_tags` text NULL,
          `post_comments` enum('open', 'closed') NOT NULL DEFAULT 'open',
          `post_subscribe` tinyint(1) NOT NULL DEFAULT '0',
          `post_updateby` int(20) NOT NULL DEFAULT 0, 
          FULLTEXT (`post_title`, `post_domain`, `post_url`, `post_content`, `post_tags`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Story Posts';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    
    // POSTMETA TABLE - extra information for posts
    
    if ($table_name == "postmeta") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `postmeta_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `postmeta_archived` enum('Y','N') NOT NULL DEFAULT 'N',
          `postmeta_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `postmeta_postid` int(20) NOT NULL DEFAULT 0,
          `postmeta_key` varchar(255) NULL,
          `postmeta_value` text NULL,
           `postmeta_updateby` int(20) NOT NULL DEFAULT 0, 
          INDEX  (`postmeta_postid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Post Meta';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    
    // POSTVOTES TABLE - votes
    
    if ($table_name == "postvotes") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `vote_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `vote_archived` enum('Y','N') NOT NULL DEFAULT 'N',
          `vote_post_id` int(11) NOT NULL DEFAULT '0',
          `vote_user_id` int(11) NOT NULL DEFAULT '0',
          `vote_user_ip` varchar(32) NOT NULL DEFAULT '0',
          `vote_date` timestamp NOT NULL,
          `vote_type` varchar(32) NULL,
          `vote_rating` enum('positive','negative','alert') NULL,
          `vote_reason` tinyint(3) NOT NULL DEFAULT 0,
          `vote_updateby` int(20) NOT NULL DEFAULT 0,
           INDEX  (`vote_post_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Post Votes';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    } 
    
    
    // SETTINGS TABLE
    
    if ($table_name == "settings") {
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `settings_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `settings_name` varchar(64) NOT NULL,
          `settings_value` text NOT NULL DEFAULT '',
          `settings_default` text NOT NULL DEFAULT '',
          `settings_note` text NOT NULL DEFAULT '',
          `settings_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `settings_updateby` int(20) NOT NULL DEFAULT 0,
          UNIQUE KEY `key` (`settings_name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Application Settings';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
        
        // Default settings:
        
        // Friendly urls
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'SITE_OPEN', 'true', 'true', 'true/false'));
        
        // Site name
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'SITE_NAME', 'Hotaru CMS', 'Hotaru CMS', ''));
        
        // Main theme
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'THEME', 'default/', 'default/', 'You need the "\/"'));
        
        // Admin theme
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'ADMIN_THEME', 'admin_default/', 'admin_default/', 'You need the "\/"'));
        
        // Language_pack 
        /* Defined in hotaru_settings because we need it for this installation script, but here we check it has been defined, just in case.*/
        if (!isset($language_pack)) { $language_pack = 'default/'; }
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'LANGUAGE_PACK', $language_pack, 'language_default/', 'You need the "\/"'));
        
        // Friendly urls
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'FRIENDLY_URLS', 'false', 'false', 'true/false'));
        
        // Site email
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'SITE_EMAIL', 'admin@mysite.com', 'admin@mysite.com', 'Must be changed'));
        
        // Database cache
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'DB_CACHE_ON', 'false', 'false', 'true/false'));
        
        // Database cache duration (hours)
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %d, %d, %s)";
        $db->query($db->prepare($sql, 'DB_CACHE_DURATION', 12, 12, 'Hours'));
        
        // RSS cache
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'RSS_CACHE_ON', 'true', 'true', 'true/false'));
        
        // RSS cache duration (hours)
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %d, %d, %s)";
        $db->query($db->prepare($sql, 'RSS_CACHE_DURATION', 60, 60, 'Minutes'));
        
        // CSS/JavaScript cache
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'CSS_JS_CACHE_ON', 'true', 'true', 'true/false'));
        
        // HTML cache
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'HTML_CACHE_ON', 'true', 'true', 'true/false'));
        
        // Debug
        $sql = "INSERT INTO " . DB_PREFIX . $table_name . " (settings_name, settings_value, settings_default, settings_note) VALUES (%s, %s, %s, %s)";
        $db->query($db->prepare($sql, 'DEBUG', 'false', 'false', 'true/false'));
    }
    
    
    // TAGS TABLE - tags
    
    if ($table_name == "tags") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `tags_post_id` int(11) NOT NULL DEFAULT '0',
          `tags_archived` enum('Y','N') NOT NULL DEFAULT 'N',
          `tags_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `tags_date` timestamp NOT NULL,
          `tags_word` varchar(64) NOT NULL DEFAULT '',
          `tags_updateby` int(20) NOT NULL DEFAULT 0, 
          UNIQUE KEY `tags_post_id` (`tags_post_id`,`tags_word`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Post Tags';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    
    // TEMPDATA TABLE - temporary data
    
    if ($table_name == "tempdata") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `tempdata_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `tempdata_key` varchar(255) NULL,
          `tempdata_value` text NULL,
          `tempdata_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `tempdata_updateby` int(20) NOT NULL DEFAULT 0
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Temporary Data';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    
    // TOKENS TABLE - used to prevent against CSRF attacks
    
    if ($table_name == "tokens") {
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `token_id` MEDIUMINT unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `token_sid` varchar(32) NOT NULL,
          `token_key` CHAR(32) NOT NULL,
          `token_stamp` INT(11) NOT NULL default '0',
          `token_action` varchar(64),
          INDEX  (`token_key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Tokens for CSRF protection';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql);
    }
    
    
    // USERS TABLE
    
    if ($table_name == "users") {    
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `user_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `user_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `user_username` varchar(32) NOT NULL,
          `user_role` varchar(32) NOT NULL DEFAULT 'member',
          `user_date` timestamp NOT NULL,
          `user_password` varchar(64) NOT NULL DEFAULT '',
          `user_password_conf` varchar(128) NULL,
          `user_email` varchar(128) NOT NULL DEFAULT '',
          `user_email_valid` tinyint(3) NOT NULL DEFAULT 0,
          `user_email_conf` varchar(128) NULL,
          `user_permissions` text NOT NULL DEFAULT '',
          `user_ip` varchar(32) NOT NULL DEFAULT '0',
          `user_lastlogin` timestamp NULL,
          `user_updateby` int(20) NOT NULL DEFAULT 0,
          UNIQUE KEY `key` (`user_username`),
          KEY `user_email` (`user_email`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Users and Roles';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    
    // USERMETA TABLE - extra information for posts
    
    if ($table_name == "usermeta") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `usermeta_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `usermeta_userid` int(20) NOT NULL DEFAULT 0,
          `usermeta_key` varchar(255) NULL,
          `usermeta_value` text NULL,
          `usermeta_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `usermeta_updateby` int(20) NOT NULL DEFAULT 0, 
          INDEX  (`usermeta_userid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='User Meta';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    
    // USERACTIVITY TABLE - record user activity
    
    if ($table_name == "useractivity") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `useract_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `useract_archived` enum('Y','N') NOT NULL DEFAULT 'N',
          `useract_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `useract_userid` int(20) NOT NULL DEFAULT 0,
          `useract_status` varchar(32) NOT NULL DEFAULT 'show',
          `useract_key` varchar(255) NULL,
          `useract_value` text NULL,
          `useract_key2` varchar(255) NULL,
          `useract_value2` text NULL,
          `useract_date` timestamp NOT NULL,
          `useract_updateby` int(20) NOT NULL DEFAULT 0, 
          INDEX  (`useract_userid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='User Activity';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
    
    
    // WIDGETS TABLE - widgets
    
    if ($table_name == "widgets") {
        //echo "table doesn't exist. Stopping before creation."; exit;
        $sql = "CREATE TABLE `" . DB_PREFIX . $table_name . "` (
          `widget_id` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `widget_updatedts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
          `widget_plugin` varchar(32) NOT NULL DEFAULT '',
          `widget_function` varchar(255) NULL, 
          `widget_args` varchar(255) NULL, 
          `widget_updateby` int(20) NOT NULL DEFAULT 0
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Widgets';";
        echo $lang['install_step3_creating_table'] . ": '" . $table_name . "'...<br />\n";
        $db->query($sql); 
    }
}
?>
