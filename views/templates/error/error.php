<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/templates/error/error.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>

<div id="statusWrap">
    <h1>НЕ НАЙДЕНО</h1>
    <h2><?php echo($_SERVER['REQUEST_URI']); ?></h2>
</div>

</body>
</html>
