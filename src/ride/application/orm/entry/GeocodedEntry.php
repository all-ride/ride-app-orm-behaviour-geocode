<?php

namespace ride\application\orm\entry;

use ride\library\geocode\coordinate\GeocodeCoordinate;

/**
 * Interface for an entry ith geo location support
 */
interface GeocodedEntry extends GeocodeCoordinate {

    /**
     * Sets the latitude coordinate
     * @param float $latitude
     * @return null
     */
    public function setLatitude($latitude);

    /**
     * Sets the longitude coordinate
     * @param float $longitude
     * @return null
     */
    public function setLongitude($longitude);

    /**
     * Gets the address to lookup the coordinates for
     * @return string
     */
    public function getGeocodeAddress();

}
