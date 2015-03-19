<?php

namespace TomGud\FoosLeader\CoreBundle\Service;

use TomGud\FoosLeader\CoreBundle\Entity\Result;

class MessageService
{
    public function getMessageForScore(Result $result)
    {
        $message_list = array();

        // $message_list [number of players (victor count), score difference , random message]
        // array of messages
        $message_list[1][10][] = "Legendary __VICTOR_ONE__ defeated __LOSER_ONE__ with no mercy!";
        $message_list[1][10][] = "__LOSER_ONE__ humiliation by victorious __VICTOR_ONE__";
        $message_list[1][10][] = "Only __VICTOR_ONE__ that remains, and loser got forgoten by the history";
        $message_list[1][7][] = "A stroll in the park for __VICTOR_ONE__, __LOSER_ONE__ can go back to the training grounds";
        $message_list[1][7][] = "__LOSER_ONE__ will have an empty wallet if he is a betting man, and __VICTOR_ONE__ a rich one";
        $message_list[1][5][] = "After a good match, where __LOSER_ONE__ put up a good fight, __VICTOR_ONE__ emerged victorious.";
        $message_list[1][5][] = "Good game from  __LOSER_ONE__, but __VICTOR_ONE__ emerged victorious.";
        $message_list[1][3][] = "The audience were gritting their teeth after this match, but __VICTOR_ONE__ drove it home in the end.";
        $message_list[1][3][] = "Really? Really? Should we say that __LOSER_ONE__ just got \"unlucky\"? Isn't everyone happy then?";
        $message_list[1][1][] = "What a thunderous comeback by __VICTOR_ONE__ or what do I know, I am just an automated bot. Sorry __LOSER_ONE__, you suck.";
        $message_list[1][1][] = "I can not believe this!!! Only one goal difference. What a close match. Now, play again!";

        $message_list[2][10][] = "Legendary team __VICTOR_ONE__ and __VICTOR_TWO__ defeated __LOSER_ONE__ and __LOSER_TWO__ with no mercy!";
        $message_list[2][10][] = "__LOSER_ONE__ and __LOSER_TWO__ humiliation by victorious __VICTOR_ONE__ and __VICTOR_TWO__";
        $message_list[1][10][] = "Only __VICTOR_ONE__ and __VICTOR_TWO__ that remains, and losers got forgoten by the history";
        $message_list[2][7][] = "A stroll in the park for __VICTOR_ONE__ and __VICTOR_TWO__, __LOSER_ONE__ and __LOSER_TWO__ can go back to the training grounds";
        $message_list[2][7][] = "__LOSER_ONE__ and __LOSER_TWO__ will have an empty wallet if he is a betting man, and __VICTOR_ONE__ and __VICTOR_TWO__ a rich one";
        $message_list[2][5][] = "After a good match, where __LOSER_ONE__ and __LOSER_TWO__ put up a good fight, __VICTOR_ONE__ and __VICTOR_TWO__ emerged victorious.";
        $message_list[2][5][] = "Good game from __LOSER_ONE__ and __LOSER_TWO__, but __VICTOR_ONE__ and __VICTOR_TWO__ emerged victorious.";
        $message_list[2][3][] = "The audience were gritting their teeth after this match, but __VICTOR_ONE__ and __VICTOR_TWO__ drove it home in the end.";
        $message_list[2][3][] = "Really? Really? Should we say that __LOSER_ONE__ and __LOSER_TWO__ just got \"unlucky\"? Isn't everyone happy then?";
        $message_list[2][1][] = "What a thunderous comeback by __VICTOR_ONE__ and __VICTOR_TWO__ or what do I know, I am just an automated bot. Sorry __LOSER_ONE__ and __LOSER_TWO__, you suck.";
        $message_list[2][1][] = "I can not believe this!!! What a difference. What a close match. Now, play again!";

        $victors = $result->getVictors();
        $losers = $result->getLosers();
        // get messages
        $victorsCount = count($victors);
        $closestMessageIndex = $this->recursive_array_search(abs((int)$result->getTeam1Score() - (int)$result->getTeam2Score()),$message_list[$victorsCount]);
        $randomMessage = rand(0, count($message_list[$victorsCount][$closestMessageIndex])-1);
        $game_description = $message_list[$victorsCount][$closestMessageIndex][$randomMessage];

        //replace names
        $game_description = str_replace('__VICTOR_ONE__', $victors[0]->getUsername(), $game_description);
        $game_description = str_replace('__LOSER_ONE__', $losers[0]->getUsername(), $game_description);

        // check if there is more players
        if(isset($victors[1])){
            $game_description = str_replace('__VICTOR_TWO__', $victors[1]->getUsername(), $game_description);
            $game_description = str_replace('__LOSER_TWO__', $losers[1]->getUsername(), $game_description);
        }

        return $game_description;
    }

    private function getClosestIndexOfArray($search, $arr) {
        $closest = null;
        foreach($arr as $item) {
            if($closest == null || abs($search - $closest) > abs($item - $search)) {
                $closest = $item;
            }
        }
        return $closest;
    }
    private function recursive_array_search($needle,$haystack) {
        foreach(array_keys($haystack) as $key) {
            if($key <= $needle ) {
                return $key;
            }
        }
        return false;
    }
}

?>
