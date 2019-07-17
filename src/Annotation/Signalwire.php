<?php

namespace Drupal\signalwire\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines Signalwire Annotation object.
 *
 * @Annotation
 */
class Signalwire extends Plugin{

    /**
     * The machine name of the signalwire client.
     *
     * @var string
     */
    protected $id;

    /**
     * Translated user-readable label.
     *
     * @var string
     */
    protected $label;
}