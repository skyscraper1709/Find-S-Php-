<?php
$handle = fopen("inputfile.txt", "r");
$h = array(0,0,0,0,0);
$hypothesisCounter = 0;
show_hypothesis($h);
$first = true;
$c=1;
// s,w,s,a,r,-1
if ($handle) {
  while (($line = fgets($handle)) !== false) {
    // process the line read.
    $values = explode(",",$line);
    if($c==1 && $values[5]==1 ){
      $h = $values;
      $c++;
      show_hypothesis($h);//show h1
      continue;
    }
    if($values[5]==-1){
      show_hypothesis($h);//show skipped h
      continue;
    }
    else{

      for ($i=0; $i<5 ;$i++) {
        if($h[$i] == $values[$i]){
          continue;
        }else {
          $h[$i] = "?";
        }
      }
    }
    show_hypothesis($h);//show h after processing attributes
  }

    fclose($handle);
} else {
    echo  "error opening the file.";
}

function show_hypothesis($hypo){
	if($hypo){
		global $hypothesisCounter;
		echo "h".$hypothesisCounter." : ";
		echo "<";
		foreach ($hypo as $literal) {
			echo " ".$literal." ";
		}
		echo "></br>";
		
		$hypothesisCounter++;
	}
}
?>
