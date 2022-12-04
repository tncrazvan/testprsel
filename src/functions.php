<?php

namespace App;

use function Amp\call;
use function Amp\File\createDirectoryRecursively;

use function Amp\File\deleteFile;
use function Amp\File\exists;
use function Amp\File\isDirectory;
use function Amp\File\write;
use Amp\Promise;
use Error;

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

/**
 * Save phone numbers in repository.
 * @param  string        $fileName
 * @param  string        $content
 * @throws Error
 * @return Promise<void>
 */
function save(string $fileName, string $content):Promise {
    return call(function() use ($fileName, $content) {
        $dirName = dirname($fileName);
        if (!yield exists($dirName)) {
            yield createDirectoryRecursively($dirName);
        } else {
            if (yield isDirectory($dirName)) {
                // yield deleteDirectoryRecursively($dirName);
                yield createDirectoryRecursively($dirName);
            } else {
                yield deleteFile($dirName);
            }
        }
        yield write($fileName, $content);
    });
}