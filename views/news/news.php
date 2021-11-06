<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/news/news.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/js/utils.js"></script>
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<?php if ($this->adminPresence() || $this->moderPresence()): ?>
    <div id="newPostWrap">
        <a href="/news/add" id="newPostLink">СОЗДАТЬ</a>
    </div>
<?php endif; ?>
<div id="newsContainer">
    <div id="newsContainerCenter">
        <?php foreach ($newsList as $news): ?>
            <?php if ($news->isPublic() || $this->adminPresence() || $this->isModerAndAuthorOf($news)): ?>
                <div class="shortNews" id="news<?php echo($news->getID()); ?>>">
                    <div class="authorTimeImportance">
                        <div class="author infoItem">
                            <img src="/data/account/avatars/<?php echo($news->getAuthor()->getAvatar()); ?>" class="avatar">
                            <a href="/account/<?php echo($news->getAuthor()->getID()); ?>">
                                <?php echo($news->getAuthor()->getNickname()); ?>
                            </a>
                        </div>
                        <div class="time infoItem">
                            <h3><?php echo($news->getPublicationDate()); ?></h3>
                        </div>
                    </div>
                    <div class="title">
                        <a href="/news/<?php echo($news->getID()); ?>" id="newsTitle<?php echo($news->getID()); ?>"><?php echo($news->getTitle()); ?></a>
                    </div>
                    <div class="likesCommentsViews">
                        <div class="likesAndComments">
                            <div class="likesSection" onclick="location.href = '/news/<?php echo($news->getID()); ?>'">
                                <img src="/views/img/likeBlack.svg" class="icon">
                                <?php echo($news->getLikes()); ?>
                            </div>
                            <div class="likesSpace"></div>
                            <div class="commentsSection" onclick="location.href = '/news/<?php echo($news->getID()); ?>'">
                                <img src="/views/img/commentBlack.svg" class="icon">
                                <?php echo($news->getComments()); ?>
                            </div>
                        </div>
                        <div class="views">
                            <img src="/views/img/eyeBlack.svg" class="icon">
                            <?php echo($news->getViews()); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<script>
    function f() {
        <?php foreach ($newsList as $news): ?>
            <?php if (!$news->isPublic() && ($this->adminPresence() || $this->isModerAndAuthorOf($news))): ?>
                setPrivateStyleNews("newsTitle"+<?php echo($news->getID()); ?>);
            <?php endif; ?>
        <?php endforeach; ?>
    }
    window.onload = f;
</script>
</body>
</html>