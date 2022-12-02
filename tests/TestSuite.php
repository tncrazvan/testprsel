<?php

use Amp\Loop;

use function App\normalize;
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

            $attempt1 = $parser->tryCorrect(0, "123123wqeqwrqw");
            $attempt2 = $parser->tryCorrect(0, "12345678912qw3dqwerqw");
            $attempt3 = $parser->tryCorrect(0, "wqedqwe12345678912qw3dqwerqw");

            $this->assertEquals("123123", $attempt1->corrected);
            $this->assertEquals("123456789123", $attempt2->corrected);
            $this->assertEquals("123456789123", $attempt3->corrected);

            $this->assertFalse($parser->isCorrect($attempt1->corrected));
            $this->assertTrue($parser->isCorrect($attempt2->corrected));
            $this->assertTrue($parser->isCorrect($attempt3->corrected));


            $tmp = 'f'.uuid().'.csv';
            yield $parser->save($tmp, <<<CSV
                id,sms_phone
                103343262,6478342944
                103300640,730276061
                103395136,381644138004
                103332664,6085012947
                103317353,847266801
                103231021,_DELETED_1486284887
                103266195,639156553262_DELETED_1486721886
                CSV);
            $items = $parser->parse($tmp);
            echo "ok";
        });
    }
}