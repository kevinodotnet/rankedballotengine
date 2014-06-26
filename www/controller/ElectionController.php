<?

class ElectionController {
 
  public static function getCandidates() {
    $candidates = array();

    $candidates[] = array(
      'id' => 1,
      'name' => "Elephant",
      'desc' => 'random facts about elephant',
      'age' => 38,
      'sex' => 'Male'
    );
    $candidates[] = array(
      'id' => 2,
      'name' => "Owl",
      'desc' => 'random facts about owl',
      'age' => 29,
      'sex' => 'Female'
    );
    $candidates[] = array(
      'id' => 3,
      'name' => "Horse",
      'desc' => 'random facts about horse',
      'age' => 51,
      'sex' => 'Male'
    );
    $candidates[] = array(
      'id' => 4,
      'name' => "Eagle",
      'desc' => 'random facts about eagle',
      'age' => 46,
      'sex' => 'Trans'
    );
    $candidates[] = array(
      'id' => 5,
      'name' => "Mouse",
      'desc' => 'random facts about mouse',
      'age' => 46,
      'sex' => 'Female'
    );
    $candidates[] = array(
      'id' => 7,
      'name' => "Loon",
      'desc' => 'random facts about loon',
      'age' => 46,
      'sex' => 'Male'
    );

    return $candidates;
  }

}
