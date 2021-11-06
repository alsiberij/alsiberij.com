<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/account/signUp.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<div id="signUpFormWrap">
    <div id="signUpForm" class="selectedForm">
        <h5>РЕГИСТРАЦИЯ</h5>
        <form action="" method="POST">
            <input type="text" name="nickname" placeholder="Имя пользователя" autocomplete="off" maxlength="16">
            <input type="email" name="email" placeholder="Электронная почта" autocomplete="off" maxlength="32">
            <input type="password" name="password1" placeholder="Пароль" autocomplete="off" maxlength="32">
            <input type="password" name="password2" placeholder="Повторите пароль" autocomplete="off" maxlength="32">
            <input type="submit" name="submitSignUpData" value="ЗАРЕГИСТРИРОВАТЬСЯ">
        </form>
        <h6>УЖЕ ЕСТЬ АККАУНТ? ПОПРОБУЙТЕ <a href="/account/login">ВОЙТИ</a>!</h6>
    </div>
</div>

</body>
</html>
