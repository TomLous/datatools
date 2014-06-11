<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     26/05/14 10:42
 */

namespace Tool\KBOOpenData;


use R;
use PclZip;


class KBOOpenDataImport extends \Tool\Tool
{
    const tableNamePrefix = 'KBOOpenData_';


    const kboLoginUrl = 'https://kbopub.economie.fgov.be/kbo-open-data/static/j_spring_security_check';
    const kboOpenDataListUrl = 'https://kbopub.economie.fgov.be/kbo-open-data/affiliation/xml/?files';
    const kboOpenDataZipBaseUrl = 'https://kbopub.economie.fgov.be/kbo-open-data/affiliation/xml/';

    private static $KBOLoggedIn = false;
    private static $KBOJSessionId = null;
    private static $KBOUsername = null;
    private static $KBOPassword = null;


    protected function handleToolSubmit()
    {
        $filesToDelete = array();

        try{
            // Retrieve the $_FILES info for the upload (throws errors if something is wrong)
            $uploadedFileInfo = $this->fetchFileInfo('dataZipUpload');



            // fetch mime type
            $mimeType = $uploadedFileInfo['type'];

            // add to delete files when all is done
            $filesToDelete[$uploadedFileInfo['name']] = $uploadedFileInfo['tmp_name'];

            // check if valid archive
            if (!in_array($mimeType, array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed'))) {
                $this->deleteFiles($filesToDelete);
                throw new \Exception('File not valid archive: ' . $mimeType);
            }

            $this->handleZipFile($uploadedFileInfo['tmp_name']);


            // delete the files (clean up)
            $this->deleteFiles($filesToDelete);
        }catch (\Exception $e){
            // delete the files (clean up)
            $this->deleteFiles($filesToDelete);

            throw $e;
        }


        print_r($this->getErrors());
    }

    protected function handleZipFile($zipFilePath)
    {

        $filesToDelete = array();

        try {

            // define locations for extracted files (assume tmpStoragePath ends with seperator) @todo: check it?
            $tmpPath = $this->slim->environment()['tmpStoragePath'];
            $newFilePrefix = date('YmdHis_');

            // load csv file references from zip
            $csvFileLocations = $this->extractCSVFilesFromZip($zipFilePath, $tmpPath, $newFilePrefix);

            $filesToDelete += $csvFileLocations;

            $this->checkCreateDatabase();

            $this->handleCSVFiles($csvFileLocations);
        } catch (\Exception $e) {
            // delete the files (clean up)
            $this->deleteFiles($filesToDelete);

            throw $e;
        }

    }

    private function handleCSVFiles($csvFileLocations)
    {
        foreach ($csvFileLocations as $fileName => $filePath) {
            print_r($fileName);
            if (preg_match("/^(\w+)_insert/is", $fileName, $matches)) {
                $tableName = self::tableNamePrefix . $matches[1];

                $this->loopCSVRecords($filePath, $tableName, 'insertRowIntoTable', true);

            } elseif (preg_match("/^(\w+)_delete/is", $fileName, $matches)) {
                $tableName = self::tableNamePrefix . $matches[1];

                $this->loopCSVRecords($filePath, $tableName, 'deleteRowFromTable', true);

            } elseif (preg_match("/^(\w+)\.csv$/is", $fileName, $matches)) {
                $tableName = self::tableNamePrefix . $matches[1];

                print $tableName . nl2br(PHP_EOL);


                $this->truncateTable($tableName);

                $this->loopCSVRecords($filePath, $tableName, 'insertRowIntoTable', true);
            }

        }
    }

    private function loopCSVRecords($csvFilePath, $tableName, $methodName, $firstLineIsHeader = true)
    {
        $csvFilePointer = fopen($csvFilePath, "r");

        $rowCount = 0;
        if ($csvFilePointer === false) {
            throw new \Exception("Cannot open CSV " . $csvFilePath);
        }

        $header = null;
        while (($rowData = fgetcsv($csvFilePointer, 1000, ",")) !== false) {

            if ($firstLineIsHeader && $rowCount == 0) {
                $header = $rowData;
            } else {
                call_user_func_array(array($this, $methodName), array($rowData, $tableName, $header, $rowCount));
            }

            $rowCount++;

        }

        print 'num records: ' . $rowCount . nl2br(PHP_EOL);

        fclose($csvFilePointer);

    }


    private function insertRowIntoTable($rowData, $tableName, $header = null, $rowNum = 0)
    {
        $colsString = "";
        if ($header !== null) {
            $colsString = " (`" . implode("`, `", $header) . "`) ";
        }

        $insertQuery = "INSERT INTO `{$tableName}` ${colsString} VALUES(" . str_repeat('?,', count($rowData) - 1) . "?);";

        try {
            R::exec($insertQuery, $rowData);
        } catch (\Exception $e) {
            $error['sqlError'] = $e->getMessage();
            $error['query'] = $insertQuery;
            $error['data'] = implode(",", $rowData);
            $error['csvRowNum'] = $rowNum;

            $this->error('Error in query ' . $insertQuery . ' for data ' . $error['data']);
        }
    }

    private function deleteRowFromTable($rowData, $tableName, $header = null, $rowNum = 0)
    {
        if ($header === null) {
            return;
        }

        $parts = array();
        foreach ($header as $field) {
            $parts[] = "`${field} = ?";
        }

        $deleteQuery = "DELETE FROM`{$tableName}` WHERE " . implode(' AND ', $parts);

        try {
            R::exec($deleteQuery, $rowData);
        } catch (\Exception $e) {
            $error['sqlError'] = $e->getMessage();
            $error['query'] = $deleteQuery;
            $error['data'] = implode(",", $rowData);
            $error['csvRowNum'] = $rowNum;

            $this->error('Error in query ' . $deleteQuery . ' for data ' . $error['data']);
        }
    }


    private function truncateTable($tableName)
    {
        try {
            $truncateQuery = "TRUNCATE TABLE `{$tableName}`";
//            $truncateData = array(':tableName' => $tableName);
            R::exec($truncateQuery);
        } catch (\Exception $e) {
            $error['sqlError'] = $e->getMessage();
            $error['query'] = $truncateQuery;
//            $error['data'] = implode(",", $truncateData);


            $this->error('Error in truncating table ' . $tableName);
        }
    }


    /**
     * Extracts CSV-Files from Zip archive to target path (ignore directories) and optionally prefix files
     * @param $zipFilePath
     * @param string $targetPath
     * @param null $newFilePrefix
     * @return array
     * @throws \RuntimeException
     */
    private function extractCSVFilesFromZip($zipFilePath, $targetPath = '/tmp', $newFilePrefix = null)
    {
        $csvFileLocations = array();

        // creat target prefix
        $newFilePathPrefix = $targetPath . $newFilePrefix;

//        require_once('pclzip.lib.php');
//        $archive = new PclZip($zipFilePath);
//
//        $list = $archive->listContent();
//
////        print_r($archive);
//        print_r($zipFilePath);
//        print_r($list);
//        print_r($archive->errorInfo(true));
//        exit();


        $zip = zip_open($zipFilePath);

        if (is_resource($zip)) {
            // loop all entries
            while ($zipEntry = zip_read($zip)) {
                $filePath = zip_entry_name($zipEntry);
                $fileName = basename($filePath);

                // non hidden .csv files only
                if (preg_match('/^[^\.].*\.csv$/is', $fileName)) {

                    if (zip_entry_open($zip, $zipEntry)) {
                        // copy contents to target location
                        $targetFilePointer = fopen($newFilePathPrefix . $fileName, "w+b");

                        while ($data = zip_entry_read($zipEntry)) {
                            fwrite($targetFilePointer, $data);
                        }
                        zip_entry_close($zipEntry);
                        fclose($targetFilePointer);

                        // add to return list
                        $csvFileLocations[$fileName] = $newFilePathPrefix . $fileName;
                    } else {
                        throw new \RuntimeException('Could not extract csv-file: ' . $filePath . ' from archive file: ' . $zipFilePath);
                    }
                }
            }
            zip_close($zip);
        } else {
            throw new \RuntimeException('Could not open archive file: ' . $zipFilePath);
        }

        return $csvFileLocations;
    }

    /**
     * clean up by deleting files passed as array
     * @param $csvFileLocations
     */
    private function deleteFiles($csvFileLocations)
    {
        foreach ($csvFileLocations as $filePath) {
            @unlink($filePath);
        }
    }

    /**
     * loops through all sql files in sub dir and executes them
     */
    private function checkCreateDatabase()
    {
        // get relative SQL dir
        $sqlFilesDir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        $sqlFiles = scandir($sqlFilesDir);
        $sqlFilePaths = array();
        foreach ($sqlFiles as $fileName) {
            if (preg_match('/^[^\.].*\.sql$/is', $fileName)) {
                $sqlFilePaths[$fileName] = $sqlFilesDir . $fileName;
            }
        }

        // sort the files
        ksort($sqlFilePaths);

        foreach ($sqlFilePaths as $sqlFilePath) {
            $sqlData = file_get_contents($sqlFilePath);
            try {
                R::exec($sqlData);
            } catch (\Exception $e) {
                $error['sqlError'] = $e->getMessage();
                $error['dataFile'] = $sqlFilePath;


                $this->error('Error in SQL file ' . $sqlFilePath);
            }

        }


    }

    protected function downloadOpenDataFile($url, $filename)
    {
        if (!self::$KBOLoggedIn) {
            self::getOpenDataLogIn();
        }

        if (!$url || !$filename) {
            $this->error('Missing URL (' . $url . ') or filename (' . $filename . ')');
            return true;
        }


        $storagePath = $this->slim->environment()['storagePath'];
        $zipFilePath = $storagePath . $filename;

        $success = copy($url, $zipFilePath);

        if (!$success) {
            $this->error('Cannot copy URL (' . $url . ') to  path ' . $zipFilePath . ')');
            return true;
        }


        $this->handleZipFile($zipFilePath);


        return false;
    }

    /**
     * Returns an array of open data zip files from https://kbopub.economie.fgov.be/kbo-open-data/
     * @return array
     */
    public static function getOpenDataFileList()
    {
        if (!self::$KBOLoggedIn) {
            self::getOpenDataLogIn();
        }


        $options =
            array("http" =>
                array(
                    "method" => "GET",
                    "header" => "Accept-language: nl\r\n" .
                        "Cookie: JSESSIONID=" . self::$KBOJSessionId . "\r\n",
                )
            );

        $context = stream_context_create($options);
        $result = file_get_contents(self::kboOpenDataListUrl, false, $context);

        preg_match_all('`<td>(?<month>\w+)\s(?<year>\d+)</td>\s+<td><a href="(?<fullFileUri>files[^"]*)[^>]+>(?<fullFileName>[^<]*)</a></td>\s*<td><a href="(?<updateFileUri>files[^"]*)[^>]+>(?<updateFileName>[^<]*)</a></td>`is', $result, $matches, PREG_SET_ORDER);

        if (count($matches) <= 0) {
            self::openDataLogOut();
            return array();
        }


        $returnData = array();

        foreach ($matches as $record) {

            $monthNum = self::localeDateStringToTimestamp("1 {$record['month']} {$record['year']}", 'nl_BE', '%e %B %Y', '%m');
            $returnData[] = array(
                'month' => $monthNum,
                'monthName' => $record['month'],
                'year' => $record['year'],
                'fullFileUrl' => (strlen($record['fullFileName']) > 0 ? self::kboOpenDataZipBaseUrl . $record['fullFileUri'] : ''),
                'fullFileName' => $record['fullFileName'],
                'updateFileUrl' => (strlen($record['updateFileName']) > 0 ? self::kboOpenDataZipBaseUrl . $record['updateFileUri'] : ''),
                'updateFileName' => $record['updateFileName'],
            );
        }

        return $returnData;
    }

    /**
     * convert a local date string to applicable format or timestamp
     * @param $dateString
     * @param $locale
     * @param string $inFormat
     * @param null $outFormat
     * @return int|null|string
     */
    protected static function localeDateStringToTimestamp($dateString, $locale, $inFormat = '%e %B %Y', $outFormat = null)
    {
        $returnValue = null;


        $currentLocale = setlocale(LC_TIME, 0);
        setlocale(LC_TIME, $locale);
        $ftimeArray = strptime($dateString, $inFormat);
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
     * Log in into KBO open data sytem, save sesion id for cookie param
     * @return bool
     */
    protected static function getOpenDataLogIn()
    {
        $postData = array();
        $postData['j_username'] = self::$KBOUsername;
        $postData['j_password'] = self::$KBOPassword;

        $data = http_build_query($postData);

        $options =
            array("http" =>
                array(
                    "method" => "POST",
                    "header" => "Accept-language: nl\r\n" .
                        "Content-type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: " . strlen($data) . "\r\n",
                    "content" => $data,
                    "follow_location" => false
                )
            );

        $context = stream_context_create($options);
        $result = file_get_contents(self::kboLoginUrl . '?' . $data, false, $context);


        $matches = preg_grep('/^Set-Cookie: /is', $http_response_header);


        $sessionCookieHeaders = preg_grep('/JSESSIONID/is', $matches);

        if (!$sessionCookieHeaders || count($sessionCookieHeaders) == 0) {
            self::openDataLogOut();
            return false;
        }

        $cookieHeader = current($sessionCookieHeaders);

        preg_match('/JSESSIONID=([^;]+)/is', $cookieHeader, $sessionData);

        if (!$sessionData || count($sessionData) < 1) {
            self::openDataLogOut();
            return false;
        }

        //Set-Cookie: JSESSIONID=xxxxx.worker4b; Path=/kbo-open-data/; HttpOnly

        self::$KBOJSessionId = $sessionData[1];
        self::$KBOLoggedIn = true;

        return true;

    }

    /**
     * Log out of KBO open Data syetm
     */
    protected static function openDataLogOut()
    {
        self::$KBOLoggedIn = false;
        self::$KBOJSessionId = null;
    }


    /**
     * Needed for setting static properties from environment
     * @param string $KBOPassword
     */
    public static function setKBOPassword($KBOPassword)
    {
        self::$KBOPassword = $KBOPassword;
    }

    /**
     * Needed for setting static properties from environment
     * @param string $KBOUsername
     */
    public static function setKBOUsername($KBOUsername)
    {
        self::$KBOUsername = $KBOUsername;
    }

}