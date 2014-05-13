<?php
/**
 * Invokes form search and crawls requests from this site: http://kbopub.economie.fgov.be/kbopub
 * @todo: Open Data will become available soon. check that?
 *
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     KBOPublicSearch
 * Datetime:     02/05/14 15:05
 *
 */

namespace DataInterface\KBOPublicSearch;

use DataInterface\DataInterface;
use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\IncompatibleInputException;
use DataInterface\Exception\InterfaceQuotaExceededException;
use models\Address;
use models\GeoLocation;


class KBOPublicSearchCrawler extends DataInterface
{

    private $mappingFrench = array(
        'companyNumber' => "Numéro d'entreprise",
        'addressNl' => 'Adresse du siège social',
        'numberOfEstablishments' => "Nombre d'unités d'établissement",
        'numberOfEstablishments2' => "Nombre d''unités d'établissement (UE):",
        'numberAndStartDate' => "Numéro d'entreprise et date de début",
        'noData' => 'Pas de données reprises dans la BCE',
        'startDate' => 'Date de début',
        'name' => "Dénomination de l'entreprise",
        'additionalInfo' => "Info supplémentaire",
        'phoneNumber' => 'Numéro de téléphone',
        'faxNumber' => 'Numéro de fax',
        'emailAddress' => 'Adresse e-mail',
        'website' => 'Adresse web',
        'type' => "Type d'entreprise",
        'status' => 'Statut',
        'legalForm' => 'Forme juridique',
        'abbreviation' => 'Abréviation',
        'commercialName' => 'Dénomination commerciale',
        'revocation' => 'Ambtshalve doorhaling',
        'next' => 'Suivant',
        'active' => 'AC',
        'naturalPerson' => 'Personne morale',
        'corporation' => 'Association des copropriétaires',
        'nonProfit' => 'Association sans but lucratif',
        'closedPartnership' => 'Société privée à responsabilité limitée',
        'namelessPartnership' => 'Société privée à responsabilité limitée unipersonnelle',
        'foundEnterprises' => 'entreprises trouvées',
        'corporationLimitedLiability' => 'Société coopérative à responsabilité limitée',
        'corporationOld' => 'Société coopérative (ancien statut)',
        'oneManBusiness' => 'Société privée à responsabilité limitée',
        'noLegal' => 'Société ou association sans personnalité juridique',
        'foreign' => 'Société étrangère',
        'other' => 'Autres formes juridiques',
        'email' => 'E-mail',
        'legalStatus' => 'Situation juridique',
        'normal' => 'Situation normale',
    );

    private $mappingDutch = array(
        'companyNumber' => 'Ondernemingsnummer',
        'addressNl' => 'Adres van de maatschappelijke zetel',
        'numberOfEstablishments' => 'Aantal vestigingen',
        'numberOfEstablishments2' => 'Aantal vestigingseenheden (VE)',
        'numberAndStartDate' => "Ondernemings- nummer en begindatum",
        'noData' => 'Geen gegevens opgenomen in KBO',
        'startDate' => 'Begindatum',
        'additionalInfo' => "Bijkomende info",
        'name' => 'Maatschappelijke Naam',
        'phoneNumber' => 'Telefoonnummer',
        'faxNumber' => 'Faxnummer',
        'emailAddress' => 'E-mailadres',
        'website' => 'Webadres',
        'type' => 'Type onderneming',
        'status' => 'Status',
        'legalForm' => 'Rechtsvorm',
        'abbreviation' => 'Afkorting',
        'commercialName' => 'Commerciële Naam',
        'revocation' => 'Ambtshalve doorhaling',
        'next' => 'Volgende',
        'active' => 'AC',
        'naturalPerson' => 'Rechtspersoon',
        'corporation' => 'Vereniging van mede-eigenaars',
        'nonProfit' => 'Vereniging zonder winstoogmerk',
        'closedPartnership' => 'Besloten vennootschap met beperkte aansprakelijkheid',
        'namelessPartnership' => 'Naamloze vennootschap',
        'foundEnterprises' => 'ondernemingen gevonden',
        'corporationLimitedLiability' => 'Coöperatieve vennootschap met beperkte aansprakelijkheid',
        'corporationOld' => 'Coöperatieve vennootschap (oud statuut)',
        'oneManBusiness' => 'Eenmans besloten vennootschap met beperkte aansprakelijkheid',
        'noLegal' => 'Vennootschap of vereniging zonder rechtspersoonlijkheid',
        'foreign' => 'Buitenlandse onderneming',
        'other' => 'Overige rechtsvormen',
        'email' => 'E-mail',
        'legalStatus' => 'Rechtstoestand',
        'normal' => 'Normale toestand',
    );


    /**
     * Constanst used for this API
     */
    const apiUrl = 'http://kbopub.economie.fgov.be/kbopub/';


    /**
     * Execute form request based on this page
     * @see http://kbopub.economie.fgov.be/kbopub/zoekwoordenform.html
     * Required params:
     * ondernemingsnummer (Opzoeking volgens ondernemingsnummer) or searchWord (Opzoeking volgens zoekwoord )
     * optional:
     * params available in form on page
     *
     * additional:
     * page (page num)
     * lang (language nl or fr)
     * follow_nextpage boolean, for loading all results after another
     *
     * @param array $params
     * @return array
     * @throws \DataInterface\Exception\IncompatibleInputException
     */
    public function searchOndernemingen($params = array())
    {
        $type = 'zoekwoordenform';

        // All available fields that can be set
        $queryParams = array(
            'ondernemingsnummer' => '',
            'natuurlijkPersoon' => 'true',
            '_natuurlijkPersoon' => 'on',
            'rechtsPersoon' => 'true',
            '_rechtsPersoon' => 'on',
            'searchWord' => '', // check
            '_oudeBenaming' => 'on',
            'pstcdeNPRP' => '',
            'postgemeente1' => '',
            'familynameFonetic' => '',
            'pstcdeNPFonetic' => '',
            'postgemeente2' => '',
            'searchwordRP' => '',
            '_oudeBenaming' => 'on',
            'pstcdeRPFonetic' => '',
            'postgemeente3' => '',
            'rechtsvormFonetic' => 'ALL',
            'familynameExact' => '',
            'firstName' => '',
            'pstcdeNPExact' => '',
            'postgemeente4' => '',
            'firmName' => '',
            'pstcdeRPExact' => '',
            'postgemeente5' => '',
            'rechtsvormExact' => 'ALL',
            'page' => '1', // set hardcoded to 1, so it can be overwritten
            'lang' => 'nl', // set hardcoded to nl, so it can be overwritten
        );

        // sanitize input params
        if (!is_array($params)) {
            throw new IncompatibleInputException('Missing properties');
        }


        foreach ($params as $param => $value) {
            if (isset($queryParams[$param]) && is_scalar($value)) {
                $queryParams[$param] = $value;
            }
        }

        $follow_nextpage = false;
        if (isset($params['follow_nextpage']) && is_scalar($params['follow_nextpage'])) {
            $follow_nextpage = strtolower($params['follow_nextpage']) == 'true' || $params['follow_nextpage'] == 1;
        }

        // Check if either of the two obligated values isset
        if (!empty($queryParams['ondernemingsnummer'])) {
            $queryParams['actionEntnr'] = 'Zoek onderneming';
        } elseif (!empty($queryParams['searchWord'])) {
            $queryParams['actionNPRP'] = 'Zoek onderneming';
        } else {
            throw new IncompatibleInputException('Missing property `ondernemingsnummer` or `searchWord`');
        }

        // @todo create possible switch for other params / combis


        // create a new URL for this request e.g. http://kbopub.economie.fgov.be/kbopub/zoekwoordenform.html?query
        $requestUrl = $this->buildUrl($type, $queryParams);

        // do request to Geocodefarms
        $returnData = $this->doRequestAndInterpretHTML($requestUrl, $type, $follow_nextpage);


        return $returnData;
    }

    private function doRequestAndInterpretHTML($url, $type, $follow_nextpage = false, $maxLoopCount = -1)
    {
        $returnData = array();
        $returnData['Meta'] = array();
        $returnData['Meta']['url'] = $url;
        $returnData['Meta']['type'] = $type;

        // Retrieve JSON for url
        $html = $this->doGetRequest($url);

        // When request rate is too high, it throws this error
        if (preg_match('/Request rate too high/is', $html)) {
            throw new InterfaceQuotaExceededException('Access Denied to service reason: `Request rate too high` for request to ' . $url);
        }

        // convert HTML into DOMDocument
        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->strictErrorChecking = false;
        $document->preserveWhiteSpace = false;
        $document->loadHTML($html);

        $xpath = new \DOMXPath($document);

        $returnData['data'] = null;
        $tableArray = array();

        $resultsPerPage = 0;


        // All data in KBO is returned as table, so parse the tables in the returned data
        $tableNodeList = $document->getElementsByTagName("table");
        foreach ($tableNodeList as $tableNode) {
            $tableData = array();
            $tableData['numRows'] = 0;
            $tableData['numColums'] = 0;

            // retrieve all table rows for this table
            $rowNodeList = $tableNode->getElementsByTagName("tr");
            $isHeaderRow = false;

            foreach ($rowNodeList as $rowNode) {
                $tableData['numRows']++;
                $numColums = 0;
                $rowData = array();

                // retrieve all cells for this row
                $cellNodeList = $rowNode->getElementsByTagName('td');
                if ($cellNodeList->length == 0) {

                    // when no cells check all th's for this row (this row becomes the header)
                    $cellNodeList = $rowNode->getElementsByTagName('th');
                    if ($cellNodeList->length > 0) {
                        $isHeaderRow = true;
                    }
                }

                // loop all cells
                foreach ($cellNodeList as $cellNode) {
                    $numColums++;
                    $cellData = array();

                    // all nested html (cellparts, like spans, div's, etc) in a cell as an array element
                    foreach ($cellNode->childNodes as $cellPartNode) {
                        $cellPartData = array();
                        $cellPartData['content'] = $this->translateText($cellPartNode->nodeValue);

                        // a cellpart with interesting (content) attriutes, like href should be included
                        if ($cellPartNode->attributes && $cellPartNode->attributes->length > 0 && !$isHeaderRow) {
                            $cellAttributes = array();

                            // loop all attributes for this cell
                            foreach ($cellPartNode->attributes as $attribute) {
                                if (in_array($attribute->name, array('href'))) { // allowed tags for content
                                    // trim & remove session id from url
                                    $cellAttributes[$attribute->name] = $this->cleanUrl($attribute->value, $type);
                                }
                            }

                            // if interesting cellparts are found add them as attributes
                            if (count($cellAttributes) > 0) {
                                $cellPartData['attributes'] = $this->cleanArray($cellAttributes);
                            }
                        }

                        // set cell data
                        $cellData[] = $this->cleanArray($cellPartData);
                    }
                    $cellData = $this->cleanArray($cellData, true);

                    // key is next number in line
                    $key = count($rowData);

                    // if its a header row, use the current cellpart as value
                    if ($isHeaderRow) {
                        $cellData = current($cellData);
                    } // if there is a header for current position
                    elseif (isset($tableData['header'][$key]['content'])) {
                        $key = (string)$tableData['header'][$key]['content'];
                    }

                    // set the current cell with the appropiate key
                    $rowData[$key] = $cellData;
                }

                // assume row with th elements is the header row
                if ($isHeaderRow) {
                    $tableData['header'] = array_values($rowData);
                    $isHeaderRow = false;
                } else {
                    // filter the empty cells
                    $tableData['rows'][] = $this->cleanArray($rowData);
                }

                $tableData['numColums'] = max($tableData['numColums'], $numColums);
            }

            // filter the empty rows
            if (isset($tableData['rows'])) {
                $tableData['rows'] = $this->cleanArray($tableData['rows']);
            }

            if ($tableData['numRows'] > $resultsPerPage) {
                $resultsPerPage = $tableData['numRows'];
            }

            if ($tableData['numColums'] == 0 && $tableData['numRows'] == 0) {
                unset($tableData['numRows']);
                unset($tableData['numColums']);
            }

            // filter the empty table properties (eg no header)
            $tableArray[] = $this->cleanArray($tableData);
        }

        // filter the empty tables
        $tableArray = $this->cleanArray($tableArray, true);


        // Now we have all table structures turned into an array

        // find a possible next page link in the DOM
        $domNodes = $xpath->query("//a[contains(.,'{$this->mappingFrench['next']}') or contains(.,'{$this->mappingDutch['next']}')]");
        if ($domNodes->length > 0) {
            $firstNode = $domNodes->item(0);

            if ($firstNode) {
                $hrefAttribute = $firstNode->getAttribute('href');

                if ($hrefAttribute) {
                    $nextPageUrl = $this->cleanUrl($hrefAttribute, $type);
                }
            }
        }


        if ($nextPageUrl) {
            $returnData['Meta']['nextPageUrl'] = $nextPageUrl;
        }


        // parse the number of results from HTML
        $numResults = 1;
        if (preg_match("/(\d+) ({$this->mappingFrench['foundEnterprises']}|{$this->mappingDutch['foundEnterprises']})/is", $html, $matches)) {
            if (isset($matches[1])) {
                $numResults = $matches[1];
            }
        }
        $returnData['Meta']['totalNumberOfResults'] = $numResults;

        $returnData['data'] = array();
        // loop table structures
        foreach ($tableArray as $table) {
            if ($type == 'zoekwoordenform') { // ondernemingen
                if ($table['numColums'] == 7) { // if it's a recordset from company search
                    foreach ($table['rows'] as $record) {
                        $kboCompany = $this->parseRecordSetToKBOCompany($record);
                        $returnData['data'][] = $kboCompany;

                    }
                } elseif ($table['numColums'] == 3) { // if it's a detail page from company search
                    $kboCompany = $this->parseListToKBOCompany($table['rows']);
//                    print_r(json_encode($kboCompany));
                    $returnData['data'][] = $kboCompany;
                }else{
                    $returnData['data'][] = $table;
                }
            }


        }

        // Set the max loopcount if unset
        $maxLoopCount = $maxLoopCount < 0 ? ceil($numResults / $resultsPerPage) : $maxLoopCount;

        if ($nextPageUrl && $maxLoopCount > 0) {
//            sleep(1);
            $newData = $this->doRequestAndInterpretHTML($nextPageUrl, $type, $follow_nextpage, --$maxLoopCount);
            $returnData['data'] = array_merge($returnData['data'], $newData['data']);
            $returnData['Meta'] = array_merge($returnData['Meta'], $newData['Meta']);
//            $returnData['page'][] = $newData;
        }

//        print_r($returnData);
//        exit();

        return $returnData;
    }

    private function parseListToKBOCompany($rows){
        $kboCompany = new KBOCompany();
        foreach ($rows as $row) {
            if(count($row) >= 2){
                $key = $row[0][0]['content'];
                $data = $row[1];

//                print_r('$key');
//                print_r($key);
//                print_r('$data');
//                print_r($data);
//                print_r('$row');
//                print_r($data);
//                print_r('$row[0]');
//                print_r($row[0]);

                $this->setKBOCompanyProperty($kboCompany, $key, $data);
            }
            else{
//                print 'no data for key ';
//                print_r(count($row));
//                print_r($row);
            }

        }
        return $kboCompany;
    }

    /**
     * loops through table array params and sets KBOCompany properties
     * @param $record
     * @return KBOCompany
     */
    private function parseRecordSetToKBOCompany($record)
    {
        $kboCompany = new KBOCompany();
        foreach ($record as $key => $data) {
            if ($key === 0) {
                $key = 'num';
            }
            $this->setKBOCompanyProperty($kboCompany, $key, $data);


        }
        $kboCompany->setResultNum(1);

        return $kboCompany;
    }

    private function setKBOCompanyProperty(&$kboCompany, $key, $data){
        $value = $this->translateText($data[0]['content']);

        switch ($key) {
            case 'num':
                $kboCompany->setResultNum($value);
                break;
            case 'numberAndStartDate':
                $kboCompany->setCompanyVat($value);
                $kboCompany->setDetailUrl($data[0]['attributes']['href']);
                $kboCompany->setStartDate($data[1]['content']);
                break;
            case 'companyNumber':
                $kboCompany->setCompanyVat($value);
                break;
            case 'startDate':
                $kboCompany->setStartDate($value);
                break;
            case 'name':
                $kboCompany->setCompanyName($value);
                break;
            case 'addressNl':
                $kboCompany->setAddressParts(KBOCompany::LANGUAGE_NL, $data[0]['content'], $data[2]['content']);
                break;
            case 'addressFr':
                $kboCompany->setAddressParts(KBOCompany::LANGUAGE_FR, $data[0]['content'], $data[2]['content']);
                break;
            case 'additionalInfo':
                if ($value == 'addressNl') {
                    $kboCompany->setAddressParts(KBOCompany::LANGUAGE_NL, $data[1]['content'], $data[2]['content']);
                }
                break;
            case 'type':
            case 'legalForm':
                $kboCompany->resetLegalTypes();
                foreach ($data as $content) {
                    if(strpos($content['content']," ")===false){
                        $kboCompany->addLegalType($content['content']);
                    }
                }
                break;
            case 'status':
                $kboCompany->setStatus($value);
                break;
            case 'numberOfEstablishments':
            case 'numberOfEstablishments2':
                $kboCompany->setNumberOfEstablishments($value);
                break;
            case 'phoneNumber':
                $kboCompany->setPhoneNumber($value);
                break;
            case 'faxNumber':
                $kboCompany->setFaxNumber($value);
                break;
            case 'email':
                $kboCompany->setEmailAddress($value);
                break;
            case 'website':
                $kboCompany->setWebsite($value);
                break;
            case 'legalStatus':
                $kboCompany->setLegalStatus($value);
                break;
            default:
//                print "\n\n".'No key switch for key '. $key;
                break;
        }
    }

    /**
     * Translate text into properties
     * @param $string
     * @return mixed|string
     */
    private function translateText($string)
    {
        if (is_scalar($string)) {
            $string = trim($string);
            $string = preg_replace('/:$/', '', $string);
            $string = preg_replace('/"(.*)"/', '$1', $string);
            $string = preg_replace('/\.$/', '', $string);

            if ($key = array_search(strtolower($string), array_map('strtolower', $this->mappingDutch))) {
                $string = $key;
            } elseif ($key = array_search(strtolower($string), array_map('strtolower', $this->mappingFrench))) {
//                $string = $key;
            }

            if($string == 'noData'){
                $string = null;
            }
        }
        return $string;
    }

    /**
     * filters an array using filterData method and optionally returns array as numeric array
     * @param $array
     * @param bool $valuesOnly
     * @return array
     */
    private function cleanArray($array, $valuesOnly = false)
    {
        $array = array_filter($array, array($this, 'filterData'));
        if ($valuesOnly) {
            $array = array_values($array);
        }
        return $array;
    }

    /**
     * Removes session info and rebuilds URL's, optionally the current endpoint for relative url's
     * @param $url
     * @param null $endpoint
     * @return mixed|string
     */
    private function cleanUrl($url, $endpoint = null)
    {
        $url = preg_replace('/;jsessionid=[^&?]+/', '', utf8_encode(trim($url)));
        $url = str_replace(self::apiUrl, '', $url);
        if (!preg_match('/^http/is', $url)) {
            if (preg_match('/^\?/is', $url)) {
                $url = substr($url, 1);
                parse_str($url, $params);
                $url = $this->buildUrl($endpoint, $params);
            } else {
                $url = self::apiUrl . $url;
            }
        }
        return $url;
    }

    /**
     * filter the array values that evaluate to false, except 0! for array_filter()
     * @param $value
     * @return bool
     */
    private function filterData($value)
    {
        return ($value !== null && $value !== false && $value !== '' && (!is_array($value) || count($value) > 0));
    }

    /**
     * Create an GET URL based on endpoint (HTML page) and query params
     * @param $endpoint
     * @param array $queryParameters
     * @return string
     */
    private function buildUrl($endpoint, $queryParameters = array())
    {
        $url = self::apiUrl . $endpoint . '.html?' . http_build_query($queryParameters);
        return $url;
    }

    /**
     * Execute a GET request and return HTML as string
     * @param $url
     * @return mixed|string
     * @throws \DataInterface\Exception\IncompatibleInterfaceException
     */
    private function doGetRequest($url)
    {
        // retrieve data from url
        $result = file_get_contents($url);

        // If it's not available, throw an alert
        if ($result === null) {
            throw new IncompatibleInterfaceException('Invalid result from request to ' . $url . ' result: ' . $result);
        }

        // replace nbsp's to spaces for matchin purposes
//        $result = str_replace("\xc2\xa0",' ',$result);
        $result = str_replace("&nbsp;", ' ', $result);

        return $result;
    }
} 