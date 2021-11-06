function scrollDown() {
    window.scrollTo(0, document.body.scrollHeight);
}

function setPrivateStyleNews(newsTitleID) {
    document.getElementById(newsTitleID).style.background = 'black';
    document.getElementById(newsTitleID).style.color = 'white';
}

function setPrivateStylePhoto(photoID) {
    document.getElementById(photoID).classList.add("hiddenPhoto");
}

function changeLikedState(counterID, iconID, liked) {
    document.getElementById(counterID).style.color = (liked ? "#f00" : "#000");
    document.getElementById(iconID).src = "/views/img/like" + (liked ? "Red" : "Black") + ".svg";
}

function configureHiddenElementStyle(elemID) {
    document.getElementById(elemID).style.color = 'gray';
}

function uploadFile(target, ID) {
    document.getElementById(ID).innerHTML = target.files[0].name;
}

function toggleBuyPanel(ID, ID2) {
    var elem = document.getElementById(ID);
    var elem2 = document.getElementById(ID2);
    if (elem.style.display === 'flex') {
        elem.style.display = 'none';
        elem2.style.display = 'flex';
    } else {
        elem.style.display = 'flex';
        elem2.style.display = 'none';
    }
}

function setCheckedStatus(elemID, status) {
    document.getElementById(elemID).checked = status;
}

function setValue(elemID, value) {
    document.getElementById(elemID).value = value;
}

function configureAdminOrModerAccountView(avatarID) {
    document.getElementById(avatarID).style.borderRadius = '20px 20px 0 0';
}

function redirectToBuyPage(formID) {
    var form = document.getElementById(formID);
    return form.elements['bundleOption'].value;
}