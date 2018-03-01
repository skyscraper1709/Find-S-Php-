<?php
use voku\db\DB;

// require_once 'composer/autoload.php';
require_once 'vendor/autoload.php';

$db = DB::getInstance('localhost', 'root', 'root', 'twitter_data');

function getUserAverageTweetStats($tweets_array){
    $avgTweetLength = 1;
    $numberOfRTs = 0;
    $numberOfHashtags = 0;
    $numberOfUserMentions = 0;
    foreach ($tweets_array as $key => $tweetData) {
        // if(strlen($tweetData['text'])>280)
        //     var_dump(($tweetData['text']));
        $avgTweetLength += strlen($tweetData['text']);
        $entities = json_decode($tweetData['entities_str'], true);
        $numberOfHashtags += count($entities['hashtags']);
        $numberOfUserMentions += count($entities['user_mentions']);
        
        if(substr( $tweetData['text'], 0, 2 ) === "RT"){
            $numberOfRTs++;
        }
    }
    $avgTweetLength = round( $avgTweetLength / count($tweets_array) , 0);
    $percentOfRTs = round(($numberOfRTs / count($tweets_array))*100, 3);
    $avgHashtagsPerTweet = round(($numberOfHashtags / count($tweets_array)), 3);
    $avgUserMentionsPerTweet = round(($numberOfUserMentions / count($tweets_array)), 3);
    return array( 'length' => $avgTweetLength,  'rts_percent' => $percentOfRTs, 'hashtagsPerTweet'=>$avgHashtagsPerTweet, 'userMentionsPerTweet' => $avgUserMentionsPerTweet);
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
    echo "<h1>Unique User and Data List: ".count($userids_tweets_array)."</h1>";
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
        $u_tc [$user_id]['hashtagsPerTweet'] = getUserAverageTweetStats($user_tweets)['hashtagsPerTweet'];
        $u_tc [$user_id]['userMentionsPerTweet'] = getUserAverageTweetStats($user_tweets)['userMentionsPerTweet'];
        $u_tc [$user_id]['tweets_per_day'] = getUserTweetsinADay($user_tweets);
    }
    // arsort($u_tc);
    // var_dump($u_tc);
    ?>
    <table border="1" cellpadding="5" cellspacing="0">
    <tr><th>@handle</th><th>totalcount</th><th>followers</th> <th>following</th> <th>ratio</th> <th>avg_twt_len</th> <th>rts_percent</th> <th>hashtagsPerTweet</th> <th>userMentionsPerTweet</th> <th>tweets_per_day</th></tr>
    <?php
    foreach ($u_tc as $user => $twt_stats):
        ?>
            <tr>
                <td> <?php echo $user?> </td>
            <?php
        foreach ($twt_stats as $stat => $stat_value):
            ?>
            <td> <?php echo $stat_value?> </td>
            <?php
        endforeach;      
        ?> </tr>  <?php
    endforeach;

    ?>
    </table>
    <?php
    /*
    @todo
    generate per user data in DB
    */

}
function lb(){
    echo "</br>";
}
function getDataFromDB(){
    
    global $db;
    $tweets = $db->select('dataset');

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

