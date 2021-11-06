<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/store/newPhoto.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="/views/js/utils.js"></script>
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<div id="formWrap">
    <form action="" method="post" enctype="multipart/form-data">
        <h3>НОВАЯ ПУБЛИКАЦИЯ</h3>
        <div id="newPhotoOption">
            <input type="file" name="newPhotoRaw" id="newPhotoRaw" onchange="uploadFile(this, 'newPhotoRawLabel')" required>
            <label for="newPhotoRaw" id="newPhotoRawLabel">ВЫБРАТЬ RAW</label>
        </div>
        <div id="newPhotoOption2">
            <input type="file" name="newPhotoJpg" id="newPhotoJpg" onchange="uploadFile(this, 'newPhotoJpgLabel')" required>
            <label for="newPhotoJpg" id="newPhotoJpgLabel">ВЫБРАТЬ JPG</label>
        </div>
        <div id="newPhotoOption3">
            <input type="file" name="newPhotoJpgCompressed" id="newPhotoJpgCompressed" onchange="uploadFile(this, 'newPhotoJpgCompressedLabel')" required>
            <label for="newPhotoJpgCompressed" id="newPhotoJpgCompressedLabel">ВЫБРАТЬ JPG (СЖАТЫЙ)</label>
        </div>
        <select name="newPhotoCategory" id="categorySelector" class="option" required>
            <?php foreach ($categoriesList as $category): ?>
                <option value="<?php echo($category->getID()); ?>"><?php echo($category->getNameRu()); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="newPhotoPrice" min="0" placeholder="Цена" class="option" id="newPhotoPrice" required>
        <textarea name="newPhotoDescription" id="descriptionArea" class="option" placeholder="Описание" required></textarea>
        <div id="photoPrivacy">
            <input type="checkbox" name="newPhotoPrivacy" value="1" id="privacy" checked>
            <label for="privacy">ПОКАЗЫВАТЬ ВСЕМ</label>
        </div>
        <input type="submit" name="submitNewPhoto">
    </form>
</div>
</body>
</html>