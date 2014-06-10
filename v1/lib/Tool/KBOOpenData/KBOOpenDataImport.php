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


    protected function handleToolSubmit()
    {
        // Retrieve the $_FILES info for the upload (throws errors if something is wrong)
        $uploadedFileInfo = $this->fetchFileInfo('dataZipUpload');

        $filesToDelete = array();

        // fetch mime type
        $mimeType = $uploadedFileInfo['type'];

        // add to delete files when all is done
        $filesToDelete[$uploadedFileInfo['name']] = $uploadedFileInfo['tmp_name'];

        // check if valid archive
        if (!in_array($mimeType, array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed'))) {
            $this->deleteFiles($filesToDelete);
            throw new \Exception('File not valid archive: ' . $mimeType);
        }


        // define locations for extracted files (assume tmpStoragePath ends with seperator) @todo: check it?
        $tmpPath = $this->slim->environment()['tmpStoragePath'];
        $newFilePrefix = date('YmdHis_');

        // load csv file references from zip
        $csvFileLocations = $this->extractCSVFilesFromZip($uploadedFileInfo['tmp_name'], $tmpPath, $newFilePrefix);
        $filesToDelete += $csvFileLocations;

        print "<pre>";
        $this->checkCreateDatabase();

        $this->handleCSVFiles($csvFileLocations);

        // delete the files (clean up)
        $this->deleteFiles($filesToDelete);


        print_r($this->getErrors());
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

        print 'num records: ' . $rowCount    . nl2br(PHP_EOL);

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

        require_once('pclzip.lib.php');
        $archive = new PclZip($zipFilePath);

        $list  =  $archive->listContent();

//        print_r($archive);
        print_r($zipFilePath);
        print_r($list);
        print_r($archive->errorInfo(true));
        exit();


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
            $fp = fopen($zipFilePath,'rb');


            throw new \RuntimeException('Could not open archive file: ' . $zipFilePath. ' fopen result:'. $fp);
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


}