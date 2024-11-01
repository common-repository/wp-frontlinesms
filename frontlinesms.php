<?php

/*
 * Plugin Name: WP-FrontlineSMS
 * Plugin URI: https://github.com/pr4ka5a/wp-frontlinesms/
 * Description: Plugin for fetch messages from Frontline SMS
 * Author: Kaka E. Prakasa
 * Version: 0.1
 * Author URI: http://pr4ka5a.wordpress.com
 */
?>
<?
function frontlinesms_install()
{
    global $wpdb;
    $table = $wpdb->prefix."frontlinesms";
    $structure = "CREATE TABLE $table (
        id INT(9) NOT NULL AUTO_INCREMENT,
        FRONTLINE_key VARCHAR(8),
        sender_number VARCHAR(15) NOT NULL,
        message_content VARCHAR(800),
        dt datetime NOT NULL default '0000-00-00',
        UNIQUE KEY id (id)
    );";
    $wpdb->query($structure);
}
register_activation_hook(__FILE__,'frontlinesms_install');

function frontlinesms()
{
    include('frontlinesms_admin.php');
}

function frontlinesms_admin_menu()
{
    add_options_page('frontlineSMS','frontlineSMS',1,'frontlineSMS','frontlineSMS');

}

add_action('admin_menu', 'frontlinesms_admin_menu');
add_shortcode("wp-frontlinesms","laman_flsms");

//to retrieve from message database
function ambil($bbawah,$batas)
{
    global $wpdb;
    $table = $wpdb->prefix."frontlinesms";
    $results = $wpdb->get_results("SELECT sender_number, message_content FROM $table ORDER BY dt DESC limit $bbawah,$batas ",ARRAY_N);
   /*  
    for($i=1; $i<count($results); $i++)
    {
        echo $results[$i][0]." : ".$results[$i][1]."<br>";
    } 
   */
    return $results;

}

function penghalaman(){
	$hal = ambil();
}
/********************************
	Widget WP-FrontlineSMS
*/
function samping_flsms(){
	$test = ambil(0,5);	
		
	 for($i=1; $i<count($test); $i++)
    {
       echo $test[$i][0]." : ".$test[$i][1]."<hr />";
    } 
}

/*********************************
	Shortcode WP-FrontlineSMS
*/
function laman_flsms(){
	$perhal = 5;
	$nomhal = 1;	
	global $wpdb;
    	$table = $wpdb->prefix."frontlinesms";
    	$results = $wpdb->get_results("SELECT COUNT(sender_number) AS jumbar FROM $table",ARRAY_A);
   	$tothal = ceil($results[0]['jumbar']/$perhal);
	if(isset($_GET['page']))
		{
		    $nomhal = $_GET['page'];
		}
	$offset = ($nomhal - 1) * $perhal;
	$test = ambil($offset,$perhal);
	for($i=1; $i<count($test); $i++)
	    {
	       echo 'SMS Dari '.$test[$i][0]." : ".$test[$i][1]."<hr />";
	    } 

    	$self = $_SERVER['PHP_SELF'];
   	$nav  = '';

	for($page = 1; $page <= $tothal; $page++)
	{
	   if ($page == $nomhal)
	   {
	      $nav .= " $page "; // no need to create a link to current page
	   }
	   else
	   {
	      $nav .= " <a href=\"$self?page=$page\">$page</a> ";
	   }
	}
    echo $nav;
}

/****************************
	Widget plugins sidebar
*/
//error_reporting(E_ALL);
add_action("widgets_init", array('FrontlineSMS_widget', 'register'));
class FrontlineSMS_widget {
      function control(){
              echo 'FrontlineSMS Widget control panel';
                }
        function widget($args){
                echo $args['before_widget'];
                echo $args['before_title'] . 'SMS Message' . $args['after_title'];
                echo samping_flsms();
                echo $args['after_widget'];
                      }
        function register(){
                register_sidebar_widget('FrontlineSMS Widget', array('FrontlineSMS_widget', 'widget'));
                register_widget_control('FrontlineSMS Widget', array('FrontlineSMS_widget', 'control'));
                  }
}


//Retrieve post from frontlinesms

function frontline_post($args)
{
    if (isset($_SERVER['QUERY_STRING']))
    {
            global $wpdb;
            $table = $wpdb->prefix."frontlinesms";
            $default = array (
                'ss' => '',
                'mm' => '',
                'kk' => '',
                'lewat' => TRUE
                );
                $dt = date("Y-m-d H:i:s");

                //Melewatkan argumen yang datang dan memasukkannya dalam $default
                $args = wp_parse_args( $args, $default);

                //Mendeklarasikan setiap item pada $args menjadi variabel
                extract( $args, EXTR_SKIP);
                $frontlinesms_key = $wpdb->get_var($wpdb->prepare("SELECT FRONTLINE_key FROM $table"));

                if(!empty($ss) AND !empty($mm) AND !empty($kk))
                {


                       if($kk==$frontlinesms_key){
                           /*$send = "INSERT INTO $table( FRONTLINE_key, sender_number, message_content, dt) VALUES( '%s', '%d', '%s', $dt)";
                            $wpdb->query($wpdb->prepare($send, $kk, $ss, $mm));
                            */
					    $wpdb->insert($table, array('FRONTLINE_key' => $kk, 'sender_number' => $ss,'message_content' => $mm, 'dt' => $dt ));
																				     	    $wpdb->show_errors();
																					      
																					         //echo $kk;
																						 	 }
                        


                }
    }

}

frontline_post($_SERVER['QUERY_STRING'])
?>
