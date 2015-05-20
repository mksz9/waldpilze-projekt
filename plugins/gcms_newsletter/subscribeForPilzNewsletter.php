<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title></title>
</head>
<body>

    <?php if(!isset($_POST['email'])) { ?>
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
        <h1>Enter your Email for PilzNewsletter-Registration</h1>
            <input type="text" name="email" maxlength="30">
            <button type="reset">Eingaben zurücksetzen</button>
            <button type="submit">Eingaben absenden</button>
        </form>
    <?php } else { ?>
        <p>successfully subscribed for newsletter</p>

        <?php



        include_once('C:\xampp\htdocs\wordpress_waldpilze\wp-includes\plugin.php');
        do_action('newSubscribe');
        ?>
    <?php } ?>

</body>
</html>














<!--<form action="http://localhost/wordpress_waldpilze/wp-content/plugins/gcms_newsletter/subscribe.php">-->