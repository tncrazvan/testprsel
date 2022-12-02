<?php

use App\Service\ParserService;
use CatPaw\Web\Attributes\Consumes;
use CatPaw\Web\Attributes\Produces;

use function CatPaw\Web\ok;

return 
#[Produces("application/json")]
function(
    ParserService $parser
){
    $items = $parser->parse("./resources/numbers.csv");

    $result = [];

    foreach ($items as $item) {
        $result = $item;
    }

    return ok($result);
};