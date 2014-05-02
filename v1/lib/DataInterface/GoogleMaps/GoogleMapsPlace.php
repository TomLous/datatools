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

    private $phoneNumber; //international_phone_number or formatted_phone_number
    private $website; // website or else url
    private $ratingLastTimestamp;


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
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $ratingLastTimestamp
     */
    public function setRatingLastTimestamp($ratingLastTimestamp)
    {
        $this->ratingLastTimestamp = $ratingLastTimestamp;
    }

    /**
     * @return mixed
     */
    public function getRatingLastTimestamp()
    {
        return $this->ratingLastTimestamp;
    }

    /**
     * @param mixed $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
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
        $obj->phoneNumber = $this->getPhoneNumber(); //international_phone_number or formatted_phone_number
        $obj->website = $this->getWebsite(); // website or else url
        $obj->ratingLastTimestamp = $this->getRatingLastTimestamp();
        return $obj;
    }
}