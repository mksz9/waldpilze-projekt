document.getElementById('captchaImage').onclick = function() {
    //alert('test');
    var image = document.getElementById('captchaImage');

    image.src = image.src + "?=" + new Date().getTime();
    //newImage.src = "image.jpg?t=" + new Date().getTime();
   /*var val = document.getElementById('imagename').value,
        src = 'http://webpage.com/images/' + val +'.png',
        img = document.createElement('img');

    img.src = src;
    document.body.appendChild(img);*/
};