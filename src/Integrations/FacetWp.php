<?php

namespace Genero\Sage\Timber\Integrations;

class FacetWp
{
    public function __construct()
    {
        add_filter('facetwp_pager_html', [$this, 'pager'], 9, 2);
    }

    /**
     * Render a FacetWP pager using Timber templates if available.
     *
     * @param string $output
     * @param array $params
     * @return string
     */
    public function pager($output, $params)
    {
        if ($template = Timber::fetch(['facets/pager.twig'], $params)) {
            $output = $template;
        }

        return $output;
    }
}
