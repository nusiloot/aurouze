<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Coordonnees
{
    /**
     * @MongoDB\Float
     */
    public $lat;

    /**
     * @MongoDB\Float
     */
    public $lon;

    /**
     * @MongoDB\Float
     */
    public $zoom;

    /**
     * Set lat
     *
     * @param float $lat
     * @return self
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * Get lat
     *
     * @return float $lat
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lon
     *
     * @param float $lon
     * @return self
     */
    public function setLon($lon)
    {
        $this->lon = $lon;
        return $this;
    }

    /**
     * Get lon
     *
     * @return float $lon
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * Set zoom
     *
     * @param float $zoom
     * @return self
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;
        return $this;
    }

    /**
     * Get zoom
     *
     * @return float $zoom
     */
    public function getZoom()
    {
        return $this->zoom;
    }
}
