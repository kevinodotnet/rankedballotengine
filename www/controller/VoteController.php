<?php

class VoteController {

  public static function ballot ($electorid) {

		$showForm = 1;
		if (isset($_POST['email'])) {
			$showForm = 0;
			$email = $_POST['email'];
		}

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
		if (count($votes) == 0) {
			top();
			?>
			<center>
			Sorry - we couldn't find the results for ballot #<?php print $electorid; ?>... Weird!
			</center>
			<?php
			bottom();
			return;
		}
    $electionid = '';
    foreach ($votes as $v) {
      $electionid = $v['electionid']; 
      break;
    }

    top("Results for 'secret' ballot #$electorid");

		if ($showForm == 1) {
			?>
			<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
			<p>
			Thank you for checking out our Ranked Choice Ballot simulator. Our goal
			is to make a small, simple change that would make Ottawa's elections more fair, diverse and friendly.
			Join our campaign by signing up to our newsletter!
			</p>
			<center>
			<form class="form-inline" role="form" method="post"> 
			<div class="form-group"> 
			<label class="sr-only" for="exampleInputEmail2">Email address
			</label> 
			<input type="email" class="form-control" id="exampleInputEmail2" name="email" placeholder="Enter email"> 
			</div>
			<button type="submit" class="btn btn-default">Sign up</button>
			</form>
			</center>
			</div>
			</div>
			<?php
		} else {
			?>
			<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
			Thanks for signing up!
			</div>
			</div>
			<?php
	    ElectionController::sendContactEmail($electionid, "Ballot signup from $email", "Ballot signup from $email\n\nhttp://ottawa123.ca/content/get-involved");
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
		$winnerFound = 0;
		if ($electionid == 2) { $extra = " width: 200px; height: 200px; "; }
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
      <img class="img-responsive" src="<?php print $candidate['img']; ?>" style="float: left; padding-right: 5px; <?php print $extra; ?>"/>
      <?php
      #pr($v);
      #pr($candidate);
      print "{$candidate['name']}, your $ord choice, ";
      if ($winner) {
				$winnerFound = 1;
				$winnerName = $candidate['name'];
        ?>
        <b>won</b>!
        <?php
      } else {
				if ($winnerFound == 1) {
	        ?>
	        was <b>eliminated</b> in the <?php print $elimOrd; ?> round. Who cares! <?php print $winnerName; ?> won!
	        <?php
				} else {
	        ?>
	        was <b>eliminated</b> in the <?php print $elimOrd; ?> round of instant-runoff voting.
	        <?php
				}
      }
      ?>
      </div>
      <?php
    }

    ?>
    </div>

		<!--
		<div class="row">
		<div class="col-sm-6">
			<center>
			<h1>Share Your Ballot!</h1>
			<?php fbLike("/vote/ballot/$electorid"); ?>
			</center>
		</div>
		<div class="col-sm-6">
			<center>
			<h1>Invite Friends to Vote!</h1>
			</center>
		</div>
		</div>
		-->

	  <h1 class="center " style="margin-top: 20px;">Overall Ballot Results</h1>
    <?php
    ElectionController::showResultsInner($electionid);

		?>
	  <div class="center jumbotron" style="margin-top: 20px;">
	    <a href="<?php print RBEConfig::WWW; ?>/vote/start/<?php print $electionid; ?>" class="btn btn-primary">Vote Again</a>
		</div>
		<?php

    bottom($electionid);
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
    $m[++$i] = '11th';
    $m[++$i] = '12th';
    $m[++$i] = '13th';
    $m[++$i] = '14th';
    $m[++$i] = '15th';
    $m[++$i] = '16th';
    $m[++$i] = '17th';
    $m[++$i] = '18th';
    $m[++$i] = '19th';
    $m[++$i] = '20th';
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

    $electionid = getSession()->get('election');
    $candidates = ElectionController::getCandidates($electionid);
    foreach ($candidates as $c) {
      if ($c['id'] == $id) {
        $votes[$step] = $id;
        getSession()->set('votes',$votes);
        break;
      }
    }

    header("Location: ".RBEConfig::WWW."/vote/");
  }

  public static function start($election) {
		if ($election == '') {
			$election = 1;
		}
    getSession()->set('votes',null);
    getSession()->set('candidates',null);
    getSession()->set('election',$election);
		VoteController::vote();
  }

  public static function done() {

    $votes = @getSession()->get('votes');

    $electionid = getSession()->get('election');
    $electorid = getDatabase()->execute(" insert into elector (created) values (CURRENT_TIMESTAMP) ");

    foreach ($votes as $rank => $candidateid) {
      getDatabase()->execute(" insert into vote (electionid,electorid,rank,candidateid) values ($electionid,$electorid,$rank,$candidateid) ");
    }

    header("Location: ".RBEConfig::WWW."/vote/ballot/$electorid");
  }

  public static function vote() {

    $electionid = getSession()->get('election');
    $votes = @getSession()->get('votes');
    $step = VoteController::getStep();

    $candidates = @getSession()->get('candidates');
    if ($candidates == null) {
      $candidates = ElectionController::getCandidates($electionid);
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
			# nobody left to vote for means "auto done"
			header("Location: done");
			return;
      #top("You've filled your ballot. Now click Save!");
    } else {
      top("Who is your " . VoteController::toOrdinal($step) . " pick?");
    }

		if ($electionid == 2) {
		?>
		<div class="row" style="margin-bottom: 20px; background: #ffc0c0; font-size: 120%; padding: 20px;">
		<div class="col-sm-6 col-sm-offset-3">
		<i>
		Hi Ottawa Media! This is a "soft launch" so when we're meeting with you in person it's easier to explain what ranked-choice-voting is all about!<br/><br/>
		So, who are your 1st, 2nd, 3rd, etc choices for Ottawa's next <a href="http://metronews.ca/voices/one-ten-laurier/695989/city-communications-nicknames-reporters-or-why-this-cat-was-grumpy/">'Grumpy Cat'</a>?
		</i>
		</div>
		</div>
		<?php
		}

    if ($step == 1) {
			?>
      <div style="padding-bottom: 20px;">
      <center>
      <i>
			This is a <b>Ranked Choice Ballot</b> voting simulator. Confused? <a href="http://ottawa123.ca/content/what-ranked-choice-voting-initiative">Learn more here</a> or
			just get started by picking your 1st candidate from the <?php print count($candidates_todo); ?> candidates listed below.
      </i>
      </center>
      </div>
			<?php
		}

    if ($step > 1) {
      ?>
      <div style="padding-bottom: 20px;">
      <center>
      <i>
      If you don't want to make a 
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

			if ($electionid == 2) { $extra = " width: 200px; height: 200px; "; }
      ?>

      <div class="col-sm-6 ">
      <div style="padding: 10px; background: <?php print $bg; ?>; font-size: 120%;">
        <p>
        <center>
        <a href="<?php print $voteUrl; ?>"><img src="<?php print $c['img']; ?>" class="" style="float: left; padding-left: 5px; padding-right: 5px; <?php print $extra; ?>"/></a>
        <b><?php print $c['name']; ?></b>:
				<?php print $c['description']; ?><br/>
        <a href="<?php print $voteUrl; ?>">Pick <b><?php print $c['name']; ?></b> <?php print VoteController::toOrdinal($step); ?>!</a>
        </p>
        </center>
        <div style="clear: both;"> </div>
      </div>
      </div><!-- /candidateOUTER -->

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
	    <a href="<?php print RBEConfig::WWW; ?>/vote/start/<?php print $electionid; ?>" class="btn btn-danger">Cancel and start over</a>
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
        <img src="<?php print $c['img']; ?>" class="img-responsive"/>
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
		if ($electionid == 1) {
		?>
		<div style="text-align: center; margin-top: 10px;">
		Animal portraits by <a href="http://www.lydiapepin.com/">Lydia Pepin</a>.
		</div>
		<?php
		}
		?>

    <?php

    bottom($electionid);
  }



}

