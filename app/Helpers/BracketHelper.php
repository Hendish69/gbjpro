<?php

namespace App\Helpers;

class BracketHelper
{
   
    public static function getRoundTitle($round, $maxRounds)
    {
        $titles = [
            1 => 'First Round',
            2 => 'Second Round', 
            3 => 'Quarter Finals',
            4 => 'Semi Finals',
            5 => 'Grand Final'
        ];
        
        if (isset($titles[$round])) {
            return $titles[$round];
        }
        
        if ($round === $maxRounds) {
            return 'Grand Final';
        }
        
        if ($round === $maxRounds - 1) {
            return 'Semi Finals';
        }
        
        if ($round === $maxRounds - 2) {
            return 'Quarter Finals';
        }
        
        return "Round {$round}";
    }
    
    public static function getStatusIcon($status)
    {
        $icons = [
            'scheduled' => 'clock',
            'in_progress' => 'play-circle',
            'completed' => 'check-circle',
            'pending' => 'hourglass-half',
            'cancelled' => 'times-circle'
        ];
        
        return $icons[$status] ?? 'question-circle';
    }
}