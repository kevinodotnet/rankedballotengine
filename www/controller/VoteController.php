<?php

class VoteController {

  public static function ballot ($electorid) {

    top("Viewing vote #$electorid");
		?>
		<p>
		<center>
    <a href="<?php print RBEConfig::WWW; ?>/vote/start" class="btn btn-primary">Vote again!</a>
		</center>
		</p>
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

    ?>
    <table class="table table-condensed table-hover">
    <?php

    $election = ElectionController::getResults($electionid);
    $eliminated = array();
    $round = 0;
    foreach ($election['rounds'] as $r) {
      $round++;
      ?>
      <tr><td colspan="4"><h3><?php print VoteController::toOrdinal($round); ?> Round</h3></td></tr>
      <tr>
      <th>Rank</th>
      <th>Percent</th>
      <th>Name</th>
      <th>Total Votes</th>
      <th>Status</th>
      </tr>
      <?php
      $rank = 1;
      foreach ($r['candidates'] as $c) {
        $ranked = VoteController::toOrdinal($rank++);
        $percForm = sprintf("%.1f%%", $c['perc'] * 100);
        $detail = $election['candidates'][$c['candidateid']];
        $trClass = '';
        if ($c['winner'] == 1) {
          $trClass = 'success';
        }
        if ($c['eliminated'] == 1) {
          $trClass = 'danger';
          $eliminated[] = $c['candidateid'];
        }
        ?>
        <tr class="<?php print $trClass; ?>" >
        <td><?php print $ranked; ?></td>
        <td><?php print $percForm; ?></td>
        <td><?php print $detail['name']; ?></td>
        <td><?php print $c['votes']; ?></td>
        <td>
          <?php
	        if ($c['winner'] == 1) {
            ?>
            WINNER!
            <?php
	        }
	        else if ($c['eliminated'] == 1) {
            ?>
            Eliminated
            <?php
          } else {
            ?>
            Hanging on
            <?php
          }
          ?>
        </td>
        </tr>
        <?php
        #print "$ranked: {$detail['name']}<br/>";
        #pr($c);
        #pr($detail);

      }
      ?>
      <tr>
        <td>Your Ballot:</td>
        <td colspan="3">
        <?php
          foreach ($votes as $v) {
            $rank = VoteController::toOrdinal($v['rank']);
            foreach ($eliminated as $id) {
              if ($v['candidateid'] == $id) {
                $v['eliminated'] = 1;
              }
            }
            $spanStyle = '';
            if ($v['eliminated'] == 1) {
              $spanStyle = 'text-decoration:line-through';
            }
            print "<span style=\"$spanStyle\"><b>$rank</b> {$v['name']}</span><br/>";
          }
        ?>
        </td>
      </tr>
      <?php
    }
    ?>
    </table>
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

    ?>

    <div class="row">
    <div class="col-xs-12">
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
      <div class="center col-xs-6" style="padding: 20px; ">
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
      </div><!-- /candidate -->
      <?php
    }

    ?>
    </div>
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
        <div class="col-xs-6"><img src="<?php print RBEConfig::WWW; ?>/<?php print $c['img']; ?>" class="img-responsive"/></div>
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

