<?php
namespace UiCoreElements\Utils;

class Email_Exception extends \Exception {}
class Redirect_Exception extends \Exception {}
class Submit_Exception extends \Exception {}

class Contact_Form_Service {

    protected $form_data,
              $settings,
              $files;
    public function __construct($form_data, $settings, $files) {
        $this->form_data = $form_data;
        $this->settings = $settings;
        $this->files = $files;
    }

    public function handle() {

        $processed_data = [];
        $responses = [];
        $data = [];

        // Checks for reCAPTCHA validation
        if (isset($this->form_data['grecaptcha_token']) && !empty($this->form_data['grecaptcha_token'])) {

            $recaptcha = $this->validate_recaptcha($this->form_data['grecaptcha_token'], $this->form_data['grecaptcha_version']);

            if(!$recaptcha['success']){
                return [
                    'status' => 'error',
                    'data' => [
                        'message' => esc_html__('reCAPTCHA validation failed.', 'uicore-elements'),
                    ]
                ];
            }
        }

        // Check for honeypot spam
        if(!$this->validate_spam()){
            return [
                'status' => 'success',
                'data' => [
                    'message' => $this->get_response_message('success') // Fakes a successfull submission
                ]
            ];
        }

        // Run all registered submit actions
        if (isset($this->settings['submit_actions']) && !empty($this->settings['submit_actions'])) {
            foreach ($this->settings['submit_actions'] as $action) {
                try {
                    switch ($action) {
                        case 'email':
                            $data = $this->send_mail($action);
                            $responses['email'] = $data['response'];
                            break;

                        case 'email_2' :
                            $data = $this->send_mail($action, $data);
                            $responses['email'] = $data['response'];
                            break;

                        case 'redirect':
                            $responses['redirect'] = $this->redirect();
                            break;

                        default:
                            throw new Submit_Exception(esc_html__('Unknown submit action: ', 'uicore-elements') . $action . esc_html__('. Check your settings.', 'uicore-elements'));
                    }
                } catch (Email_Exception $e) {
                    $responses['email'] = [
                        'status' => false,
                        'message' => $e->getMessage()
                    ];
                } catch (Redirect_Exception $e) {
                    $responses['redirect'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                } catch (Submit_Exception $e) {
                    $responses['submit'] = [
                        'message' => $e->getMessage()
                    ];
                }
            }

        // There's no need to continue without a submit action enabled
        } else {
            return [
                'status' => 'error',
                'data' => [
                    'message' => esc_html__('No submit action enabled.', 'uicore-elements')
                ]
            ];
        }

        // Consider `current_user_can( 'manage_options' )` as filter to return more specific messages on frontend (not tested)

        // Since attachments may be used up to two times (both emails), they need to be deleted only after processing submits
        if (isset($data['attachments']) && !empty($data['attachments']['files'])) {
            register_shutdown_function('unlink', $data['attachments']['files']);
        }

        // Build mail response
        if (isset($responses['email'])) {
            $status = $responses['email']['status'] ? 'success' : 'error';
            $processed_data['message'] = $responses['email']['message'];

            // Build attachment response
            if(isset($responses['email']['attachment'])) {
                $processed_data['attachment'] = $responses['attachment'];
            }
        }
        // Build redirect response (only if email was successful)
        if ($status === 'success' && isset($responses['redirect'])) {
            $processed_data['redirect'] = $responses['redirect'];
        }
        // Build submit response
        if (isset($responses['submit'])) {
            $processed_data['submit'] = $responses['submit'];
        }

        return [
            'status' => $status,
            'data' => $processed_data,
        ];
    }

    /**
     * Mail submition
     */
    protected function send_mail(string $action, array $data = []){

        $attachments = isset($data['attachments']) ? $data['attachments'] : []; // Check if there's attachments from previous mail submit action

        $mail_data = $this->compose_mail_data($action, $attachments); // build mail data

        // Check if there's any attachment error before sending mail
        if (!empty($mail_data['attachments']['errors'])) {
            // throwing exceptions here will block proper data flow. Is best directly returning the error on email action
            return [
                'response' => [
                    'status' => false,
                    'message' => $mail_data['attachments']['errors']
                ],
            ];
        }

        $email = wp_mail(
            $mail_data['email']['to'],
            $mail_data['email']['subject'],
            $mail_data['email']['message'],
            $mail_data['email']['headers'],
            $mail_data['email']['attachments']
        );

        return [
            'response' => [
                'status' => $email,
                'message' => $email ? $this->get_response_message('success') : $this->get_response_message('error')
            ],
            'attachments' => $mail_data['attachments'] // Return attachments for deletion and error handling
        ];
    }
    protected function compose_mail_data(string $action, array $attachments = []) {

        // Set short vars for the data
        $settings = $this->settings;
        $data = $this->form_data;
        $files = $this->files;

        $slug = $action == 'email_2' ? '_2' : ''; // Update controls slugs based on the mail submit type
        $line_break = $settings['email_content_type'.$slug] === 'html' ? '<br>' : "\n"; // Set line break type

        // Replace shortcodes by form data
        $content = $this->replace_content_shortcode( $settings['email_content'.$slug], $line_break );

        // Adds the metadata to content
        $content = $this->compose_metadata($content, $settings['form_metadata'.$slug], $line_break);

        // If theres attachments from previous submit action, use it, otherwhise prepare it from $files
        $attachments = !empty($attachments) ? $attachments : $this->prepare_attachments($files);

        // Validate and replace fields shortcodes
        $mail_to = $this->replace_content_shortcode( $this->validate_field($settings['email_to'.$slug], 'Recipient (to)'));
        $mail_subject = $this->replace_content_shortcode( $this->validate_field($settings['email_subject'.$slug], 'Subject'));
        $mail_name = $this->replace_content_shortcode( $this->validate_field($settings['email_from_name'.$slug], 'From Name'));
        $mail_from = $this->replace_content_shortcode( $this->validate_field($settings['email_from'.$slug], 'From'));
        $mail_reply = $this->replace_content_shortcode( $this->validate_field($settings['email_reply_to'.$slug], 'Reply To'));

        // Build the data
        $mail_data = [
            'to' => $mail_to,
            'subject' => $mail_subject,
            'message' => $content,
            'headers' => [
                'Content-Type: text/' . $settings['email_content_type'.$slug] . '; charset=UTF-8',
                'From: ' . $mail_name . ' <'.$mail_from.'>',
                'Reply-To: ' . $mail_reply,
            ],
            'attachments' => $attachments['files']
        ];

        // Build optional data
        if (!empty($settings['email_to_cc'.$slug])) {
            $mail_data['headers'][] = 'Cc: ' . $settings['email_to_cc'];
        }
        if (!empty($settings['email_to_bcc'.$slug])) {
            $mail_data['headers'][] = 'Bcc: ' . $settings['email_to_bcc'];
        }

        return [
            'email' => $mail_data,
            'attachments' => $attachments
        ];
    }
    protected function replace_content_shortcode(string $content, string $line_break = ''){

        // Set short vars for the data
        $fields = $this->settings['form_fields'];
        $form_data = $this->form_data;

        // [all-fieds] shortcode replacement
        if ( false !== strpos( $content, '[all-fields]' ) ) {
            $text = '';
            // Return formated text as key: value
            foreach ( $form_data['form_fields'] as $key => $field ) {
                $field_value = is_array($field) ? implode(', ', $field) : $field;
                $text .= !empty($field_value) ? sprintf('%s: %s', $key, $field_value) . $line_break : '';
            }
            $content = str_replace( '[all-fields]', $text, $content );
        }

        // Custom [field id="{id}"] shortcode replacement
        foreach ($fields as $field) {
            $shortcode = '[field id="' . $field['custom_id'] . '"]';
            $value = isset($form_data['form_fields'][$field['custom_id']]) ? $form_data['form_fields'][$field['custom_id']] : '';
            $value = is_array($value) ? implode(', ', $value) : $value;
            $content = str_replace($shortcode, $value, $content);
        }

        // Replaces all manual line breaks from content
        if(!empty($line_break)){
            $content = str_replace( array( "\r\n", "\r", "\n" ), $line_break, $content );
        }

        return $content;
    }
    protected function prepare_attachments(array $files) {
        $attachments = [];
        $errors = '';

        // Requires wp_handle_upload() file if unavailable
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        // Check if theres a valid file to upload
        foreach ($files['form_fields']['tmp_name'] as $input => $value) {
            if ($files['form_fields']['error'][$input] !== UPLOAD_ERR_NO_FILE) {
                $file = [
                    'name' => $files['form_fields']['name'][$input],
                    'type' => $files['form_fields']['type'][$input],
                    'tmp_name' => $files['form_fields']['tmp_name'][$input],
                    'error' => $files['form_fields']['error'][$input],
                    'size' => $files['form_fields']['size'][$input],
                ];

                // Handle the file upload
                $uploaded_file = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($uploaded_file['error'])) {
                    $attachments = $uploaded_file['file'];
                } else {
                    // Since throwing exceptions here will block the proper data flow, we return the error and let send_mail() handle it
                    $errors = esc_html__('Failed to upload file: ', 'uicore-elements') . $uploaded_file['error'];
                }

                // Break after processing the first valid file
                break;
            }
        }

        return [
            'files' => $attachments,
            'errors' => $errors
        ];
    }
    protected function compose_metadata(string $content, array $metadada, string $line_break){

        if (empty($metadada)) {
            return $content;
        }

        $content = $content . $line_break . $line_break . '--' . $line_break . $line_break; // Adds spacing between content and metadata

        foreach ($metadada as $meta) {
            switch($meta){
                case 'date':
                    $content .= sprintf( '%s: %s', 'Date', date('Y-m-d') . $line_break);
                    break;

                case 'time' :
                    $content .= sprintf( '%s: %s', 'Time', date('H:i:s') . $line_break);
                    break;

                case 'remote_ip':
                    $content .= sprintf( '%s: %s', 'IP', $_SERVER['REMOTE_ADDR'] . $line_break); // TODO: test if indeed working
                    break;

                case 'user_agent':
                    $content .= sprintf( '%s: %s', 'User Agent', $_SERVER['HTTP_USER_AGENT'] . $line_break);
                    break;

                case 'page_url':
                    $content .= sprintf( '%s: %s', 'Page URL', $_SERVER['HTTP_REFERER'] . $line_break);
                    break;
            }
        }

        return $content;
    }

    /**
     * Extra submition
     */
    protected function redirect() {
        $url = $this->validate_url($this->settings['redirect_to']);
        $url = $this->replace_content_shortcode($url);
        return [
            'status' => 'success',
            'url' => $url,
            'delay' => 1500,
            'message' => $this->get_response_message('redirect')
        ];
    }

    /**
     * Validations
     */
    protected function validate_recaptcha(string $token, string $version) {

        // Check if secret and site key are set
        if (!get_option('uicore_elements_recaptcha_secret_key') || !get_option('uicore_elements_recaptcha_site_key')) {
            return [
                'success' => false,
                'message' => esc_html__('reCAPTCHA API keys are not set.', 'uicore-elements')
            ];
        }

        $data = [
            'secret' => get_option('uicore_elements_recaptcha_secret_key'),
            'response' => sanitize_text_field($token)
        ];

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($verify);

        $captcha = json_decode($res);

        if($version === 'V3') {
            return ['success' => ($captcha->success && $captcha->score >= 0.5) ? true : false];
        }

        // V2 default
        return ['success' => $captcha->success];

    }

    protected function validate_spam() {
        // `ui-e-h-p` is the key for the honeypot
        return ( isset($this->form_data['ui-e-h-p']) && !empty($this->form_data['ui-e-h-p']) ) ? false : true;
    }
    protected function validate_url(string $url) {
        if (!$url) {
            throw new Redirect_Exception(esc_html__('No redirect URL set.', 'uicore-elements'));
        }
        return $url;
    }
    protected function validate_field(string $field, string $label) {
        if (empty($field)) {
            throw new Submit_Exception(esc_html__('The field "' . $label . '" is empty.', 'uicore-elements'));
        }
        return $field;
    }

    /**
     * Responses
     */
    // Also used by form widget(s), therefore public and static
    public static function get_default_messages(){
        return [
			'success' => esc_html__( 'Your submission was successful.', 'uicore-elements' ),
			'error' => esc_html__( 'Your submission failed because of an error.', 'uicore-elements' ),
            'redirect' => esc_html__( 'Redirecting...', 'uicore-elements' ),
		];
    }
    protected function get_response_message($status){
        // non-customizable messages
        $default_messages = [
            'invalid_status' => esc_html__( 'Invalid status message.', 'uicore-elements' ),
        ];

        if($this->settings['custom_messages'] === 'yes') {
            $messages = [
                'success' => $this->settings['success_message'],
                'error' => $this->settings['error_message'],
                'redirect' => $this->settings['redirect_message'],
            ];
        } else {
            $messages = self::get_default_messages();
        }

        $messages = array_merge($default_messages, $messages);

        return isset($messages[$status]) ? $messages[$status] : $messages['invalid_status'];
    }
}