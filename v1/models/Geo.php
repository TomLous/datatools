<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * Datetime:     24/04/14 21:43
 */

namespace models;


/**
 * Class GeoLocation
 * @package models
 */
class GeoLocation implements \JsonSerializable
{
    /**
     * @var double
     */
    private $latitude;
    /**
     * @var double
     */
    private $longitude;
    /**
     * @var double
     */
    private $elevation;
    /**
     * @var double
     */
    private $direction;


    /**
     * @param $latitude
     * @param $longitude
     * @param null $elevation
     * @param null $direction
     */
    public function __construct($latitude, $longitude, $elevation = null, $direction = null)
    {
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
        $this->setElevation($elevation);
        $this->setDirection($direction);
    }

    /**
     * @param mixed $elevation
     * @throws \Exception
     */
    public function setElevation($elevation)
    {
        if (!is_numeric($elevation) && $elevation !== null) {
            throw new \Exception('Invalid value for elevation: ' . $elevation);
        }
        $this->elevation = $elevation;
    }

    /**
     * @return mixed
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * @param mixed $latitude
     * @throws \Exception
     */
    public function setLatitude($latitude)
    {
        if (!is_numeric($latitude) && $latitude !== null) {
            throw new \Exception('Invalid value for latitude: ' . $latitude);
        }
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $longitude
     * @throws \Exception
     */
    public function setLongitude($longitude)
    {
        if (!is_numeric($longitude) && $longitude !== null) {
            throw new \Exception('Invalid value for longitude: ' . $longitude);
        }
        $this->longitude = $longitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $direction
     * @throws \Exception
     */
    public function setDirection($direction)
    {
        if (!is_numeric($direction) && $direction !== null) {
            throw new \Exception('Invalid value for direction: ' . $direction);
        }
        $this->direction = $direction;
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }


    /**
     * Returns public represenation of the object model
     * @return \stdClass
     */
    function jsonSerialize()
    {
        $obj = new \stdClass();
        $obj->latitude = $this->getLatitude();
        $obj->longitude = $this->getLongitude();
        $obj->elevation = $this->getElevation();
        $obj->direction = $this->getDirection();
        return $obj;
    }


} 