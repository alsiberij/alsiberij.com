<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/news/single.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/js/ajax.js"></script>
    <script src="/views/js/utils.js"></script>
</head>
<body>
<?php require(ROOT . 'views/templates/header/header.php'); ?>
<?php if ($this->adminPresence() || $this->isModerAndAuthorOf($news)): ?>
    <div id="postManageWrap">
        <div id="postManageWrap2">
            <button id="showOrHide"><?php if($news->isPublic()): ?>скрыть<?php else: ?>показать<?php endif; ?></button>
            <button id="delete" >УДАЛИТЬ</button>
        </div>
    </div>
<?php endif; ?>
<div id="mainContainer">
    <div id="newsContent">
        <div id="authorTimeImportance">
            <div id="author" class="headItem">
                <img src="/data/account/avatars/<?php echo($news->getAuthor()->getAvatar()); ?>" class="avatar" id="avatar<?php echo($news->getID()); ?>">
                <a href="/account/<?php echo($news->getAuthor()->getID()); ?>" id="nicknameLink"><?php echo($news->getAuthor()->getNickname()); ?></a>
            </div>
            <div id="importance" class="headItem">
                <?php if ($news->isImportant()): ?>
                    <img src="/views/img/exMark.svg" id="exMark" class="icon">
                <?php endif; ?>
            </div>
            <div id="dateAndTime" class="headItem">
                <div id="dateAndTimeWrapper">
                    <div id="date">
                        <p><?php echo($news->getPublicationTime()); ?></p>
                    </div>
                    <div id="time">
                        <p><?php echo($news->getPublicationDate()); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="titleAndContent">
            <div id="title">
                <h1><?php echo($news->getTitle()); ?></h1>
            </div>
            <div id="content">
                <p><?php echo($news->getContent()); ?></p>
            </div>
        </div>
        <div id="likesCommentsViews">
            <div id="likesAndComments">
                    <div id="likesAmount">
                        <img src="/views/img/likeBlack.svg"
                        class="icon icon2" id="newsLikeIcon">
                        <span id="amountOfLikesNews">
                            <?php echo($news->getLikes()); ?>
                        </span>
                    </div>
                    <div id="likesSpace"></div>
                    <div id="commentsAmount" onclick="window.scrollTo(0, document.body.scrollHeight);">
                        <img src="/views/img/commentBlack.svg" class="icon">
                        <span id="amountOfComments"><?php echo($news->getComments()); ?></span>
                    </div>
            </div>
            <div id="views">
                <img src="/views/img/eyeBlack.svg" id="viewsIcon" class="icon">
                <?php echo($news->getViews()); ?>
            </div>
        </div>
        <div id="comments">
            <?php foreach ($commentsList as $comment): ?>
                <div class="comment" id="comment<?php echo($comment->getID()); ?>">
                    <div class="commentAuthorAndDate">
                        <div class="avatarAndNickname">
                            <img src="/data/account/avatars/<?php echo($comment->getAuthor()->getAvatar()); ?>" class="avatar">
                            <a href="/account/<?php echo($comment->getAuthor()->getID()); ?>"><?php echo($comment->getAuthor()->getNickname()); ?></a>
                        </div>
                        <div class="commentDate">
                            <p class="time"><?php echo($comment->getPublicationTime()); ?></p>
                            <p><?php echo($comment->getPublicationDate()); ?></p>
                        </div>
                    </div>
                    <div class="commentContent">
                        <p><?php echo($comment->getContent()); ?></p>
                    </div>
                    <div class="dateAndLikes">
                        <div class="commentLikes">
                            <img src="/views/img/likeBlack<?php ?>.svg"
                            class="icon icon2" id="commentsLikeIcon<?php echo($comment->getID());?>">
                            <span id="amountOfLikesComment<?php echo($comment->getID()); ?>">
                                <?php echo($comment->getLikes()); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="commentFormWrapper">
            <?php if (!$this->isAuthorized()): ?>
                <h2>Вы должны&nbsp;<a href="/account/login">ВОЙТИ</a>&nbsp;чтобы иметь возможность комментировать</h2>
            <?php else: ?>
                <form action="" method="POST" id="commentForm">
                    <div id="textareaAndButton">
                        <textarea name="commentContent"></textarea>
                        <input type="submit" name="submitComment" value="ОПУБЛИКОВАТЬ">
                    </div>
                    <div class="headerSpace"></div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    function f() {
        <?php if ($this->adminPresence() || $this->isModerAndAuthorOf($news)): ?>
            document.getElementById('showOrHide').onclick = function () {
                changeNewsPrivacy(<?php echo($news->getID()); ?>);
            };
            document.getElementById('delete').onclick = function () {
                deleteNews(<?php echo($news->getID()); ?>);
            }
        <?php endif; ?>

        document.getElementById('likesAmount').onclick = function () {
            likeNews(<?php echo($news->getID()); ?>);
        }

        <?php if ($newsIsLiked): ?>
            changeLikedState('amountOfLikesNews', 'newsLikeIcon', true);
        <?php endif; ?>

        <?php foreach ($commentsList as $comment): ?>
            document.getElementById('comment<?php echo($comment->getID()); ?>')
                .getElementsByClassName('commentLikes')[0].onclick = function () {
                likeCommentNews('<?php echo($comment->getID()); ?>');
            }
            <?php if ($this->isAuthorized() && in_array($comment->getID(), $likedComments)): ?>
                changeLikedState('amountOfLikesComment<?php echo($comment->getID()); ?>', 'commentsLikeIcon<?php echo($comment->getID()); ?>', true);
            <?php endif; ?>
        <?php endforeach; ?>

    }
    window.onload = f;
</script>
</body>
</html>
