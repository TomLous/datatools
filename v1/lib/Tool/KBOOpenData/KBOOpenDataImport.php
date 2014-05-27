<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     26/05/14 10:42
 */

namespace Tool\KBOOpenData;
use R;


class KBOOpenDataImport extends \Tool\Tool
{


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

        $this->checkCreateDatabase();

        print_r($csvFileLocations);
        // @todo import csv files into database


        // delete the files (clean up)
        $this->deleteFiles($filesToDelete);
    }

    /**
     * Extracts CSV-Files from Zip archive to target path (ignore directories) and optionally prefix files
     * @param $zipFilePath
     * @param string $targetPath
     * @param null $newFilePrefix
     * @return array
     * @throws \RuntimeException
     */
    private function extractCSVFilesFromZip($zipFilePath, $targetPath='/tmp', $newFilePrefix=null)
    {
        $csvFileLocations = array();

        // creat target prefix
        $newFilePathPrefix = $targetPath . $newFilePrefix;


        $zip = zip_open($zipFilePath);
        if ($zip)
        {
            // loop all entries
            while ($zipEntry = zip_read($zip))
            {
                $filePath = zip_entry_name($zipEntry);
                $fileName = basename($filePath);

                // non hidden .csv files only
                if(preg_match('/^[^\.].*\.csv$/is', $fileName)){

                    if(zip_entry_open($zip, $zipEntry)){
                        // copy contents to target location
                        $targetFilePointer = fopen($newFilePathPrefix.$fileName, "w+b");

                        while($data = zip_entry_read($zipEntry)){
                            fwrite($targetFilePointer, $data);
                        }
                        zip_entry_close($zipEntry);
                        fclose($targetFilePointer);

                        // add to return list
                        $csvFileLocations[$fileName] = $newFilePathPrefix.$fileName;
                    }else{
                        throw new \RuntimeException('Could not extract csv-file: '.$filePath.' from archive file: '.$zipFilePath);
                    }
                }
            }
            zip_close($zip);
        }else{
            throw new \RuntimeException('Could not open archive file: '.$zipFilePath);
        }

        return $csvFileLocations;
    }

    /**
     * clean up by deleting files passed as array
     * @param $csvFileLocations
     */
    private function deleteFiles($csvFileLocations){
        foreach($csvFileLocations as $filePath){
            @unlink($filePath);
        }
    }

    private function checkCreateDatabase(){
        // get relative SQL dir
        $sqlFilesDir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR .'sql'.DIRECTORY_SEPARATOR;
        $sqlFiles = scandir($sqlFilesDir);
        $sqlFilePaths = array();
        foreach($sqlFiles as $fileName){
            if(preg_match('/^[^\.].*\.sql$/is', $fileName)){
                $sqlFilePaths[$fileName] = $sqlFilesDir.$fileName;
            }
        }

        // sort the files
        ksort($sqlFilePaths);

        foreach($sqlFilePaths as $sqlFilePath){
            $sqlData = file_get_contents($sqlFilePath);
            R::exec($sqlData);
        }


    }


}