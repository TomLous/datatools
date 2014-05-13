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
    private $companyVat;
    private $companyName;
    private $addressNl;
    private $addressFr;
    private $detailUrl;
    private $startDate;
    private $legalTypes;
    private $status;
    private $numberOfEstablishments;
    private $language;
    private $phoneNumber;
    private $faxNumber;
    private $emailAddress;
    private $website;
    private $legalStatus;


    const LANGUAGE_NL = 'nl';
    const LANGUAGE_FR = 'fr';

    public function __construct()
    {

    }

    public function setAddressParts($language, $part1, $part2 = null)
    {
        $address = new Address();

        $postalCodeTown = !$part2 ? $part1 : $part2;
        $streetHouseNumber = !$part2 ? null : $part1;


        if ($streetHouseNumber) {
            // streetHouseNumber split
            preg_match("/([^\d\(]+)(\((\D+)\))?(,(\D+))? (\d+)?(.*)?/is", $streetHouseNumber, $matches);
            if (count($matches) != 8) {
                throw new IncompatibleInputException('Not a valid street & housenumber `' . $streetHouseNumber . ' ` for KBO Company');
            }
            $address->setStreetName($matches[1]);
            $address->setStreetType($matches[3] ? $matches[3] : $matches[5]);
            if (is_numeric($matches[6])) {
                $address->setStreetNumber($matches[6]);
            }
            $address->setStreetNumberSuffix($matches[7]);
        }

        if ($postalCodeTown) {
            // $postalCodeTown split
            preg_match("/(\d+)?\s*(.*)/is", $postalCodeTown, $matches);
            if (count($matches) != 3) {
                throw new IncompatibleInputException('Not a valid postalcode & town `' . $postalCodeTown . ' ` for KBO Company');
            }
            $address->setPostalArea($matches[1]);
            $address->setLocality($matches[2]);
        }


        if (!$this->getLanguage()) {
            $this->setLanguage($language);
        }

        if ($language == self::LANGUAGE_FR) {
            $address->setCountry('Belgique');
            $this->addressFr = $address;

        } else {
            $address->setCountry('België');
            $this->addressNl = $address;
        }
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
    public function setAddressNl(\Address $address)
    {
        $this->addressNl = $address;
    }

    /**
     * @return \Address
     */
    public function getAddressNl()
    {
        return $this->addressNl;
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
     * @param mixed $companyVat
     * @throws \DataInterface\Exception\IncompatibleInputException
     */
    public function setCompanyVat($companyVat)
    {
        $companyVat = preg_replace("/\D+/is", "", $companyVat);
        if (strlen($companyVat) < 9 || strlen($companyVat) > 10) {
            throw new IncompatibleInputException('Invalid value `' . $companyVat . '` for KBO CompanyVat');
        }
        if (strlen($companyVat) == 9) {
            $companyVat = '0' . $companyVat;
        }
        $this->companyVat = $companyVat;
    }

    /**
     * @return string
     */
    public function getCompanyVat()
    {
        return $this->companyVat;
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

    /**
     * @param \Address $addressFr
     */
    public function setAddressFr(\Address $addressFr)
    {
        $this->addressFr = $addressFr;
    }

    /**
     * @return \Address
     */
    public function getAddressFr()
    {
        return $this->addressFr;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        if ($language == self::LANGUAGE_FR || self::LANGUAGE_NL) {
            $this->language = $language;
        }
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $faxNumber
     */
    public function setFaxNumber($faxNumber)
    {
        $this->faxNumber = $faxNumber;
    }

    /**
     * @return mixed
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
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
     * @param mixed $legalStatus
     */
    public function setLegalStatus($legalStatus)
    {
        $this->legalStatus = $legalStatus;
    }

    /**
     * @return mixed
     */
    public function getLegalStatus()
    {
        return $this->legalStatus;
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
        $obj->companyVat = $this->getCompanyVat();
        $obj->name = $this->getCompanyName();
        $obj->addressNl = $this->getAddressNl();
        $obj->addressFr = $this->getAddressFr();
        $obj->language = $this->getLanguage();
        $obj->detailUrl = $this->getDetailUrl();
        $obj->startDate = $this->getStartDate();
        $obj->legalTypes = $this->getLegalTypes();
        $obj->status = $this->getStatus();
        $obj->numberOfEstablishments = $this->getNumberOfEstablishments();
        $obj->phoneNumber = $this->getPhoneNumber();
        $obj->faxNumber = $this->getFaxNumber();
        $obj->emailAddress = $this->getEmailAddress();
        $obj->website = $this->getWebsite();
        $obj->legalStatus = $this->getLegalStatus();
        return $obj;
    }

    public function __toString()
    {
        $obj = $this->jsonSerialize();
        return json_encode($obj);
    }
}