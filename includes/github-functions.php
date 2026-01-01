<?php
/**
 * GitHub helper functions for TradePress
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Convert GitHub timestamp to human-readable "time ago" format
 *
 * @param string|DateTime $github_time The timestamp from GitHub API
 * @return string Formatted "time ago" string
 */
function TRADEPRESS_GITHUB_time_ago($github_time) {
    // Handle both DateTime objects and string timestamps
    if ($github_time instanceof DateTime) {
        $timestamp = $github_time->getTimestamp();
    } else {
        $timestamp = strtotime($github_time);
    }
    
    $current_time = time();
    $time_difference = $current_time - $timestamp;
    
    // Different time intervals in seconds
    $minute = 60;
    $hour = 60 * $minute;
    $day = 24 * $hour;
    $week = 7 * $day;
    $month = 30 * $day;
    $year = 365 * $day;
    
    if ($time_difference < $minute) {
        return 'just now';
    } elseif ($time_difference < $hour) {
        $minutes = floor($time_difference / $minute);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time_difference < $day) {
        $hours = floor($time_difference / $hour);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time_difference < $week) {
        $days = floor($time_difference / $day);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time_difference < $month) {
        $weeks = floor($time_difference / $week);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($time_difference < $year) {
        $months = floor($time_difference / $month);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time_difference / $year);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}
