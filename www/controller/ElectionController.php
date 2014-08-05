<?

class ElectionController {
 
  public static function getCandidates() {

    $rows = getDatabase()->all(" select * from candidate order by rand() ");
    return $rows;

  }

  public static function getResults ($id) {

    $result = array();

    # prepare a 'candidate HASH array'
    $cand = array();
    $rows = getDatabase()->all(" select * from candidate where electionid = $id order by name ");
    foreach ($rows as $r) {
      $cand[$r['id']] = $r;
      $cand[$r['id']]['winner'] = 0;
    }

    # number of votes cast per 'round' of voting
    $rows = getDatabase()->all(" select rank,count(1) count from vote where electionid = $id group by rank order by rank ");
    $result['rankingSummary'] = $rows;

    # keep track of who has been eliminated as each round is processed
    $eliminated = array();
    $eliminated[] = -1; // so it is not empty

    # foreach round of voting
    $roundNum = 0;
    foreach ($result['rankingSummary'] as $rank) {
      $eliminatedCSV = implode(",",$eliminated);

      $sql = "
        select
          candidateid,count(1) votes
        from vote v
          join ( select electorid, min(rank) rank from vote where electionid = $id and candidateid not in ($eliminatedCSV) group by electorid order by min(rank) ) v1 on 
            v1.electorid = v.electorid
            and v1.rank = v.rank
        group by
          candidateid
        order by count(1) desc
      ";
      # print "<hr>$sql<hr>";
      $round = getDatabase()->all($sql);

      $min = 999999999;
      $ballots = 0;
      foreach ($round as &$r) {
        $ballots += $r['votes'];
        if ($r['votes'] < $min) {
          $min = $r['votes'];
        }
      }

      $winner = 0;
      foreach ($round as &$r) {
        $r['perc'] = $r['votes'] / $ballots;
        $r['winner'] = 0;
        if ($r['perc'] > 0.5) {
          $winner = 1;
          $r['winner'] = 1;
          # we have found the winner, so mark them in the candidate details
          $cand[$r['candidateid']]['winner'] = 1;
        }
        $r['eliminated'] = 0;
        if ($r['winner'] == 0) {
	        if ($r['votes'] == $min) {
	          $r['eliminated'] = 1;
            $cand[$r['candidateid']]['eliminated'] = $roundNum;
	          $eliminated[] = $r['candidateid'];
	        }
        }
      }

      $result['rounds'][$roundNum] = array('ballots'=>$ballots,'candidates'=>$round);

      if ($winner) {
        break;
      }
      $roundNum++;
      
    }

    $result['candidates'] = $cand;

    return $result;

  }

  public static function showResults ($electionid) {

    top("Election Results: $electionid");

    $election = ElectionController::getResults($electionid);

    ?>
    <table class="table table-condensed table-hover">
    <?php

    $eliminated = array();
    $round = 0;
    foreach ($election['rounds'] as $r) {
      $round++;
      ?>
      <tr><td colspan="5"><h3><?php print VoteController::toOrdinal($round); ?> Instant Runoff Round</h3></td></tr>
      <tr>
      <th>Status</th>
      <th>Rank</th>
      <th>Percent</th>
      <th>Name</th>
      <th>Total Votes</th>
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
        <td><?php print $ranked; ?></td>
        <td><?php print $percForm; ?></td>
        <td>
        <img src="<?php print RBEConfig::WWW; ?>/<?php print $detail['img']; ?>" style="width: 50px; height: 56px;"/>
        <?php print $detail['name']; ?>
        </td>
        <td><?php print $c['votes']; ?></td>
        </tr>
        <?php
      }
      ?>
      <!--
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
      -->
      <?php
    }
    ?>
    </table>
    <?php

    bottom();
  }

}
