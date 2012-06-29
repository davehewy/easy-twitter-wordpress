<?php
/*
Plugin Name: Easy Twitter
Plugin URI: http://www.bytewire.co.uk/wordpress/plugins/easy-twitter
Description: Uses PHP and caching to fetch tweets from a twitter username
Version: 1.40
Author: David Heward
Author URI: http://www.davidheward.com

Copyright 2011 David Heward (dave@bytewire.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_menu', 'eztw_Menu');
add_action('admin_init', 'eztw_save');
add_shortcode( 'eztwitter', 'eztw_shortcode_handler' );

function eztw_Menu() {
	add_options_page('Easy Twitter Options', 'Easy Twitter', 'manage_options', 'easy-twitter', 'eztw_twitter_init');
}

function eztw_save(){
	if(isset($_POST['eztw_settings_submit'])){
		
		// Collect data
		$eztw_username = $_POST['eztw_username'];
		$eztw_tweet_count = $_POST['eztw_tweet_count'];
		$eztw_use_cache = ($_POST['eztw_use_cache']) ? 1 : 0;
				
		if($eztw_username)
			update_option('eztw_username',$eztw_username);
		
		if($eztw_tweet_count)
			update_option('eztw_tweet_count',$eztw_tweet_count);
		
		// update this option no matter
		update_option('eztw_use_cache',$eztw_use_cache);
		
	}
}

function eztw_twitter_init(){ 
		
		$eztw_username = get_option('eztw_username',TRUE);
		$eztw_tweet_count = get_option('eztw_tweet_count',TRUE);
		$eztw_use_cache = get_option('eztw_use_cache',0);
						
	?>
	<div class="wrap">
		<h2>Twitter Settings</h2>
		<p>You can setup your basic configuration settings (these can be overwritten using the shortcode's supported methods).</p>
		
		<h3>Basic settings</h3>
		<form action="" method="post">
			<table>
				<tr>
					<td><label for="eztw_username">Twitter username:</label></td>
					<td><input type="text" name="eztw_username" id="eztw_username" value="<?=$eztw_username?>"></td>
				</tr>
				<tr>
					<td><label for="eztw_tweet_count">Tweet limit:</label></td>
					<td><input name="eztw_tweet_count" type="text" id="eztw_tweet_count" value="<?=$eztw_tweet_count?>"></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" value="Submit" class="button-primary" name="eztw_settings_submit">
			</p>
		</form>
		<h3>Advanced settings</h3>
		<form action="" method="post">
			<table>
				<tr>
					<td><label for="eztw_use_cache">Use cache?:</label></td>
					<td><input type="checkbox" name="eztw_use_cache" id="eztw_use_cache" value="1" <?php checked($eztw_use_cache,1); ?></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" value="Submit" class="button-primary" name="eztw_settings_submit">
			</p>
		</form>	
		
		<h3>Using the shortcode</h3>
		<p>[eztwitter]</p>	
	</div>
<?php }

function eztw_shortcode_handler($args){
		
	$eztw_username = get_option('eztw_username',TRUE);
	$eztw_tweet_count = get_option('eztw_tweet_count',TRUE);
	$eztw_use_cache = get_option('eztw_use_cache',0);
	
	// Detect overwrites
	$eztw_username = ($args['username']) ? $args['username'] : $eztw_username;
	$eztw_tweet_count = ($args['tweetcount']) ? $args['tweetcount'] : $eztw_tweet_count;
	
	// Force overwrites to be valid.
	if($eztw_username 
		&& strlen($eztw_username)>3 
		&& is_numeric($eztw_tweet_count) 
		&& $eztw_tweet_count>0){
			
			$opts = "?count=$eztw_tweet_count&screen_name=$eztw_username&include_entities=true&include_rts=true";
			$json = file_get_contents("https://api.twitter.com/1/statuses/user_timeline.json$opts", true); 
			$decoded_tweets = json_decode($json, true);
			
			if($eztw_tweet_count){
				$html = eztw_twitterify($decoded_tweets[0]['text']);
				
				// Make a nice date from the time.
				$tweet_date = eztw_nice_tweet($decoded_tweets[0]['created_at']);
				
			}else{
				
				// There are multiple tweets being fetched, spew them out in list format.
				
				foreach($decoded_tweets as $k=>$v){
					
				}
				
			}
		
		
		$html.= '<time>'.$tweet_date.'<a href="https://twitter.com/'.$eztw_username.'" class="follow-link" target="_blank" title="Follow us on twitter">@'.$eztw_username.'</a></time>';
		
		return $html;
	
	}

}


function eztw_twitterify($ret) {
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
	$ret = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
	$ret = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $ret);
	return $ret;
}



#If your running PHP 5.3 you need to set this or your app will throw errors
date_default_timezone_set('Europe/London');

function eztw_nice_tweet ($date) {

$blocks = array (
	array('year',  (3600 * 24 * 365)),
	array('month', (3600 * 24 * 30)),
	array('week',  (3600 * 24 * 7)),
	array('day',   (3600 * 24)),
	array('hour',  (3600)),
	array('min',   (60)),
	array('sec',   (1))
);

#Get the time from the function arg and the time now
$argtime = strtotime($date);
$nowtime = time();

#Get the time diff in seconds
$diff    = $nowtime - $argtime;

#Store the results of the calculations
$res = array ();

#Calculate the largest unit of time
for ($i = 0; $i < count($blocks); $i++) {      
	$title = $blocks[$i][0];      
	$calc  = $blocks[$i][1];      
	$units = floor($diff / $calc);      
	if ($units > 0) {
		$res[$title] = $units;
	}
}

if (isset($res['year']) && $res['year'] > 0) {
	if (isset($res['month']) && $res['month'] > 0 && $res['month'] < 12) {       
	 	$format = "About %s %s %s %s ago";         	
		$year_label = $res['year'] > 1 ? 'years' : 'year';
		$month_label = $res['month'] > 1 ? 'months' : 'month';
		return sprintf($format, $res['year'], $year_label, $res['month'], $month_label);
	} else {
		$format = "About %s %s ago";
		$year_label = $res['year'] > 1 ? 'years' : 'year';
		return sprintf($format, $res['year'], $year_label);
	}
}

if (isset($res['month']) && $res['month'] > 0) {
	if (isset($res['day']) && $res['day'] > 0 && $res['day'] < 31) {        
		$format      = "About %s %s %s %s ago";         	$month_label = $res['month'] > 1 ? 'months' : 'month';
		$day_label   = $res['day'] > 1 ? 'days' : 'day';
		return sprintf($format, $res['month'], $month_label, $res['day'], $day_label);
	} else {
		$format      = "About %s %s ago";
		$month_label = $res['month'] > 1 ? 'months' : 'month';
		return sprintf($format, $res['month'], $month_label);
	}
}

if (isset($res['day']) && $res['day'] > 0) {
	if ($res['day'] == 1) {
	return sprintf("Yesterday at %s", date('h:i a', $argtime));
	}
	if ($res['day'] <= 7) {
	return date("\L\a\s\\t l \a\\t h:i a", $argtime);
	}
	if ($res['day'] <= 31) {         
		return date("l \a\\t h:i a", $argtime);       
	}     
}         

if (isset($res['hour']) && $res['hour'] > 0) {
	if ($res['hour'] > 1) {
		return sprintf("About %s hours ago", $res['hour']);
	} else {
		return "About an hour ago";
	}
}

if (isset($res['min']) && $res['min']) {
	if ($res['min'] == 1) {
		return "About one minute ago";
	} else {
		return sprintf("About %s minutes ago", $res['min']);
	}
}

if (isset ($res['sec']) && $res['sec'] > 0) {
	if ($res['sec'] == 1) {
		return "One second ago";
	} else {
		return sprintf("%s seconds ago", $res['sec']);
	}
}

}

function eztw_parse_feed($feed) {
	preg_match("'<published>(.*?)</published>'si", $feed, $match);
	if($match) $published = $match[1];
	
    $stepOne = explode("<content type=\"html\">", $feed);
    $stepTwo = explode("</content>", $stepOne[1]);
    $tweet = $stepTwo[0];
	$tweet = htmlspecialchars_decode($tweet,ENT_QUOTES);
	$tweet = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $tweet);
    return $tweet;
}

?>