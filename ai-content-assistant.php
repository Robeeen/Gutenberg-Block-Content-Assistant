<?php

/*
Plugin Name: AI-Powered Content Assistant
Description: A plugin that provides AI-powered content generation and optimization tools.
Version: 1.0
Author: Shams Khan
*/

function generate_ai_content($prompt) {
    $api_key = '';
    $url = 'https://api.openai.com/v1/completions';

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'model' => 'gpt-3.5-turbo',

            'prompt' => $prompt,
            'max_tokens' => 200,
            'temperature' => 0.7,
        )),
    ));

    if (is_wp_error($response)) {
        error_log('API request error: ' . $response->get_error_message()); // Log the error
        return 'Error in API request';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    error_log('API response: ' . print_r($data, true)); // Log the response for debugging

    if (isset($data['choices'][0]['text'])) {
        return $data['choices'][0]['text'];
    }

    return 'No content generated.';
}

add_action('admin_menu', 'ai_content_assistant_menu');

function ai_content_assistant_menu() {
    add_menu_page(
        'AI Content Assistant', 
        'AI Content', 
        'manage_options', 
        'ai-content-assistant', 
        'ai_content_assistant_settings_page', 
        'dashicons-edit', 
        100
    );
}

function ai_content_assistant_settings_page() {?>
    <div class="wrap">
        <h1>AI-Powered Content Assistant</h1>
        <form method="post" action="">
            <?php
            // CSRF protection
            wp_nonce_field('ai_content_generate_action', 'ai_content_generate_nonce');
            ?>
            <textarea name="ai_prompt" placeholder="Enter your content topic or idea here..."><?php echo esc_attr(isset($_POST['ai_prompt']) ? $_POST['ai_prompt'] : ''); ?></textarea>
            <?php
            // Output save button
            submit_button('Generate Content');
            ?>
        </form>

        <?php
        // Check if form is submitted and nonce is valid
        if (isset($_POST['ai_prompt']) && check_admin_referer('ai_content_generate_action', 'ai_content_generate_nonce')) {
            // Get the prompt from the form
            $ai_prompt = sanitize_text_field($_POST['ai_prompt']);

            // Call the AI content generation function
            $generated_content = generate_ai_content($ai_prompt);

            // Output the generated content
            echo '<h2>Generated Content:</h2>';
            echo '<p>' . esc_html($generated_content) . '</p>';
        }
        ?>
    </div>
   
<?php         
        // Check if the user has entered a prompt and process the AI content generation
        
}

add_action('admin_init', 'ai_content_assistant_register_settings' );

function ai_content_assistant_register_settings(){
    register_setting('ai-content-assistant-group', 'ai_prompt');

    add_settings_section(
        'ai-content-section',
        '',
        null,
        'ai_content_fields_main'
    );

    add_settings_field(
        'ai-fields-lists',
        '',
        'ai_content_fields_callback',
        'ai-content-fields_main',
        'ai-content-section'

    );
}

function ai_content_fields_callback(){
?><div><?php
    if (isset($_POST['ai_prompt'])) {
        echo '<h2>Generated Content:</h2>';
        echo '<p>' . generate_ai_content($_POST['ai_prompt']) . '</p>';
    } else{
        echo "Error";
    }
    
    
    ?>
    </div>
    <?php

}

