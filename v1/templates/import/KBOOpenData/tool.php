<?php

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
    <p>Upload Files obtaind from KBO Open Data</p>
    <p>Login <a href="https://kbopub.economie.fgov.be/kbo-open-data/login" target="_blank">here</a> and download appropriate zip file (either complete db or update)</p>
    <form action="<?=$formAction;?>" id="importForm" name="importForm" enctype="multipart/form-data" method="post">
        <label for="dataZipUpload">KBO Data zip-file</label>
        <input type="file" name="dataZipUpload" id="dataZipUpload">
        <input type="submit">
    </form>

</div>


</body>
</html>