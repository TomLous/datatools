<?php

//$list = \Tool\KBOOpenData\KBOOpenDataImport::getOpenDataFileList();

$list = array();

$formAction = fileUploadUrl();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">

    <!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <!-- Important stuff for SEO, don't neglect. (And don't dupicate values across your site!) -->
    <title>Import KBO Open Data</title>

    <!-- Use Iconifyer to generate all the favicons and touch icons you need: http://iconifier.net -->
    <link rel="shortcut icon" href="/favicon.ico" />

    <!-- concatenate and minify for production -->
    <link rel="stylesheet" href="/css/reset.css" />
    <link rel="stylesheet" href="/css/style.css" />
</head>

<body>

<div class="wrapper">
    <!--
    <h2>Upload KBO Open Data.zip</h2>
    <p>Upload Files obtaind from KBO Open Data</p>
    <p>Login <a href="https://kbopub.economie.fgov.be/kbo-open-data/login" target="_blank">here</a> and download appropriate zip file (either complete db or update)</p>
    <form action="<?=$formAction;?>" id="importForm" name="importForm" enctype="multipart/form-data" method="post">
        <input type="hidden" name="uploadType" value="zip">
        <label for="dataZipUpload">KBO Data zip-file</label>
        <input type="file" name="dataZipUpload" id="dataZipUpload" accept=".zip">
        <input type="submit">
    </form>

    <p style="height: 80px"></p>
    <h2>Select KBO Open Data.zip</h2>
    <p>Or select from list</p>
    <table style="width: 100%">
        <tr><th>Date</th><th>Full file</th><th>Update File</th></tr>
        <?php foreach($list as $record){ ?>
            <tr>
            <td><?=$record['year'].' - '. $record['month'];?></td>
            <td><a href="?action=downloadOpenDataFile&url=<?=$record['fullFileUrl'];?>&filename=<?=$record['fullFileName'];?>" title="<?=$record['fullFileName'];?>"><?=substr($record['fullFileName'], strrpos($record['fullFileName'],'_')+1);?></a></td>
            <td><a href="?action=downloadOpenDataFile&url=<?=$record['updateFileUrl'];?>&filename=<?=$record['updateFileName'];?>" title="<?=$record['updateFileName'];?>"><?=substr($record['updateFileName'], strrpos($record['fullFileName'],'_')+1);?></a></td>
            </tr>

        <?php } ?>
    </table>
    -->

    <p style="height: 80px"></p>
    <h2>Upload csv files manually</h2>
    <form action="<?=$formAction;?>" id="importForm" name="importForm" enctype="multipart/form-data" method="post">
        <input type="hidden" name="uploadType" value="csv">
        <label for="dataCSVUpload">KBO Data csv-files</label>
        <input type="file" name="dataCSVUpload[]" id="dataCSVUpload"  accept=".csv" multiple>
        <input type="submit">
    </form>

</div>


</body>
</html>