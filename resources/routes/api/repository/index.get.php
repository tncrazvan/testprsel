<?php

use function Amp\File\exists;
use function Amp\File\read;

use Amp\Http\Status;
use App\Service\ParserService;

use CatPaw\Web\Attributes\Produces;
use function CatPaw\Web\error;
use function CatPaw\Web\ok;

return 
#[Produces("application/json")]
function(
    ParserService $parser
) {
    if (!yield exists("./resources/numbers.json")) {
        return error(Status::FAILED_DEPENDENCY, "You must first upload a \".csv\" file.");
    }
    $results = [];
    try {
        $text    = yield read("./resources/numbers.json");
        $results = json_decode($text);
    } catch(Throwable $e) {
        return error(Status::INTERNAL_SERVER_ERROR, $e->getMessage());
    }
    return ok($results);
};