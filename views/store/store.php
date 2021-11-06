<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/store/store.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/js/utils.js"></script>
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>

<div id="mainWrap">
    <div class="edge"></div>
    <div id="categoriesWrap">
        <div id="categories">
            <h1>Категории</h1>
            <div id="spaceCategories"></div>

            <div class="singleCategory" id="firstCategory">
                <a href="/store">Все</a>
                <p><?php echo($photosAmount); ?></p>
            </div>
            <?php foreach ($categoriesList as $category): ?>
                <?php if (!$category->hasOnlyHiddenPhotos() || ($this->adminPresence() || $this->moderPresence())): ?>
                    <div class="singleCategory">
                        <a href="/store/<?php echo($category->getName()); ?>"><?php echo($category->getNameRu()); ?></a>
                        <p><?php if ($this->adminPresence() || $this->moderPresence()) {echo($category->getPhotos());} else {echo($category->getPublicPhotos());} ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php if ($this->adminPresence() || $this->moderPresence()): ?>
            <a class="addOption" id="addCategory" href="/store/add/category">
                Добавить категорию
            </a>
        <?php endif; ?>
        <?php if ($this->adminPresence() || $this->moderPresence()): ?>
            <a class="addOption" id="addPhoto" href="/store/add/photo">
                Добавить фото
            </a>
        <?php endif; ?>
    </div>
    <div class="edge"></div>
    <div id="catalog">
        <h1>Каталог <span><?php if (isset($selectedCategory)) {echo('/ '.$selectedCategory->getNameRu()); } ?></span></h1>
        <div id="storeWrap">
            <?php $i = 1; foreach ($photosList as &$photo): ?>
                <?php if ($photo->isPublic() || $this->adminPresence() || $this->isModerAndAuthorOf($photo)): ?>
                    <div class="storeElem">
                        <div class="author">
                            <a href="/account/<?php echo($photo->getAuthor()->getID()); ?>"><?php echo($photo->getAuthor()->getNickname()); ?></a>
                        </div>
                        <div class="storeItem animated" id="photoItem<?php echo($photo->getID()); ?>" style="background-image:url('/data/store/<?php echo($photo->getID()) ?>min.jpg');" onclick="location.href = '<?php echo('/store/'.$photo->getCategory()->getName().'/'.$photo->getID()); ?>'"></div>
                        <div class="LCDW">
                            <div class="L LCDWblock" onclick="location.href = '<?php echo('/store/'.$photo->getCategory()->getName().'/'.$photo->getID()); ?>'">
                                <img src="/views/img/like2.svg" class="icon">
                                <p><?php echo($photo->getLikes()); ?></p>
                            </div>
                            <div class="C  LCDWblock" onclick="location.href = '<?php echo('/store/'.$photo->getCategory()->getName().'/'.$photo->getID()); ?>'">
                                <img src="/views/img/comment2.svg" class="icon">
                                <p><?php echo($photo->getComments()); ?></p>
                            </div>
                            <div class="D  LCDWblock" onclick="location.href = '<?php echo('/store/'.$photo->getCategory()->getName().'/'.$photo->getID()); ?>'">
                                <img src="/views/img/download3.svg" class="icon">
                                <p><?php echo($photo->getDownloads()); ?></p>
                            </div>
                            <div class="W  LCDWblock" onclick="location.href = '<?php echo('/store/'.$photo->getCategory()->getName().'/'.$photo->getID()); ?>'">
                                <img src="/views/img/eye2.svg" class="icon">
                                <p><?php echo($photo->getViews()); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php if ($i++ % 3 != 0): ?>
                    <div class="edge"></div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="edge"></div>
</div>
<script>
    function f() {
        <?php foreach ($photosList as &$photo): ?>
            <?php if (!$photo->isPublic() && ($this->adminPresence() || $this->isModerAndAuthorOf($photo))): ?>
                setPrivateStylePhoto("photoItem"+<?php echo($photo->getID()); ?>);
            <?php endif; ?>
        <?php endforeach; ?>
    }
    window.onload = f;
</script>
</body>
</html>