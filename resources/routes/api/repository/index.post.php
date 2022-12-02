<?php

use App\Service\ParserService;
use CatPaw\Web\Attributes\Body;
use CatPaw\Web\Attributes\Consumes;
use CatPaw\Web\Attributes\Produces;

use function CatPaw\Web\error;
use function CatPaw\Web\ok;

return 
#[Consumes("text/plain")]
#[Produces("application/json")]
function(
    ParserService $parser,
    #[Body] string $content,
){
    try{
        yield $parser->save("./resources/numbers.csv", $content);
        return ok();
    } catch(\Error $e) {
        return error($e->getMessage());
    }
};