<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/account/activation.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<div id="statusWrap">
    <h1><?php if($success): ?>АККАУНТ АКТИВИРОВАН<?php else: ?>ОШИБКА АКТИВАЦИИ<?php endif; ?></h1>
</div>

</body>
</html>