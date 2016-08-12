<?php
/**
 * This file implements the YouTube plugin.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * YouTube Plugin
 *
 * This plugin allows you to easily post your youtube.com videos on your blog
 *
 * @package plugins
 */
class youtube_plugin extends Plugin
{
	/**
	 * Variables below MUST be overriden by plugin implementations,
	 * either in the subclass declaration or in the subclass constructor.
	 */
	var $name = 'YouTube plugin';
	var $code = 'youtube'; /* gets also used as default classname for the DIV container */
	var $priority = 30; /* Should be at least below the Auto-P plugin's one */
	var $version = '2.3.1';
	var $author = 'Danny Ferguson';
	var $help_url = 'http://manual.b2evolution.net/Plugins/youtube_plugin';
	var $group = 'rendering';

	/*
	 * These variables MAY be overriden.
	 */
	var $apply_rendering = 'lazy';

	function PluginInit( & $params )
	{
		$this->short_desc = T_('YouTube plugin');
		$this->long_desc = T_('Insert YouTube.com videos into posts.');
	}



	/**
	 * YouTube formatting search pattern. The tag in it gets replaced by an object displaying the video.
	 *
	 * @access private
	 */
	var $search = array(	'#\[youtube]([-&;_\w]+?)\[/youtube]#s',
				'#\[ifilm](\w+?)\[/ifilm]#s',
				'#\[gvideo](.+?)\[/gvideo]#s',
				'#\[gvid](.+?)\[/gvid]#s',
				'#\[current](.+?)\[/current]#s',
				'#\[dm](.+?)\[/dm]#s',
				'#\[jumpcut](.+?)\[/jumpcut]#s',
				'#\[revver](.+?)\[/revver]#s',
				'#\[vsocial](.+?)\[/vsocial]#s',
				'#\[blip](.+?)\[/blip]#s',
				'#\[metacafe](.+?)\[/metacafe]#s',
				'#\[break](.+?)\[/break]#s',
				'#\[livevideo](.+?)\[/livevideo]#s',
				'#\[onion](.+?)\[/onion]#s'				
				);

	var $replace = array();


	/**
	 * Get the settings that the plugin can use.
	 *
	 * Those settings are transfered into a Settings member object of the plugin
	 * and can be edited in the backoffice (Settings / Plugins).
	 *
	 * @see Plugin::GetDefaultSettings()
	 * @see PluginSettings
	 * @return array
	 */
	function GetDefaultSettings( & $params )
	{
		return array(
			'perpage' => array(
				'label' => $this->T_('Thumbnails per page'),
				'defaultvalue' => 3,
				'note' => $this->T_('How many YouTube thumbnails do you want to display for each page of results?'),
				'size' => 2,
				'type' => 'integer',
				'maxlength' => 2
			),
			'class_container' => array(
				'label' => $this->T_('CSS class for the container'),
				'defaultvalue' => $this->code.' center', // "youtube center"
				'note' => $this->T_('This CSS class(es) get used for the DIV container holding the video.'),
				'type' => 'text',
			),
		);
	}

	/**
	 * Event handlers:
	 */

	/**
	 * Event handler: Called when displaying editor toolbars.
	 *
	 * @param array Associative array of parameters
	 * @return boolean did we display a toolbar?
	 */
	function AdminDisplayToolbar( & $params )
	{
		// Add the JS here
		// The AdminEndHtmlHead event comes before jQuery, so it won't work there
		?>
		<script type="text/javascript" src="<?php echo $this->my_get_plugin_url() ?>youtube.js?v=<?php echo $this->version ?>"></script>
		<script type="text/javascript">
			youtube_url = '<?php echo $this->get_htsrv_url( 'vidList', array(), '&' ) ?>';
		</script>


		<div class="edit_toolbar" id="yttoolbar">
			<a href="javascript:void(0)" id="youtube_close" title="Hide the 
			 toolbar"><img src="rsc/icons/close.gif" alt="hide" id="ythide" /></a>
			<div style="float:right; margin:3px"><?php echo $this->get_edit_settings_link() ?></div>
			<div style="float:right; margin:3px"><?php echo $this->get_README_link() ?></div>
			<span id="ytsearch"> Search <img src="plugins/youtube_plugin/youtube.png" alt="YouTube" class="middle" />
				<input type="text" id="yttag" onKeyDown="if(event.keyCode==13) { sndReq(1); return false; }" />
				<input type="button" value="Go" onclick="sndReq(1)" />
			</span>
			<span style="margin: 10px" class="notes"><a href="javascript:void(0)" id="youtube_othersites">Other sites</a></span>
			<img src="plugins/youtube_plugin/loading.gif" id="ytloading" alt="Loading..." class="middle" />
			<div id="ytresults" style="display:none"></div>
		</div>

		<?php
		return true;
	}


	/**
	 * Event handler: Called when ending the admin html head section.
	 *
	 * @param array Associative array of parameters
	 * @return boolean did we do something?
	 */
	function AdminEndHtmlHead( & $params )
	{
		?>

		<link rel="stylesheet" href="plugins/youtube_plugin/youtube.css" type="text/css" />

		<?php
		return true;
	}

	// Set the plugin up to take AJAX calls
	function GetHtsrvMethods() {
			return array( 'vidList' );
	}

	function htsrv_vidList( $params ) {
		$perpage = $this->Settings->get('perpage');
		$searchtype = 'tag';
		if (isset($_GET['ytpage'])) {
			$ytpage = $_GET['ytpage'];
		} else {
			$ytpage = 1;
		}
		$dev_id = "T-lHDiH4ExA";  // This YouTube developer ID belongs to Danny Ferguson.  If you make substantial changes to this plugin, feel free to change the id.

		if ($searchtype == 'user') {
			$rest_url = "http://www.youtube.com/api2_rest?method=youtube.videos.list_by_user&dev_id=$dev_id&user=$ytuser";
		}
		elseif ($searchtype == 'tag') {
			if ($_GET['yttag'] == '') die('Please enter a search term.');
			$yttag = urlencode( $_GET['yttag']);
			$rest_url = "http://www.youtube.com/api2_rest?method=youtube.videos.list_by_tag&dev_id=$dev_id&tag=$yttag&page=$ytpage&per_page=$perpage";
		} else {
			die('Unknown search type');
		}

		//echo '<a href="'.$rest_url.'">url</a>';
		$xmlObj = new youtube_XmlToArray($rest_url);
		$arrayData = $xmlObj->createArray();
		$VideoList = $arrayData['ut_response']['video_list'];
		if( is_array($VideoList) && sizeof($VideoList) > 0 ) {
			$VideoList = $VideoList[0]['video']; // the "real" list
			echo '<div id="ytpagestore">'.$ytpage.'</div>';
			if ($ytpage != 1) {
				echo '<a href="javascript:void(0)" title="Previous page" id="ytprev" onclick="prevpage()"></a>';
			} else {
				echo '<div id="ytprev"></div>';
			}
			//echo '<div style="height:110px;margin:2px">';
			echo '<div style="margin:2px">';
			foreach ($VideoList as $video) {
				echo '<a href="javascript:void(0)" onclick="textarea_replace_selection(document.getElementById(\'itemform_post_content\'), \'[youtube]'.$video['id'].'[/youtube]\', document)" title="Click to add to your post">';
				echo '<img class="vidthumbnail" src="'.$video['thumbnail_url'].'" alt="'.$video['title'].'" />'."\n";
				echo '<div class="viddetail">';
				echo $video['title'].'<br />';
				echo '<div class="notes">';
				echo sprintf( $this->T_( 'Tags: %s' ), $video['tags'] ).'<br />';
				echo sprintf( $this->T_('Length: %s'), $this->sec2hms($video['length_seconds']) ).'<br />';
				//echo sprintf( $this->T_('ID: %s'), $video['id'] ).'<br />';
				echo '</div></div></a>';
			}
			if (sizeof($VideoList) == $perpage) {
				echo '<a href="javascript:void(0)" title="Next page" id="ytnext" onclick="nextpage()"></a>';
			}
		} else {
			echo '<div style="height:1.5em;margin:2px;text-align:center">';
			echo $this->T_('No results found.');
		}
		echo '<br style="clear:both" /></div>';
	}

	/**
	 * Perform rendering
	 *
	 * @return boolean true if we can render something for the required output format
	 */
	function RenderItemAsHtml( & $params )
	{

		$replace = array(
			'<div class="'.$this->Settings->get('class_container').'"><object type="application/x-shockwave-flash" style="width:425px; height:350px" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /></object></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed width="410" height="332" src="http://www.ifilm.com/efp" quality="high" bgcolor="000000" name="efp" align="center" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="flvBaseClip=$1" /></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed style="width:400px; height:326px;" align="middle" type="application/x-shockwave-flash" src="$1" allowScriptAccess="sameDomain" quality="best" bgcolor="#ffffff" scale="noScale" wmode="window" salign="TL"  FlashVars="playerMode=embedded"> </embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed style="width:400px; height:326px;" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=$1&hl=en"> </embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed src="http://www.current.tv/studio/vm2/vm2.swf?type=vcc&id=$1" quality="high" flashvars="videoType=vcc&videoID=$1" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" height="360" width="335"></embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><object width="400" height="240"><param name="movie" value="http://www.dailymotion.com/swf/$1"></param><embed src="http://www.dailymotion.com/swf/$1" type="application/x-shockwave-flash" width="400" height="240"></embed></object></div>',
			'<div class="'.$this->Settings->get('class_container').'"><object width="400" height="335"><param name="movie" value="http://jumpcut.com/media/flash/jump.swf?id=$1"></param><param name="flashvars" value="asset_type=movie&asset_id=$1&eb=1"></param><embed src="http://jumpcut.com/media/flash/jump.swf?id=$1" width="400" height="335" flashvars="asset_type=movie&asset_id=$1&eb=1" type="application/x-shockwave-flash"></embed></object></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed type="application/x-shockwave-flash" src="http://flash.revver.com/player/1.0/player.swf" pluginspage="http://www.macromedia.com/go/getflashplayer" scale="noScale" salign="TL" bgcolor="#ffffff" flashvars="width=480&height=392&mediaId=$1&affiliateId=0&javascriptContext=true&skinURL=http://flash.revver.com/player/1.0/skins/Default_Raster.swf&skinImgURL=http://flash.revver.com/player/1.0/skins/night_skin.png&actionBarSkinURL=http://flash.revver.com/player/1.0/skins/DefaultNavBarSkin.swf&resizeVideo=True" wmode="transparent" height="392" width="480"></embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed src="http://www.vsocial.com/v/$1" height="286" width="330"></embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed src="http://blip.tv/scripts/flash/blipplayer.swf?autoStart=false&file=http://blip.tv/file/get/$1" quality="high" width="320" height="256" name="movie" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed src="http://www.metacafe.com/fplayer/$1" width="400" height="300" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><object width="425" height="350"><param name="movie" value="http://embed.break.com/$1"></param><embed src="http://embed.break.com/$1" type="application/x-shockwave-flash" width="425" height="350"></embed></object></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed src="http://www.livevideo.com/flvplayer/embed/$1" type="application/x-shockwave-flash" quality="high" WIDTH="445" HEIGHT="369" wmode="transparent"></embed></div>',
			'<div class="'.$this->Settings->get('class_container').'"><embed src="http://www.theonion.com/content/themes/common/assets/videoplayer/flvplayer.swf" type="application/x-shockwave-flash" allowScriptAccess="always" wmode="transparent" width="400" height="355" flashvars="$1"></embed></div>',
		);

		$content = & $params['data'];
		$content = preg_replace( $this->search, $replace, $content);
		return true;
	}

	/**
	 * Also render XML.
	 */
	function RenderItemAsXml( & $params )
	{
		return $this->RenderItemAsHtml( $params );
	}


	/**
	 * We detect if our renderer gets used by looking at the content.
	 * @since 1.9
	 */
	function ItemApplyAsRenderer( & $params )
	{
		foreach( $this->search as $search )
		{
			if( preg_match( $search, $params['Item']->content ) )
			{
				return true;
			}
		}
		return false;
	}


	/**
	 * We detect, if our renderer gets used, by looking at the content and add our renderer code, if it gets used.
	 * @deprecated since 1.9 by ItemApplyAsRenderer()
	 */
	function PrependItemInsertTransact( & $params )
	{
		$match = false;
		foreach ($this->search as $item) {
			if( preg_match( $item, $params['Item']->content ) )
			{
				$match = true;
				break;
			}
		}
		if ($match) { // there's our code in there
			$params['Item']->add_renderer( $this->code );
		}
		else
		{
			$params['Item']->remove_renderer( $this->code );
		}
	}

	/**
	 * We detect, if our renderer gets used, by looking at the content and add our renderer code, if it gets used.
	 * @deprecated since 1.9 by ItemApplyAsRenderer()
	 */
	function PrependItemUpdateTransact( & $params )
	{
		$this->PrependItemInsertTransact( $params );
	}

	/**
	 * Add the renderer also for previews.
	 * @deprecated since 1.9 by ItemApplyAsRenderer()
	 */
	function AppendItemPreviewTransact( & $params )
	{
		$this->PrependItemInsertTransact( $params );
	}


	/**
	 * Upgrade procedure.
	 */
	function PluginVersionChanged( & $params )
	{
		if( version_compare( $params['old_version'], '0.8.1-dev', '<' ) )
		{ // the default apply_rendering setting since 0.8.1-dev is "lazy"
			if( $this->apply_rendering == 'opt-out' )
			{ // only change it, if it's still set to the old default
				if( function_exists('get_Cache') && ($Plugins_admin = & get_Cache('Plugins_admin')) )
				{ // since after b2evo 1.9:
					$Plugins_admin->set_apply_rendering( $this->ID, 'lazy' );
				}
				else
				{ // NOTE: $Plugins member is deprecated since b2evo 1.10
					$this->Plugins->set_apply_rendering( $this->ID, 'lazy' );
				}
			}
		}

		return true;
	}


	/**
	 * Event handler: Called when displaying editor buttons.
	 *
	 * Note: this gets invoked on all "edit_layout" settings, also "simple",
	 *       because it targets the plugin's audience.
	 *
	 * @param array Associative array of parameters
	 * @return boolean did we display ?
	 */
	function AdminDisplayEditorButton( & $params )
	{
		?>

		<input type="button" value="YouTube" class="ActionButton" id="youtube_under_button" title="Show / Hide the YouTube toolbar" />

		<?php
		return true;
	}


	/**
	 * WRAPPER METHOD FOR b2evo 1.8 (before 1.8.1)
	 *
	 * Get the absolute URL to the plugin's directory (trailing slash included).
	 *
	 * This is either below {@link $plugins_url}, if no Blog is set or we're in the
	 * backoffice, or the "plugins" directory below the Blog's URL root otherwise.
	 *
	 * @return string
	 */
	function my_get_plugin_url()
	{
		if( method_exists($this, 'get_plugin_url') )
		{ // since b2evo 1.8.1
			return $this->get_plugin_url();
		}

		global $ReqHost, $Blog, $plugins_url, $plugins_path;

		if( isset($Blog) && ! is_admin_page() )
		{
			$base = $Blog->get('baseurl').'plugins/';
		}
		else
		{
			$base = $plugins_url;
		}

		if( strpos( $base, $ReqHost ) !== 0 )
		{ // the base url does not begin with the requested host:

			// Fix "http:" to "https:":
			if( strpos( $ReqHost, 'https:' ) === 0 && strpos( $base, 'http:' ) === 0 )
			{
				$base_fixed = 'https:'.substr( $base, 5 );

				if( strpos( $base_fixed, $ReqHost ) === 0 )
				{
					$base = $base_fixed;
				}
			}
		}

		// Append sub-path below $plugins_path, if any:
		$sub_path = preg_replace( ':^'.preg_quote($plugins_path, ':').':', '', dirname($this->classfile_path).'/' );

		return $base.$sub_path;
	}


	/**
	 * Method for converting UNIX timestamp to minutes and seconds
	 */

	function sec2hms ($sec, $padSecs = false)
	{
	    $hms = "";
	    $minutes = intval(($sec / 60) % 60);
	    $hms .= ($padSecs) ? str_pad($minutes, 2, "0", STR_PAD_LEFT). ':' : $minutes. ':';
	    $seconds = intval($sec % 60);
	    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
	    return $hms;
	}

}
/**
* Author   : MA Razzaque Rupom (rupom_315@yahoo.com, rupom.bd@gmail.com)
* Version  : 1.0
* Date     : 02 March, 2006
* Purpose  : Creating Hierarchical Array from XML Data
* Released : Under GPL
*/

class youtube_XmlToArray extends youtube_plugin
{

	var $xml='';

	/**
	* Default Constructor
	* @param $xml = xml data
	* @return none
	*/

	function __construct( $xml_url )
	{
		$this->fetch_remote_page($xml_url);
	}


	/**
	* Fetch remote page
	* Attempt to retrieve a remote page, first with cURL, then fopen, then fsockopen
	* @param $url
	* @return $data = The remote page as a string
	*/

	function fetch_remote_page( $url ) {
		$data = '';
		if (extension_loaded('curl')) {
	   		$ch = curl_init();
	      	curl_setopt($ch, CURLOPT_URL, $url);
	      	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	      	$data = curl_exec ($ch);
	      	curl_close ($ch);
	  	} elseif ( ini_get('allow_url_fopen') ) {
			// cURL not supported, try fopen
			$hf = fopen($url, 'r');
	  		for ($data =''; $buf=fread($hf,1024); ) {  //read the complete file (binary safe)
	     		$data .= $buf;
	      	}
		    fclose($hf);
		} else {
			// As a last resort, try fsockopen
		    $url_parsed = parse_url($url);
		    if ( empty($url_parsed['scheme']) ) {
		        $url_parsed = parse_url('http://'.$url);
		    }

		    $port = $url_parsed["port"];
		    if ( !$port ) {
		        $port = 80;
		    }

		    $path = $url_parsed["path"];
		    if ( empty($path) ) {
		            $path="/";
		    }
		    if ( !empty($url_parsed["query"]) ) {
		        $path .= "?".$url_parsed["query"];
		    }

		    $host = $url_parsed["host"];
		    $foundBody = false;

		    $out = "GET $path HTTP/1.0\r\n";
		    $out .= "Host: $host\r\n";
		    $out .= "Connection: Close\r\n\r\n";

		    if ( !$fp = @fsockopen($host, $port, $errno, $errstr, 30) ) {
		        $error = $errno;
		        $error .= $errstr;
		        return $error;
		    }
		    fwrite($fp, $out);
		    while (!feof($fp)) {
		        $s = fgets($fp, 128);
		        if ( $s == "\r\n" ) {
		            $foundBody = true;
		            continue;
		        }
		        if ( $foundBody ) {
		            $body .= $s;
		        }
		    }
		    fclose($fp);

		    $data = trim($body);
		}
	   $this->xml = $data;
	}


	/**
	* _struct_to_array($values, &$i)
	*
	* This is adds the contents of the return xml into the array for easier processing.
	* Recursive, Static
	*
	* @access    private
	* @param    array  $values this is the xml data in an array
	* @param    int    $i  this is the current location in the array
	* @return    Array
	*/

	function _struct_to_array($values, &$i)
	{
		$child = array();
		if (isset($values[$i]['value'])) array_push($child, $values[$i]['value']);

		while ($i++ < count($values)) {
			switch ($values[$i]['type']) {
				case 'cdata':
            	array_push($child, $values[$i]['value']);
				break;

				case 'complete':
					$name = $values[$i]['tag'];
					if(!empty($name)){
					$child[$name]= ( isset($values[$i]['value']) ? $values[$i]['value'] : '' );
					if(isset($values[$i]['attributes'])) {
						$child[$name] = $values[$i]['attributes'];
					}
				}
          	break;

				case 'open':
					$name = $values[$i]['tag'];
					$size = isset($child[$name]) ? sizeof($child[$name]) : 0;
					$child[$name][$size] = $this->_struct_to_array($values, $i);
				break;

				case 'close':
            	return $child;
				break;
			}
		}
		return $child;
	}//_struct_to_array

	/**
	* createArray($data)
	*
	* This is adds the contents of the return xml into the array for easier processing.
	*
	* @access    public
	* @param    string    $data this is the string of the xml data
	* @return    Array
	*/
	function createArray()
	{
		$xml    = $this->xml;
		$values = array();
		$index  = array();
		$array  = array();
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $xml, $values, $index);
		xml_parser_free($parser);
		$i = 0;
		$name = $values[$i]['tag'];
		$array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
		$array[$name] = $this->_struct_to_array($values, $i);
		return $array;
	}//createArray


}//XmlToArray


?>
