<?php

error_reporting(E_ERROR | E_PARSE);

session_start();
date_default_timezone_set("Canada/Eastern");

require '../vendor/autoload.php';
require '../lib/config.php';
require 'controller/VoteController.php';
require 'controller/ElectionController.php';

Epi::init('api');
Epi::init('route','session-php');
Epi::init('database');
Epi::setSetting('exceptions', true);

EpiDatabase::employ(RBEConfig::DB_TYPE, RBEConfig::DB_NAME, RBEConfig::DB_HOST, RBEConfig::DB_USER, RBEConfig::DB_PASS);
rbeinit();

getRoute()->get('/', 'home');
getRoute()->get('/vote/', array('VoteController','vote'));
getRoute()->get('/vote/start', array('VoteController','start'));
getRoute()->get('/vote/done', array('VoteController','done'));
getRoute()->get('/vote/save/(\d+)', array('VoteController','save'));
getRoute()->get('.*', 'error404');
getRoute()->run();

function rbeinit() {
  EpiDatabase::employ(RBEConfig::DB_TYPE, RBEConfig::DB_NAME, RBEConfig::DB_HOST, RBEConfig::DB_USER, RBEConfig::DB_PASS);
}

function home() {
  top('Ranked Ballot Engine');
  ?>
  <div class="row">
  <div class="center col-sm-4">
  <h2>Vote Now!</h2>
  <a href="<?php print RBEConfig::WWW; ?>/vote/">Vote now!</a>
  </div>
  <div class="center col-sm-4">
  <h2>Election Results</h2>
  See the results.
  </div>
  <div class="center col-sm-4">
  <h2>Learn More</h2>
  <a href="http://ottawa123.ca">Learn more at ottawa123.ca</a>
  </div>
  </div>
  <?php
  bottom();
}

function error404() {
  ?>
  Page not found.
  <?php
}

function top($title = '') {
  ?>
  <html>
  <head>
  <title><?php print $title; ?></title>
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css"/>
  <link rel="stylesheet" href="/style.css"/>
  <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  </head>
  <body>
  <div class="center jumbotron"><?php print $title; ?></div>
  <div id="content">
  <?php
}

function bottom() {
  ?>
  </div><!-- #content -->
  </body>
  </html>
  <?php
}

function pr($o) {
  print "<pre>";
  print print_r($o);
  print "</pre>";
}

