<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/store/download.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>

<div id="statusWrap">
    <?php if ($link->getBundle() == 'JPG' || $link->getBundle() == 'JPGRAW'): ?>
        <form action="" method="post" id="downloadJPG">
            <input type="hidden" name="bundle" value="JPG">
            <input type="submit" name="submit" value="Скачать JPG">
        </form>
    <?php endif;?>
    <?php if ($link->getBundle() == 'RAW' || $link->getBundle() == 'JPGRAW'): ?>
        <form action="" method="post" id="downloadRAW">
            <input type="hidden" name="bundle" value="RAW">
            <input type="submit" name="submit" value="Скачать RAW">
        </form>
    <?php endif; ?>
</div>

</body>
</html>
