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
        'address' => 'Adresse du siège social',
        'numberOfEstablishments' => "Nombre d'unités d'établissement",
        'numberAndStartDate' => "Numéro d'entreprise et date de début",
        'noData' => 'Geen gegevens opgenomen in KBO',
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
    );

    private $mappingDutch = array(
        'companyNumber' => 'Ondernemingsnummer',
        'address' => 'Adres van de maatschappelijke zetel',
        'numberOfEstablishments' => 'Aantal vestigingen',
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
        'Adresse du siège social' => 'Adres van de maatschappelijke zetel',
        'revocation' => 'Ambtshalve doorhaling',
        'next' => 'Volgende',
        'active' => 'AC',
        'naturalPerson' => 'Rechtspersoon',
        'corporation' => 'Vereniging van mede-eigenaars',
        'nonProfit' => 'Vereniging zonder winstoogmerk',
        'closedPartnership' => 'Besloten vennootschap met beperkte aansprakelijkheid',
        'namelessPartnership' => 'Naamloze vennootschap',
        'foundEnterprises' => 'ondernemingen gevonden',
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
     * @todo
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
        $returnData = $this->doRequestAndInterpretHTML($requestUrl, $type);

        return $returnData;
    }

    private function doRequestAndInterpretHTML($url, $type)
    {
        $returnData = array();
        $returnData['Meta'] = array();
        $returnData['Meta']['url'] = $url;
        $returnData['Meta']['type'] = $type;

        // Retrieve JSON for url
        $html = $this->doGetRequest($url);


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
//        $tableCells =$xpath->query("//table[@id='onderneminglist']//tr//td//text()");
//
//        print_r($tableCells);
//        $table =$xpath->query("//table")->item(0);

        $returnData['data'] = null;
        $tableArray = array();


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
                                    $cellAttributes[$attribute->name] = $this->cleanUrl($attribute->value);
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

            if($tableData['numColums'] == 0 &&  $tableData['numRows'] == 0){
                unset($tableData['numRows']);
                unset($tableData['numColums']);
            }


            // filter the empty table properties (eg no header)
            $tableArray[] = $this->cleanArray($tableData);
        }

        // filter the empty tables
        $tableArray = $this->cleanArray($tableArray, true);

        // find a possible next page link in the DOM
        $domNodes = $xpath->query("//a[contains(.,'{$this->mappingFrench['next']}') or contains(.,'{$this->mappingDutch['next']}')]");
        if ($domNodes->length > 0) {
            $firstNode = $domNodes->item(0);
            if ($firstNode) {
                $hrefAttribute = $firstNode->getAttribute('href');
                if ($hrefAttribute) {
                    $nextPageUrl = $this->cleanUrl($hrefAttribute);
                }
            }
        }
//            $nextPageUrl = $this->cleanUrl($xpath->query("//a[contains(.,'{$this->mappingFrench['next']}') or contains(.,'{$this->mappingDutch['next']}')]")->item(0)->getAttribute('href'));
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

        // loop table structures
        foreach($tableArray as $table){
            if($table['numColums'] == 7){ // if it's a recordset
                $returnData['data'] = array();
                foreach($table['rows'] as $record){


                    $kboCompany = $this->parseRecordSetToKBOCompany($record);
                    $returnData['data'][] = $kboCompany;

                }
            }

        }

        return $returnData;
    }

    private function parseRecordSetToKBOCompany($record){
        $kboCompany = new KBOCompany();
        foreach($record as $key=>$data){
            if($key === 0){
                $key = 'num';
            }

            switch($key){
                case 'num':
                    $kboCompany->setResultNum($data[0]['content']);
                    break;
                case 'numberAndStartDate':
                    $kboCompany->setCompanyNumber($data[0]['content']);
                    $kboCompany->setDetailUrl(self::apiUrl . $data[0]['attributes']['href']);
                    $kboCompany->setStartDate($data[1]['content']);
                    break;
                case 'name':
                    $kboCompany->setCompanyName($data[0]['content']);
                    break;
                case 'additionalInfo':
                    if($data[0]['content'] == 'address'){
                        $kboCompany->setAddressParts($data[1]['content'], $data[2]['content']);
                    }
                    break;
                case 'type':
                    $kboCompany->resetLegalTypes();
                    foreach($data as $content){
                        $kboCompany->addLegalType($content['content']);
                    }
                    break;
                case 'status':
                    $kboCompany->setStatus($data[0]['content']);
                    break;
                case 'numberOfEstablishments':
                    $kboCompany->setNumberOfEstablishments($data[0]['content']);
                    break;
            }

        }


        return $kboCompany;
    }

    private function translateText($string)
    {
        if (is_scalar($string)) {
            $string = trim($string);
            $string = preg_replace('/:$/', '', $string);
            $string = preg_replace('/"(.*)"/', '$1', $string);
            if ($key = array_search(strtolower($string), array_map('strtolower', $this->mappingDutch))) {
                $string = $key;
            } elseif ($key = array_search(strtolower($string), array_map('strtolower', $this->mappingFrench))) {
//                $string = $key;
            }
        }
        return $string;
    }

    private function cleanArray($array, $valuesOnly = false)
    {
        $array = array_filter($array, array($this, 'filterData'));
        if ($valuesOnly) {
            $array = array_values($array);
        }
        return $array;
    }

    private function cleanUrl($url)
    {
        return preg_replace('/;jsessionid=[^&?]+/', '', utf8_encode(trim($url)));
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

//        print_r($queryParameters);
//        print_r($url);
//        exit();
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