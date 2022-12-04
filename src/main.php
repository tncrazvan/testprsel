<?php

use function Amp\File\createDirectoryRecursively;
use function Amp\File\deleteFile;
use function Amp\File\exists;
use function Amp\File\isDirectory;
use function Amp\File\read;
use function Amp\File\write;

use Amp\Http\Server\Request;
use App\ParserResult;
use App\Service\ParserService;

use CatPaw\Attributes\Option;

use CatPaw\Utilities\StringExpansion;
use function CatPaw\uuid;
use CatPaw\Web\Attributes\Header;

use function CatPaw\Web\fileServer;


use CatPaw\Web\HttpConfiguration;

use CatPaw\Web\Services\ByteRangeService;
use CatPaw\Web\Utilities\Route;

use CatPaw\Web\WebServer;
use Psr\Log\LoggerInterface;

function main(
    LoggerInterface $logger,
    ParserService $service,
    #[Option("--input-file")] string $inputFile,
    #[Option("--output-file")] string $outputFile,
    #[Option("--serve")] false|string $serve,
) {
    if (false !== $serve) {
        $interface = $serve?$serve:'127.0.0.1:8000';

        Route::notFound(function(
            #[Header("range")] false | array $range,
            ByteRangeService $byterange,
            HttpConfiguration $config,
            LoggerInterface $logger,
            Request $request,
        ):mixed {
            static $fileServer = false;
            if (!$fileServer) {
                $fileServer = fileServer($config, fn (string $path):string => match ($path) {
                    '/file-picker' => '/index.html',
                    '/file-viewer' => '/index.html',
                    default        => $path,
                });
            }
            
            return ($fileServer)(
                $range,
                $request,
                $byterange,
                $logger,
            );
        });

        yield WebServer::start(
            interfaces: $interface,
        );

        echo Route::describe().PHP_EOL;
        
        return;
    }

    if ($outputFile && !$inputFile) {
        $logger->error("Please specify a valid --filename.");
        die();
    }

    if (!yield exists($inputFile)) {
        $logger->error("File $inputFile not found.");
        die();
    }

    $contents = yield read($inputFile);

    $inputFile = 'f'.uuid().'.csv';
    $dirName   = dirname($inputFile);


    if (!yield isDirectory($dirName)) {
        yield deleteFile($dirName);
        yield createDirectoryRecursively($dirName);
    }
    
    yield write($inputFile, $contents);

    $items = $service->parse($inputFile);

    yield deleteFile($inputFile);


    $acceptableResults            = [];
    $successfulCorrectionsResults = [];
    $failedCorrectionsResults     = [];

    if ($outputFile) {
        for ($items->rewind();$items->valid();$items->next()) {
            /** @var ParserResult */
            $item = $items->current();

            if ($item->corrected === $item->original && $item->isCorrect) {
                $acceptableResults[] = [
                    "id"        => $item->id,
                    "original"  => $item->original,
                    "corrected" => $item->corrected,
                    "isCorrect" => $item->isCorrect,
                ];
            } else if ($item->corrected !== $item->original && $item->isCorrect) {
                $successfulCorrectionsResults[] = [
                    "id"        => $item->id,
                    "original"  => $item->original,
                    "corrected" => $item->corrected,
                    "isCorrect" => $item->isCorrect,
                ];
            } else {
                $failedCorrectionsResults[] = [
                    "id"        => $item->id,
                    "original"  => $item->original,
                    "corrected" => $item->corrected,
                    "isCorrect" => $item->isCorrect,
                ];
            }
        }
        
        $acceptableFile            = StringExpansion::variable($outputFile, ["type" => "acceptable"]);
        $successfulCorrectionsFile = StringExpansion::variable($outputFile, ["type" => "successul-corrections"]);
        $failedCOrrectionsFile     = StringExpansion::variable($outputFile, ["type" => "failed-corrections"]);

        if ($acceptableFile === $outputFile) {
            $acceptableFile = "acceptable-$outputFile";
        }

        if ($successfulCorrectionsFile === $outputFile) {
            $successfulCorrectionsFile = "successul-corrections-$outputFile";
        }

        if ($failedCOrrectionsFile === $outputFile) {
            $failedCOrrectionsFile = "failed-corrections-$outputFile";
        }
        
        if (yield exists($acceptableFile)) {
            yield deleteFile($acceptableFile);
        }

        if (yield exists($successfulCorrectionsFile)) {
            yield deleteFile($successfulCorrectionsFile);
        }

        if (yield exists($failedCOrrectionsFile)) {
            yield deleteFile($failedCOrrectionsFile);
        }
        

        if (str_ends_with(strtolower($outputFile), '.csv')) {
            $logger->info("Writing acceptable results to $acceptableFile...");
            $handle = fopen($acceptableFile, "w");
            fputcsv($handle, ['id','original','corrected','is_correct']);
            foreach ($acceptableResults as $result) {
                $result['isCorrect'] = ($result['isCorrect'] ?? false)?'true':'false';
                fputcsv($handle, $result);
            }
            fclose($handle);
            $logger->info("Ok.");

            $logger->info("Writing acceptable results to $successfulCorrectionsFile...");
            $handle = fopen($successfulCorrectionsFile, "w");
            fputcsv($handle, ['id','original','corrected','is_correct']);
            foreach ($successfulCorrectionsResults as $result) {
                $result['isCorrect'] = ($result['isCorrect'] ?? false)?'true':'false';
                fputcsv($handle, $result);
            }
            fclose($handle);
            $logger->info("Ok.");
            
            $logger->info("Writing acceptable results to $failedCOrrectionsFile...");
            $handle = fopen($failedCOrrectionsFile, "w");
            fputcsv($handle, ['id','original','corrected','is_correct']);
            foreach ($failedCorrectionsResults as $result) {
                $result['isCorrect'] = ($result['isCorrect'] ?? false)?'true':'false';
                fputcsv($handle, $result);
            }
            fclose($handle);
            $logger->info("Ok.");
        } else {
            $logger->info("Writing acceptable results to $acceptableFile...");
            yield write($acceptableFile, json_encode($acceptableResults));
            $logger->info("Ok.");
            $logger->info("Writing acceptable results to $successfulCorrectionsFile...");
            yield write($successfulCorrectionsFile, json_encode($successfulCorrectionsResults));
            $logger->info("Ok.");
            $logger->info("Writing acceptable results to $failedCOrrectionsFile...");
            yield write($failedCOrrectionsFile, json_encode($failedCorrectionsResults));
            $logger->info("Ok.");
        }
    }

    $logger->info("done!");
}
