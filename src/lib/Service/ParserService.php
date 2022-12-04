<?php

namespace App\Service;

use function Amp\File\deleteFile;
use function Amp\File\exists;
use Amp\Promise;

use function App\normalize;
use App\ParserResult;

use CatPaw\Attributes\Service;
use CatPaw\Utilities\LinkedList;
use Error;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

#[Service]
class ParserService {
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Attempt to fix a number, converting it (if possible) to an valid format.
     * @param  string       $id
     * @param  string       $number
     * @return ParserResult
     */
    public function tryCorrect(string $id, string $number):ParserResult {
        static $pattern = '/([^0-9]+)/';
        
        $removed = [];

        $corrected = preg_replace_callback($pattern, function($matches) use (&$removed) {
            $removed[] = $matches[1] ?? '';
            return '';
        }, $number);

        if ($corrected !== $number) {
            return new ParserResult(
                id: $id,
                original: $number,
                corrected: $corrected,
                removed: array_values(array_filter($removed, static fn (string $item):bool => (bool)$item)),
            );
        }

        return new ParserResult(
            id: $id,
            original: $number,
            corrected: $number,
        );
    }

    /**
     * Check if the `$hystack` number is an valid format.
     * @param  string $hystack
     * @return bool   true if the format is valid, false otherwise.
     */
    public function isCorrect(string $hystack):bool {
        static $pattern = '/^[0-9]{11,}$/';
        $result         = preg_match($pattern, $hystack);
        return false !== $result && $result > 0;
    }

    /**
     * @param  string        $fileName
     * @throws Error
     * @return Promise<void>
     */
    public function delete(string $fileName):Promise {
        return deleteFile($fileName);
    }

    /**
     * Check if the repository exists.
     * @param  string        $fileName
     * @throws Error
     * @return Promise<bool> true if it exists, false otherwise
     */
    public function exists(string $fileName):Promise {
        return exists($fileName);
    }

    /**
     * Parse phone numbers from repository.
     * @param  string                   $fileName
     * @throws InvalidArgumentException
     * @return LinkedList<ParserResult>
     */
    public function parse(string $fileName):LinkedList {
        $rows   = [];
        $stream = fopen($fileName, 'r');
        while (false !== ($cell = fgetcsv($stream, 1000, ","))) {
            $rows[] = $cell;
        }

        fclose($stream);
        if (!$rows) {
            throw new \InvalidArgumentException("File $fileName is empty or nor a valid .csv file.");
        }

        $items = LinkedList::create();

        foreach ($rows as $y => $cols) {
            static $id = '';

            if (0 === $y) {
                // skiping header
                continue;
            }
            
            foreach ($cols as $x => $value) {
                $value = normalize($value);
                if ($x > 1) {
                    $this->logger->warning("Skipping column $x or row $y.");
                    continue;
                }
            
                if (0 === $x) {
                    $id = $value;
                    continue;
                }

                if ($this->isCorrect($value)) {
                    $items->push(new ParserResult(
                        id: $id,
                        original: $value,
                        corrected: $value,
                    ));
                    continue;
                }
                
                $result = $this->tryCorrect($id, $value);

                $result->isCorrect = $this->isCorrect($result->corrected);

                $items->push($result);
            }
        }
        return $items;
    }
}