<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/account/logIn.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/account/failed.js"></script>
</head>
<body>
<?php require(ROOT.'views/templates/header/header.php'); ?>
<div id="logInFormWrap">
    <div id="logInForm" class="selectedForm">
        <h5>ВХОД</h5>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Электронная почта" autocomplete="off">
            <input type="password" name="password" placeholder="Пароль" autocomplete="off">
            <input type="submit" name="submitLogInData" value="ВОЙТИ">
        </form>
        <h6>Еще нет аккаунта?<br> Попробуйте <a href="/account/signup">зарегистрироваться</a>!</h6>
    </div>
</div>

</body>
</html>
