<?php
/**
 * @package WordHub
 * @version 1.0
 */
/*
Plugin Name: WordHub
Plugin URI: http://clioweb.org/developing/wordhub
Description: Display your GitHub account on your WordPress site.
Author: Jeremy Boggs
Version: 1.0alpha
Author URI: http://clioweb.org
*/

/*
    Copyright (C) 2010, Jeremy Boggs. All rights reserved.    

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( !class_exists( 'Wordhub_Loader' ) ) :

class Wordhub_Loader {

	function wordhub_loader() {
		add_action( 'init', array ( $this, 'init' ) );
		add_action( 'admin_init', array ( $this, 'admin_init' ) );
		
		add_action( 'plugins_loaded', array( $this, 'loaded' ) );
		add_action( 'wordhub_loaded', array( $this, 'includes' ) );
		add_action( 'wordhub_init', array( $this, 'textdomain' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		
		add_action( 'rewrite_rules_array', array( $this, 'rewrite_rules_array') );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_filter( 'template_redirect', array( $this, 'public_template' ) );	
		
		// activation sequence
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// deactivation sequence
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	function init() {
		do_action( 'wordhub_init' );
	}
	
	function admin_init() {
	    do_action( 'wordhub_admin_init');
	}
	
	function loaded() {
		do_action( 'wordhub_loaded' );
	}

    function activation() {
        $this->flush_rewrite_rules();
    }
    
    function admin_notices() {
        $html = '';
        if ( !get_option('WordHubOptions') && !isset($_POST['submit']) ) {
            $html .= '<div id="wordhub-warning" class="updated fade">';
            $html .= '<p><strong>'.__('WordHub is almost ready.').'</strong> ';
            $html .= sprintf(__('You must <a href="%1$s">enter your Github username and API key</a> for it to work.'), "admin.php?page=wordhub-admin");
            $html .= '</p></div>';
        }
        echo $html;
    }
    
    function deactivation() {
        delete_option('WordHubOptions');
    }
    
	function includes() {
	    require( dirname( __FILE__ ) . '/wordhub-helpers.php' );
		require( dirname( __FILE__ ) . '/php-github-api/lib/phpGitHubApi.php' );
		if ( is_admin() ) {
			require( dirname( __FILE__ ) . '/wordhub-admin.php' );
        }
	}
	
	// Allow this plugin to be translated by specifying text domain
	// Todo: Make the logic a bit more complex to allow for custom text within a given language
	function textdomain() {
		$locale = get_locale();

		// First look in wp-content/wordhub-files/languages, where custom language files will not be overwritten by WordHub upgrades. Then check the packaged language file directory.
		$mofile_custom = WP_CONTENT_DIR . "/wordhub-files/languages/wordhub-$locale.mo";
		$mofile_packaged = WP_PLUGIN_DIR . "/wordhub/languages/wordhub-$locale.mo";

    	if ( file_exists( $mofile_custom ) ) {
      		load_textdomain( 'wordhub', $mofile_custom );
      		return;
      	} else if ( file_exists( $mofile_packaged ) ) {
      		load_textdomain( 'wordhub', $mofile_packaged );
      		return;
      	}
	}

    function flush_rewrite_rules() {
       global $wp_rewrite;
       $wp_rewrite->flush_rules();
    }

    function rewrite_rules_array($rules) {
        $newrules = array();
    	$newrules['wordhub/(.+)'] = 'index.php?wordhub=true&wordhub_repo=$matches[1]';
    	$newrules['wordhub'] = 'index.php?wordhub=true';
    	return $newrules + $rules;
    }
    
    function query_vars($vars) {
      array_push($vars, 'wordhub', 'wordhub_repo');
      return $vars;
    }


    function public_template() {
        global $wp_query;
        $wordhub = isset($wp_query->query_vars['wordhub']); 
        if(!empty($wordhub)) {
            // Check if there's a template in the active theme, use the default one if not.
            $template = file_exists(TEMPLATEPATH.'/wordhub-template.php') ? TEMPLATEPATH.'/wordhub-template.php' :  dirname( __FILE__ ) . '/wordhub-template.php';
            include($template);
            exit;
        }
    }
}

endif; // class exists

$wordhub_loader = new Wordhub_Loader();