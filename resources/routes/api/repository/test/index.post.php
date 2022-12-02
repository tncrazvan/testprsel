<?php

use Amp\Http\Status;
use App\Service\ParserService;
use CatPaw\Web\Attributes\Body;
use CatPaw\Web\Attributes\Consumes;
use CatPaw\Web\Attributes\Produces;

use function CatPaw\Web\error;
use function CatPaw\Web\ok;

return 
#[Consumes("application/json")]
#[Produces("application/json")]
function(
    ParserService $parser,
    #[Body] array $payload
) {
    if (empty($number = $payload['number'] ?? '')) {
        return error(Status::BAD_REQUEST, "A number is required in order to execute a test.");
    }

    $result            = $parser->tryCorrect(0, $number);
    $result->isCorrect = $parser->isCorrect($result->corrected);
    
    if (!$result->isCorrect) {
        return ok($result, 'Number is incorrect.', Status::NOT_ACCEPTABLE);
    }

    return ok($result);
};