<?php

namespace Genero\Sage\Timber\Integrations;

use Genero\Sage\Timber\Timber;
use TimberExtended;

class DisplayPosts
{
    /** @var array */
    protected $config;

    public function __construct($config = [])
    {
        add_filter('shortcode_atts_display-posts', [$this, 'setDefaultShortcodeAttributes'], 10, 3);
        add_filter('display_posts_shortcode_output', [$this, 'shortcodePostOutput'], 10, 9);
        add_filter('display_posts_shortcode_post_class', [$this, 'shortcodePostClass'], 10, 4);

        $this->config = array_replace_recursive([
            'attributes' => [
                'teaser' => false,
                'id' => false,
                'post_type' => 'post',
                'wrapper_class' => 'display-posts-listing',
                'grid' => true,
            ],
        ], $config);
    }

    /**
     * Set the grid cell class if a grid is rendered.
     *
     * @return array
     */
    public function shortcodePostClass($class, $post, $listing, $atts)
    {
        $atts = $this->getAttributes($atts);

        // Only use cell classes if teasers are rendered in a grid.
        if ($atts['teaser'] && $atts['grid']) {
            $class = apply_filters('sage/timber/class/post_cell', $class, $post);
        }
        return $class;
    }

    /**
     * Change the output of an individual post object.
     *
     * @return string
     */
    public function shortcodePostOutput($output, $atts, $image, $title, $date, $excerpt, $inner_wrapper, $content, $class)
    {
        $atts = $this->getAttributes($atts);

        // Only act if teasers are rendered.
        if ($atts['teaser']) {
            $output = $this->renderTeaser(get_the_id());

            return sprintf('<%s class="%s">%s</%s>', $inner_wrapper, implode(' ', $class), $output, $inner_wrapper);
        }

        return $output;
    }

    /**
     * Set default shortcode attributes contextually.
     *
     * @param array $out
     * @param array $pairs
     * @param array $atts
     * @return array
     */
    public function setDefaultShortcodeAttributes($out, $pairs, $original_atts)
    {
        $atts = $this->getAttributes($original_atts);

        // Only act if teasers are rendered.
        if ($atts['teaser']) {
            // Default to `div` when teasers are rendered.
            if (!isset($atts['wrapper'])) {
                $out['wrapper'] = 'div';
            }

            // Use post grid classes
            if ($atts['grid']) {
                $out['wrapper_class'] = apply_filters('sage/timber/class/post_grid', explode(' ', $out['wrapper_class']), $atts['post_type']);
                $out['wrapper_class'] = implode(' ', $out['wrapper_class']);
            }
       }

        return $out;
    }

    /**
     * Return a rendered teaser as a string
     *
     * @param int $post_id
     * @return string
     */
    protected function renderTeaser($post_id)
    {
        $post = TimberExtended::object_getter('post', $post_id);
        $templates = ["teasers/teaser--{$post->post_type}.twig", 'teasers/teaser.twig'];
        $context['post'] = $post;

        return \Timber::fetch($templates, $context);
    }

    /**
     * Get the shortcode attributes while taking into account custom additions.
     *
     * @param array $original
     * @return array
     */
    protected function getAttributes($original)
    {
        $attributes = $original + $this->config['attributes'];
        foreach ($original as $name => $default) {
            if (is_numeric($name) && in_array($default, array_keys($this->config['attributes']))) {
                $attributes[$default] = $default;
            }
        }
        return $attributes;
    }
}
