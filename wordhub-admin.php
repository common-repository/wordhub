<?php

if ( !class_exists( 'Wordhub_Admin')):

class Wordhub_Admin {

	function wordhub_admin() {
		add_action( 'admin_init', array ( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}
	
	function init() {
	    do_action( 'wordhub_admin_init' );
	}
	
    function admin_menu() {
    	if ( function_exists( 'add_menu_page' ) ) {    	    
    		add_menu_page(__('WordHub'), __('WordHub'), 'manage_options', 'wordhub-admin', array($this, 'display'));
    	}
    }

    function display() {
        if (isset($_POST['save_options'])) {
            $githubUserName = isset($_REQUEST['github_username']) ? $_REQUEST['github_username'] : '';
            $githubApiToken = isset($_REQUEST['github_api_token']) ? $_REQUEST['github_api_token'] : '';
            
            $github = new phpGitHubApi();
            $github->authenticate($githubUserName, $githubApiToken); 
            try {
                $user = $github->getUserApi()->show($githubUserName); 
                wordhub_set_options($githubUserName, $githubApiToken);     
                $message = '<div id="message" class="updated fade"><p>Your GitHub information has been saved!</p></div>';
                    
            } catch (Exception $e) {
                $message = '<div id="message" class="error"><p>Your GitHub username or API token are incorrect. Please check and try again.</p></div>';
            }
        }
        
        $wordhubOptions = @wordhub_get_options();
    
        ?>
        <div class="wrap">
        <h2>WordHub</h2>
        <?php if ( isset($message) ) echo $message; ?>
        <br class="clear" />
        <form method="post">
            		<input type="hidden" name="updateinfo" value="<?php echo $mode?>" />
            <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="github_username">GitHub Username</label>
                </th>
                <td>
                    <input name="github_username" type="text" id="github_username" value="<?php echo $wordhubOptions['github_username']; ?>" class="regular-text" />
                    <p class="description">Your GitHub user name.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="github_api_token">GitHub API Token</label>
                </th>
                <td>
                    <input name="github_api_token" type="text" id="github_api_token" value="<?php echo $wordhubOptions['github_api_token']; ?>" class="regular-text" />
                    <p class="description">Your GitHub API token, found under "Administrative Information" on your <a href="http://github.com/account">Account Settings</a> page.</p>
                </td>
            </tr>
            </table>
            <p class="submit">
            <input type="submit" name="save_options" class="button-primary" value="Save Changes" />
            </p>
        
        </form>
        <br class="clear" />    

        </div>
    <?php 
    }
}
endif; 

$wordhub_admin = new Wordhub_Admin();