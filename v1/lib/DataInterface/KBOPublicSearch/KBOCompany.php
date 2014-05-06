<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     06/05/14 20:08
 */

namespace DataInterface\KBOPublicSearch;


use DataInterface\Exception\IncompatibleInputException;
use models\Address;

class KBOCompany implements \JsonSerializable
{

    private $resultNum;
    private $companyNumber;
    private $companyName;
    private $address;
    private $detailUrl;
    private $startDate;
    private $legalTypes;
    private $status;
    private $numberOfEstablishments;


    public function __construct()
    {

    }

    public function setAddressParts($part1, $part2=null)
    {
        $address = new Address();

        $postalCodeTown = !$part2?$part1:$part2;
        $streetHouseNumber = !$part2?null:$part1;



        if($streetHouseNumber){
            // streetHouseNumber split
            preg_match("/(\D+) (\d+)(.*)?/is", $streetHouseNumber, $matches);
            if (count($matches) != 4) {
                throw new IncompatibleInputException('Not a valid street & housenumber `' . $streetHouseNumber . ' ` for KBO Company');
            }
            $address->setStreetName($matches[1]);
            $address->setStreetNumber($matches[2]);
            $address->setStreetNumberSuffix($matches[3]);
        }

        if($postalCodeTown){
            // $postalCodeTown split
            preg_match("/(\d+) (.*)/is", $postalCodeTown, $matches);
            if (count($matches) != 3) {
                throw new IncompatibleInputException('Not a valid postalcode & town `' . $postalCodeTown . ' ` for KBO Company');
            }
            $address->setPostalArea($matches[1]);
            $address->setLocality($matches[2]);
        }

        $address->setCountry('Belgium');

        $this->address = $address;
    }

    public function resetLegalTypes()
    {
        $this->legalTypes = array();
    }

    public function addLegalType($type)
    {
        $this->legalTypes[] = $type;
    }

    /**
     * @param \Address $address
     */
    public function setAddress(\Address $address)
    {
        $this->address = $address;
    }

    /**
     * @return \Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param mixed $companyNumber
     * @throws \DataInterface\Exception\IncompatibleInputException
     */
    public function setCompanyNumber($companyNumber)
    {
        $companyNumber = preg_replace("/\D+/is", "", $companyNumber);
        if (strlen($companyNumber) < 9) {
            throw new IncompatibleInputException('Invalid value `' . $companyNumber . '` for KBO CompanyNumber');
        }
        $this->companyNumber = $companyNumber;
    }

    /**
     * @return string
     */
    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }

    /**
     * @param string $detailUrl
     */
    public function setDetailUrl($detailUrl)
    {
        $this->detailUrl = $detailUrl;
    }

    /**
     * @return string
     */
    public function getDetailUrl()
    {
        return $this->detailUrl;
    }

    /**
     * @param mixed $legalTypes
     */
    public function setLegalTypes($legalTypes)
    {
        $this->legalTypes = $legalTypes;
    }

    /**
     * @return mixed
     */
    public function getLegalTypes()
    {
        return $this->legalTypes;
    }

    /**
     * @param int $numberOfEstablishments
     * @throws \DataInterface\Exception\IncompatibleInputException
     */
    public function setNumberOfEstablishments($numberOfEstablishments)
    {
        if (!is_numeric($numberOfEstablishments)) {
            throw new IncompatibleInputException('Invalid value `' . $numberOfEstablishments . '` for KBO Number of Establishments');
        }
        $this->numberOfEstablishments = $numberOfEstablishments;
    }

    /**
     * @return int
     */
    public function getNumberOfEstablishments()
    {
        return $this->numberOfEstablishments;
    }

    /**
     * @param int $resultNum
     */
    public function setResultNum($resultNum)
    {
        $this->resultNum = (int)$resultNum;
    }

    /**
     * @return int
     */
    public function getResultNum()
    {
        return $this->resultNum;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $this->localeDateStringToTimestamp($startDate);
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $status = $status == 'active' || $status;
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    private function localeDateStringToTimestamp($dateString, $outFormat = '%Y-%m-%d')
    {
        $returnValue = null;

        $locales = array('nl_BE', 'fr_BE', 'nl_NL', 'fr_FR');
        $possibleFormats = array('%e %B %Y', '%Y-%m-%d');

        $currentLocale = setlocale(LC_TIME, 0);
        $ftimeArray = false;
        foreach ($locales as $locale) {
            setlocale(LC_TIME, $locale);

            foreach ($possibleFormats as $format) {

                $ftimeArray = strptime($dateString, $format);
                if ($ftimeArray !== false) {
                    break;
                }
            }

            if ($ftimeArray !== false) {
                break;
            }
        }

        setlocale(LC_TIME, $currentLocale);

        if ($ftimeArray !== false) {
            $returnValue = mktime(
                $ftimeArray['tm_hour'],
                $ftimeArray['tm_min'],
                $ftimeArray['tm_sec'],
                $ftimeArray['tm_mon'] + 1,
                $ftimeArray['tm_mday'],
                $ftimeArray['tm_year'] + 1900
            );

            if ($outFormat) {
                $returnValue = strftime($outFormat, $returnValue);
            }
        }

        return $returnValue;
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
        $obj->resultNum = $this->getResultNum();
        $obj->companyNumber = $this->getCompanyNumber();
        $obj->name = $this->getCompanyName();
        $obj->address = $this->getAddress();
        $obj->detailUrl = $this->getDetailUrl();
        $obj->startDate = $this->getStartDate();
        $obj->legalTypes = $this->getLegalTypes();
        $obj->status = $this->getStatus();
        $obj->numberOfEstablishments = $this->getNumberOfEstablishments();
        return $obj;
    }

    public function __toString()
    {
        $obj = $this->jsonSerialize();
        return json_encode($obj);
    }
}