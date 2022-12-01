<?php

namespace App;

/**
 * Trim and remove unknown padding characters from `$hystack`,
 * @param  string $hystack
 * @return string normalized string.
 */
function normalize(string $hystack):string {
    static $leftPadding  = '/(?<=\W)(\w+)/';
    static $rightPadding = '/(\w+)(?=\W)/';

    $hystack = utf8_encode(trim($hystack));
    if (preg_match($rightPadding, $hystack, $groups)) {
        $hystack = $groups[1] ?? '';
    }
    if (preg_match($leftPadding, $hystack, $groups)) {
        $hystack = $groups[1] ?? '';
    }
    return $hystack;
}