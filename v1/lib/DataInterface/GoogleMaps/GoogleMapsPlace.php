<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     29/04/14 17:34
 */

namespace DataInterface\GoogleMaps;


use models\Address;
use models\GeoLocation;

class GoogleMapsPlace implements \JsonSerializable {

    private $id;
    private $reference;
    private $name;
    private $geoLocation;
    private $address;
    private $rating;
    private $types;

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param GeoLocation $geoLocation
     */
    public function setGeoLocation(GeoLocation $geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    /**
     * @return GeoLocation
     */
    public function getGeoLocation()
    {
        return $this->geoLocation;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $rating
     */
    public function setRating( $rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param string $reference
     */
    public function setReference( $reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param array $types
     */
    public function setTypes( $types)
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();
        $obj->id = $this->getId();
        $obj->reference = $this->getReference();
        $obj->name = $this->getName();
        $obj->geoLocation = $this->getGeoLocation();
        $obj->address = $this->getAddress();
        $obj->rating = $this->getRating();
        $obj->types = $this->getTypes();
        return $obj;
    }
}