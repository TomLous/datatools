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
            'page' => '1',
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

//        $xpath = new \DOMXPath($document);
//        $table =$xpath->query("//table")->item(0);
//        $table =$xpath->query("//table")->item(0);

        $returnData['data'] = array();

        // All data in KBO is returned as table, so parse the tables in the returned data
        $tables = $document->getElementsByTagName("table");
        foreach ($tables as $table) {
            $tableData = array();

            // retrieve all table rows for this table
            $rows = $table->getElementsByTagName("tr");
            $isHeaderRow = false;
            foreach ($rows as $row) {
                $rowData = array();

                // retrieve all cells for this row
                $cells = $row->getElementsByTagName('td');
                if ($cells->length == 0) {

                    // when no cells check all th's for this row (this row becomes the header)
                    $cells = $row->getElementsByTagName('th');
                    if($cells->length > 0){
                        $isHeaderRow = true;
                    }
                }

                // loop all cells
                foreach ($cells as $cell) {
                    $cellData = array();

                    // all nested html (cellparts, like spans, div's, etc) in a cell as an array element
                    foreach ($cell->childNodes as $cellPart) {
                        $cellPartData = array();
                        $cellPartData['content'] = trim($cellPart->nodeValue);

                        // a cellpart with interesting (content) attriutes, like href should be included
                        if ($cellPart->attributes && $cellPart->attributes->length > 0 && !$isHeaderRow) {
                            $cellAttributes = array();

                            // loop all attributes for this cell
                            foreach ($cellPart->attributes as $attribute) {
                                if (in_array($attribute->name, array('href'))) { // allowed tags for content
                                    // trim & remove session id from url
                                    $cellAttributes[$attribute->name] = preg_replace('/;jsessionid=[^&?]+/','',utf8_encode(trim($attribute->value)));
                                }
                            }

                            // if interesting cellparts are found add them as attributes
                            if(count($cellAttributes) > 0){
                                $cellPartData['attributes'] = array_filter($cellAttributes, array($this, 'filterData'));
                            }
                        }

                        // set cell data
                        $cellData[] = array_filter($cellPartData, array($this, 'filterData'));
                    }
                    $cellData = array_values(array_filter($cellData, array($this, 'filterData')));

                    // key is next number in line
                    $key = count($rowData);

                    // if its a header row, use the current cellpart as value
                    if($isHeaderRow){
                        $cellData = current($cellData);
                    }
                    // if there is a header for current position
                    elseif(isset($tableData['header'][$key]['content'])){
                        $key = (string) $tableData['header'][$key]['content'];
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
                    $tableData['rows'][] = array_filter($rowData, array($this, 'filterData'));
                }
            }

            // filter the empty rows
            if(isset($tableData['rows'])){
                $tableData['rows'] = array_filter($tableData['rows'], array($this, 'filterData'));
            }

            // filter the empty table properties (eg no header)
            $returnData['data'][] = array_filter($tableData, array($this, 'filterData'));
        }

        // filter the empty tables
        $returnData['data'] = array_values(array_filter($returnData['data'], array($this, 'filterData')));


        // @todo reformat resultset
        // @todo search for pages (for MEta)
        //

//        print_r($returnData);
//        exit();
////        //

        return $returnData;
    }

    /**
     * filter the array values that evaluate to false, except 0! for array_filter()
     * @param $value
     * @return bool
     */
    private function filterData($value)
    {
        return ($value !== null && $value !== false && $value !== '' && (!is_array($value) || count($value)>0));
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