<?php
    /**
     * Plugin Name:  Quant A.I
     * Plugin URI:   https://github.com/talhaQ96/
     * Description:  The plugin fetches the posts from Opensource API JSONPlaceholder
     * Version:      1.0
     * Author:       Talha Qureshi 
     * Author URI:   https://github.com/talhaQ96/
     **/


// Exit if files accessed directly.
defined( 'ABSPATH' ) || die();

define('PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ));

define('PLUGIN_ROOT_URL' , plugin_dir_url( __FILE__ ));


class Quant_AI {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        add_action('admin_menu', array($this, 'register_options_page'));
    }


    /**
     * The function loads all required assets for Admin.
     * 
     * The function is called inside class constructor using `admin_enqueue_scripts` hook
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style('quant-ai', PLUGIN_ROOT_URL . 'assets/css/quant-ai.css');
    }


    /**
     * Registers options page `Quant A.I` 
     * 
     * The function is called inside class constructor using `admin_menu` hook
     */
    public function register_options_page() {
        add_menu_page(
            'Quant A.I Settings',
            'Quant A.I',
            'manage_options',
            'quant-ai-options',
            array($this, 'render_options_page'), // callback function for rendering output
            'dashicons-admin-plugins',
            2
        );
    }


    /**
     * Renders output for Quant A.I Settings page
     * 
     * This is call back function which is being called inside function register_options_page --> add_menu_page 
     */
    public function render_options_page() {
    
        if (!current_user_can('manage_options')){
            return;
        }
    
        else{
             $user_input     = get_option('user_input');
             $saved_response = get_option('saved_response');
             $posts = unserialize($saved_response);
    
            if (isset($_POST['submit'])) {
                $user_input = sanitize_textarea_field($_POST['post-id']);
                update_option('user_input', $user_input);
    
                $response = wp_remote_get('https://jsonplaceholder.typicode.com/posts/'. $user_input);
    
                if (!is_wp_error($response)){
                    $response_body = wp_remote_retrieve_body($response);
                    $decoded_body = json_decode($response_body);
                    $serialized_data = serialize($decoded_body);
    
                    update_option('saved_response', $serialized_data);
    
                    $posts = unserialize($serialized_data);
    
                }
    
                else {
                    $error = 'Error making API request.';
                }
            }
    ?>
                <div class="qai-wrapper">
                    <h2>Quant A.I Settings</h2>

                    <form method="post">
                        <label for="post-id">Enter Post ID (Between 1 - 100):</label>
                        <input type="number" name="post-id" value="<?php echo esc_textarea($user_input); ?>" />
                        <input type="submit" name="submit" class="button-primary" value="Fetch Post" />
                    </form>

                    <div class="qai-response">
                        <h3>API Response</h3>
                        <?php
        
                            if (is_array($posts)) {
                                foreach ($posts as $post){
                                    echo '<ul>';
                                        echo '<li><b>UserId:</b> '. $post->userId .'</li>';
                                        echo '<li><b>Id:</b> '. $post->id .'</li>';
                                        echo '<li><b>Title:</b> '. $post->title .'</li>';
                                        echo '<li><b>Body:</b> '. $post->body .'</li>';
                                    echo '</ul>';
                                }
                            }
        
                            elseif (is_object($posts)) {
                                if(count(get_object_vars($posts)) !== 0){
                                    echo '<ul>';
                                        foreach ($posts as $key => $value) {
                                            echo '<li><b>'. $key .':</b> '. $value .'</li>';   
                                        }
                                    echo '</ul>'; 
                                }

                                else{
                                    echo '<p>No Result Found.</p>';
                                }
                            }
        
                            else{
                                echo $error;
                            }
        
                        ?>
                    </div>
                </div>
    <?php
        }
    }
}

new Quant_AI ();