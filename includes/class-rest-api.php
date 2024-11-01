<?php
namespace UiCoreElements;
use \Elementor\Plugin;
use UiCoreElements\Helper;
use UiCoreElements\Utils\Contact_Form_Service;

/**
 * REST_API Handler
 */
class REST_API {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(){
        register_rest_route('uielem/v1', '/load_more/', [
            'methods' => 'GET',
            'show_in_index' => true,
            'callback' => [$this, 'settings_update'],
            'permission_callback' => '__return_true',
            'args' => [
                'widget_id' => [
                    'required' => true,
                ],
                'widget_type' => [
                    'required' => true,
                ],
                'page' => [
                    'required' => false,
                    'default' => 1,
                ],
                'type' => [
                    'required' => false,
                    'default' => '',
                ],
                'term' => [
                    'required' => false,
                    'default' => '',
                ],
            ],
        ]);
        register_rest_route('uielem/v1', '/submit_actions/', [
            'methods' => 'POST',
            'callback' => [$this, 'process_submission'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function check_for_permission()
    {
        return current_user_can('manage_options');
    }

    public function settings_update(\WP_REST_Request $request) {
        // Identify the widget
        $widget_id   = $request->get_param('widget_id');
        $widget_type = $request->get_param('widget_type');

        // Get widget settings
        $settings = get_transient('ui_elements_widgetdata_'.$widget_id);

        $settings['__current_page'] = $request->get_param('page');

        $tax =  'posts-filter_' . $request->get_param('type') . '_ids';
        $settings[$tax] = $request->get_param('term');

        // Create the element data
        $widget = [
            'elType'     => 'widget',
            'widgetType' => $widget_type,
        ];

        // Generate a new instance of the widget with those settings and return the markup
        //$widget = new AdvancedPostGrid($widget, $settings); todo: discover why this method don't work
        $widget = Plugin::instance()->elements_manager->create_element_instance($widget, $settings);
        $widget->set_settings($settings);

        $markup = $widget->render_ajax();

        return [
            'markup' => $markup,
            'total_pages' => $widget->get_query()->max_num_pages,
        ];
    }

    public function process_submission(\WP_REST_Request $request) {

        // Get referer origin, form and widget data
        $form_data = $request->get_params();
        $files = $request->get_file_params();
        $settings = Helper::get_widget_settings($form_data['page_id'], $form_data['widget_id']);

        // Request the contact form service and return the response
        $service = new Contact_Form_Service($form_data, $settings, $files);
        $response = $service->handle();
        return $response;
    }
}