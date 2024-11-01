<?php
namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

defined('ABSPATH') || exit();

trait Grid_Trait {

    /**
     * Registers the grid layout controls.
     *
     * @param bool $maso Whether to enable masonry layout. (Default: true)
     * @param array $maso_conditions The conditions for masonry layout.
     * @param bool $legacy_maso Whether to use legacy masonry layout.
     * @param bool/array $column_prefix Set a prefix class to be returned with the column value
     * @param array $gap_conditions The conditions for columns.
     */
    function TRAIT_register_grid_layout_controls()
    {
        $this->add_responsive_control(
            'columns',
            [
                'label'           => __( 'Columns', 'uicore-elements' ),
                'type'            => Controls_Manager::SELECT,
                'desktop_default' => 3,
                'tablet_default'  => 2,
                'mobile_default'  => 1,
                'options'         => [
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5',
                    6 => '6',
                    7 => '7',
                    8 => '8',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); --ui-e-column-count: {{VALUE}};', // the var is used by masonry
                    '{{WRAPPER}} .ui-e-adv-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr))', // Old APG Suport
                ],
            ]
        );
        $this->add_responsive_control(
            'gap',
            [
                'label'     => esc_html__( 'Items Gap', 'uicore-elements' ),
                'type'      => Controls_Manager::SLIDER,
                'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-grid' => 'grid-gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .ui-e-adv-grid' => 'grid-gap: {{SIZE}}{{UNIT}};', // Old APG Suport
                ],
            ]
        );
        $this->add_control(
            'masonry',
            [
                'label'   => __('Masonry', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'prefix_class' => 'ui-e-maso-',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-grid' => 'column-count: var(--ui-e-column-count); column-gap: {{gap.SIZE}}{{gap.UNIT}};',
                    '{{WRAPPER}} .ui-e-wrp' => 'margin-bottom: {{gap.SIZE}}{{gap.UNIT}};'
                ],
                //'render_type' => 'template',
            ]
        );
    }
}