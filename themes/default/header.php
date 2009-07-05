<?php

/* ******* TEMPLATE ******************************************************************************** 
 * Theme name: default
 * Template name: header.php
 * Template author: Nick Ramsay
 * Version: 0.1
 * License:
 *
 *   This file is part of Hotaru CMS (http://www.hotarucms.org/).
 *
 *   Hotaru CMS is free software: you can redistribute it and/or modify it under the terms of the 
 *   GNU General Public License as published by the Free Software Foundation, either version 3 of 
 *   the License, or (at your option) any later version.
 *
 *   Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 *   even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License along with Hotaru CMS. If not, 
 *   see http://www.gnu.org/licenses/.
 *   
 *   Copyright (C) 2009 Hotaru CMS - http://www.hotarucms.org/
 *
 **************************************************************************************************** */

global $hotaru, $plugin; // don't remove
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
   <title><?php echo site_name; ?></title>
   <link rel="stylesheet" href="<?php echo baseurl . '3rd_party/YUI-CSS/reset-fonts-grids.css'; ?>" type="text/css">
   <link rel="stylesheet" href="<?php echo baseurl . 'themes/' . theme . 'style.css'; ?>" type="text/css">
   <?php $plugin->check_actions('header_include'); ?>
   <link rel="shortcut icon" href="<?php echo baseurl; ?>favicon.ico">
</head>
<body>
<?php if($announcements = $hotaru->check_announcements()) { ?>
	<div id="announcement">
		<?php $plugin->check_actions('announcement_first'); ?>
		<?php foreach($announcements as $announcement) { echo $announcement . "<br />"; } ?>
		<?php $plugin->check_actions('announcement_last'); ?>
	</div>
<?php } ?>

<div id="doc2" class="yui-t7">
	<div id="hd" role="banner">
		<a href="<?php echo baseurl; ?>"><img src="<?php echo baseurl; ?>themes/default/images/hotaru_468x60.png"></a>
		<?php $plugin->check_actions('header_post_logo'); ?>
		<!-- NAVIGATION -->
		<?php echo $hotaru->display_template('navigation'); ?>
	</div>