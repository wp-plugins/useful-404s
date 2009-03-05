<?php
/*
Plugin Name: Useful 404's
Plugin URI: http://skullbit.com/wordpress-plugin/useful-404s/
Description: Create more useful 404 error pages, including email notifications for bad links.  See http://www.alistapart.com/articles/amoreuseful404 for the inspiration behind this plugin.
Author: Devbits
Version: 1.5

Settings: 
1. Mistyped URL/Out-of-date Bookmark
	* User Message
2. Broken Link on Website
	* User Message
	* Email Message
3. Broken Link on Search Engine
	* User Message
	* Email Message
4. Broken Link on External Website
	* User Message
	* Email Messageftp

*/
if( !class_exists('Useful404s') ):
	class Useful404s{
		function Useful404s() { //constructor
			//ACTIONS
				#Add Settings Panel
				add_action( 'admin_menu', array($this, 'AddPanel') );
				#Update Settings on Save
				if( $_POST['action'] == 'u404_update' )
					add_action( 'init', array($this,'SaveSettings') );
				#Save Default Settings
					add_action( 'init', array($this, 'DefaultSettings') );
			//LOCALIZATION
				#Place your language file in the plugin folder and name it "wpfrom-{language}.mo"
				#replace {language} with your language value from wp-config.php
				load_plugin_textdomain( 'u404', '/wp-content/plugins/useful404s' );
		}
		
		function AddPanel(){
			add_options_page( __("Useful 404's",'u404s'), __("Useful 404's",'u404s'), 10, 'useful404s', array($this, 'Settings') );
		}
		
		function DefaultSettings () {
			$default = array( 
								'mistype_output'	=> "<p>Sorry, but the page you were trying to get to, {404}, does not exist.</p>\n<p>It looks like this was the result of either:</p>\n<ul>\n<li>A mistyped address</li>\n<li>or an out-of-date bookmark in your web browser</li>\n</ul>\n<p>You may want to try searching this site or using our site map to find what you are looking for.</p>",
								'internal_output'	=> "<p>Sorry, but the page you were trying to get to, {404}, does not exist.</p>\n<p>Apparently, we have a broken link on our page. An e-mail has just been sent to the website owner and this should be corrected shortly. No further action is required on your part.</p>",
								'internal_email'	=> array('subject' => 'Broken link on '.get_option('siteurl'), 'msg' => "There appears to be a broken link on the page, {ref}. Someone was trying to get to {404} from that page.  Please take a look and see if this can be fixed.", 'disable'=>0),
								'search_output'		=> "<p>Sorry, but the page you were trying to get to, {404}, does not exist.</p>\n<p>It looks like the search engine, {search}, has returned a link to an old page.  These old links should eventually be removed from their indexes but since these are automatically generated there is no one to contact to correct the problem.</p>\n<p>You may want to try searching this site or using our site map to find what you are looking for.</p>",
								'search_email'		=> array('subject' => 'Broken link on {search}', 'msg' => "There appears to be a broken link on the Search Engine, {search}. Someone was trying to get to {404} from that page.  Please take a look and see if this can be fixed.", 'disable'=>1),
								'external_output'	=> "<p>Sorry, but the page you were trying to get to, {404}, does not exists.</p>\n<p>Apparently, there is a broken link on the page you just came from. We have been notified and will attempt to contact the owner of that page and let them know about the error.</p>\n<p>You may want to try searching this site or using our site map to find what you are looking for.</p>",
								'external_email'	=> array('subject' => 'Broken link on External Domain', 'msg' => "BROKEN LINK ON EXTERNAL WEBSITE.\n\nThere appears to be a broken link on the page, {ref}. Someone was trying to get to {404} from that page. Why don't you take a look at it and see if you can contact the page owner and let them know about it?", 'disable'=>0),
								'search_list'	=> 'google.com,yahoo.com,altavista.com,dogpile.com,ask.com,live.com,alltheweb.com,lycos.com,a9.com,aol.com',
								'ignore'		=> 'wp-admin,favicon.ico'
							);
			if( !get_option('useful404s') ): #Set Defaults if no values exist
				add_option( 'useful404s', $default );
			else: #Set Defaults if new value does not exist
				$u404 = get_option( 'useful404s' );
				foreach( $default as $key => $val ):
					if( !$u404[$key] ):
						$u404[$key] = $val;
						$new = true;
					endif;
				endforeach;
				if( $new )
					update_option( 'useful404s', $u404 );
			endif;
		}
		
		function SaveSettings(){
			check_admin_referer('u404-update-options');
			$update = get_option( 'useful404s' );
			$update["mistype_output"] = $_POST['mistype_output'];
			$update["internal_output"] = $_POST['internal_output'];
			$update["internal_email"] = array( 'subject'=>$_POST['internal_subject'], 'msg'=>$_POST['internal_msg'], 'disable'=>$_POST['internal_disable'] );
			$update["search_output"] = $_POST['search_output'];
			$update["search_email"] = array( 'subject'=>$_POST['search_subject'], 'msg'=>$_POST['search_msg'], 'disable'=>$_POST['search_disable'] );
			$update["external_output"] = $_POST['external_output'];
			$update["external_email"] = array( 'subject'=>$_POST['external_subject'], 'msg'=>$_POST['external_msg'], 'disable'=>$_POST['external_disable'] );
			$update["search_list"] = $_POST['search_list'];
			$update["ignore"] = $_POST['ignore'];
			update_option( 'useful404s', $update );
			$_POST['notice'] = __('Settings Saved', 'u404');
		}
		
		function Settings(){
			$u404 = get_option( 'useful404s' );
			if( $_POST['notice'] )
				echo '<div id="message" class="updated fade"><p><strong>' . $_POST['notice'] . '</strong></p></div>';
			?>
             <div class="wrap">
            	<h2><?php _e('Useful 404\'s Settings', 'u404')?></h2>
                <p><strong><?php _e('Instructions', 'u404');?></strong><br /><?php _e('Put the function call <code>useful404s();</code> in your 404.php template file below the title and set the options below.','u404');?></p>
                <p><strong><?php _e('Replacement Codes', 'u404');?></strong><br /><?php _e('<code>{ref}</code> = Refferal URL<br \><code>{404}</code> = 404 URL<br \><code>{search}</code> = Search Engine','u404');?></p>
                <form method="post" action="">
                	<?php if( function_exists( 'wp_nonce_field' )) wp_nonce_field( 'u404-update-options'); ?>
                    <table class="form-table">
                        <tbody>
                        	<tr valign="top">
                       			 <th scope="row"><label for="mistype_output"><?php _e('Mistyped URL 404 Notice', 'u404');?></label></th>
                        		<td><textarea name="mistype_output" id="mistype_output" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['mistype_output']);?></textarea></td>
                        	</tr>
                            <tr valign="top">
                       			 <th scope="row"><label for="internal_output"><?php _e('Internal Broken Link 404 Notice', 'u404');?></label></th>
                        		<td><textarea name="internal_output" id="internal_output" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['internal_output']);?></textarea></td>
                        	</tr>
                            <tr valign="top">
                       			 <th scope="row"><label for="internal_subject"><?php _e('Internal Broken Link Email', 'u404');?></label></th>
                        		<td><label for="internal_subject">Subject:</label><br /><input type="text" name="internal_subject" id="internal_subject" value="<?php echo stripslashes($u404['internal_email']['subject']);?>" style="width:90%" /><br />
                                <label for="internal_msg">Message:</label><br />
                                <textarea name="internal_msg" id="internal_msg" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['internal_email']['msg']);?></textarea><br />
                                <label><input type="checkbox" name="internal_disable" value="1"<?php if($u404['internal_email']['disable'])echo ' checked="checked"';?> /> <?php _e('Disable Email Notice');?></label></td>
                        	</tr>
                            <tr valign="top">
                       			 <th scope="row"><label for="search_output"><?php _e('Search Engine Broken Link 404 Notice', 'u404');?></label></th>
                        		<td><textarea name="search_output" id="search_output" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['search_output']);?></textarea></td>
                        	</tr>
                            <tr valign="top">
                       			 <th scope="row"><label for="search_subject"><?php _e('Search Engine Broken Link Email', 'u404');?></label></th>
                        		<td><label for="search_subject">Subject:</label><br /><input type="text" name="search_subject" id="search_subject" value="<?php echo $u404['search_email']['subject'];?>"  style="width:90%" /><br />
                                <label for="search_msg">Message:</label><br />
                                <textarea name="search_msg" id="search_msg" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['search_email']['msg']);?></textarea><br />
                                <label><input type="checkbox" name="search_disable" value="1"<?php if($u404['search_email']['disable'])echo ' checked="checked"';?> /> <?php _e('Disable Email Notice');?></label></td>
                        	</tr>
                            
                            <tr valign="top">
                       			 <th scope="row"><label for="external_output"><?php _e('External Broken Link 404 Notice', 'u404');?></label></th>
                        		<td><textarea name="external_output" id="external_output" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['external_output']);?></textarea></td>
                        	</tr>
                            <tr valign="top">
                       			 <th scope="row"><label for="external_subject"><?php _e('External Broken Link Email', 'u404');?></label></th>
                        		<td><label for="external_subject">Subject:</label><br /><input type="text" name="external_subject" id="external_subject" value="<?php echo stripslashes($u404['external_email']['subject']);?>" style="width:90%" /><br />
                                <label for="external_msg">Message:</label><br />
                                <textarea name="external_msg" id="external_msg" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['external_email']['msg']);?></textarea><br />
                                <label><input type="checkbox" name="external_disable" value="1"<?php if($u404['external_email']['disable'])echo ' checked="checked"';?> /> <?php _e('Disable Email Notice');?></label></td>
                        	</tr>
                            
                             <tr valign="top">
                       			 <th scope="row"><label for="search_list"><?php _e('Search Engine URL List', 'u404');?></label></th>
                        		<td><textarea name="search_list" id="search_list" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['search_list']);?></textarea><br /><small><?php _e('Enter Search Engine Domains seperated by commas.','u404');?></small></td>
                        	</tr>
                            
                            <tr valign="top">
                       			 <th scope="row"><label for="ignore"><?php _e('Ignore List', 'u404');?></label></th>
                        		<td><textarea name="ignore" id="ignore" cols="40" rows="20" style="width:90%;height:125px;"><?php echo stripslashes($u404['ignore']);?></textarea><br /><small><?php _e('Seperate keywords with commas for the plugin to ignore.  Any 404 url containing these keywords will not trigger a notification email.','u404');?></small></td>
                        	</tr>
                         </tbody>
                     </table>
                     </div>
                     
                    <p class="submit"><input name="Submit" value="<?php _e('Save Changes','u404');?>" type="submit" />
                    <input name="action" value="u404_update" type="hidden" />
                </form>
              
            </div>
            <?php
		}
		function Coded($in, $ref=false, $req=false, $search=false){
			$out = $in;
			$out = str_replace('{ref}', $ref, $out);
			$out = str_replace('{404}', $req, $out);
			$out = str_replace('{search}', $search, $out);
			return $out;
		}
		
		function Ignore($uri){
			$u404 = get_option( 'useful404s' );
			$ignore = explode(',',$u404['ignore']);
			foreach($ignore as $ig):
				if( strpos($uri, trim($ig)) !== false )
					return true;
			endforeach;			
			return false;
		}
		
		function Output(){
			$u404 = get_option( 'useful404s' );
			$ref = $_SERVER['HTTP_REFERER'];
			$req = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$search = false;
			if( !isset($ref) ) //MISTYPE 404
				$output = $u404['mistype_output'];
			if( strpos($ref, $_SERVER['SERVER_NAME']) !== false ): //INTERNAL LINK 404
				$output = $u404['internal_output'];
				if( !$u404['internal_email']['disable'] && !$this->Ignore($req) )
					if (!wp_mail( get_option('admin_email'), $this->Coded($u404['internal_email']['subject'], $ref, $req, $search), $this->Coded($u404['internal_email']['msg'], $ref, $req, $search) ) )
					$output = $output. '<p>email error</p>';
			endif;
			if( !$output ):
				$engines = explode( ',', $u404['search_list'] );
				foreach( $engines as $se ):
					if( strpos($ref, trim($se)) !== false )
						$search = $se;
				endforeach;
				if( $search ): //SEARCH ENGINE 404
					$output = $u404['search_output'];
					if( !$u404['search_email']['disable'] && !$this->Ignore($req) ):
						if(!wp_mail( get_option('admin_email'), $this->Coded($u404['search_email']['subject'], $ref, $req, $search), $this->Coded($u404['search_email']['msg'], $ref, $req, $search) ) )
						$output = $output. '<p>email error</p>';
					endif;
				else: //EXTERNAL LINK 404
					$output = $u404['external_output'];
					if( !$u404['external_email']['disable'] && !$this->Ignore($req) )
						if(!wp_mail( get_option('admin_email'), $this->Coded($u404['external_email']['subject'], $ref, $req, $search), $this->Coded($u404['external_email']['msg'], $ref, $req, $search) ))
							$output = $output. '<p>email error</p>';
				endif;						
			endif;
			$output = stripslashes($output);
			return $this->Coded($output, $ref, $req, $search);
		}
	}//END Class Useful404s
endif;

if( class_exists('Useful404s') )
	$u404s = new Useful404s();
	
function useful404s(){
	global $u404s;
	echo $u404s->Output();
}
?>