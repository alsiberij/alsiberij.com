<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/store/photo.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/js/utils.js"></script>
    <script src="/views/js/ajax.js"></script>
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<div id="mainWrap">
    <div class="pictureWrap" >
        <div id="pictureAndInfo">
            <div id="authorAndDate">
                <div id="avatarAndNickname">
                    <img src="/data/account/avatars/<?php echo($photo->getAuthor()->getAvatar()); ?>" class="icon2">
                    <a href="/account/<?php echo($photo->getAuthor()->getID()); ?>"><?php echo($photo->getAuthor()->getNickname()); ?></a>
                </div>
                <div id="date">
                    <p><?php echo($photo->getPublicationDate()); ?></p>
                </div>
            </div>
            <div id="imgAndDownload">
                <img src="/data/store/<?php echo($photo->getID()); ?>min.jpg" id="mainImg">
                <div id="downloadArea">
                    <span id="downloadLink" onclick="toggleBuyPanel('addToCartForm', 'downloadArea')">
                        <h1>КУПИТЬ</h1>
                    </span>
                </div>
                <div id="addToCartForm">
                    <form action="" method="post" id="addForm">
                        <div class="radios">
                            <div class="buyOption">
                                <input type="radio" name="bundleOption" value="JPG" id="buyJpgOption" checked>
                                <label for="buyJpgOption">
                                    <span>JPG</span>
                                    <span><?php echo($photo->getPrice()); ?><span> $</span></span>
                                </label>
                            </div>
                            <div class="buyOption">
                                <input type="radio" name="bundleOption" value="RAW" id="buyRawOption">
                                <label for="buyRawOption">
                                    <span>RAW</span>
                                    <span><?php echo(round($photo->getPrice() * 1.5, 0, PHP_ROUND_HALF_DOWN)); ?><span> $</span></span>
                                </label>
                            </div>
                            <div class="buyOption">
                                <input type="radio" name="bundleOption" value="JPGRAW" id="buyAllOption">
                                <label for="buyAllOption">
                                    <span>ВСЁ</span>
                                    <span><?php echo($photo->getPrice() * 2); ?><span> $</span></span>
                                </label>
                            </div>
                        </div>
                        <div id="icons2">
                            <img src="/views/img/close.svg" class="icon icon3" onclick="toggleBuyPanel('addToCartForm', 'downloadArea')">
                            <img src="/views/img/next.svg" class="icon3" onclick="location.href = '/store/buy?item=<?php echo($photo->getID()) ?>&bundle='+redirectToBuyPage('addForm')">
                        </div>
                    </form>
                </div>
            </div>
            <div id="descriptionArea">
                <div id="description">
                    <p><?php echo($photo->getDescription()); ?></p>
                </div>
            </div>
            <div id="icons">
                <div id="likesCommentsDownloads">
                    <div id="likes" class="iconWrap" onclick="likePhoto(<?php echo($photo->getID()); ?>)">
                        <img src="/views/img/likeBlack.svg" class="icon" id="likeIconPhoto">
                        <p id="amountOfLikesPhoto"><?php echo($photo->getLikes()); ?></p>
                    </div>
                    <div id="comments" class="iconWrap" onclick="scrollDown()">
                        <img src="/views/img/commentBlack.svg" class="icon">
                        <p><?php echo($photo->getComments()); ?></p>
                    </div>
                    <div id="downloads" class="iconWrap">
                        <img src="/views/img/downloadBlack.svg" class="icon">
                        <p><?php echo($photo->getDownloads()); ?></p>
                    </div>
                </div>
                <div id="views" class="iconWrap">
                    <img src="/views/img/eyeBlack.svg" class="icon">
                    <p><?php echo($photo->getViews()); ?></p>
                </div>
            </div>
            <div id="commentsWrap1">
                <?php foreach ($commentsList as &$comment): ?>
                    <div class="comment">
                        <div class="commentAuthorAndDate">
                            <div class="avatarAndNickname">
                                <img src="/data/account/avatars/<?php echo($comment->getAuthor()->getAvatar()); ?>" class="icon2">
                                <a href="/account/<?php echo($comment->getAuthor()->getID());?>"><?php echo($comment->getAuthor()->getNickname()); ?></a>
                            </div>
                            <div class="commentDate">
                                <p class="time"><?php echo($comment->getPublicationDate()); ?></p>
                                <p><?php echo($comment->getPublicationTime()); ?></p>
                            </div>
                        </div>
                        <div class="commentContent">
                            <p><?php echo($comment->getContent()); ?></p>
                        </div>
                        <div class="dateAndLikes">
                            <div class="commentLikes"
                                 onclick="likeCommentPhoto(<?php echo($comment->getID()); ?>)">
                                <img src="/views/img/likeBlack.svg"
                                     class="icon" id="commentsLikeIcon<?php echo($comment->getID());?>">
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
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
    </script>
    <footer>

    </footer>
</div>

<script>
    function f() {
        <?php if (!$photo->isHorizontal()): ?>
            document.getElementsByClassName('pictureWrap')[0].classList.add('vertical');
        <?php else: ?>
            document.getElementById('mainImg').classList.add('horizontalImg');
        <?php endif; ?>
        <?php if ($photoIsLiked): ?>
            changeLikedState("amountOfLikesPhoto", "likeIconPhoto", true);
        <?php endif; ?>
        <?php foreach ($commentsList as &$comment): ?>
            <?php if(in_array($comment->getID(), $likedComments)): ?>
                changeLikedState("amountOfLikesComment<?php echo($comment->getID()); ?>", "commentsLikeIcon<?php echo($comment->getID()); ?>", true);
            <?php endif;?>
        <?php endforeach; ?>
    }

    window.onload = f;
    window.onunload = f;
</script>
</body>
</html>