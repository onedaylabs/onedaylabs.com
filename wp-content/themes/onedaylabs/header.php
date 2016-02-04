<!doctype html>

<!--[if IEMobile 7 ]> <html <?php language_attributes(); ?>class="no-js iem7"> <![endif]-->
<!--[if lt IE 7 ]> <html <?php language_attributes(); ?> class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html <?php language_attributes(); ?> class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html <?php language_attributes(); ?> class="no-js ie8"> <![endif]-->
<!--[if (gte IE 9)|(gt IEMobile 7)|!(IEMobile)|!(IE)]><!-->
<html <?php language_attributes(); ?> class="no-js">
<!--<![endif]-->

<head <?php do_action( 'add_head_attributes' ); ?>>
<meta name="google-site-verification" content="SB7-mBnFTujq_8tTGc6ZykJ28TasxQHryhrqsRW3ck4" />
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>
<?php wp_title( '|', true, 'right' ); ?>
</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

<!-- wordpress head functions -->
<?php wp_head(); ?>
<!-- end of wordpress head -->
<!-- IE8 fallback moved below head to work properly. Added respond as well. Tested to work. -->
<!-- media-queries.js (fallback) -->
<!--[if lt IE 9]>
			<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>			
		<![endif]-->

<!-- html5.js -->
<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

<!-- respond.js -->
<!--[if lt IE 9]>
		          <script type='text/javascript' src="http://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.js"></script>
		<![endif]-->
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
</head>

<body <?php body_class(); ?>>
<header role="banner" class="top">
  <div class="container-fluid">
    <div class="col-md-4"> 
      <div class="title logo"> <a href="/"><img src="<?php echo get_stylesheet_directory_uri(); ?>/images/onedaylabs-logo.svg" title="One Day Labs - Collaborative Web Design" class="img-responsive title-img smalllogo"></a> </div>
    </div>
    <div class="col-md-8 no_padding">
      <div class="row main-nav">
        <div class="navbar navbar-default navbar-static-top ">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
          </div>
          <div class="collapse navbar-collapse navbar-responsive-collapse">
            <?php wp_bootstrap_main_nav(); // Adjust using Menus in Wordpress Admin ?>
            <div class="cta">
              <?php dynamic_sidebar('cta'); ?>
            </div>
          </div>
        </div>
        
        
      </div>
    </div>
  </div>
  <!-- /container --> 
</header>
<!-- end header --> 

