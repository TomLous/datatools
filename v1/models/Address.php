<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * Datetime:     24/04/14 21:40
 */

namespace models;


/**
 * Class Address
 * @package models
 */
class Address implements \JsonSerializable {

    /**
     * input/output addressNl as string, base for addressNl extracting or combined properties
     * @var string
     */
    private $addressString;

    /**
     * house number, numeric, without suffix
     * @var int
     */
    private $streetNumber;

    /**
     * Some houses/buildings are identified by name, not by number
     * @var string
     */
    private $houseOrBuildingName;

    /**
     * house numer suffix, like: A, B etc
     * @var string
     */
    private $streetNumberSuffix;


    /**
     * Actual street name of the addressNl
     * @var string
     */
    private $streetName;

    /**
     * Type of street, like: Boulevard, lane, strass, rue, alley, etc
     * @var string
     */
    private $streetType;

    /**
     * Direction of the street: N, E, S, W, NE, SE, NW, SW
     * @var string
     */
    private $streetDirection;

    /**
     * Type of the addressNl, like: PO Box, Apartment, Building, Floor, Office, Suite,
     * @var string
     */
    private $addressType;

    /**
     * i.e. Box Number, Apartment Number, Floor Number
     * @var string
     */
    private $addressTypeIdentifier;

    /**
     * Prefix local part before locality. For instance, if your hamlet/village/area appears in the addressNl before the locality
     * @var string
     */
    private $subLocality;


    /**
     * Actual town/city name
     * @var string
     */
    private $locality;

    /**
     * NUTS 3 level info, like: County, Arrondissements, Kreis, Departments, etc
     * @var string
     */
    private $governingDistrictLevel1;

    /**
     * NUTS 2 level info, like: State, Province, Region, etc
     * @var string
     */
    private $governingDistrictLevel2;


    /**
     * Postal code:  Zip (U.S.), Postal Code (Canada, Mexico), Postcode (U.K.)
     * @var string
     */
    private $postalArea;

    /**
     * Country name
     * @var string
     */
    private $country;


    // GETTERS & SETTERS

    /**
     * input addressNl as string, base for addressNl extracting or combined properties
     * @param string $addressString
     * @throws \Exception
     */
    public function setAddressString($addressString)
    {
        if(!is_scalar($addressString) && $addressString!==null){
            throw new \Exception('Invalid value for addressString: '.$addressString);
        }

        $this->addressString = $addressString;
    }

    /**
     * output addressNl as string, concattenated properties of addressNl
     * @todo regernerate as option
     * @param bool $regenerate
     * @return string
     */
    public function getAddressString($regenerate=false)
    {
        if(!$this->addressString || $regenerate){
            $this->generateString();
        }
        return $this->addressString;
    }

    /**
     * Type of the addressNl, like: PO Box, Apartment, Building, Floor, Office, Suite,
     * @param string $addressType
     * @throws \Exception
     */
    public function setAddressType($addressType)
    {
        if(!is_scalar($addressType) && $addressType!==null){
            throw new \Exception('Invalid value for addressType: '.$addressType);
        }

        $this->addressType = $addressType;
    }

    /**
     * Type of the addressNl, like: PO Box, Apartment, Building, Floor, Office, Suite,
     * @return string
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * i.e. Box Number, Apartment Number, Floor Number
     * @param string $addressTypeIdentifier
     * @throws \Exception
     */
    public function setAddressTypeIdentifier($addressTypeIdentifier)
    {
        if(!is_scalar($addressTypeIdentifier) && $addressTypeIdentifier!==null){
            throw new \Exception('Invalid value for addressTypeIdentifier: '.$addressTypeIdentifier);
        }

        $this->addressTypeIdentifier = $addressTypeIdentifier;
    }

    /**
     * i.e. Box Number, Apartment Number, Floor Number
     * @return string
     */
    public function getAddressTypeIdentifier()
    {
        return $this->addressTypeIdentifier;
    }

    /**
     * Country name
     * @param string $country
     * @throws \Exception
     */
    public function setCountry($country)
    {
        if(!is_scalar($country) && $country!==null){
            throw new \Exception('Invalid value for country: '.$country);
        }

        $this->country = $country;
    }

    /**
     * Country name
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * NUTS 3 level info, like: County, Arrondissements, Kreis, Departments, etc
     * @param string $governingDistrictLevel1
     * @throws \Exception
     */
    public function setGoverningDistrictLevel1($governingDistrictLevel1)
    {
        if(!is_scalar($governingDistrictLevel1) && $governingDistrictLevel1!==null){
            throw new \Exception('Invalid value for governingDistrictLevel1: '.$governingDistrictLevel1);
        }

        $this->governingDistrictLevel1 = $governingDistrictLevel1;
    }

    /**
     * NUTS 3 level info, like: County, Arrondissements, Kreis, Departments, etc
     * @return string
     */
    public function getGoverningDistrictLevel1()
    {

        return $this->governingDistrictLevel1;
    }

    /**
     * NUTS 2 level info, like: State, Province, Region, etc
     * @param string $governingDistrictLevel2
     * @throws \Exception
     */
    public function setGoverningDistrictLevel2($governingDistrictLevel2)
    {
        if(!is_scalar($governingDistrictLevel2) && $governingDistrictLevel2!==null){
            throw new \Exception('Invalid value for governingDistrictLevel2: '.$governingDistrictLevel2);
        }

        $this->governingDistrictLevel2 = $governingDistrictLevel2;
    }

    /**
     * NUTS 2 level info, like: State, Province, Region, etc
     * @return string
     */
    public function getGoverningDistrictLevel2()
    {
        return $this->governingDistrictLevel2;
    }

    /**
     * Some houses/buildings are identified by name, not by number
     * @param string $houseOrBuildingName
     * @throws \Exception
     */
    public function setHouseOrBuildingName($houseOrBuildingName)
    {
        if(!is_scalar($houseOrBuildingName) && $houseOrBuildingName!==null){
            throw new \Exception('Invalid value for houseOrBuildingName: '.$houseOrBuildingName);
        }

        $this->houseOrBuildingName = $houseOrBuildingName;
    }

    /**
     * Some houses/buildings are identified by name, not by number
     * @return string
     */
    public function getHouseOrBuildingName()
    {
        return $this->houseOrBuildingName;
    }

    /**
     * Prefix local part before locality. For instance, if your hamlet/village appears in the addressNl before the locality
     * @param string $subLocality
     * @throws \Exception
     */
    public function setSubLocality($subLocality)
    {
        if(!is_scalar($subLocality) && $subLocality!==null){
            throw new \Exception('Invalid value for subLocality: '.$subLocality);
        }

        $this->subLocality = $subLocality;
    }

    /**
     * Prefix local part before locality. For instance, if your hamlet/village appears in the addressNl before the locality
     * @return string
     */
    public function getSubLocality()
    {
        return $this->subLocality;
    }

    /**
     * Postal code:  Zip (U.S.), Postal Code (Canada, Mexico), Postcode (U.K.)
     * @param string $postalArea
     * @throws \Exception
     */
    public function setPostalArea($postalArea)
    {
        if(!is_scalar($postalArea) && $postalArea!==null){
            throw new \Exception('Invalid value for postalArea: '.$postalArea);
        }

        $this->postalArea = $postalArea;
    }

    /**
     * Postal code:  Zip (U.S.), Postal Code (Canada, Mexico), Postcode (U.K.)
     * @return string
     */
    public function getPostalArea()
    {
        return $this->postalArea;
    }

    /**
     * Direction of the street: N, E, S, W, NE, SE, NW, SW
     * @todo: check n, e, s, w, etc.
     * @param string $streetDirection
     * @throws \Exception
     */
    public function setStreetDirection($streetDirection)
    {
        if(!is_scalar($streetDirection) && $streetDirection!==null){
            throw new \Exception('Invalid value for streetDirection: '.$streetDirection);
        }

        $this->streetDirection = $streetDirection;
    }

    /**
     * Direction of the street: N, E, S, W, NE, SE, NW, SW
     * @return string
     */
    public function getStreetDirection()
    {

        return $this->streetDirection;
    }

    /**
     * Actual street name of the addressNl
     * @param string $streetName
     * @throws \Exception
     */
    public function setStreetName($streetName)
    {
        if(!is_scalar($streetName) && $streetName!==null){
            throw new \Exception('Invalid value for streetName: '.$streetName);
        }

        $this->streetName = $streetName;
    }

    /**
     * Actual street name of the addressNl
     * @return string
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * house number, numeric, without suffix
     * @param int $streetNumber
     * @throws \Exception
     */
    public function setStreetNumber($streetNumber)
    {
        if(!is_numeric($streetNumber) && $streetNumber!==null){
            throw new \Exception('Invalid value for streetNumber: '.$streetNumber);
        }

        $this->streetNumber = $streetNumber;
    }

    /**
     * house number, numeric, without suffix
     * @return int
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * house numer suffix, like: A, B etc
     * @param string $streetNumberSuffix
     * @throws \Exception
     */
    public function setStreetNumberSuffix($streetNumberSuffix)
    {
        if(!is_scalar($streetNumberSuffix) && $streetNumberSuffix!==null){
            throw new \Exception('Invalid value for streetNumberSuffix: '.$streetNumberSuffix);
        }

        $this->streetNumberSuffix = $streetNumberSuffix;
    }

    /**
     * house numer suffix, like: A, B etc
     * @return string
     */
    public function getStreetNumberSuffix()
    {
        return $this->streetNumberSuffix;
    }

    /**
     * Type of street, like: Boulevard, lane, strass, rue, alley, etc
     * @param string $streetType
     * @throws \Exception
     */
    public function setStreetType($streetType)
    {
        if(!is_scalar($streetType) && $streetType!==null){
            throw new \Exception('Invalid value for streetType: '.$streetType);
        }

        $this->streetType = $streetType;
    }

    /**
     * Type of street, like: Boulevard, lane, strass, rue, alley, etc
     * @return string
     */
    public function getStreetType()
    {
        return $this->streetType;
    }

    /**
     * Actual town/city name
     * @param string $locality
     * @throws \Exception
     */
    public function setLocality($locality)
    {
        if(!is_scalar($locality) && $locality!==null){
            throw new \Exception('Invalid value for locality: '.$locality);
        }

        $this->locality = $locality;
    }

    /**
     * Actual locality/city name
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }






    // METHODS

    /**
     * parses the addressstring into addressNl parts for the addressNl object
     * @todo: very complex.
     */
    public function parseString(){
        $addressString = $this->getAddressString();
        // 530 WEST MAIN STREET, ANOKA, MN 55303, USA
        // 530 W. Main street Anoka Minnesota
        // Křemencova 11, 110 00 Prague 1, Czech Republic
        // Heustraße 19, 57392 Schmallenberg, Germany
        // Spinel 7, 2651 RV Berkel en Rodenrijs, The Netherlands
        // Spinel 7 2651 RV Berkel en Rodenrijd Nederland
        // Spinel 7  Berkel en Rodenrijd Nederland
        // Spinel 7  Berkel en Rodenrijs

        // @todo: create intelligent parser that parses any addressNl sting into parts


    }

    /**
     * Generates an addressNl string from addressNl properties
     * @return string
     */
    public function generateString(){
        $parts = array();

        // street + housenumber
//      $this->getStreetDirection();$this->getStreetType(); // ignore these parts (redundant, part of streetname)
        $parts[] = $this->getStreetName() . ' ' . $this->getStreetNumber().$this->getStreetNumberSuffix(). ' '.$this->getHouseOrBuildingName();

        // po.box
        $parts[] = $this->getAddressType() . ' '. $this->getAddressTypeIdentifier();

        // postal
        $parts[] = $this->getPostalArea();

        // city, state, etc
        $parts[] = $this->getLocality();
        $parts[] = $this->getSubLocality();
        $parts[] = $this->getGoverningDistrictLevel1();
        $parts[] = $this->getGoverningDistrictLevel2();
        $parts[] = $this->getCountry();


        // remove the empty elements
        $parts = array_filter(array_map('trim', $parts));

        // concatenate with comma's
        $addressString = implode(', ', $parts);

        // overwrite the current string
        $this->setAddressString($addressString);

        // return it
        return $addressString;

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
        $obj->addressString =  $this->getAddressString();
        $obj->streetNumber = $this->getStreetNumber();
        $obj->houseOrBuildingName = $this->getHouseOrBuildingName();
        $obj->streetNumberSuffix = $this->getStreetNumberSuffix();
        $obj->streetName = $this->getStreetName();
        $obj->streetType = $this->getStreetType();
        $obj->streetDirection = $this->getStreetDirection();
        $obj->addressType = $this->getAddressType();
        $obj->addressTypeIdentifier = $this->getAddressTypeIdentifier();
        $obj->subLocality = $this->getSubLocality();
        $obj->locality = $this->getLocality();
        $obj->governingDistrictLevel1 = $this->getGoverningDistrictLevel1();
        $obj->governingDistrictLevel2 = $this->getGoverningDistrictLevel2();
        $obj->postalArea = $this->getPostalArea();
        $obj->country = $this->getCountry();
        return $obj;
    }
}