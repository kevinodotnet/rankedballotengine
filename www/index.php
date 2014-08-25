<?php

/* 

Feedback TODO:

- There was feedback that every Round of Voting should have a narrative of what just happened.  For example, after first round -  “Your fourth choice, Benny, was eliminated from the race because he has the least amount of voted, but your first choice, Omar, is still in the race.”
or “Your first choice, Bev, got eliminated this round because she has the fewest votes.  However, your vote will be transferred to your next preferred candidate in the race, Will.


-  Is it possible to highlight the voters current active choice in each round?  And to add to that, maybe bold the candidate(s) who get struck off each round?

- If there is a tie at the end, both candidates are eliminated.  Not quite sure what to do there…maybe at least say tie?  Hopefully it won’t be an issue later down the road when there are a lot more votes.

We had talked about this, but we need to point people to share ie. “Did you like that?”  Click here to share on FB, Twitter, etc.

*/

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
getRoute()->get('/vote/start/(\d+)', array('VoteController','start'));
getRoute()->get('/vote/done', array('VoteController','done'));
getRoute()->get('/vote/ballot/(\d+)', array('VoteController','ballot'));
getRoute()->post('/vote/ballot/(\d+)', array('VoteController','ballot'));
getRoute()->get('/vote/save/(\d+)', array('VoteController','save'));
getRoute()->get('/election/(\d+)/results', array('ElectionController','showResults'));
getRoute()->get('/election/(\d+)/results/since/(\d\d\d\d-\d\d-\d\d)', array('ElectionController','showResults'));

# TODO: authentication for these
getRoute()->get('/election/(\d+)/candidate/add', array('ElectionController','candidateAdd'));
# OFF UNTIL AUTH IS DONE getRoute()->post('/election/(\d+)/candidate/add', array('ElectionController','candidateAdd'));

# CATCH all and route
getRoute()->get('.*', 'error404');
getRoute()->run();

function rbeinit() {
  EpiDatabase::employ(RBEConfig::DB_TYPE, RBEConfig::DB_NAME, RBEConfig::DB_HOST, RBEConfig::DB_USER, RBEConfig::DB_PASS);
}

function home() {
  top('Ranked Ballot Engine');
  bottom();
}

function footer($electionid) {
	if ($electionid == '') { $electionid = 1; }
  ?>
  <div class="row" style="margin-top: 20px; background: #f0f0f0; padding-bottom: 10px;">
  <div class="center col-sm-4">
  <h2>Vote Now!</h2>
  <a href="<?php print RBEConfig::WWW; ?>/vote/start/<?php print $electionid; ?>">Vote now!</a>
  </div>
  <div class="center col-sm-4">
  <h2>Election Results</h2>
  <a href="<?php print RBEConfig::WWW; ?>/election/<?php print $electionid; ?>/results">See the results!</a>
  </div>
  <div class="center col-sm-4">
  <h2>About Ottawa123</h2>
  <a href="http://ottawa123.ca">Learn more about Ranked Choice Voting</a>
  </div>
  </div>
  <?php
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
  <title><?php print $title; ?> | Ottawa123.ca</title>
	<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css"/>
  <link rel="stylesheet" href="<?php print RBEConfig::WWW; ?>/style.css"/>
	<!--
  <link rel="stylesheet" href="http://ottawa123.ca/sites/all/themes/ottawa123/css/style.css?n9zmn8"/>
	-->
  <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  </head>
  <body>
	<?php fbRoot(); ?>

	<!--
	-->

  <?php
	if ($title != '') {
	?>
  <div class="center row">
	<div class="col-xs-8 col-xs-offset-2">
	<h1><?php print $title; ?></h1>
	</div>
	<div class="col-xs-2">
	<center>
	<a href="http://ottawa123.ca"><img src="http://ottawa123.ca/sites/all/themes/ottawa123/images/ottawa123-300px.png" class="img-responsive toplogo" alt="Ottawa 123"></a>
	</center>
	</div>
	</div>
	<?
	}
	?>
  <div id="content">
	<?
}

function bottom($electionid) {
	footer($electionid);
  ?>
  </div><!-- #content -->
	<?php googleAnalytics(); ?>
  </body>
  </html>
  <?php
}

function pr($o) {
  print "<pre>";
  print print_r($o);
  print "</pre>";
}

function googleAnalytics() {
	?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', 'UA-6324294-29', 'auto');
  ga('send', 'pageview');
</script>
	<?php
}

function fbLike($relative) {
	return;
	?>
	<div class="fb-like" data-href="http://vote.ottawa123.ca/vote/ballot/10" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
	<?php
}

function fbRoot() {
	?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=743342905682343&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
	<?php
}

function sendEmail($to,$subject,$body) {

  $mail = new PHPMailer;
  $mail->isSMTP();
  $mail->Host = RBEConfig::SMTP_HOST;
  $mail->Port = RBEConfig::SMTP_PORT;
  $mail->From = RBEConfig::SMTP_FROM_EMAIL;
  $mail->FromName = RBEConfig::SMTP_FROM_NAME;
  $mail->addAddress($to);
  $mail->Subject = $subject;
  $mail->Body = $body;

  if(!$mail->send()) {
    return $mail->ErrorInfo;
  }

  return '';
}

function db_save($table, $values, $key) {
	$count = getDatabase()->one(" select count(1) c from $table where $key = :key ",array('key'=>$values[$key]));
	if ($count['c'] == 0) {
		return db_insert($table,$values);
	}
	db_update($table,$values, $key);
}

function db_insert($table, $values) {
	$sql = db_generate_insert($table, $values);
	return getDatabase()->execute($sql, $values);
}

function db_update($table,$values,$key) {
	if ($key == null) {
		$key = 'id';
	}
  $sql = " update $table set ";
  foreach ($values as $k => $v) {
    #if ($k == $key) { continue; }
    $sql .= " `$k` = :$k, ";
  }
  $sql = preg_replace('/, $/','',$sql);
  $sql .= " where $key = :$key ";
	getDatabase()->execute($sql,$values);
}

function db_generate_insert($table, $values) {
  $sql = "insert into $table (";
  foreach ( $values as $k => $v ) {
    $sql .= "`{$k}`,";
  }
  $sql = rtrim($sql, ',');
  $sql .= ") values (";
  foreach ( $values as $k => $v ) {
    $sql .= ":{$k},";
  }
  $sql = rtrim($sql, ',');
  $sql .= ")";
  return $sql;
}


