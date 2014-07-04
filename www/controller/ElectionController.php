<?

class ElectionController {
 
  public static function getCandidates() {

    $rows = getDatabase()->all(" select * from candidate order by rand() ");
    return $rows;

  }

  public static function getResults ($id) {

    $result = array();

    $cand = array();
    $rows = getDatabase()->all(" select * from candidate where electionid = $id order by id ");
    foreach ($rows as $r) {
      $cand[$r['id']] = $r;
    }
    $result['candidates'] = $cand;


    $row = getDatabase()->one(" select count(distinct(electorid)) c from vote where electionid = $id ");

    $rows = getDatabase()->all(" select rank,count(1) count from vote where electionid = $id group by rank order by rank ");
    $result['rankingSummary'] = $rows;

    $eliminated = array();
    $eliminated[] = -1; // so it is not empty

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
        }
        $r['eliminated'] = 0;
        if ($r['winner'] == 0) {
	        if ($r['votes'] == $min) {
	          $r['eliminated'] = 1;
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

    return $result;

  }

}
