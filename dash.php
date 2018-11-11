
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="author" content="NXT">
    <link rel="icon" href="../../favicon.ico">

    <title>ScrollOut Admin</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300i,400,400i,700" rel="stylesheet">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" 	crossorigin="anonymous">
  </head>

  <body>

    <!--<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div>
      </div>
    </nav>-->

    <div class="container">

      <div class="dash_logo">
      	<img src="assets/img/logo_v3.png" style="max-height: 100%"/>
      </div>

      <div class="dash_contents">

        <div class="dash_item" onClick="goTo('connection_nxt.php');">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-2 text-center-xs">
            	   <i class="fa dash_item_img fa-plug" aria-hidden="true"></i>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-10">
                	<div class="dash_item_title">
                		CONNECT
                	</div>
                    <div class="dash_item_description">
                        Some 2 rows short details here.
                    </div>
                </div>
            </div>
        </div>

        <div class="dash_item" onClick="goTo('traffic_nxt.php');">
        	<div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-2 text-center-xs">
                   <i class="fa dash_item_img fa-exchange" aria-hidden="true"></i>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-10">
                    <div class="dash_item_title">
                        ROUTE
                    </div>
                    <div class="dash_item_description">
                        Some 2 rows short details here.
                    </div>
                </div>
            </div>
        </div>

        <div class="dash_item" onClick="goTo('security_nxt.php#/security');">
        	<div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-2 text-center-xs">
                   <i class="fa dash_item_img fa-shield" aria-hidden="true"></i>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-10">
                    <div class="dash_item_title">
                        SECURE
                    </div>
                    <div class="dash_item_description">
                        Some 2 rows short details here.
                    </div>
                </div>
            </div>
        </div>

        <div class="dash_item" onClick="goTo('collector_nxt.php');">
        	<div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-2 text-center-xs">
                   <i class="fa dash_item_img fa-recycle" aria-hidden="true"></i>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-10">
                    <div class="dash_item_title">
                        COLLECT
                    </div>
                    <div class="dash_item_description">
                        Some 2 rows short details here.
                    </div>
                </div>
            </div>
        </div>

        <div class="dash_item" onClick="goTo('monitor_nxt.php#/monitor');">
        	<div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-2 text-center-xs">
                   <i class="fa dash_item_img fa-eye" aria-hidden="true"></i>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-10">
                    <div class="dash_item_title">
                        MONITOR
                    </div>
                    <div class="dash_item_description">
                        Some 2 rows short details here.
                    </div>
                </div>
            </div>
        </div>

        <div class="dash_toggle_night_mode">
            Night mode: <input type="checkbox" name="hasBigImage" class="js-switch" id="nightmode_toggle" onChange="toggleNightmode(this);" />
        </div>
      </div>

    </div><!-- /.container -->


    <footer class="footer">
    Copyright 2017 ScrollOutF1. All rights reserved.
    </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="assets/js/ie10-viewport-bug-workaround.js"></script>

    <!-- Switchery -->
    <script src="assets/switchery/dist/switchery.min.js"></script>
    <link href="assets/switchery/dist/switchery.min.css" rel="stylesheet">

    <script src="assets/js/script.js"></script>

</html>
