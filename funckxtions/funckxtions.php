<?php
/**
 Plugin Name

 @package     funCKXtions
 @author      Christopher Kurth
 @copyright   2016 Christopher Kurth
 @license     GPL-2.0+
 *
 @wordpress-plugin
 Plugin Name: funCKXtions
 Plugin URI:  https://github.com/christopherkurth/funckxtions
 GitHub Plugin URI: https://github.com/christopherkurth/funckxtions
 Description: Zusatzfunktionen wie REST-API authentication; Entfernen der Generator Tags; Altersberechnung [ckx_age birthday="dd.mm.yy"]; Anzahl Beiträge [ckx_beitragsanzahl] und Kommentare [ckx_kommentaranzahl]; Mailadressen Verschleierung [ckx_mail]Adresse[/ckx_mail]
 Version:     2018-05-05-1800
 Author:      Christopher Kurth
 Author URI:  https://christopherkurth.com
 Text Domain: funckxtions
 License:     GPL-2.0+
 License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

 /* ------------------------------------------------------------------------- *
* Returning an authentication error if a user who is not logged in tries to query the REST API
/* ------------------------------------------------------------------------- */
function only_allow_logged_in_rest_access( $access ) {
    if( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_API_cannot_access', 'Only authenticated users can access the REST API.', array( 'status' => rest_authorization_required_code() ) );
    }
    return $access;
}
add_filter( 'rest_authentication_errors', 'only_allow_logged_in_rest_access' );

remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );

/* --- Entferne Generator Tags --- */
remove_action('wp_head', 'wp_generator');

/**
 * Lebensalter berechnen und ausgeben [ckx_age birthday="dd.mm.yy"]
 */
function ckx_altersberechnung_function( $atts, $content = null )
{
    $age = '';

    extract( shortcode_atts( array(
        'birthday' => '',
        'prefix' => '',
        'postfix' => ''
    ), $atts ) );

    $dateFormat  = "d.m.Y";
    $datePattern = '/^([123]0|[012][1-9]|31).(0[1-9]|1[012]).(19[0-9]{2}|2[0-9]{3})$/';
    if (preg_match($datePattern, $birthday, $matches))  {
        $day   = $matches[1];
        $month = $matches[2];
        $year  = $matches[3];
        $actDate = explode(".", date($dateFormat));

        $age = $actDate[2] - $year;
        if ($actDate[1] < $month ||
            ($actDate[1] == $month && $actDate[0] < intval($day))) {
            $age--;
        }
        $age = $prefix . $age . $postfix;
    }
    return $age;
}

add_shortcode('ckx_age', 'ckx_altersberechnung_function');

/**
 * Kommentar Anzahl ausgeben mit Shortcode: [ckx_beitragsanzahl]
 */
function ckx_beitragsanzahl_function(){
   $art_count = wp_count_posts('post');
   $nr_art =  $art_count->publish;
     
   return $nr_art;
}

add_shortcode('ckx_beitragsanzahl', 'ckx_beitragsanzahl_function' );

/**
 * Kommentar Anzahl ausgeben mit Shortcode: [ckx_kommentaranzahl]
 */
function ckx_kommentaranzahl_function(){
    $comments_count = wp_count_comments();
    $nr_komm =  $comments_count->approved;
     
    return $nr_komm; 
}

add_shortcode('ckx_kommentaranzahl', 'ckx_kommentaranzahl_function' );

/**
 * Eine E-Mail-Adresse die mit dem Shortcode [ckx_mail]Adresse[/ckx_mail] übergeben wird, kann anschließen von vielen Spam-Bots nicht mehr aus dem HTML-Code ausgelesen werden.
 */
function ckx_email_verschleiern_function( $atts , $content = null ) {
	if ( ! is_email( $content ) ) {
		return;
	}

	return '<a href="mailto:' . antispambot( $content ) . '">' . antispambot( $content ) . '</a>';
}

add_shortcode( 'ckx_mail', 'ckx_email_verschleiern_function' );