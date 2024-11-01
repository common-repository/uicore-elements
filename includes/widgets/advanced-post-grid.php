<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Pagination_Trait;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Grid_Trait;
use uicoreElements\Utils\Post_Trait;
use uiCoreElements\Utils\Post_Filters_Trait;

defined('ABSPATH') || exit();

/**
 * Scripts and Styles Class
 *
 */
class AdvancedPostGrid extends UiCoreWidget
{
    use Pagination_Trait,
        Animation_Trait,
        Grid_Trait,
        Post_Filters_Trait,
        Post_Trait;

    private $_query;

    public function get_name()
    {
        return 'uicore-advanced-post-grid';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_title()
    {
        return __('Advanced Post Grid', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-gallery-grid ui-e-widget';
    }
    public function get_keywords()
    {
        return ['post', 'grid', 'blog', 'recent', 'news'];
    }
    public function get_styles()
    {
        $styles = [
            'advanced-post-grid',
            'animation', // hover animations
            'entrance', // entrance basic style
        ];
        if(!class_exists('\UiCore\Core') && !class_exists('\UiCoreAnimate\Base')){
            $styles['e-animations'] = [ // entrance animations
                'external' => true,
            ];
        }
        return $styles;
    }
    public function get_scripts()
    {
        return [
            'advanced-post-grid',
            'entrance' => [
                'condition' => [
                    'animate_items' => 'ui-e-grid-animate'
                ],
            ],
            'ajax-request' => [
                'condition' => [
                    'pagination_type' => 'load_more'
                ]
            ]
        ];
    }

    private function filter_missing_taxonomies($settings)
    {
        $taxonomy_filter_args = [
            'show_in_nav_menus' => true,
        ];
        if (!empty($settings['posts-filter_post_type'])) {
            $taxonomy_filter_args['object_type'] = [$settings['posts-filter_post_type']];
        }

        $taxonomies = get_taxonomies($taxonomy_filter_args, 'objects');

        foreach ($taxonomies as $taxonomy => $object) {
            $controll_id = 'posts-filter_' . $taxonomy . '_ids';
            \error_log($controll_id);
            \error_log(print_r($settings[$controll_id], true));

            if (!isset($settings[$controll_id]) || empty($settings[$controll_id])) {
                continue;
            }

            //if is set check if this id still exists
            $settings[$controll_id] = array_filter($settings[$controll_id], function ($term_id) use ($object) {
                return term_exists($term_id, $object->name);
            });

            if (empty($settings[$controll_id])) {
                $settings[$controll_id] = null;
            }
        }

        return $settings;
    }
    public function on_import($element)
    {
        $element['settings'] = $this->filter_missing_taxonomies($element['settings']);
        return $element;
    }
    function get_query()
    {
        return $this->_query;
    }

    protected function register_controls()
    {
        // Query(curent/custom/related/manual)

        // /Pagination (filtering?)
        // /Aditional (no posts)

        // Contents and Settings
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Grid', 'uicore-elements'),
            ]
        );
            $this->TRAIT_register_grid_layout_controls();
        $this->end_controls_section();

        $this->TRAIT_register_post_item_controls();
        $this->TRAIT_register_post_button_controls();
        $this->TRAIT_register_post_meta_controls();
        $this->TRAIT_register_post_query_controls();
        $this->TRAIT_register_filter_controls();
        $this->TRAIT_register_pagination_controls();

        // Styles
        $this->TRAIT_register_post_item_style_controls();
        $this->TRAIT_register_post_content_style_controls();
        $this->TRAIT_register_post_button_style_controls();
        $this->TRAIT_register_post_meta_style_controls();
        $this->TRAIT_register_filter_style_controls();
        $this->TRAIT_register_pagination_style_controls();

        $this->start_controls_section(
            'section_style_animations',
            [
                'label' => __('Animations', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
            $this->TRAIT_register_entrance_animations_controls();
            $this->TRAIT_register_hover_animation_control(
                'Item Hover Animation',
                [],
                ['underline'],
                'anim_item'
            );
            $this->TRAIT_register_post_animation_controls();
        $this->end_controls_section();

        // Update masonry from component to a legacy version
        $this->update_control('masonry', [
            'label' => __( 'Masonry', 'uicore-elements' ),
            'type' => Controls_Manager::SWITCHER,
            'frontend_available' => true,
            'default'  => 'no',
            'return_value' => 'ui-e-maso',
            // reset values from component
            'prefix_class' => '',
            'condition' => [],
            'selectors' => [],
        ]);
    }

    function content_template()
    {
    }

    /**
     * Renders the widget content using AJAX.
     *
     * This method retrieves the query arguments and widget settings, sets up the query, and renders each post item.
     * If no posts are found, it returns false. After rendering, it resets the query and returns the output.
     *
     * @return string|false The rendered widget content or false if no posts are found.
     */
    public function render_ajax()
    {
        // Get query args and widget settings
        global $wp_query;
        $default_query = $wp_query;
        $settings = $this->get_settings();
        $this->TRAIT_query_posts($settings);
        $wp_query = $this->get_query();

        //return false if no post found
        if (!$wp_query->have_posts()) {
            return false;
        }

        \ob_start();
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $this->TRAIT_render_item(false, true);
        }
        wp_reset_query();
        $wp_query = $default_query;
        return \ob_get_clean();
    }

    protected function render()
    {

        // Get query args and widget settings
        global  $wp_query;
        $default_query = $wp_query;
        $settings = $this->get_settings();
        $this->TRAIT_query_posts($settings);
        $wp_query = $this->get_query();

        // Store widget settings in a transient
        $ID = strval($this->get_ID());
        set_transient('ui_elements_widgetdata_' . $ID, $settings, \MONTH_IN_SECONDS);

        // Get the quantity of items and creates a loop control
        $items = $settings['item_limit']['size'];
        $loops = 0;

        $this->TRAIT_render_filters();

        // No posts found
        if (!$wp_query->have_posts()) {
            echo '<p style="text-align:center">' . __('No posts found.', 'uicore-elements') . '</p>';
        } else {

        ?>
            <div class="ui-e-adv-grid">
                <?php
                while ($wp_query->have_posts()) {

                    if ($settings['sticky'] && $items == $loops) {
                        break; // sticky posts disregards posts per page, so ending the loop if $items == $loop forces the query respects the users item limit
                    }

                    $wp_query->the_post();
                    $this->TRAIT_render_item(false, true);

                    $loops++;
                }
                ?>
            </div>
        <?php
            $this->TRAIT_render_pagination();
        }

        //reset query
        wp_reset_query();
        $wp_query = $default_query;
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new AdvancedPostGrid());
