<?php

namespace Genero\Sage\Timber;

use WpMenuCart;
use Twig_SimpleFilter;
use TimberExtended;

class Timber
{
    protected $integrations = [];

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->addIntegrations([
            Integrations\DisplayPosts::class,
            Integrations\FacetWp::class,
        ]);

        if (class_exists('Timber')) {
            \Timber::$dirname = $config['dirname'];
            \Timber::$cache = $config['cache'];
        }
    }

    /**
     * Initialize all integrations with their respective configurations
     *
     * @param array $classes
     */
    public function addIntegrations($classes)
    {
        foreach ($classes as $class) {
            // Get the class name without a namespace.
            $short_class = substr(strrchr($class, '\\'), 1);
            // Convert the camel case to snake case.
            $config_key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $short_class));
            // Retrieve the optional config for the integration.
            $config = isset($this->config[$config_key]) ? $this->config[$config_key] : [];
            // Initialize the integration
            $this->integrations[] = new $class($config);
        }
    }

    /**
     * Register Twig helpers.
     *
     * @param Twig_Environment $twig
     */
    public function registerTwig($twig)
    {
    }
}
