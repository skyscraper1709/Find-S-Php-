<?php
use voku\db\DB;

// require_once 'composer/autoload.php';
require_once 'vendor/autoload.php';

$db = DB::getInstance('localhost', 'root', 'root', 'twitter_data');

function getUserAverageTweetStats($tweets_array){
    $avgTweetLength = 1;
    $numberOfRTs = 0;
    foreach ($tweets_array as $key => $tweetData) {
        $avgTweetLength += strlen($tweetData['text']);
        if(substr( $tweetData['text'], 0, 2 ) === "RT"){
            $numberOfRTs++;
        }
    }
    $avgTweetLength = round( $avgTweetLength / count($tweets_array) , 0);
    $percentOfRTs = ($numberOfRTs / count($tweets_array))*100;
    return array( 'length' => $avgTweetLength,  'rts_percent' => $percentOfRTs);
}

function getUserTweetsinADay($tweets_array){
    $dateHolder = "anything";
    $dayCount = 0;
    foreach ($tweets_array as $key => $tweetData) {
        $date = DateTime::createFromFormat('d/m/Y G:i:s', $tweetData['time'])->format("Y-m-d");
        if($dateHolder != $date){
            $dayCount++;
            $dateHolder = $date;
        }
        // var_dump($date);
    }
    $tweetsPerDay = round( count($tweets_array) / $dayCount , 0);
    // var_dump($dayCount);
    return $tweetsPerDay;
}

function createUsersData($tweets){
    //getUserList
    $userids_tweets_array = $tweets->fetchGroups('from_user');
    echo "user_list:";
    lb();
    // var_dump(array_keys($userids_tweets_array));
    $u_tc = array();
    // var_dump($userids_tweets_array);
    foreach ($userids_tweets_array as $user_id => $user_tweets) {
        //tweet count
        $u_tc [$user_id]['total_count'] = count($user_tweets);
        
        //follower count
        $u_tc [$user_id]['followers'] = $user_tweets[0]['user_followers_count'];
        $u_tc [$user_id]['following'] = $user_tweets[0]['user_friends_count'];
        $u_tc [$user_id]['ratio'] = round( $u_tc [$user_id]['followers'] / $u_tc [$user_id]['following'], 3 );
        $u_tc [$user_id]['avg_twt_len'] = getUserAverageTweetStats($user_tweets)['length'];
        $u_tc [$user_id]['rts_percent'] = getUserAverageTweetStats($user_tweets)['rts_percent'];
        $u_tc [$user_id]['tweets_per_day'] = getUserTweetsinADay($user_tweets);
    }
    // arsort($u_tc);
    var_dump($u_tc);
    /*
    @todo
    generate per user data in DB
    */

}
function lb(){
    echo "</br>";
}
function getDataFromDB(){
    // $where = array(
    //     'page_type ='=> 'article',
    //     'page_type NOT LIKE'  => '%öäü123',
    //     'page_id >='=> 2,
    // );

    global $db;
    $tweets = $db->select('dataset');
    // $tweets = $tweets->fetchGroups('from_user');

    // echo 'There are ' . count($tweets) . ' tweets(s):' . PHP_EOL;

  // var_dump($tweets);
    foreach ($tweets as $tweet) {
        // var_dump($tweet);
    }
    createUsersData($tweets);
}

getDataFromDB();

function getDataiNArray(){
    $tweetData =array();
    $atts = array();
    $row = 1;
    $readAtrributes = true;
    if (($handle = fopen("dataset.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            $row++;
            $tempAttValues = array();
            for ($c=0; $c < $num; $c++) {
            if($readAtrributes){//for first time read the column names
                $atts[] =  $data[$c];
            }else{
                $tempAttValues[$atts[$c]] = $data[$c];
            }
                            // echo $data[$c] . "<br />\n";
            }
            if(!$readAtrributes){
                $tweetData[] = $tempAttValues;
            }
            $readAtrributes=false;
        }

    }
}

?>

