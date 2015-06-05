function captchaRelaod(imageElement) {
    var src = imageElement.src;
    var startIndex = src.indexOf("wp-content");

    if (startIndex != -1) {
        src = src.substr(0, startIndex);
        src = src + "index.php?captchaReload=true";
    }

    var xhr = new XMLHttpRequest();
    xhr.open("GET", src, true);
    xhr.onload = function (e) {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                imageElement.src = xhr.responseText;
            }
        }
    };
    xhr.send(null);
}
