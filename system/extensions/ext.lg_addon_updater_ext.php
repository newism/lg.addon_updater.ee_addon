<?php
/**
* LG Addon Updater extension file
* 
* This file must be placed in the
* /system/extensions/ folder in your ExpressionEngine installation.
* The concept for this extension comes from the one and only Ryan Masuga! Thanks
*
* @package LgAddonUpdater
* @version 1.1.0
* @author Leevi Graham <http://leevigraham.com>
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-addon-updater/
* @copyright Copyright (c) 2007-2008 Leevi Graham
* @license {@link http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported} All source code commenting and attribution must not be removed. This is a condition of the attribution clause of the license.
*/

if ( ! defined('EXT')) exit('Invalid file request');

if ( ! defined('LG_AU_version')){
	define("LG_AU_version",			"1.1.0");
	define("LG_AU_docs_url",		"http://leevigraham.com/cms-customisation/expressionengine/addon/lg-addon-updater/");
	define("LG_AU_addon_id",		"LG Addon Updater");
	define("LG_AU_extension_class",	"Lg_addon_updater_ext");
	define("LG_AU_cache_name",		"lg_cache");
}

/**
* This extension adds an easy way to check if extensions need updating
*
* @package LgAddonUpdater
* @version 1.1.0
* @author Leevi Graham <http://leevigraham.com>
* @see http://leevigraham.com/cms-customisation/expressionengine/addon/lg-addon-updater/
* @copyright Copyright (c) 2007-2008 Leevi Graham
* @license {@link http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported} All source code commenting and attribution must not be removed. This is a condition of the attribution clause of the license.
* @todo Add per field custom settings
* @since version 1.0.0
*/
class Lg_addon_updater_ext {

	/**
	* Extension settings
	* @var array
	*/
	var $settings			= array();

	/**
	* Extension name
	* @var string
	*/
	var $name				= 'LG Addon Updater';

	/**
	* Extension version
	* @var string
	*/
	var $version			= LG_AU_version;

	/**
	* Extension description
	* @var string
	*/
	var $description		= 'Provides hooks for other extensions to check for updates';

	/**
	* If $settings_exist = 'y' then a settings page will be shown in the ExpressionEngine admin
	* @var string
	*/
	var $settings_exist 	= 'y';

	/**
	* Link to extension documentation
	* @var string
	*/
	var $docs_url			= LG_AU_docs_url;

	// Donate button
	var $paypal 			=  array(
		"account"				=> "sales@newism.com.au",
		"donations_accepted"	=> TRUE,
		"donation_amount"		=> "20.00",
		"currency_code"			=> "USD",
		"return_url"			=> "http://leevigraham.com/donate/thanks/",
		"cancel_url"			=> "http://leevigraham.com/donate/cancel/"
	);

	/**
	* PHP4 Constructor
	*
	* @see __construct()
	* @since version 1.0.0
	*/
	function Lg_addon_updater_ext($settings='')
	{
		$this->__construct($settings);
	}



	/**
	* PHP 5 Constructor
	*
	* @param	array|string $settings Extension settings associative array or an empty string
	* @since version 1.0.0
	*/
	function __construct($settings='')
	{
		global $IN, $SESS;

		if(isset($SESS->cache['lg']) === FALSE){ $SESS->cache['lg'] = array();}

		$this->settings = $this->_get_settings();
		$this->debug = $IN->GBL('debug');
	}



	/**
	* Get the site specific settings from the extensions table
	*
	* @param $force_refresh		bool	Get the settings from the DB even if they are in the $SESS global
	* @param $return_all		bool	Return the full array of settings for the installation rather than just this site
	* @return array 					If settings are found otherwise false. Site settings are returned by default. Installation settings can be returned is $return_all is set to true
	* @since version 1.0.0
	*/
	function _get_settings($force_refresh = FALSE, $return_all = FALSE)
	{

		global $SESS, $DB, $REGX, $LANG, $PREFS;

		// assume there are no settings
		$settings = FALSE;
		
		// Get the settings for the extension
		if(isset($SESS->cache['lg'][LG_AU_addon_id]['settings']) === FALSE || $force_refresh === TRUE)
		{
			// check the db for extension settings
			$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = '" . LG_AU_extension_class . "' LIMIT 1");

			// if there is a row and the row has settings
			if ($query->num_rows > 0 && $query->row['settings'] != '')
			{
				// save them to the cache
				$SESS->cache['lg'][LG_AU_addon_id]['settings'] = $REGX->array_stripslashes(unserialize($query->row['settings']));
			}
		}
		// check to see if the session has been set
		// if it has return the session
		// if not return false
		if(empty($SESS->cache['lg'][LG_AU_addon_id]['settings']) !== TRUE)
		{
			$settings = ($return_all === TRUE) ?  $SESS->cache['lg'][LG_AU_addon_id]['settings'] : $SESS->cache['lg'][LG_AU_addon_id]['settings'][$PREFS->ini('site_id')];
		}

		return $settings;
	}



	/**
	* Configuration for the extension settings page
	* 
	* @param $current	array 		The current settings for this extension. We don't worry about those because we get the site specific settings
	* @since version 1.0.0
	**/
	function settings_form($current)
	{
		global $DB, $DSP, $LANG, $IN, $PREFS, $REGX, $SESS;

		// create a local variable for the site settings
		$settings = $this->_get_settings();
		$settings['cache_refresh'] = (isset($settings['cache_refresh']) === FALSE || empty($settings['cache_refresh']) === TRUE) ? '3200' : $settings['cache_refresh'];


		$lgau_query = $DB->query("SELECT class FROM exp_extensions WHERE class = 'Lg_addon_updater_ext' AND enabled = 'y' LIMIT 1");
		$lgau_enabled = $lgau_query->num_rows ? TRUE : FALSE;

		$DSP->title  = $LANG->line('extension_settings');

		$DSP->crumbline = TRUE;
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
		$DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));
		$DSP->crumb .= $DSP->crumb_item("{$this->name} {$this->version}");

		$DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));

		$DSP->body = "<div class='mor settings-form'>";
		$DSP->body .= "<p class='donate paypal'>
							<a rel='external'"
								. "href='https://www.paypal.com/cgi-bin/webscr?"
									. "cmd=_donations&amp;"
									. "business=".rawurlencode($this->paypal["account"])."&amp;"
									. "item_name=".rawurlencode($this->name . " Development: Donation")."&amp;"
									. "amount=".rawurlencode($this->paypal["donation_amount"])."&amp;"
									. "no_shipping=1&amp;return=".rawurlencode($this->paypal["return_url"])."&amp;"
									. "cancel_return=".rawurlencode($this->paypal["cancel_url"])."&amp;"
									. "no_note=1&amp;"
									. "tax=0&amp;"
									. "currency_code=".$this->paypal["currency_code"]."&amp;"
									. "lc=US&amp;"
									. "bn=PP%2dDonationsBF&amp;"
									. "charset=UTF%2d8'"
								."class='button'
								target='_blank'>
								Support this addon by donating via PayPal.
							</a>
						</p>";
		$DSP->body .= $DSP->heading($this->name . " <small>{$this->version}</small>");
		$folder = strtolower(get_class($this));
		ob_start(); include(PATH_LIB.'lg_addon_updater/views/lg_addon_updater_ext/form_settings.php'); $DSP->body .= ob_get_clean();
		$DSP->body .=   "</div>";
	}



	/**
	* Save Settings
	* 
	* @since version 1.0.0
	**/
	function save_settings()
	{
		// make somethings global
		global $DB, $IN, $PREFS, $REGX, $SESS;

		$default_settings = array();

		// merge the defaults with our $_POST vars
		$_POST = array_merge($default_settings, $_POST);

		// unset the name
		unset($_POST['name']);
		
		// load the settings from cache or DB
		// force a refresh and return the full site settings
		$settings = $this->_get_settings(TRUE, TRUE);

		// add the posted values to the settings
		$settings[$PREFS->ini('site_id')] = $_POST;

		// update the settings
		$query = $DB->query($sql = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($settings)) . "' WHERE class = '" . LG_AU_extension_class . "'");
	}



	/**
	* Activates the extension
	*
	* @return	bool Always TRUE
	* @since version 1.0.0
	*/
	function activate_extension()
	{
		global $DB, $PREFS;

		$default_settings = array(
									'check_for_extension_updates' 	=> 'y', // Checks for LG Addon Updater updates
									'check_for_updates' 			=> 'y', // Check for all 3rd party updates
									'cache_refresh' 				=> 6400, // Cache the results
									'show_donate'					=> 'y',
									'show_promos'					=> 'y'
							);

		// get the list of installed sites
		$query = $DB->query("SELECT * FROM exp_sites");

		// if there are sites - we know there will be at least one but do it anyway
		if ($query->num_rows > 0)
		{
			// for each of the sites
			foreach($query->result as $row)
			{
				// build a multi dimensional array for the settings
				$settings[$row['site_id']] = $default_settings;
			}
		}

		$hooks = array(
			'sessions_end' 						=> 'sessions_end',
			'control_panel_home_page' 			=> 'control_panel_home_page',
			'lg_addon_update_register_source'	=> 'lg_addon_update_register_source',
			'lg_addon_update_register_addon'	=> 'lg_addon_update_register_addon'
		);

		foreach ($hooks as $hook => $method)
		{
			$sql[] = $DB->insert_string( 'exp_extensions', 
											array('extension_id' 	=> '',
												'class'			=> get_class($this),
												'method'		=> $method,
												'hook'			=> $hook,
												'settings'		=> addslashes(serialize($settings)),
												'priority'		=> 1,
												'version'		=> $this->version,
												'enabled'		=> "y"
											)
										);
		}

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
		return TRUE;
	}



	/**
	* Updates the extension
	*
	* @param	string $current If installed the current version of the extension otherwise an empty string
	* @return	bool FALSE if the extension is not installed or is the current version
	* @since version 1.0.0
	*/
	function update_extension($current = '')
	{
		global $DB;
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		$sql[] = "UPDATE exp_extensions SET version = '" . $DB->escape_str($this->version) . "' WHERE class = '" . get_class($this) . "'";

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
	}



	/**
	* Disables the extension the extension and deletes settings from DB
	* 
	* @since version 1.0.0
	*/
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM exp_extensions WHERE class = '" . get_class($this) . "'");
	}



/**
* Register a new Addon Source
*
* @param    array $sources The existing sources
* @return   array The new source list
* @since version 1.0.0
*/
function lg_addon_update_register_source($sources)
{
    global $EXT;
    // -- Check if we're not the only one using this hook
    if($EXT->last_call !== FALSE)
        $sources = $EXT->last_call;

    // add a new source
    // must be in the following format:
    /*
    <versions>
        <addon id='LG Addon Updater' version='2.0.0' last_updated="1218852797" docs_url="http://leevigraham.com/" />
    </versions>
    */
    if($this->settings['check_for_extension_updates'] == 'y')
    {
        $sources[] = 'http://leevigraham.com/version-check/versions.xml';
    }
    return $sources;

}


	/**
	* Register a new Addon
	*
	* @param	array $addons The existing sources
	* @return	array The new addon list
	* @since version 1.0.0
	*/
	function lg_addon_update_register_addon($addons)
	{
		global $EXT;
		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$addons = $EXT->last_call;

		// add a new addon
		// the key must match the id attribute in the source xml
		// the value must be the addons current version
		if($this->settings['check_for_extension_updates'] == 'y')
		{
			$addons[LG_AU_addon_id] = $this->version;
		}
		return $addons;
	}



	/**
	* Provides hooks for extension developers to add their addons
	* 
	* @since version 1.0.0
	*/
	function sessions_end(&$obj)
	{
		global $EXT, $IN;

		// if its a CP request and the homepage
		if (REQ == 'CP' && $IN->GBL('M') === FALSE)
		{

			$obj->cache['lg'][LG_AU_addon_id]['addons'] = FALSE;
			$obj->cache['lg'][LG_AU_addon_id]['sources'] = FALSE;

			/**	----------------------------------------
			/**	'lg_addon_update_register_source' hook.
			/**	----------------------------------------
			/*	This allows developers to add their own sources
			/**	----------------------------------------*/
			if ($EXT->active_hook('lg_addon_update_register_source') === TRUE)
			{
				$obj->cache['lg'][LG_AU_addon_id]['sources'] = $EXT->call_extension('lg_addon_update_register_source', $obj->cache['lg'][LG_AU_addon_id]['sources']);
				if ($EXT->end_script === TRUE) return;
			}
			/**	----------------------------------------
			/**	'lg_addon_update_register_addon' hook.
			/**	----------------------------------------
			/*	This allows developers to add their own addons
			/**	----------------------------------------*/
			if ($EXT->active_hook('lg_addon_update_register_addon') === TRUE)
			{
				$obj->cache['lg'][LG_AU_addon_id]['addons'] = $EXT->call_extension('lg_addon_update_register_addon', $obj->cache['lg'][LG_AU_addon_id]['addons']);
				if ($EXT->end_script === TRUE) return;
			}
		}
	}



	/**
	* Takes the control panel home page and adds an update method if needed
	*
	* @param	string $out The control panel html
	* @return	string The modified control panel html
	* @since 	Version 1.0.0
	*/
	function control_panel_home_page($home)
	{
		global $EXT, $LOC, $SESS;

		$updates = '';
		$updates_available = FALSE;

		// -- Check if we're not the only one using this hook
		if($EXT->last_call !== FALSE)
			$home = $EXT->last_call;

		// see if we even need to check for updates
		if($this->settings['check_for_updates'] == 'n')
		{
			return $home;
		}

		if ( ! class_exists('EE_XMLparser'))
		{
			require PATH_CORE.'core.xmlparser'.EXT;
		}

		$XML = new EE_XMLparser;

		// valid XML?
		if (($versions = $XML->parse_xml($this->_get_latest_versions())) === FALSE)
		{
			$updates = "<div class='alert'>LG Addon Updater failed. There may be a problem with some of the addon sources.</div>";
		}
		else
		{
			foreach ($versions->children as $addon)
			{
				$addon_id = $addon->attributes['id'];
				if(
					isset($SESS->cache['lg'][LG_AU_addon_id]['addons'][$addon_id]) === TRUE
					&& $addon->attributes['version'] > $SESS->cache['lg'][LG_AU_addon_id]['addons'][$addon_id])
				{
					if($updates_available === FALSE)
					{
						$updates_available = TRUE;
						$updates = "<div class='alert'>Module / extension / plugin updates available:</div><ul class='alert-list'>";
					}
					$updates .= "<li><a href='" . $addon->attributes['docs_url'] . "'>" . $addon->attributes['id'] . " v" . $addon->attributes['version'] . "</a> <small>Last Updated: ". $LOC->set_human_time($addon->attributes['last_updated'])."</small></li>";
				}
			}
			if($updates_available === TRUE)
			{
				$updates .= "</ul><style type='text/css' media='screen'>
#contentNB > .box:first-child .alert{border-top:1px dotted #CCC9A4; margin-top:3px; padding-top:9px;}
#contentNB > .box:first-child .alert:first-child{border:none; margin:0; padding:0}
#contentNB > .box:first-child ul.alert-list{margin-bottom:0}
</style>";
			$home->messages = array_merge($home->messages, array($updates));
			}
		}

		return $home;
	}



	/**
	* Gets the latest versions from multiple sources as xml
	*
	* @return	mixed array|bool XML if updates exist FALSE if no updates are available
	* @since 	version 1.0.0
	*/
	function _get_latest_versions()
	{
		global $SESS;
		
		$raw_xml = FALSE;
		$cache_key = LG_AU_addon_id . "_latest_versions";

		// check the $SESS for this version
		if(isset($SESS->cache['lg'][LG_AU_addon_id]['latest_versions_xml']) === FALSE)
		{
			// if there is nothing in the cache
			if(($raw_xml = $this->_check_cache($cache_key)) === FALSE)
			{
				// cache has expired
				$cache_expired = TRUE;

				// see if we have sources
				if(empty($SESS->cache['lg'][LG_AU_addon_id]['sources']) === FALSE)
				{
					$sources = $SESS->cache['lg'][LG_AU_addon_id]['sources'];
					// for each unique source
					foreach(array_unique($sources) as $source_url)
					{
						// are we using curl?
						if ( function_exists('curl_init') === TRUE)
						{
							$page = $this->_curl_fetch($source_url);
						}
						// or fsockopen?
						else
						{
							$page = $this->_fsockopen_fetch($source_url);
						}
						// if there was no error grabbing this source
						if(empty($page['errno']) === TRUE && empty($page['content']) === FALSE)
						{
							// add this xml to our xml but remove the parent node first
							$raw_xml .= trim(substr(trim($page['content']), 10, -11));
						}
					}
					// add a parent node back
					$raw_xml = "<versions>" . $raw_xml . "</versions>";
				}
				if ($cache_expired === TRUE)
				{
					$this->_write_cache($raw_xml, $cache_key);
				}
			}
		}
		return $raw_xml;
	}


	/**
	* Fetches a files contents using CURL
	*
	* @param $url string The page URL
	* @return array The page information including errors
	* @since version 1.0.0
	*/
	function _curl_fetch($url)
	{
		$ch      = curl_init( $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "EE/LG Addon Updater PHP/" . phpversion());
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

		$content = curl_exec( $ch );
		$err     = curl_errno( $ch );
		$errmsg  = curl_error( $ch );
		$header  = curl_getinfo( $ch );
		curl_close( $ch );

		$header['errno']   = $err;
		$header['errmsg']  = $errmsg;
		$header['content'] = $content;
		return $header;
	}



	/**
	* Fetches a files contents using fsockopen();
	*
	* @param $url string The page URL
	* @return array The page information including errors
	* @since version 1.0.0
	*/
	function _fsockopen_fetch($url)
	{
		$target = parse_url($url);

		$data = '';

		$fp = fsockopen($target['host'], 80, $err, $errmsg, 8); 

		if (is_resource($fp))
		{
			fputs($fp, "GET {$url} HTTP/1.0\r\n");
			fputs($fp, "Host: {$target['host']}\r\n");
			fputs($fp, "User-Agent: EE/LG Addon Updater PHP/" . phpversion() . "\r\n\r\n");

			$headers = TRUE;

			while( ! feof($fp))
			{
				$line = fgets($fp, 4096);
				if ($headers === FALSE)
				{
					$data .= $line;
				}
				elseif (trim($line) == '')
				{
					$headers = FALSE;
				}
			}

			fclose($fp); 
			}

		$header['errno']   = $err;
		$header['errmsg']  = $errmsg;
		$header['content'] = $data;
		return $header;
	}


	/**
	* Check Cache
	*
	* @param	string
	* @return	mixed - string if pulling from cache, FALSE if not
	* @since 	version 1.0.0
	*/
	function _check_cache($url)
	{	
		global $TMPL;

		// build the cache directory path
		$dir = PATH_CACHE.LG_AU_cache_name.'/';

		if ( ! @is_dir($dir))
		{
			if ( ! @mkdir($dir, 0777))
			{
				@chmod($dir, 0777);
			}
			return FALSE;
		}

		// set the filename
	    $file = $dir.md5($url);

		// does the file doesn't exist or we can't open it
		if ( ! file_exists($file) OR ! ($fp = @fopen($file, 'rb')))
		{
			return FALSE;
		}

		// lock the file
		flock($fp, LOCK_SH);

		// read the contents
		$cache = @fread($fp, filesize($file));

		// unlock te file
		flock($fp, LOCK_UN);

		// close the file
		fclose($fp);

		// end of line
		$eol = strpos($cache, "\n");

		// get the timestamp
		$timestamp = substr($cache, 0, $eol);

		// get the cache
		$cache = trim((substr($cache, $eol)));

		// if the current time is greater than the timestamp plus the refresh amount
		if (time() > ($timestamp + ($this->settings['cache_refresh'] * 60)))
		{
			// return false
			return FALSE;
		}

	    return $cache;
	}



	/**
	* Write Cache
	*
	* @param	string
	* @return	void
	* @since 	version 1.0.0
	*/
	function _write_cache($data, $url)
	{

		// check for cache
		$dir = PATH_CACHE.LG_AU_cache_name.'/';

		if ( ! @is_dir($dir))
		{
			if ( ! @mkdir($dir, 0777))
			{
				return FALSE;
			}
			@chmod($dir, 0777);
		}

		// add a timestamp to the top of the file
		$data = time()."\n".$data;

		// create the file path
		$file = $dir.md5($url);

		// open the file if we can
		if ( ! $fp = @fopen($file, 'wb'))
		{
			return FALSE;
		}
		// lock, write, unlock, close
		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
		// change the file perms
		@chmod($file, 0777);
	}

	/**
	 * Creates a select box
	 *
	 * @access public
	 * @param mixed $selected The selected value
	 * @param array $options The select box options in a multi-dimensional array. Array keys are used as the option value, array values are used as the option label
	 * @param string $input_name The name of the input eg: Lg_polls_ext[log_ip]
	 * @param string $input_id A unique ID for this select. If no id is given the id will be created from the $input_name
	 * @param boolean $use_lanng Pass the option label through the $LANG->line() method or display in a raw state
	 * @param array $attributes Any other attributes for the select box such as class, multiple, size etc
	 * @return string Select box html
	 */
	function select_box($selected, $options, $input_name, $input_id = FALSE, $use_lang = TRUE, $key_is_value = TRUE, $attributes = array())
	{
		global $LANG;

		$input_id = ($input_id === FALSE) ? str_replace(array("[", "]"), array("_", ""), $input_name) : $input_id;

		$attributes = array_merge(array(
			"name" => $input_name,
			"id" => strtolower($input_id)
		), $attributes);

		$attributes_str = "";
		foreach ($attributes as $key => $value)
		{
			$attributes_str .= " {$key}='{$value}' ";
		}

		$ret = "<select{$attributes_str}>";

		foreach($options as $option_value => $option_label)
		{
			if (!is_int($option_value))
				$option_value = $option_value;
			else
				$option_value = ($key_is_value === TRUE) ? $option_value : $option_label;

			$option_label = ($use_lang === TRUE) ? $LANG->line($option_label) : $option_label;
			$checked = ($selected == $option_value) ? " selected='selected' " : "";
			$ret .= "<option value='{$option_value}'{$checked}>{$option_label}</option>";
		}

		$ret .= "</select>";
		return $ret;
	}


}

?>