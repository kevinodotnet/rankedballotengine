<?

class ElectionController {

	public static function sendContactEmail ($id, $subject, $body) {
		$row = getDatabase()->one(" select contact from election where id = $id ");
		$to = $row['contact'];
		sendEmail($to,$subject,$body);
	}
 
  public static function getCandidates($id) {

    $rows = getDatabase()->all(" select * from candidate where electionid = $id order by rand() ");
    return $rows;

  }

  public static function getResults ($id) {

    $result = array();

		# how many people voted?
		$row = getDatabase()->one(" select count(distinct(electorid)) electors from vote where electionid = $id ");
		$result['electors'] = $row['electors'];

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
          candidateid,c.name, count(1) votes
        from vote v
					join candidate c on c.id = v.candidateid
          join ( select electorid, min(rank) rank from vote where electionid = $id and candidateid not in ($eliminatedCSV) group by electorid order by min(rank) ) v1 on 
            v1.electorid = v.electorid
            and v1.rank = v.rank
        group by
          v.candidateid
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
			$notEliminatedCount = 0;
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
						# they are eliminated
	          $r['eliminated'] = 1;
            $cand[$r['candidateid']]['eliminated'] = $roundNum;
	          $eliminated[] = $r['candidateid'];
	        } else {
						$notEliminatedCount++;
					}
        }
      }

      $result['rounds'][$roundNum] = array('ballots'=>$ballots,'candidates'=>$round);

			if ($notEliminatedCount == 0) {
				break;
			}

      $roundNum++;

      if ($winner) {
				# we have a winner in this round, so mark every non-winner as eliminated in this round.
	      foreach ($round as &$r) {
					if ($r['winner'] == 0) {
						$r['eliminated'] == 1;
					}
				}
      }
      
    }

    $result['candidates'] = $cand;

		if ($id == 2) {
			#pr($result['candidates']);
		}

    return $result;

  }

  public static function showResults ($electionid) {
    top("Election Results");
    ElectionController::showResultsInner($electionid);
    bottom($electionid);
	}

  public static function showResultsInner ($electionid) {

    $election = ElectionController::getResults($electionid);

		?>
		<center>
		<h3>
		After <?php print $election['electors']; ?> ballots and <?php print count($election['rounds']); ?> rounds of instant run-off counting:
		</h3>
		</center>

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
        <img src="<?php print $detail['img']; ?>" style="width: 50px; height: 56px;"/>
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

  }

}
