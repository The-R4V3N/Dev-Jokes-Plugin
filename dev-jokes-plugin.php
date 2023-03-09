<?php
/*
Plugin Name: Dev Jokes Plugin
Plugin URI: https://www.oliver-joisten.se
Description: A plugin that displays a random Dev joke from an API.
Version: 0.1.1
Author: The-R4V3N
Author URI: https://www.oliver-joisten.se
Settings URI: dev-joke-plugin-settings
License: GPL2
*/

// Include the settings page

function dev_joke_plugin_settings_page()
{
    $tabs = array(
        'general' => 'General Settings',
        'user_guide' => 'User Guide',
    );

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
    ?>
    <div class="wrap">
        <h1>Dev Joke Plugin Settings</h1>
        <h2 class="nav-tab-wrapper">
            <?php foreach ($tabs as $slug => $title): ?>
                <?php if ($slug == 'general' || current_user_can('manage_options')): ?>
                    <a class="nav-tab <?php echo ($active_tab == $slug) ? 'nav-tab-active' : ''; ?>"
                        href="?page=dev-joke-plugin-settings&tab=<?php echo $slug; ?>"><?php echo $title; ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </h2>
        <form method="post" action="options.php">
            <?php settings_fields('dev_joke_plugin_settings'); ?>
            <?php
            switch ($active_tab) {
                case 'general':
                    ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">API Source URL</th>
                            <td><input type="text" name="dev_joke_plugin_api_source" readonly style="width: 40%; font-size: 16px;"
                                    value="<?php echo esc_attr(get_option('dev_joke_plugin_api_source', 'https://official-joke-api.appspot.com/jokes/programming/random')); ?>" />
                            </td>
                        </tr>
                    </table>
                    <?php
                    break;
                case 'user_guide':
                    if (!current_user_can('manage_options')) {
                        wp_die(__('Sorry, you are not allowed to access this page.'));
                    }
                    ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">User Guide</th>
                            <td>
                                <?php echo dev_joke_plugin_user_guide(); ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                    break;
            }
            ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


// register the settings page 
function dev_joke_plugin_register_settings()
{
    register_setting('dev_joke_plugin_settings', 'dev_joke_plugin_api_source');
}


// create a short code to use it on the frontend
function dev_joke_shortcode()
{
    $api_source = get_option('dev_joke_plugin_api_source', 'https://official-joke-api.appspot.com/jokes/programming/random');
    $response = wp_remote_get($api_source);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    $joke = $data[0]->setup . ' ' . $data[0]->punchline;
    return '<p>' . $joke . '</p>';
}

//display the settingspage in the backend
function dev_joke_plugin_register_settings_page()
{
    add_menu_page(
        'Dev Joke Plugin Settings',
        'Dev Joke Plugin',
        'manage_options',
        'dev-joke-plugin-settings',
        'dev_joke_plugin_settings_page',
        'dashicons-smiley',
        100
    );
}

// user guide for the plugin
function dev_joke_plugin_user_guide()
{
    ?>
    <h2>User Guide</h2>
    <h3>Welcome to the Dev Joke Plugin! </h3>
    <p>This plugin allows you to add a random Dev joke to your site.</p>
    <p>To display a random Dev joke, simply use the following shortcode where ever you want on your page.</p>
    <br>
    <p><strong>Shortcode:</strong> <input type="text" value="[dev_joke]" readonly /></p>

    <?php
}

// adding actions and shortcode for the plugin
add_action('admin_init', 'dev_joke_plugin_register_settings');
add_action('admin_menu', 'dev_joke_plugin_register_settings_page');
add_shortcode('dev_joke', 'dev_joke_shortcode');