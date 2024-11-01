<?php
namespace UiCoreElements\Controls;

use Elementor\Control_Select2;
defined('ABSPATH') || exit();

class Query extends Control_Select2
{
    const CONTROL_ID = 'query';

    public function get_type()
    {
        return self::CONTROL_ID;
    }

    public static function get_query_args($control_id, $settings, $current_id = null)
    {

        $defaults = [
            $control_id . '_post_type' => 'post',
            $control_id . '_posts_ids' => [],
            'orderby' => 'date',
            'order' => 'desc',
        ];

        $settings = wp_parse_args($settings, $defaults);

        $post_type = $settings[$control_id . '_post_type'];
        if($post_type == 'current'){

            $query_type = get_post_meta( $current_id, 'tb_rule_include', true );
            $query_type = isset($query_type[0]['rule']['value']) ? $query_type[0]['rule']['value'] : '';
            switch ( $query_type ) {
                case 'special-blog':
                    $post_type = 'post';
                    break;
                default:
                if( ($type = substr( $query_type, 0, 11 )) === "cp-archive-" ){
                    $post_type = str_replace($type,'',$query_type);
                    break;
                }
                $post_type = 'post';
                break;
            }
        }

        if(!isset($settings['__current_page'])){
            if (get_query_var('paged')) {
                $paged = get_query_var('paged');
            } elseif (get_query_var('page')) {
                $paged = get_query_var('page');
            } else {
                $paged = 1;
            }
        } else {
            $paged = $settings['__current_page'];
        }

        $query_args = [
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'post_status' => 'publish', // Hide drafts/private posts for admins
            'paged' => $paged,
            'ignore_sticky_posts' => true,
            'posts_per_page' => isset( $settings['item_limit'] ) ? $settings['item_limit']['size'] : get_option('posts_per_page'),
        ];

        // If the offset is set, we need to set the offset and paged parameters
        if(isset($settings['offset']) && !empty($settings['offset']['size'])){
            $query_args['offset'] = $settings['offset']['size'];
        }

        if( isset( $settings['sticky'] ) && $settings['sticky']){
            $query_args['ignore_sticky_posts'] = false;
        }

        // If filters are enabled, checks for url taxonomy params. Eg: since not every post widget has filters, so we also need to check if is set.
        if( isset($settings['post_filtering']) && $settings['post_filtering'] && isset($_GET['tax']) && isset($_GET['term'])){

            $query_args['tax_query'][] = [
                'taxonomy' => $_GET['tax'],
                'field' => 'term_id',
                'terms' => $_GET['term'],
            ];

        } else {
            $query_args['post_type'] = $post_type;
            $query_args['tax_query'] = [];

            $taxonomies = get_object_taxonomies($post_type, 'objects');

            foreach ($taxonomies as $object) {
                $setting_key = $control_id . '_' . $object->name . '_ids';

                if (!empty($settings[$setting_key])) {
                    $terms_list = $settings[$setting_key];
                    $query_args['tax_query'][] = [
                        'taxonomy' => $object->name,
                        'field' => 'term_id',
                        'terms' => $terms_list,
                    ];
                }
            }
        }

        //Enable for data analysis
        // error_log( __FILE__ . '@' . __LINE__ );
        // error_log( __METHOD__);
        // \error_log(print_r($query_args, true));
        // \error_log("-----------------");

        return $query_args;
    }

}

\Elementor\Plugin::$instance->controls_manager->register_control('query', new Query());
