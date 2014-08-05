<?php

class VoteController {

  public static function ballot ($electorid) {

    top("Viewing ballot #$electorid");
		?>
    <!--
		<p>
		<center>
    <a href="<?php print RBEConfig::WWW; ?>/vote/start" class="btn btn-primary">Vote again!</a>
		</center>
		</p>
    -->
		<?php
    $votes = getDatabase()->all("
      select
        v.electionid,
        v.rank,
        c.id candidateid,
        c.name,
        c.img
      from 
        elector e
        join vote v on v.electorid = e.id
        join candidate c on c.id = v.candidateid
      where
        e.id = $electorid
      order by
        v.rank
    ");
    $electionid = '';
    foreach ($votes as $v) {
      $electionid = $v['electionid']; 
      break;
    }
    $election = ElectionController::getResults($electionid);

    $backgrounds = array();
    $backgrounds[] = 'rgba(00,155,160,0.5)';
    $backgrounds[] = 'rgba(245,133,34,0.5)';
    $backgrounds[] = 'rgba(217,18,133,0.5)';

    ?>
    <div class="row">
    <?php
    $count = -1;
    foreach ($votes as $v) {
      $count++;
      $bg = $backgrounds[ $count % count($backgrounds)  ];

      $rank = $v['rank'];
      $ord = VoteController::toOrdinal($rank);
      $candidate = $election['candidates'][$v['candidateid']];
      $winner = $candidate['winner'];
      $eliminated = $candidate['eliminated'];
      $elimOrd = VoteController::toOrdinal(($eliminated+1));
      ?>
      <div class="col-xs-6" style="background: <?php print $bg; ?>; padding-top: 5px; padding-bottom: 5px; font-size: 150%;">
      <?php
      ?>
      <img src="<?php print RBEConfig::WWW; ?>/<?php print $candidate['img']; ?>" style="float: left; padding-right: 5px;"/>
      <?php
      #pr($v);
      #pr($candidate);
      print "{$candidate['name']}, your $ord choice, ";
      if ($winner) {
        ?>
        <b>won</b>!
        <?php
      } else {
        ?>
        was <b>eliminated</b> on the <?php print $elimOrd; ?> round of instant-runoff voting.
        <?php
      }
      ?>
      </div>
      <?php
      if ($winner) {
        break; 
      }
    }

    ?>
    </div>
    <?php

    ?>
    <div style="padding-top: 20px;">
    <center>
    Now check out the <a class="" href="<?php print RBEConfig::WWW; ?>/election/<?php print $electionid; ?>/results">Detailed Election Results</a>.
    </center>
    </div>
    <?php

    bottom();
  }

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

    $electionid = 1; // TODO: hard coded.
    $electorid = getDatabase()->execute(" insert into elector (created) values (CURRENT_TIMESTAMP) ");

    foreach ($votes as $rank => $candidateid) {
      getDatabase()->execute(" insert into vote (electionid,electorid,rank,candidateid) values ($electionid,$electorid,$rank,$candidateid) ");
    }

    header("Location: ".RBEConfig::WWW."/vote/ballot/$electorid");
  }

  public static function vote() {

    $votes = @getSession()->get('votes');
    $step = VoteController::getStep();

    $candidates = @getSession()->get('candidates');
    if ($candidates == null) {
      $candidates = ElectionController::getCandidates();
      @getSession()->set('candidates',$candidates);
    }

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

    if ($step > 1) {
      ?>
      <div style="padding-bottom: 20px;">
      <center>
      <i>
      (You don't have to keep picking. If you don't want to make a 
      <?php print VoteController::toOrdinal($step); ?> pick then
      <a href="done" class="">click here to submit your ballot as-is</a>.
      </i>
      </center>
      </div>
      <?php
    }

    ?>

    <div class="row">
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

      <div class="col-sm-6 ">
      <div style="padding-bottom: 10px; padding-top: 10px; background: <?php print $bg; ?>;">
        <p>
        <a href="<?php print $voteUrl; ?>"><img src="<?php print RBEConfig::WWW; ?>/<?php print $c['img']; ?>" class="" style="float: left; padding-left: 5px; padding-right: 5px;"/></a>
        <b><?php print $c['name']; ?></b> <?php print $c['description']; ?>
        <br/><br/>
        <center>
        <span style="font-size: 250%;">
        <a href="<?php print $voteUrl; ?>">Pick <b><?php print $c['name']; ?></b> <?php print VoteController::toOrdinal($step); ?>!</a>
        </span>
        </center>
        </p>
        <div style="clear: both;"> </div>
      </div>
      </div><!-- /candidateOUTER -->

      <!--
      <div class="col-sm-4">
      <div class="col-sm-4">
      <div class="row" style="background: <?php print $bg; ?>;">
      <div class="center col-xs-6" style="padding: 20px; background: <?php print $bg; ?>;">
      <a href="<?php print $voteUrl; ?>"><center><img src="<?php print RBEConfig::WWW; ?>/<?php print $c['img']; ?>" class="center img-responsive" style=" align: left;"/></center></a>
      </div>
      <div class="center col-xs-6" style="font-size: 150%; padding-top: 20px;">
      <p>
      <?php print $c['description']; ?>
      </p>
      <p>
      <span style="font-size: 150%;"><a href="<?php print $voteUrl; ?>">Pick <b><?php print $c['name']; ?></b> <?php print VoteController::toOrdinal($step); ?>!</a></span>
      </p>
      </div>
      </div>
      </div>
      -->
      <?php
    }

    ?>
    </div>

    <div class="row">
    <div class="col-xs-12">

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
	      <a href="done" class="btn btn-primary">Submit Ballot As-Is</a>
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
	    <h2 class="center">Your ballot so far:</h2>
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
        <div class="col-xs-3">
        <?php
	      print "<b>$ordinal:</b> ".$c['name']."<br/>";
        ?>
        <img src="<?php print RBEConfig::WWW; ?>/<?php print $c['img']; ?>" class="img-responsive"/>
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

