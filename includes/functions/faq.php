<?php

namespace Akka\Includes\Functions;

// Register Custom Post Type
add_action('init', __NAMESPACE__ . '\register_question_post_type');
function register_question_post_type() {
    register_post_type('question', [
        'labels' => [
            'name' => __('Questions', 'tiburon'),
            'singular_name' => __('Question', 'tiburon'),
            'menu_name' => __('FAQ', 'tiburon'),
            'name_admin_bar' => __('FAQ', 'tiburon'),
        ],
        'public' => true,
        'has_archive' => false,
        'publicly_queryable' => false,
        'show_in_rest' => true, 
        'supports' => ['title', 'editor'],
        'rewrite' => false,
        'menu_icon' => 'dashicons-format-chat',
    ]);
}

// Register Taxonomy 'Topic'
add_action('init', __NAMESPACE__ . '\register_topic_taxonomy');
function register_topic_taxonomy() {
    register_taxonomy('topic', 'question', [
        'labels' => [
            'name' => 'Topics',
            'singular_name' => 'Topic',
        ],
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        ''
    ]);
}