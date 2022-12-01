<?php

use function Amp\File\createDirectoryRecursively;
use function Amp\File\deleteFile;
use function Amp\File\exists;
use function Amp\File\isDirectory;
use function Amp\File\read;
use function Amp\File\write;

use App\ParserResult;
use App\Service\ParserService;

use CatPaw\Attributes\Option;

use function CatPaw\CUI\text;
use function CatPaw\uuid;

use CatPaw\Web\WebServer;
use Psr\Log\LoggerInterface;

function main(
    LoggerInterface $logger,
    ParserService $service,
    #[Option("--filename")] string $fileName,
    #[Option("--serve")] false|string $serve,
) {
    if (false !== $serve) {
        $interface = $serve?$serve:'127.0.0.1:8000';
        yield WebServer::start(
            interfaces: $interface,
        );
        return;
    }

    if (!$fileName && !$serve) {
        $logger->error("Please specify a --filename or start the server with --serve.");
        die();
    }

    if (!yield exists($fileName)) {
        $logger->error("File $fileName not found.");
        die();
    }

    $contents = yield read($fileName);

    $fileName = 'f'.uuid().'.csv';
    $dirName  = dirname($fileName);


    if (!yield isDirectory($dirName)) {
        yield deleteFile($dirName);
        yield createDirectoryRecursively($dirName);
    }
    
    yield write($fileName, $contents);

    $items = $service->parse($fileName);

    yield deleteFile($fileName);

    $correctLines   = [];
    $incorrectLines = [];

    for ($items->rewind();$items->valid();$items->next()) {
        $removed = [];
        
        /** @var ParserResult $cell */
        $cell = $items->current();

        foreach ($cell->removed as $piece) {
            $removed[] = text("$piece", [0,0,0], [255,100,100]);
        }
        
        $valueStringified = text($cell->corrected, [0,150,150]);
        

        $removedStringified = join(',', $removed);
        $removedStringified = join(',', array_filter([$removedStringified], fn ($item) => $item));

        if ($removedStringified) {
            $removedStringified = "[REMOVED:$removedStringified]";
        }

        $originalStringified = '';

        if ($removedStringified) {
            $originalStringified = "[ORIGINAL:".text($cell->original, [100,100,200])."]";
        }

        if ($cell->isCorrect) {
            $correctLines[] = "[ID:$cell->id] $valueStringified $removedStringified $originalStringified";
        } else {
            $incorrectLines[] = "[ID:$cell->id] $valueStringified $removedStringified $originalStringified";
        }
    }
    
    $countCorrect  = count($correctLines);
    $countInorrect = count($incorrectLines);


    echo "======== CORRECT NUMBERS ($countCorrect) ==========".PHP_EOL;
    foreach ($correctLines as $line) {
        echo $line.PHP_EOL;
    }

    
    echo "======== INCORRECT NUMBERS ($countInorrect) ========".PHP_EOL;
    foreach ($incorrectLines as $line) {
        echo $line.PHP_EOL;
    }

    $logger->info("done!");
}
