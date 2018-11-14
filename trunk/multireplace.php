<?php
/**
* @link              https://github.com/RTO-Websites/WordPress-MultiReplace
* @since             1.0.0
* @package           MultiReplace
*
* @wordpress-plugin
* Plugin Name:       MultiReplace
* Plugin URI:        https://github.com/RTO-Websites/WordPress-MultiReplace
* Description:       Replace strings in database
* Version:           1.0.2
* Author:            RTO GmbH
* Author URI:        https://www.rto.de
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       multireplace
* Domain Path:       /languages
*/

if (is_admin()) {
    include( 'class/MultiReplace.php' );
    new MultiReplace();
}
