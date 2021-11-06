<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/account/manage.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/js/utils.js"></script>
    <script src="/views/js/ajax.js"></script>
</head>

<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>

<div id="mainWrap">
    <div id="manageProfile">
        <form action="" method="post" enctype="multipart/form-data">
            <div id="privacySection">
                <div class="manageOption privacyOption">
                    <p>Аккаунт</p>
                    <div class="labels">
                        <div class="labelAndCircle">
                            <input type="radio" name="accountPrivacy" value="1" id="publicProfile">
                            <label for="publicProfile">ВИДЕН ВСЕМ</label>
                        </div>
                        <div class="labelAndCircle">
                            <input type="radio" name="accountPrivacy" value="0" id="privateProfile">
                            <label for="privateProfile">СКРЫТ</label>
                        </div>
                    </div>
                </div>
                <div class="manageOption privacyOption">
                    <p>Баланс</p>
                    <div class="labels">
                        <div class="labelAndCircle">
                            <input type="radio" name="balancePrivacy" value="1" id="publicBalance">
                            <label for="publicBalance">ВИДЕН ВСЕМ</label>
                        </div>
                        <div class="labelAndCircle">
                            <input type="radio" name="balancePrivacy" value="0" id="privateBalance">
                            <label for="privateBalance">СКРЫТ</label>
                        </div>
                    </div>
                </div>
                <div class="manageOption privacyOption">
                    <p>Эл. почта</p>
                    <div class="labels">
                        <div class="labelAndCircle">
                            <input type="radio" name="emailPrivacy" value="1" id="publicEmail">
                            <label for="publicEmail">ВИДНА ВСЕМ</label>
                        </div>
                        <div class="labelAndCircle">
                            <input type="radio" name="emailPrivacy" value="0" id="privateEmail">
                            <label for="privateEmail">СКРЫТА</label>
                        </div>
                    </div>

                </div>
            </div>
            <div id="nicknameAndLocationSection">
                <div class="manageOption">
                    <p>Имя пользователя</p>
                    <input type="text" name="newNickname" value="<?php echo($account->getNickname()); ?>" maxlength="16">
                </div>
                <div class="manageOption">
                    <p>Местоположение</p>
                    <input type="text" name="newLocation" value="<?php echo($account->getLocation()); ?>" maxlength="50">
                </div>
            </div>
            <div id="birthdayAndBioSection">
                <div class="manageOption">
                    <p>День рождения</p>
                    <input type="date" name="newBirthday" id="birthday">
                </div>
                <div class="manageOption">
                    <p>Биография</p>
                    <input type="text" name="newBio" value="<?php echo($account->getBio()); ?>" maxlength="256">
                </div>
            </div>
            <div id="avatarSection">
                <div class="manageOptionButton" id="avatarOption">
                    <input type="file" name="newAvatar" id="uploadAvatar" onchange="uploadFile(this, 'avatarLabel')">
                    <label for="uploadAvatar" id="avatarLabel">НОВЫЙ АВАТАР</label>
                </div>
                <div class="manageOptionButton" id="avatarDeleteOption">
                    <input type="checkbox" name="avatarDeletion" value="1" id="deleteAvatar">
                    <label for="deleteAvatar">УДАЛИТЬ АВАТАР</label>
                </div>
            </div>
            <?php if ($this->adminPresence()): ?>
                <div id="privilegesSection">
                    <input type="checkbox" id="adminPrivileges">
                    <label for="adminPrivileges" onclick="changeAdminPrivileges(<?php echo($account->getID()); ?>);">АДМИНИСТРАТОР</label>
                    <input type="checkbox" id="moderPrivileges">
                    <label for="moderPrivileges" onclick="changeModerPrivileges(<?php echo($account->getID()); ?>);">МОДЕРАТОР</label>
                </div>
            <?php endif; ?>
            <div id="submitSection">
                <div class="manageOptionButton">
                    <input type="submit" name="submitAccountData" value="СОХРАНИТЬ">
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function f() {
        <?php if ($account->isPublicAccount()): ?>
            setCheckedStatus('publicProfile', true);
        <?php else: ?>
            setCheckedStatus('privateProfile', true);
        <?php endif; ?>
        <?php if ($account->isPublicBalance()): ?>
            setCheckedStatus('publicBalance', true);
        <?php else: ?>
            setCheckedStatus('privateBalance', true);
        <?php endif; ?>
        <?php if ($account->isPublicEmail()): ?>
            setCheckedStatus('publicEmail', true);
        <?php else: ?>
            setCheckedStatus('privateEmail', true);
        <?php endif; ?>
        <?php if ($account->getAge() != ''): ?>
            setValue('birthday', "<?php echo($account->getDateOfBirth()); ?>");
        <?php endif; ?>
        <?php if ($account->isAdmin()): ?>
            setCheckedStatus('adminPrivileges', true);
        <?php endif; ?>
        <?php if ($account->isModer()): ?>
            setCheckedStatus('moderPrivileges', true);
        <?php endif; ?>
    }
    window.onload = f;
</script>
</body>
</html>
