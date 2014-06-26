<?php

class VoteController {

  public static function toOrdinal ($number) {
    $m = array();
    $i = 0;
    $m[++$i] = '1st';
    $m[++$i] = '2nd';
    $m[++$i] = '3rd';
    $m[++$i] = '4th';
    $m[++$i] = '5th';
    $m[++$i] = '6th';
    $m[++$i] = '7th';
    $m[++$i] = '8th';
    $m[++$i] = '9th';
    $m[++$i] = '10th';
    return $m[0+$number];
  }

  public static function getStep() {
    $votes = getSession()->get('votes');
    if ($votes == null) {
      $votes = array();
    } 
    $step = count($votes) + 1;
    return $step;
  }

  public static function save($id) {
    $votes = getSession()->get('votes');
    $step = VoteController::getStep();
    if ($votes == null) {
      $votes = array();
    } 

    $candidates = ElectionController::getCandidates();
    foreach ($candidates as $c) {
      if ($c['id'] == $id) {
        $votes[$step] = $id;
        getSession()->set('votes',$votes);
        break;
      }
    }

    header("Location: ".RBEConfig::WWW."/vote/");

  }

  public static function start() {
    getSession()->set('votes',null);
    header("Location: ".RBEConfig::WWW."/vote/");
  }

  public static function vote() {

    $votes = @getSession()->get('votes');
    $step = VoteController::getStep();
    $candidates = ElectionController::getCandidates();
    $candidates_voted = array();
    $candidates_todo = array();
    foreach ($candidates as $c) {
      $rank = array_search($c['id'],$votes);
      if ($rank > 0) {
        $candidates_voted[$rank] = $c;
      } else {
        $candidates_todo[] = $c;
      }
    }

    if (count($candidates_todo) == 0) {
      top("You're done!");
    } else {
      top("Who is your " . VoteController::toOrdinal($step) . " pick?");
    }

    ?>
    <div class="row">
    <?php


    foreach ($candidates_todo as $c) {
      $voteUrl = RBEConfig::WWW . "/vote/save/" . $c['id'];
      $rank = array_search($c['id'],$votes);
      ?>
      <div class="center col-sm-4 col-xs-12">
      <h3><a href="<?php print $voteUrl; ?>"><?php print $c['name']; ?></a></h3>
      (<b><?php print $c['sex']; ?> <?php print $c['age']; ?></b>)<br/>
      <?php print $c['desc']; ?><br/>
      </div>
      <?php
    }

    ?>
    </div>

    <?php 
    $showSaveBallot = 0;
    if (count($candidates_voted) > 0) { 
      $showSaveBallot = 1;
      ?>

	    <div style="margin-top: 20px;">
	    <h2 class="center">Your ballot ranking:</h2>
	    <div class="center">
	    <?php
	    // show existing ballot ranking
	    foreach ($votes as $rank => $id) {
	      foreach ($candidates as $c) {
	        if ($c['id'] == $id) {
	          break;
	        }
	      }
	      $ordinal = VoteController::toOrdinal($rank);
	      ?>
	      <?php
	      print "$ordinal: ".$c['name']."<br/>";
	    }
	    ?>
	    </div>
	    </div>
      <?php 
    } 
    ?>

    <div class="row" style="margin-top: 20px;">
    <div class="col-sm-12 center">
    <?php if ($showSaveBallot) {
      ?>
      <a href="done" class="btn btn-primary">Save Ballot As-Is</a>
      <?php
    }
    ?>
    <a href="start" class="btn btn-danger">Cancel and start over</a>
    </div>
    </div>
    <?php

    bottom();
  }

}

