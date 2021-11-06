<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/store/newCategory.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<div id="formWrap">
    <form action="" method="post">
        <h3>НОВАЯ КАТЕГОРИЯ</h3>
        <input type="text" name="categoryName" id="categoryNameInput" placeholder="Masterpiece..." autocomplete="off">
        <input type="text" name="categoryNameRu" id="categoryNameRuInput" placeholder="Шедевр..." autocomplete="off">
        <input type="submit" value="ДОБАВИТЬ" id="submitButton" name="submitCategory">
    </form>
</div>
</body>
</html>