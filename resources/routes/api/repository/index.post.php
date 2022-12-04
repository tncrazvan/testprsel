<?php

use Amp\Http\Server\FormParser\Form;
use Amp\Http\Status;
use function App\save;
use App\Service\ParserService;
use CatPaw\Web\Attributes\Body;
use CatPaw\Web\Attributes\Consumes;

use CatPaw\Web\Attributes\Produces;
use function CatPaw\Web\error;
use function CatPaw\Web\ok;

return 
#[Consumes("multipart/form-data")]
#[Produces("application/json")]
function(
    ParserService $parser,
    #[Body] Form $form,
) {
    if (!$file = $form->getFile("file")) {
        return error(Status::BAD_REQUEST, "No file detected.");
    }
    try {
        yield save("./resources/numbers.csv", $file->getContents());
        $items   = $parser->parse("./resources/numbers.csv");
        $results = [];
        for ($items->rewind(); $items->valid(); $items->next()) {
            $results[] = $items->current();
        }
        yield save("./resources/numbers.json", json_encode($results));
        return ok();
    } catch(\Error $e) {
        return error(Status::INTERNAL_SERVER_ERROR, $e->getMessage());
    }
};