<?php

namespace App;

class ParserResult {
    public function __construct(
        public string $id,
        public string $original,
        public string $corrected,
        public array $removed = [],
        public bool $isCorrect = true,
    ) {
    }
}