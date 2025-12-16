<?php

/**
 * Detta block kräver DATATABLES externa bibliotek
 *
 * Nedan script måste köras, t ex i denna fil
 * \wp_enqueue_style('datatables-style', '//cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css', [], HAJ_VERSION, 'all');
 * \wp_enqueue_script('datatables-script', '//cdn.datatables.net/2.0.3/js/dataTables.min.js', ['jquery'], HAJ_VERSION);
 *
 * Available data in this file:
 * $attributes - array of attributes for the block
 * $content - content of the block
 */

use function Demo\Includes\Functions\get_post_type_items;


// Enqueue DataTables scripts and styles directly when block is rendered
if (!wp_style_is('datatables-style', 'enqueued')) {
    wp_enqueue_style(
        'datatables-style',
        'https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css',
        [],
        '1.0'
    );
}
if (!wp_script_is('datatables-script', 'enqueued')) {
    wp_enqueue_script(
        'datatables-script',
        'https://cdn.datatables.net/2.0.3/js/dataTables.min.js',
        ['jquery'],
        '1.0',
        array(
            'strategy'  => 'defer',
            'in_footer' => true,
        )
    );
}

$sourceType = isset($attributes['sourceType']) ? $attributes['sourceType'] : false;
$postType = isset($attributes['postType']) ? $attributes['postType'] : 'post';
$postsPerPage = isset($attributes['postTypeLimit']) ? $attributes['postTypeLimit'] : 3;
$postTypeTaxonomies = isset($attributes['postTypeTaxonomies']) ? $attributes['postTypeTaxonomies'] : false;
$postTypeIds = isset($attributes['postTypeIds']) ? $attributes['postTypeIds'] : false;
$isFilter = isset($attributes['isFilter']) ? $attributes['isFilter'] : false;
$isTwoColumns = isset($attributes['isTwoColumns']) ? $attributes['isTwoColumns'] : false;
$active_taxonomy = isset($_GET['filter']) ? $_GET['filter'] : null;
$selectedTaxonomy = isset($attributes['selectedTaxonomy']) ? $attributes['selectedTaxonomy'] : false;
$selectedTaxonomyItems = isset($attributes['selectedTaxonomyItems']) ? $attributes['selectedTaxonomyItems'] : false;
$taxonomyFilterMethod = isset($attributes['taxonomyFilterMethod']) ? $attributes['taxonomyFilterMethod'] : 'exclude';
if ($isFilter) {
    $taxonomyToFilter = isset($attributes['taxonomyToFilter']) ? $attributes['taxonomyToFilter'] : false;
    $taxonomies = get_terms($taxonomyToFilter);
}
if ($sourceType == 'specific') {
    $posts = get_post_type_items($postType, '-1', false, [], $postTypeIds);
} else if ($sourceType == 'taxonomy') {
    $posts = get_post_type_items($postType, $postsPerPage, $postTypeTaxonomies);
}
if ($taxonomyFilterMethod === 'all') {
    $wp_query_args = [
        'post_type' => $postType,
        'post_status' => 'publish',
        'offset' => '1',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',

    ];
} else {
    $wp_query_args = [
        'post_type' => $postType,
        'post_status' => 'publish',
        'offset' => '1',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'tax_query' => [
            [
                'taxonomy' => $selectedTaxonomy,
                'field' => 'slug',
                'terms' => $selectedTaxonomyItems,
                'operator' => $taxonomyFilterMethod === 'include' ? 'IN' : 'NOT IN',
            ],
        ],
    ];
}

if (!function_exists('get_accordion_content')) {
    function get_accordion_content($post, $attributes, $index) {
?>
        <div data-accordion-target="<?= $attributes['blockId'] . '-' . $index; ?>" class="wp-block-accordion__item">
            <h3 class="wp-block-accordion__item-title">
                <button id="button-<?= $index; ?>">
                    <?= $post['title']; ?>
                    <svg id="b" xmlns="http://www.w3.org/2000/svg" class="haj-accordion-icon" viewBox="0 0 20 20" width="20px" height="20px" fill="none">
                        <g id="c">
                            <g id="d">
                                <path class="e" d="m15,2c7.17,0,13,5.83,13,13s-5.83,13-13,13S2,22.17,2,15,7.83,2,15,2m0-2C6.72,0,0,6.72,0,15s6.72,15,15,15,15-6.72,15-15S23.28,0,15,0h0Z" stroke-width="0px" />
                                <line class="f" x1="10" y1="0" x2="10" y2="20" fill="none" stroke="#000" stroke-linecap="round" stroke-width="2px" />
                                <line class="g" x1="20" y1="10" x2="0" y2="10" fill="none" stroke="#000" stroke-linecap="round" stroke-width="2px" />
                            </g>
                        </g>
                    </svg>
                </button>
            </h3>
            <div id="<?= 'target-' . $attributes['blockId'] . '-' . $index; ?>" class="wp-block-accordion__item-content is-w-full">
                <div class="accordion-content-wrapper">
                    <?= $post['content']; ?>
                </div>
            </div>
        </div>
<?php
    }
}


?>
<div data-block-id="<?= $attributes['blockId']; ?>" class="<?= $attributes['mainClassName']; ?>" data-taxonomy="<?= $active_taxonomy; ?>">
    <?php if ($isFilter && $taxonomyToFilter ?? !empty($taxonomies)) { ?>
        <div class="accordion-filter-buttons">
            <?php foreach ($taxonomies as $index => $taxonomy) { ?>
                <button class="<?= ($active_taxonomy && $active_taxonomy == $taxonomy->slug) || (!$active_taxonomy && $index === 0) ? 'is-active' : ''; ?>" data-filter="<?= $taxonomy->slug ?>"><?= $taxonomy->name; ?></button>
            <?php } ?>
        </div>
        <div class="wp-block-accordion_container">
            <table id="accordion-posts-<?= $attributes['blockId']; ?>" class="display dataTablePosts" <?= $isTwoColumns ? 'data-column="0"' : ''; ?>>
                <thead>
                    <tr>
                        <th>Post Content</th>
                        <th>Post Taxonomies</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $posts_query = new WP_Query($wp_query_args);
                    while ($posts_query->have_posts()) {
                        $posts_query->the_post();
                        $post = [
                            'id' => get_the_ID(),
                            'title' => get_the_title(),
                            'content' => get_the_content(),
                            'taxonomies' => get_the_terms(get_the_ID(), $taxonomyToFilter),
                        ];
                    ?>
                        <tr>
                            <td>
                                <?php get_accordion_content($post, $attributes, $post['id']); ?>
                            </td>
                            <td class="hidden td-filter">
                                <?php if (is_array($post['taxonomies'])) {
                                    foreach ($post['taxonomies'] as $taxonomy) {
                                ?>
                                        <span><?= $taxonomy->slug; ?></span>
                                <?php
                                    }
                                } ?>
                            </td>
                        </tr>
                    <?php
                    } ?>
                </tbody>
            </table>
            <?php if ($isTwoColumns) { ?>
                <table id="accordion-posts-<?= $attributes['blockId']; ?>" class="display dataTablePosts" <?= $isTwoColumns ? 'data-column="1"' : ''; ?>>
                    <thead>
                        <tr>
                            <th>Post Content</th>
                            <th>Post Taxonomies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $posts_query = new WP_Query($wp_query_args);
                        while ($posts_query->have_posts()) {
                            $posts_query->the_post();
                            $post = [
                                'id' => get_the_ID(),
                                'title' => get_the_title(),
                                'content' => get_the_content(),
                                'taxonomies' => get_the_terms(get_the_ID(), $taxonomyToFilter),
                            ];
                        ?>
                            <tr>
                                <td>
                                    <?php get_accordion_content($post, $attributes, $post['id']); ?>
                                </td>
                                <td class="hidden td-filter">
                                    <?php if (is_array($post['taxonomies'])) {
                                        foreach ($post['taxonomies'] as $taxonomy) {
                                    ?>
                                            <span><?= $taxonomy->slug; ?></span>
                                    <?php
                                        }
                                    } ?>
                                </td>
                            </tr>
                        <?php
                        } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="wp-block-accordion">
            <div class="wp-block-accordion_container">
                <?php
                $posts_query = new WP_Query($wp_query_args);
                while ($posts_query->have_posts()) {
                    $posts_query->the_post();
                    $post = [
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'content' => get_the_content(),
                    ];
                    get_accordion_content($post, $attributes, $post['id']);
                } ?>
            </div>
        </div>
    <?php } ?>
</div>
