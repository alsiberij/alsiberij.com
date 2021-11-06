function likeNews(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=LikeNews&newsID=' + ID, false);
    rq.send();
    console.log(rq.responseText);
    if (rq.status === 200) {
        if (JSON.parse(rq.responseText)["response"] !== undefined) {
            var oldValue = document.getElementById("amountOfLikesNews").innerHTML;
            var newValue = JSON.parse(rq.responseText)["response"];
            changeLikedState("amountOfLikesNews", "newsLikeIcon", newValue > oldValue);
            document.getElementById("amountOfLikesNews").innerHTML = newValue;
        }
    }
}
function likeCommentNews(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=LikeCommentNews&commentID=' + ID, false);
    rq.send();
    console.log(rq.responseText)
    if (JSON.parse(rq.responseText)["response"] !== undefined) {
        var oldValue = document.getElementById("amountOfLikesComment"+ID).innerHTML;
        var newValue = JSON.parse(rq.responseText)["response"];
        changeLikedState("amountOfLikesComment"+ID, "commentsLikeIcon"+ID, newValue > oldValue);
        document.getElementById("amountOfLikesComment"+ID).innerHTML = newValue;
    }
}
function changeNewsPrivacy(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=ChangeNewsPrivacy&newsID=' + ID, false);
    rq.send();
    console.log(rq.responseText);
    if (rq.status === 200 && JSON.parse(rq.responseText)["response"] === 'success') {
        var showOrHideButton = document.getElementById('showOrHide');
        if (showOrHideButton.innerHTML === 'показать') {
            showOrHideButton.innerHTML = 'скрыть';
        } else {
            showOrHideButton.innerHTML = 'показать';
        }
    }
}
function deleteNews(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=DeleteNews&newsID=' + ID, false);
    rq.send();
    console.log(rq.responseText);
    if (rq.status === 200 && JSON.parse(rq.responseText)["response"] != null) {
        location.href = '/news/';
    }
}

function likePhoto(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=LikePhoto&photoID=' + ID, false);
    rq.send();
    console.log(rq.responseText);
    if (rq.status === 200) {
        if (JSON.parse(rq.responseText)["response"] !== undefined) {
            var oldValue = document.getElementById("amountOfLikesPhoto").innerHTML;
            var newValue = JSON.parse(rq.responseText)["response"];
            changeLikedState("amountOfLikesPhoto", "likeIconPhoto", newValue > oldValue);
            document.getElementById("amountOfLikesPhoto").innerHTML = newValue;
        }
    }
}
function likeCommentPhoto(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=LikeCommentPhoto&commentID=' + ID, false);
    rq.send();
    console.log(rq.responseText);
    if (JSON.parse(rq.responseText)["response"] !== undefined) {
        var oldValue = document.getElementById("amountOfLikesComment"+ID).innerHTML;
        var newValue = JSON.parse(rq.responseText)["response"];
        changeLikedState("amountOfLikesComment"+ID, "commentsLikeIcon"+ID, newValue > oldValue);
        document.getElementById("amountOfLikesComment"+ID).innerHTML = newValue;
    }
}
function payOrder(ID, bundle) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=BuyPhoto&photoID='+ID+'&bundle='+bundle, false);
    rq.send();
    console.log(rq.responseText);
    if (rq.status === 200) {
        var response = JSON.parse(rq.responseText);
        if (response['success'] !== undefined) {
            document.getElementById('payButton').style.display = 'none';
            document.getElementById('payMsg2').style.display = 'flex';
            window.location.reload(false);
        } else {
            console.log(rq.responseText);
        }
    }
}

function changeAdminPrivileges(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=SwitchAdminPrivileges&userID='+ID, false);
    rq.send();
    console.log(rq.responseText);
    if (rq.status === 200) {
        var status = JSON.parse(rq.responseText)["response"];
        if (status === undefined) {
            setCheckedStatus('adminPrivileges', false);
        }
    }
}
function changeModerPrivileges(ID) {
    var rq = new XMLHttpRequest();
    rq.open('GET', '/api?method=SwitchModerPrivileges&userID=' + ID, false);
    rq.send();
    console.log(rq.responseText);
    if (rq.status === 200) {
        var status = JSON.parse(rq.responseText)["response"];
        if (status === undefined) {
            setCheckedStatus('moderPrivileges', false);
        }
    }
}
