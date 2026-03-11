<!--general helper file-->

<?php
/**
 * Returns a time-based greeting based on the current hour.
 *
 * This function determines the appropriate greeting ('Good morning', 'Good afternoon', or 'Good evening')
 * based on the current time of day. It uses the 24-hour format to check the hour.
 *
 * @return string The greeting message.
 */
function getGreeting() {
    $hour = date('G'); // Get the current hour in 24-hour format (0-23)

    if ($hour >= 5 && $hour < 12) {
        return 'Good morning';
    } elseif ($hour >= 12 && $hour < 18) {
        return 'Good afternoon';
    } else {
        return 'Good evening';
    }
}