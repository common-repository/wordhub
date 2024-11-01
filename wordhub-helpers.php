<?php

/**
 * Sets the values for GitHub options.
 * 
 * @since 1.0
 * @uses get_option()
 * @uses update_option()
 * @uses add_option()
 * @param string $githubUserName
 * @param string $githubApiToken
 **/
function wordhub_set_options($githubUserName, $githubApiToken) 
{
	$wordhubOptions = array(
	    'github_username' => $githubUserName,
	    'github_api_token' => $githubApiToken
	);
        
    $wordhubOptionsName = 'WordHubOptions';
    
    if ( get_option($wordhubOptionsName) ) {
	    update_option($wordhubOptionsName, $wordhubOptions);
    } else {
        add_option($wordhubOptionsName, $wordhubOptions, '', 'no');
    }
}

/**
 * Returns all the fields for GitHub options as an array.
 * 
 * @since 1.0
 * @uses get_option()
 * @return array
 **/
function wordhub_get_options()
{
    $wordhubSavedOptions = get_option('WordHubOptions');
	if (!empty($wordhubSavedOptions)) {
		foreach ($wordhubSavedOptions as $key => $value)
			$wordhubOptions[$key] = $value;
		}
	return $wordhubOptions;
}

function wordhub_get_query_vars()
{
    global $wp_query;
    
    $wordhubVars = array();
    
    $wordhubVarNames = array('wordhub', 'wordhub_repo');
    foreach ($wordhubVarNames as $name) {
        $wordhubVars[$name] = $wp_query->query_vars[$name];    
    }
    
    return $wordhubVars;
}

/**
 * Helper for displaying GitHub information.
 * @since 1.0
 * @param array $vars An array of variables, to display specific GitHub info.
 * @uses get_option()
 * @uses wordhub_get_query_vars()
 * @uses wordhub_get_options()
 * @uses wordhub_display_repo()
 * @return array
 */
function wordhub_get_display_data($vars = array())
{
    // If we don't pass any variables to the function, we'll check the URL.
    if (empty($vars)) {
        $vars = wordhub_get_query_vars();
    }
    
    // Only display wordhub data if the 'wordhub' var is set.
    if ( isset($vars['wordhub']) ){
        $html = '';
        $wordhubOptions = wordhub_get_options();
        $githubUserName = $wordhubOptions['github_username'];
        $githubApiToken = $wordhubOptions['github_api_token'];

        $github = new phpGitHubApi();
        $github->authenticate($githubUserName, $githubApiToken);
    
        // If the wordhub_repo var is set, we need to display that specific repo.
        if ( isset($vars['wordhub_repo']) ) {
            // If there is a repo with that name, display it. Else, say it doesn't exist.
            if ($repo = $github->getRepoApi()->show($githubUserName, $vars['wordhub_repo']) ) {
                $html .= wordhub_display_repo($repo);
            } else {
                $html .= 'There is no repo called '.$vars['wordhub_repo'].' for the user '.$githubUserName;
            }
        } else {    
            // Otherwise, we'll display a list of repos if we have some.   
            if ( $repos = $github->getRepoApi()->getUserRepos($githubUserName) ) {
                
                foreach($repos as $repo) {
                    $html .= wordhub_display_repo($repo);
                }
            } else {
                $html .= 'There are no repos for the user '.$githubUserName;
            }
        }
        return $html;
    }
    return false;
}

function wordhub_public_display($vars = array())
{
    echo wordhub_get_display_data($vars);
}

function wordhub_display_repo($repo)
{
    $html = '';
    if ($repo) {
        $html .= '<h2>'.$repo['name'].'</h2>';
        $html .= wpautop($repo['description']);
    }
    return $html;
}

function wordhub_display_user($user)
{
    $wordhubVars = wordhub_get_query_vars();
    
    $wordhubOptions = wordhub_get_options();
    
    $githubUserName = $wordhubOptions['github_username'];
    $githubApiToken = $wordhubOptions['github_api_token'];

    $github = new phpGitHubApi();
    $github->authenticate($githubUserName, $githubApiToken); 

    // Need helper to display basic user information.
    $user = $github->getUserApi()->show($githubUserName);

    // Need helper to display list of all projects
    $repos = $github->getRepoApi()->getUserRepos($githubUserName);
}