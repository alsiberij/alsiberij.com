<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/index/index.css">
    <link rel="stylesheet" href="/views/templates/base.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>

<div id="content">
    <ul id="mainMenu">
        <li class="menuItem1" id="item1">
            <img src="/views/img/news.svg" class="icon">
            <a href="/news">НОВОСТИ</a>
        </li>
        <li class="menuItem2" id="item2">
            <img src="/views/img/account.svg" class="icon">
            <a href="/account">АККАУНТ</a>
        </li>
        <li class="menuItem1" id="item3">
            <img src="/views/img/store.svg" class="icon">
            <a href="/store">МАГАЗИН</a>
        </li>
    </ul>
</div>
</body>
</html>