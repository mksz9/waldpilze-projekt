<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title(); ?></title>
    <link href="<?php bloginfo('stylesheet_url'); ?>" rel="stylesheet">
    <?php wp_head(); ?>
  </head>
  <body>
    
  <div class="container-fluid header">
    <div class="container">
      <div class="row">
        <div class="col-sm-6">
          <img src="<?php bloginfo('template_url'); ?>/img/pilz.png">
          <a href="<?php echo home_url(); ?>" class="title"><h1>Waldpilze</h1></a>
          <p>Biologisch jünger und gesünder</p>
        </div>
        <div class="col-sm-6">

        <?php if (has_nav_menu('head')) : ?>
          <nav id="site-navigation" class="main-navigation" role="navigation">
            <?php
              wp_nav_menu( array(
                'theme_location' => 'head',
              ));
            ?>
          </nav>
        <?php endif; ?>
        
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid bodywrapper">
    <div class="container">
      <div class="row content">