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

  public static function done() {

    $votes = @getSession()->get('votes');
    top('You have voted!');
    pr($votes);
    ?>
    <a href="start" class="btn btn-danger">Cancel and start over</a>
    <?php
    bottom();
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
      top("You've filled your ballot. Now click Save!");
    } else {
      top("Who is your " . VoteController::toOrdinal($step) . " pick?");
    }

    ?>
    <div class="row">
    <div class="col-sm-9">
    <?php

    $backgrounds = array();
    $backgrounds[] = 'rgba(00,155,160,0.5)';
    $backgrounds[] = 'rgba(245,133,34,0.5)';
    $backgrounds[] = 'rgba(217,18,133,0.5)';


    $count = -1;
    foreach ($candidates_todo as $c) {
      $count++;
      $voteUrl = RBEConfig::WWW . "/vote/save/" . $c['id'];
      $rank = array_search($c['id'],$votes);
      $bg = $backgrounds[ $count % count($backgrounds)  ];
      ?>
      <div class="row" style="background: <?php print $bg; ?>;">
      <div class="center col-sm-3 col-xs-12" style="padding: 20px; ">
      <a href="<?php print $voteUrl; ?>"><center><img src="<?php print $c['img']; ?>" class="center img-responsive" style=" align: left;"/></center></a>
      </div>
      <div class="center col-sm-9 col-xs-12" style="font-size: 150%; padding-top: 20px;">
      <p><b>Name:</b> <?php print $c['name']; ?>
      <b>Sex:</b> <?php print $c['sex']; ?>
      <b>Age:</b> <?php print $c['age']; ?>
      </p>
      <p>
      <?php print $c['description']; ?>
      </p>
      <p>
      <span style="font-size: 150%;"><a href="<?php print $voteUrl; ?>">Pick <b><?php print $c['name']; ?></b> <?php print VoteController::toOrdinal($step); ?>!</a></span>
      </p>
      </div>
      </div><!-- /candidate -->
      <?php
    }

    ?>
    </div>
    <div class="col-sm-3">

    <?php
    $showSaveBallot = 0;
    if (count($candidates_voted) > 0) {
      $showSaveBallot = 1;
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
    $showSaveBallot = 0;
    if (count($candidates_voted) > 0) {
      $showSaveBallot = 1;
      ?>

	    <div style="margin-top: 20px;">
	    <h2 class="center">Your ballot:</h2>
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
        <div class="row">
        <div class="col-xs-6">
        <?php
	      print "<b>$ordinal:</b> ".$c['name']."<br/>";
        ?>
        </div>
        <div class="col-xs-6"><img src="<?php print $c['img']; ?>" class="img-responsive"/></div>
        </div>
        <?php
	    }
	    ?>
	    </div>
      <?php 
    } 
    ?>

    </div>
    </div>

    <?php

    bottom();
  }



}

