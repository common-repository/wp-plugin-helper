<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    WPLHE
 * @subpackage WPLHE/includes
 * @author     Grega Radelj <info@grrega.com>
 */
class WPLHE {

	protected $loader;
	
	protected $wplhe;

	protected $version;

	public function __construct() {
		if ( defined( 'WPLHE_VERSION' ) ) {
			$this->version = WPLHE_VERSION;
		} else {
			$this->version = '1.1.1';
		}
		$this->wplhe = 'wp-plugin-helper';

		$this->wplhe_load_dependencies();
		$this->wplhe_set_locale();
		$this->wplhe_define_admin_hooks();
		
		$this->notes = $this->wplhe_get_plugins_notes();
	}
	/**
	 * Load the required dependencies for this plugin
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WPLHE_Loader. Orchestrates the hooks of the plugin
	 * - WPLHE_i18n. Defines internationalization functionality
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function wplhe_load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wplhe-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wplhe-i18n.php';

		$this->loader = new WPLHE_Loader();

	}
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function wplhe_set_locale() {

		$plugin_i18n = new WPLHE_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'wplhe_load_plugin_textdomain' );

	}
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function wplhe_define_admin_hooks() {

		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'wplhe_enqueue_scripts' );
		
		$this->loader->add_filter( 'manage_plugins_columns', $this, 'wplhe_plugin_columns');
		$this->loader->add_action( 'manage_plugins_custom_column', $this, 'wplhe_plugin_custom_column', 10 , 3);
		
		$this->loader->add_action( 'wp_ajax_wplhe_save_note',$this, 'wplhe_save_note', 10, 2 );
		$this->loader->add_action( 'wp_ajax_wplhe_remove_note',$this, 'wplhe_remove_note', 10, 2 );

	}
	/**
	 * Run the loader to execute all of the hooks with WordPress
	 *
	 * @since    1.0.0
	 */
	public function wplhe_run() {
		$this->loader->run();
	}
	/**
	 * Enqueue scripts and styles
	 *
	 * @since     1.0.0
	 */
	function wplhe_enqueue_scripts(){
		wp_enqueue_style( $this->wplhe, plugin_dir_url( __FILE__ ) . 'css/wp_plugin_helper.css', array(), $this->version, 'all' );
		wp_enqueue_script('jquery-tiptip');
		wp_enqueue_script( $this->wplhe, plugin_dir_url( __FILE__ ) . 'js/wp_plugin_helper.js', array('jquery','jquery-tiptip'), $this->version, true );
	}
	/**
	 * Add compatibility column to plugins page
	 *
	 * @since     1.0.0
	 * @var    	  array		$columns    The version number of the plugin
	 * @return    array		$columns    The version number of the plugin
	 */
	function wplhe_plugin_columns($columns){
		$columns['wplhe_note_column'] = 'Note';
		$columns['wplhe_column'] = 'Compatibility';
		return $columns;
	}
	/**
	 * Gather and format custom column html
	 *
	 * @since     1.0.0
	 * @var    	  string	$column_name    Plugin name
	 * @var    	  string	$plugin_file    Main plugin file
	 * @var    	  array		$plugin_data    Plugin data
	 */
	function wplhe_plugin_custom_column($column_name, $plugin_file, $plugin_data){
		if('wplhe_column' == $column_name){
			$data = $this->wplhe_get_compatibility_data($plugin_file);
			$newData = $this->wplhe_format_compatibility_data($data);
			echo $newData;
		}
		else if('wplhe_note_column' == $column_name){
			$note = $this->wplhe_plugins_note($plugin_file);
			echo $note;
		}
	}
	/**
	 * Compare versions with patch check added.
	 * If versions are something like 4.9 and 4.9.6 it will 
	 * return 2(custom - same major and minor version, different patch) 
	 * instead of -1(smaller). The function will only check patch difference
	 * if $tested is TRUE, we don't need it for other checks.
	 * If the second version is lower because it has no PATCH version, 
	 * but the first one does return 0(same versions).
	 *
	 * @since     1.1.0
	 * @var    	  string	$ver1   		 Version 1
	 * @var    	  string	$ver2   		 Version 2
	 * @var    	  bool		$tested   		 If comparing "Tested up to"
	 * @return 	  int						 Version compare result or 2
	 */
	function wplhe_version_compare($ver1, $ver2, $tested=FALSE){
		$check1 = version_compare($ver1, $ver2);
		//check if the difference is only in the patch version
		if($tested && $check1 == -1){
			$ver1ar = explode('.',$ver1);
			$ver2ar = explode('.',$ver2);
			if($ver1ar[0] == $ver2ar[0] && $ver1ar[1] == $ver2ar[1]) return 2;
			else return $check1;
		}
		//check if second version has no patch version and the rest is the same
		else if($check1 == 1){
			$ver1ar = explode('.',$ver1);
			$ver2ar = explode('.',$ver2);
			if(isset($ver1ar[2]) && !isset($ver2ar[2])) return 0;
			else return $check1;
		}
		else return $check1;
	}
	/**
	 * Get plugin data from readme.txt
	 *
	 * @since     1.0.0
	 * @var       array		$path		    Path to main plugin file
	 * @return    array		$readmeData 	Required data from readme
	 */
	function wplhe_get_compatibility_data($path) {
		$readmeData = FALSE;
		$readmeData = get_plugin_data(WP_PLUGIN_DIR .'/'. $path);
		
		if(strpos($path,'/') > 0){
			$folder = explode('/',$path)[0];
			$rdmfile = WP_PLUGIN_DIR .'/'. $folder . '/readme.txt';
			$rdmfile2 = WP_PLUGIN_DIR .'/'. $folder . '/README.txt';
			
			if(file_exists($rdmfile)) $handle = fopen($rdmfile, "r");
			else if(file_exists($rdmfile2)) $handle = fopen($rdmfile2, "r");
			else $handle = FALSE;
			
			if($handle){
				while(($line = fgets($handle)) !== false){
					if(strpos($line,'Description') > 0) break;
					$line = explode(':',$line);
					if(isset($line[1])){
						$value = trim(str_replace(array('\\n','\\r',' '),'',$line[1]));
						switch($line[0]){
							case 'Requires at least':
								$readmeData['requires_at_least'] = $value;
								break;
							case 'Tested up to':
								$readmeData['tested_up_to'] = $value;
								break;
							case 'WC tested up to':
								$readmeData['WC tested up to'] = $value;
								break;
							case 'WC requires at least':
								$readmeData['WC requires at least'] = $value;
								break;
							case 'Requires PHP':
								$readmeData['requires_php'] = $value;
								break;
						}
					}
				}
				fclose($handle);
			}
		}
		return $readmeData;
	}
	/**
	 * Format plugin compatibility table
	 *
	 * @since     1.0.0
	 * @var       array		$data	Path to main plugin file
	 * @return    html		$html	Required data from readme
	 */
	function wplhe_format_compatibility_data($data) {
		
		global $wp_version;
		
		$wpr = isset($data['requires_at_least']) ? $data['requires_at_least'] : FALSE;
		$wpt = isset($data['tested_up_to']) ? $data['tested_up_to'] : FALSE;
		$wcr = $wct = $phr = $wprc = $wptc = $wcrc = $wctc = $phrc  = $wprt = $wptt = $wcrt = $wctt = $phrt = $woo = FALSE;
		
		$higherwp = __('A higher version of WordPress is installed','wp-plugin-helper');
		$equalwp = __('This version of WordPress is installed','wp-plugin-helper');
		$lowerwp = __('A lower version of WordPress is installed','wp-plugin-helper');
		$lowerwppatch = __('You are using a WordPress installation with a higher patch version than this plugin supports OR the plugin developers did not specify or update the supported patch version in this plugins readme. New WordPress version SHOULD be backwards compatible and there SHOULD be no reason to worry about it. For more information check https://semver.org/ .','wp-plugin-helper');
		$higherwc = __('A higher version of WooCommerce is installed','wp-plugin-helper');
		$equalwc = __('This version of WooCommerce is installed','wp-plugin-helper');
		$lowerwc = __('A lower version of WooCommerce is installed','wp-plugin-helper');
		$lowerwcpatch = __('You are using a WooCommerce installation with a higher patch version than this plugin supports OR the plugin developers did not specify or update the supported patch version in this plugins readme. New WooCommerce version SHOULD be backwards compatible and there SHOULD be no reason to worry about it. For more information check https://semver.org/ .','wp-plugin-helper');
		$higherphp = __('A higher version of PHP is installed','wp-plugin-helper');
		$equalphp = __('This version of PHP is installed','wp-plugin-helper');
		$lowerphp = __('A lower version of PHP is installed','wp-plugin-helper');
		$reqlong = __('Requires at least','wp-plugin-helper');
		$reqshort = __('Requires','wp-plugin-helper');
		$testlong = __('Tested up to','wp-plugin-helper');
		$testshort = __('Tested','wp-plugin-helper');
		
		if(!$wpr && !$wpt) return FALSE;
		else{
			$wpcheck = $this->wplhe_version_compare($wpr,$wp_version);
			$wpcheck2 = $this->wplhe_version_compare($wpt,$wp_version,TRUE);
			if($wpcheck == -1) {$wprc = 'wplhe_success';$wprt = $higherwp;}
			else if($wpcheck == 0) {$wprc = 'wplhe_info';$wprt = $equalwp;}
			else if($wpcheck == 1) {$wprc = 'wplhe_alert';$wprt = $lowerwp;}
			
			if($wpcheck2 == -1) {$wptc = 'wplhe_alert';$wptt = $higherwp;}
			else if($wpcheck2 == 0) {$wptc = 'wplhe_info';$wptt = $equalwp;}
			else if($wpcheck2 == 1) {$wptc = 'wplhe_success';$wptt = $lowerwp;}
			else if($wpcheck2 == 2) {$wptc = 'wplhe_memo';$wptt = $lowerwppatch;}
		}
		
		if(in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ))))	$woo = TRUE;
		
		if(isset($data['WC requires at least']) && !empty($data['WC requires at least'])){
			$wcr = $data['WC requires at least'];
			if($woo){
				$woocheck = $this->wplhe_version_compare($wcr,WC_VERSION);
				if($woocheck == -1) {$wcrc = 'wplhe_success';$wcrt = $higherwc;}
				else if($woocheck == 0) {$wcrc = 'wplhe_info';$wcrt = $equalwc;}
				else if($woocheck == 1) {$wcrc = 'wplhe_alert';$wcrt = $lowerwc;}
			}
		}
		if(isset($data['WC tested up to']) && !empty($data['WC tested up to'])){
			$wct = $data['WC tested up to'];
			if($woo){
				$woocheck2 = $this->wplhe_version_compare($wct,WC_VERSION,TRUE);
				if($woocheck2 == -1) {$wctc = 'wplhe_alert';$wctt = $higherwc;}
				else if($woocheck2 == 0) {$wctc = 'wplhe_info';$wctt = $equalwc;}
				else if($woocheck2 == 1) {$wctc = 'wplhe_success';$wctt = $lowerwc;}
				else if($woocheck2 == 2) {$wctc = 'wplhe_memo';$wctt = $lowerwcpatch;}
			}
		}
		if(isset($data['requires_php'])){
			$phr = $data['requires_php'];
			$phcheck = $this->wplhe_version_compare($phr,phpversion());
			if($phcheck == -1) {$phrc = 'wplhe_success';$phrt = $higherphp;}
			else if($phcheck == 0) {$phrc = 'wplhe_info';$phrt = $equalphp;}
			else if($phcheck == 1) {$phrc = 'wplhe_alert';$phrt = $lowerphp;}
		}
		$html = '<table class="wp_plugin_helper_table">
			<tr>
				<th></th>
				<th>
					<span class="wplhe_column_long">'.$reqlong.'</span>
					<span class="wplhe_column_short">'.$reqshort.'</span>
				</th>
				<th>
					<span class="wplhe_column_long">'.$testlong.'</span>
					<span class="wplhe_column_short">'.$testshort.'</span>
				</th>
			</tr>
			<tr>
				<td>'.__('WordPress','wp-plugin-helper').'</td>
				<td title="'.$wprt.'" class="'.$wprc.'">'.$wpr.'</td>
				<td title="'.$wptt.'" class="'.$wptc.'">'.$wpt.'</td>
			</tr>';
			
			if($wcr || $wct){
				$html .= '<tr>
						<td>'.__('WooCommerce','wp-plugin-helper').'</td>
						<td title="'.$wcrt.'" class="'.$wcrc.'">'.$wcr.'</td>
						<td title="'.$wctt.'" class="'.$wctc.'">'.$wct.'</td>
					</tr>';
			}
			
			if($phr){
				$html .= '<tr>
						<td>'.__('PHP','wp-plugin-helper').'</td>
						<td title="'.$phrt.'" class="'.$phrc.'">'.$phr.'</td>
						<td></td>
					</tr>';
			}
			
		$html .= '</table>';
		return $html;
	}
	/**
	 * Return the plugin note with the form added
	 *
	 * @since     1.0.0
	 * @var       string	$file			Path to main plugin file
	 * @return    html		$html			Note and note form
	 */
	function wplhe_plugins_note($file){
		$notes = $this->notes;
		$class = $note = $html = $type = $addc = '';
		$editc = $removec = ' wplhehide';
		$addnote = __('Add note','wp-plugin-helper');
		$editnote = __('Edit note','wp-plugin-helper');
		
		if(count($notes) > 0){
			foreach($notes as $notesfile=>$notesnote){
				if($notesfile == $file){
					$note = $notesnote['note'];
					$type = $notesnote['type'];
					$class = 'active_note ' . $type;
					$addc = ' wplhehide';
					$removec = '';
					$editc = '';
				}
			}
		}
		$note_form = $this->wplhe_note_form($file,$note,$type);
		$class = $class == '' ? 'wplhe_info' : $class;
		$note = htmlspecialchars_decode(stripslashes($note));
		
		$html .= '<div class="wplhe_note_container '.$class.'">
			<div class="wplhe_note" data-oldcontent="'.htmlspecialchars($note).'">'.$note.'</div>
			'.$note_form.'
		</div>';
		
		$html .= '<div class="wplhe_plugin_notes_links">
				<a href="" class="wplhe_add_note wplheleft'.$addc.'">'.$addnote.'</a>
				<a href="" class="wplhe_edit_note wplheleft'.$editc.'">'.$editnote.'</a>
				<a href="" class="wplhe_remove_note wplheleft'.$removec.'">'.__('Remove note','wp-plugin-helper').'</a>
				<div class="spinner wplhe_update_note2 wplheleft"></div>
			</div>';
		
		return $html;
	}
	/**
	 * Format the plugin note form
	 *
	 * @since     1.0.0
	 * @var       array		$file	Path to main plugin file
	 * @var       array		$note	Note html
	 * @var       array		$type	Type of note
	 * @return    html		$html	Plugin note form
	 */
	function wplhe_note_form($file,$note,$type){
		$types = array(
		'wplhe_info'=>__('Info','wp-plugin-helper'),
		'wplhe_memo'=>__('Memo','wp-plugin-helper'),
		'wplhe_alert'=>__('Alert','wp-plugin-helper'),
		'wplhe_success'=>__('Success','wp-plugin-helper')
		);
		$nonce = wp_create_nonce('wplhe_note_form_nonce');
		
		ob_start();
			wp_editor(htmlspecialchars_decode(stripslashes($note)),uniqid(),array('media_buttons'=>false));
		$editor = ob_get_clean();
		
		$html = '<div class="wplhe_plugin_note_form_container">';
			
		$html .= '<div class="wplhe_plugin_note_form">
				<input type="hidden" name="_wpnonce" value="'.$nonce.'" />
				<input name="plugin_file" type="hidden" value="'.$file.'" />
				<p  class="form-field wplhe_note_type_container">
					<label for="wplhe_plugin_note_type">'.__('Note type','wp-plugin-helper').'</label>
					<select name="wplhe_plugin_note_type">';
					foreach($types as $opttype=>$optlabel){
						$selected = $opttype == $type ? 'selected="selected"' : '';
						$html .= '<option value="'.$opttype.'" '.$selected.'>'.$optlabel.'</option>';
					}
		$html .='
					</select>
				</p>
				<div class="wplhe_editor_container">'.$editor;
				
		$html .='</div>
				<div class="wplhe_note_buttons_container">
					<button class="wplhe_save_note wplheleft button button-primary">'.__('Save note','wp-plugin-helper').'</button>
					<button class="wplhe_cancel wplheleft button">'.__('Cancel','wp-plugin-helper').'</button>
					<div class="spinner wplhe_update_note wplheleft"></div>
				</div>
			</div>
		</div>';
		return $html;
	}
	/**
	 * Get all plugin notes and make array
	 *
	 * @since     1.0.0
	 * @return    array		$notes		All plugin notes
	 */
	function wplhe_get_plugins_notes(){
		$notes = get_option('wplhe_plugin_notes');
		if(!is_array($notes)) $notes = json_decode($notes,true);
		if(NULL == $notes || !$notes || empty($notes)) $notes = array();
		return $notes;
	}
	/**
	 * Get saved notes, add new note and save notes
	 *
	 * @since     1.0.0
	 */
	function wplhe_save_note(){
		
		$noncecheck = wp_verify_nonce($_POST['_wpnonce'], 'wplhe_note_form_nonce');
		if(!$noncecheck) return false;
		
		$notes = $this->wplhe_get_plugins_notes();
		if(isset($_POST['plugin_file']) && isset($_POST['plugin_note']) && isset($_POST['plugin_type'])){
			if(count($notes) > 0 && $notes != ''){
				foreach($notes as $file=>$note){
					if($file == $_POST['plugin_file']) $notes[$file] = array('note'=>$_POST['plugin_note'],'type'=>$_POST['plugin_type']);
					else $notes[$_POST['plugin_file']] = array('note'=>htmlspecialchars($_POST['plugin_note']),'type'=>$_POST['plugin_type']);
				}
			}
			else $notes[$_POST['plugin_file']] = array('note'=>$_POST['plugin_note'],'type'=>$_POST['plugin_type']);
			update_option('wplhe_plugin_notes',$notes);
		}
		wp_die();
	}
	/**
	 * Get saved notes, remove the selected note and save notes
	 *
	 * @since     1.0.0
	 * @var       array		$data	Path to main plugin file
	 * @return    html		$html	Required data from readme
	 */
	function wplhe_remove_note(){
		$notes = $this->wplhe_get_plugins_notes();
		
		$noncecheck = wp_verify_nonce($_POST['_wpnonce'], 'wplhe_note_form_nonce');
		if(!$noncecheck) return false;
		
		if(isset($_POST['plugin_file'])){
			if(count($notes) > 0 && $notes != ''){
				foreach($notes as $file=>$note){
					if($file == $_POST['plugin_file']) unset($notes[$file]);
				}
			}
			update_option('wplhe_plugin_notes',$notes);
		}
		wp_die();
	}
	
}	
