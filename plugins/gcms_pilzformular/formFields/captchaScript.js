document.getElementById('captchaImage').onclick = function() {
    var image = document.getElementById('captchaImage');
    image.src = image.src + "?=" + new Date().getTime();
};