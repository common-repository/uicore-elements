<?php
namespace UiCoreElements\Utils;
use Elementor\Controls_Manager;
use UiCoreElements\Helper;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Post Filter Component
 */

trait Post_Filters_Trait {

    function TRAIT_register_filter_controls($section = true)
    {
        if($section) {
            $this->start_controls_section(
                'section_filters',
                [
                    'label' => esc_html__('Filters', 'uicore-elements'),
                ]
            );
        }

            $this->add_control(
                'post_filtering',
                [
                    'label' => __( 'Filters', 'uicore-elements' ),
                    'type' => Controls_Manager::SWITCHER,
                    'default'=> 'no',
                    'render_type' => 'template',
                ]
            );
            $this->add_control(
                'filters_taxonomies',
                [
                    'label' => __( 'Taxonomies', 'uicore-elements' ),
                    'type' => Controls_Manager::SELECT2,
                    'multiple' => false,
                    'label_block' => true,
                    'options' => Helper::get_taxonomies(),
                    'default' => 'category',
                    'condition' => [
                        'post_filtering' => 'yes',
                    ],
                ]
            );
            $this->add_control(
                'custom_meta',
                [
                    'label' => esc_html__( 'Meta Slug', 'uicore-elements' ),
                    'type' => Controls_Manager::TEXT,
                    'condition' => [
                        'filters_taxonomies' => 'custom',
                    ],
                ]
            );

        if($section){
            $this->end_controls_section();
        }
    }

    function TRAIT_register_filter_style_controls()
    {
        $this->start_controls_section(
            'section_filter_style',
            [
                'label' => esc_html__('Filter Style', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'post_filtering' => 'yes',
                ),
            ]
        );
            $this->add_responsive_control(
                'filters_align',
                [
                    'label' => esc_html__( 'Alignment', 'uicore-elements' ),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'start'    => [
                            'title' => esc_html__( 'Left', 'uicore-elements' ),
                            'icon' => 'eicon-h-align-left',
                        ],
                        'center' => [
                            'title' => esc_html__( 'Center', 'uicore-elements' ),
                            'icon' => 'eicon-h-align-center',
                        ],
                        'end' => [
                            'title' => esc_html__( 'Right', 'uicore-elements' ),
                            'icon' => 'eicon-h-align-right',
                        ],
                    ],
                    'default' => 'center',
                    'selectors' => [
                        '(desktop){{WRAPPER}} .ui-e-filters' => 'justify-content: {{VALUE}}',
                        '(mobile){{WRAPPER}} .ui-e-filters' => 'align-items: {{VALUE}}' // mobile version sets flex-direction as column
                    ]
                ]
            );
            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'filters_typography',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                    ],
                    'selector' => '{{WRAPPER}} .ui-e-filter-item',
                ]
            );
            $this->start_controls_tabs('tabs_filters_style');

                $this->start_controls_tab(
                    'tab_filters_normal',
                    [
                        'label' => esc_html__( 'Normal', 'uicore-elements' ),
                    ]
                );
                    $this->add_control(
                        'filters_text_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-elements' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .ui-e-filter-item' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'filters_background',
                            'types' => [ 'classic' ],
                            'exclude' => [ 'image' ],
                            'selector' => '{{WRAPPER}} .ui-e-filter-item',
                            'fields_options' => [
                                'background' => [
                                    'default' => 'classic',
                                ],
                                'color' => [
                                    'global' => [
                                        'default' => Global_Colors::COLOR_ACCENT,
                                    ],
                                ],
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'filters_border',
                            'selector' => '{{WRAPPER}} .ui-e-filter-item',
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'filters_box_shadow',
                            'selector' => '{{WRAPPER}} .ui-e-filter-item',
                        ]
                    );
                $this->end_controls_tab();

                $this->start_controls_tab(
                    'tab_filters_hover',
                    [
                        'label' => esc_html__( 'Hover', 'uicore-elements' ),
                    ]
                );
                    $this->add_control(
                        'filters_hover_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-elements' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .ui-e-filter-item:hover' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'filters_background_hover',
                            'types' => [ 'classic', 'gradient' ],
                            'exclude' => [ 'image' ],
                            'selector' => '{{WRAPPER}} .ui-e-filter-item:hover',
                            'fields_options' => [
                                'background' => [
                                    'default' => 'classic',
                                ],
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'hover_filters_border',
                            'selector' => '{{WRAPPER}} .ui-e-filter-item:hover',
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'filters_hover_box_shadow',
                            'selector' => '{{WRAPPER}} .ui-e-filter-item:hover',
                        ]
                    );
                $this->end_controls_tab();

                $this->start_controls_tab(
                    'tab_filters_active',
                    [
                        'label' => esc_html__( 'Active', 'uicore-elements' ),
                    ]
                );
                    $this->add_control(
                        'filters_active_color',
                        [
                            'label' => esc_html__( 'Text Color', 'uicore-elements' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .ui-e-filter-item.ui-e-active' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'filters_background_active',
                            'types' => [ 'classic', 'gradient' ],
                            'exclude' => [ 'image' ],
                            'selector' => '{{WRAPPER}} .ui-e-active',
                            'fields_options' => [
                                'background' => [
                                    'default' => 'classic',
                                ],
                            ],
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'active_filters_border',
                            'selector' => '{{WRAPPER}} .ui-e-active',
                        ]
                    );
                    $this->add_group_control(
                        Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'filters_active_box_shadow',
                            'selector' => '{{WRAPPER}} .ui-e-active',
                        ]
                    );
                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_responsive_control(
                'filters_border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'uicore-elements' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                    'separator' => 'before',
                    'selectors' => [
                        '{{WRAPPER}} .ui-e-filter-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            $this->add_responsive_control(
                'filters_padding',
                [
                    'label' => esc_html__( 'Padding', 'uicore-elements' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                    'selectors' => [
                        '{{WRAPPER}} .ui-e-filter-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        $this->end_controls_section();
    }

    function TRAIT_render_filters()
    {
        $settings  = $this->get_settings();
        $taxonomy  = $settings['filters_taxonomies'] == 'custom' ? $this->get_settings('custom_meta') : $this->get_settings('filters_taxonomies');
        $ajax      = isset($settings['pagination_type']) && $settings['pagination_type'] == 'load_more';
        $post_type = $settings['posts-filter_post_type'];

        // Return if filters are disabled or if there's no taxonomies
        if ($settings['post_filtering'] !== 'yes' || !$taxonomy) {
            return;
        }

        // Check if a tax query is set in the current WP_Query
        $active_terms = [];
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        foreach ($taxonomies as $object) {
            $setting_key = 'posts-filter_' . $object->name . '_ids';
            if (!empty($settings[$setting_key])) {
                $active_terms = $settings[$setting_key];
            }
        }

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'include' => $active_terms
        ]);

        // If is not ajax, clear button needs an URL
        if(!$ajax){
            // In current query we work with standart archive pages
            if(is_main_query()){
                $archive = get_post_type_archive_link( get_post_type());
                // If no post is found, $archive will return false, so we get the archive url throught queried object method
                if(!$archive){
                    $term    = get_taxonomy( get_queried_object()->taxonomy)->object_type;
                    $archive = get_post_type_archive_link($term[0]);
                }
            // On other queries we keep at the same page
            } else {
                global $wp;
                $archive = home_url( $wp->request );
            }
        }
        ?>
        <nav class="ui-e-filters" aria-label="<?php echo esc_html($taxonomy);?>">

            <?php if(!$ajax) : ?>
                <a href="<?php echo esc_url($archive); ?>">
                    <button class="ui-e-filter-item" data-ui-e-action="clear"> <?php _e('All', 'uicore-elements'); ?> </button>
                </a>
            <?php else: ?>
                    <button class="ui-e-filter-item" data-ui-e-action="clear"> <?php _e('All', 'uicore-elements'); ?> </button>
            <?php endif; ?>

            <?php foreach ($terms as $term) :
                // work in progress
                $active_class = '';
                //
                if(is_archive() && !is_post_type_archive() && is_main_query()){
                    $current = get_queried_object();
                    if($term->term_id == $current->term_id){
                        $active_class = 'ui-e-active';
                    }
                } else {
                    $active_class = !$ajax && isset($_GET['term']) && $term->term_id == $_GET['term'] ? 'ui-e-active' : '';
                }
                ?>

                <?php if(!$ajax) :
                    // current query works with standart term links, other queries uses url params
                    $term_url = is_main_query() ? get_term_link($term->term_id) : $archive . '?tax=' . $term->taxonomy . '&term=' . $term->term_id;
                    ?>
                    <a href="<?php echo esc_url($term_url); ?>">
                <?php endif; ?>

                    <button
                        class="ui-e-filter-item <?php echo esc_attr($active_class);?>"
                        data-ui-e-action="filter",
                        data-ui-e-term="<?php echo esc_attr($term->term_id); ?>"
                        data-ui-e-taxonomy="<?php echo esc_attr($term->taxonomy);?>"
                        >
                        <?php echo esc_html($term->name); ?>
                    </button>

                <?php if(!$ajax) : ?>
                    </a>
                <?php endif; ?>

            <?php endforeach; ?>

        </nav>
        <?php
    }
}
