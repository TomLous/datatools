<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     26/05/14 10:42
 */

namespace Tool\KBOOpenData;


class KBOOpenDataImport extends \Tool\Tool {


    protected function handleToolSubmit()
    {
        $uploadedFileInfo = $this->fetchFileInfo('dataZipUpload');

        $mimeType = $uploadedFileInfo['type'];

        if(!in_array($mimeType, array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed'))){
            throw new \Exception('File not valid archive: '.$mimeType);
        }

        $zip = zip_open($uploadedFileInfo['tmp_name']);
        if ($zip)
        {
            while ($zipEntry = zip_read($zip))
            {
                print_r($zipEntry);
            }
        }
    }


}