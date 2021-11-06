<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/news/post.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<div id="formWrap">
    <form action="" method="post">
        <h3>НОВАЯ ПУБЛИКАЦИЯ</h3>
        <input type="text" name="newsTitle" id="titleInput" placeholder="Однаждый в далекой-далекой галактике..." autocomplete="off">
        <textarea name="newsContent" id="contentInput" placeholder="Родился необычный малыш. И случилось так, что он попал на землю..."></textarea>
        <div id="checkBoxes">
            <div id="importanceWrap" class="wrappers">
                <input type="checkbox" name="newsIsImportant" id="isImportantInput" value="1">
                <label for="isImportantInput">ВАЖНО</label>
            </div>
            <div id="privacyWrap" class="wrappers">
                <input type="checkbox" name="newsIsPublic" id="isShownInput" value="1" checked>
                <label for="isShownInput">ПОКАЗЫВАТЬ ВСЕМ</label>
            </div>
        </div>
        <input type="submit" value="ОПУБЛИКОВАТЬ" id="submitButton" name="submitNews">
    </form>
</div>
</body>
</html>