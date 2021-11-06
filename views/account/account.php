<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/account/account.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/js/utils.js"></script>
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>

<?php if ($this->adminPresence()): ?>
    <div id="changeBalanceWrap">
        <form id="changeBalanceForm" action="" method="post">
            <div id="radios">
                <input type="radio" name="changeBalance" value="+" id="depositMoneyRadio" checked>
                <label for="depositMoneyRadio">ПОПОЛНИТЬ</label>
                <input type="radio" name="changeBalance" value="-" id="withdrawMoneyRadio">
                <label for="withdrawMoneyRadio">СНЯТЬ</label>
            </div>
            <input type="number" name="value" placeholder="Количество" min="0">
            <input type="submit" name="submitChangeBalance" value="ВЫПОЛНИТЬ">
        </form>
    </div>
<?php endif; ?>

<?php if ($isAllowedToWatchProfile): ?>
    <div id="profileWrap">
        <div id="profileAndContact">
            <div id="profile">
                <div id="primaryInfoAndAvatar">
                    <div id="avatarAndEdit">
                        <img src="/data/account/avatars/<?php echo($account->getAvatar()) ?>" id="avatar">

                        <?php if ($account->isAdmin()): ?>
                            <div id="adminSign">
                                АДМИНИСТРАТОР
                            </div>
                        <?php elseif ($account->isModer()): ?>
                            <div id="moderSign">
                                МОДЕРАТОР
                            </div>
                        <?php endif; ?>
                        <?php if ($account->getBio() != ''): ?>
                            <p><?php echo($account->getBio()); ?></p>
                        <?php endif; ?>

                        <?php if ($isAllowedToModify): ?>
                            <a href="/account/<?php echo($account->getID()); ?>/manage" id="editButton">НАСТРОЙКИ</a>
                        <?php endif; ?>

                        <?php if ($this->authorizedUserIsOwnerOf($account)): ?>
                            <a href="/account/logout">ВЫЙТИ</a>
                        <?php endif; ?>
                    </div>
                    <div id="primaryInfo">
                        <div id="nicknameAndLocation">
                            <h1 id="ownerNickname">
                                <?php echo($account->getNickname()); ?><?php if ($account->getAge() != ''): ?>, <?php echo($account->getAge()); ?>
                                <?php endif; ?>
                            </h1>
                            <div id="location">
                                <img src="/views/img/location2Grey.svg" class="icon" id="locationIcon">
                                <h2><?php echo($account->getLocation()); ?></h2>
                            </div>
                        </div>

                        <div id="activity" class="dataOption">
                            <h3 class="dataOptionLabel">Активность</h3>
                            <div id="likesAndComments" class="dataOptionValue">
                                <h3 id="likesLeft"><img src="/views/img/likeBlack.svg" class="icon2"><?php echo($account->getLikes()); ?></h3>
                                <h3 id="commentsLeft"><img src="/views/img/commentBlack.svg" class="icon2"><?php echo($account->getComments()); ?></h3>
                                <h3 id="downloads"><img src="/views/img/downloadBlack.svg" class="icon2"><?php echo($account->getDownloads()); ?></h3>
                            </div>
                        </div>
                        <div id="balance" class="dataOption">
                            <h3 class="dataOptionLabel">Баланс</h3>
                            <h3 class="dataOptionValue" id="ownerBalance">
                                <?php if ($isAllowedToWatchBalance): ?>
                                    <?php echo($account->getBalance()); ?>&nbsp;$
                                <?php else: ?>
                                    СКРЫТО
                                <?php endif; ?>
                            </h3>
                        </div>
                        <div id="email" class="dataOption">
                            <h3 class="dataOptionLabel">Электронная почта</h3>
                            <h3 class="dataOptionValue" id="ownerEmail">
                                <?php if ($isAllowedToWatchEmail): ?>
                                    <?php echo($account->getEmail()); ?>
                                <?php else: ?>
                                    СКРЫТО
                                <?php endif; ?>
                            </h3>
                        </div>
                        <div id="dateOfRegistration" class="dataOption">
                            <h3 class="dataOptionLabel">Дата регистрации</h3>
                            <h3 class="dataOptionValue"><?php echo($account->getDateOfRegistration()); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else:?>
    <div id="privateProfileMessage">
        <h1>ЭТОТ ПРОФИЛЬ СКРЫТ</h1>
    </div>
<?php endif;?>
<script>
    function f() {
        <?php if ($isAllowedToWatchProfile): ?>
            <?php if ($account->isAdmin() || $account->isModer()): ?>
                configureAdminOrModerAccountView('avatar');
            <?php endif; ?>
            <?php if (!$account->isPublicEmail()): ?>
                configureHiddenElementStyle('ownerEmail');
            <?php endif; ?>
            <?php if (!$account->isPublicBalance()): ?>
                configureHiddenElementStyle('ownerBalance');
            <?php endif; ?>
            <?php if (!$account->isPublicAccount()): ?>
                configureHiddenElementStyle('ownerNickname');
            <?php endif; ?>
        <?php endif; ?>
    }
    window.onload = f;
</script>
</body>
</html>
