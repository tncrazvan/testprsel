<?php

use Amp\Loop;

use function App\normalize;
use function App\save;

use App\Service\ParserService;

use CatPaw\Utilities\LoggerFactory;

use function CatPaw\uuid;
use PHPUnit\Framework\TestCase;

class TestSuite extends TestCase {
    public function testNormalize() {
        $this->assertEquals("id", normalize("﻿id"));
    }

    public function testParserService() {
        Loop::run(function() {
            $logger = LoggerFactory::create();
            $parser = new ParserService($logger);
            $this->assertFalse($parser->isCorrect("123123"));
            $this->assertFalse($parser->isCorrect("123123____"));
            $this->assertFalse($parser->isCorrect("123123wqeqwrqw"));
            $this->assertTrue($parser->isCorrect("12345678912"));
            $this->assertFalse($parser->isCorrect("12345678912qw3dqwerqw"));
            $this->assertFalse($parser->isCorrect("wqedqwe12345678912qw3dqwerqw"));

            $attempt0 = $parser->tryCorrect(0, "123123wqeqwrqw");
            $attempt1 = $parser->tryCorrect(0, "12345678912qw3dqwerqw");
            $attempt2 = $parser->tryCorrect(0, "wqedqwe12345678912qw3dqwerqw");

            $this->assertEquals("123123", $attempt0->corrected);
            $this->assertEquals("123456789123", $attempt1->corrected);
            $this->assertEquals("123456789123", $attempt2->corrected);

            $this->assertFalse($parser->isCorrect($attempt0->corrected));
            $this->assertTrue($parser->isCorrect($attempt1->corrected));
            $this->assertTrue($parser->isCorrect($attempt2->corrected));

            $this->assertArrayHasKey(0, $attempt0->removed);

            $this->assertArrayHasKey(0, $attempt1->removed);
            $this->assertArrayHasKey(1, $attempt1->removed);

            $this->assertArrayHasKey(0, $attempt2->removed);
            $this->assertArrayHasKey(1, $attempt2->removed);
            $this->assertArrayHasKey(2, $attempt2->removed);
            
            $this->assertEquals("wqeqwrqw", $attempt0->removed[0]);

            $this->assertEquals("qw", $attempt1->removed[0]);
            $this->assertEquals("dqwerqw", $attempt1->removed[1]);

            $this->assertEquals("wqedqwe", $attempt2->removed[0]);
            $this->assertEquals("qw", $attempt2->removed[1]);
            $this->assertEquals("dqwerqw", $attempt2->removed[2]);

            $tmp = __DIR__.'/f'.uuid().'.csv';
            yield save($tmp, <<<CSV
                id,sms_phone
                103343262,6478342944
                103300640,730276061
                103395136,381644138004
                103332664,6085012947
                103317353,847266801
                103231021,_DELETED_1486284887
                103266195,639156553262_DELETED_1486721886
                CSV);
            $this->assertTrue(yield $parser->exists($tmp));
            $items = $parser->parse($tmp);
            $this->assertEquals("6478342944", $items[0]->corrected);
            $this->assertEquals("730276061", $items[1]->corrected);
            $this->assertEquals("381644138004", $items[2]->corrected);
            $this->assertEquals("6085012947", $items[3]->corrected);
            $this->assertEquals("847266801", $items[4]->corrected);
            $this->assertEquals("1486284887", $items[5]->corrected);
            $this->assertEquals("6391565532621486721886", $items[6]->corrected);
            yield $parser->delete($tmp);
            $this->assertFalse(yield $parser->exists($tmp));

            $attempt3 = $items->offsetGet(5);
            $attempt4 = $items->offsetGet(6);


            $this->assertArrayHasKey(0, $attempt3->removed);
            $this->assertArrayHasKey(0, $attempt4->removed);

            $this->assertEquals("_DELETED_", $attempt3->removed[0]);
            $this->assertEquals("_DELETED_", $attempt4->removed[0]);
        });
    }
}