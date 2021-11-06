<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALSIBERIJ</title>
    <link rel="stylesheet" href="/views/templates/base.css">
    <link rel="stylesheet" href="/views/store/cart.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/views/js/ajax.js"></script>
    <script src="/views/js/utils.js"></script>
</head>
<body>
<?php require(ROOT . 'views/templates/header/header.php'); ?>
<div id="mainContainer">
    <div id="mainWrap">
        <div id="cartText">
            <h3>КОРЗИНА</h3>
        </div>
        <div id="balance">
            Ваш баланс: <b><?php echo($this->getAuthorizedUser()->getBalance()); ?></b><span>&nbsp;$</span>
        </div>
        <div id="cartItem">
            <div id="preview">
                <img src="/data/store/<?php echo($photo->getID()); ?>min.jpg" id="imgPreview">
            </div>
            <div id="bundle">
                <?php echo($bundle); ?>
            </div>
            <div id="description">
                <p><?php echo($photo->getDescription()); ?></p>
            </div>
            <div id="price">
                <?php echo($bundlePrice); ?><span>&nbsp;$</span>
            </div>
        </div>
        <div id="buyButton">
            <?php if ($this->bundleAlreadyBought()): ?>
                <p id="payMsg1">Ссылка для скачивания отправлена на&nbsp;<b><?php echo($this->getAuthorizedUser()->getEmail()); ?></b></p>
            <?php elseif ($bundlePrice <= $this->getAuthorizedUser()->getBalance()): ?>
                <button onclick="payOrder(<?php echo($photo->getID().', \''.$bundle.'\''); ?>)" id="payButton">Купить</button>
                <p id="payMsg2">Ссылка для скачивания отправлена на&nbsp;<b><?php echo($this->getAuthorizedUser()->getEmail()); ?></b></p>
            <?php else: ?>
                <h2>Недостаточно денег!</h2>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function f() {
            <?php if ($bundle == 'JPGRAW'): ?>
                document.getElementById('bundle').innerHTML = 'JPG<br>RAW';
            <?php endif; ?>
        }

        window.onload = f;
        window.onunload = f;
    </script>
</div>
</body>
