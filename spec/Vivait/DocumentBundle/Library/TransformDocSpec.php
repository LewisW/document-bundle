<?php

namespace spec\Vivait\DocumentBundle\Library;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Vivait\DocumentBundle\Library\TransformDoc;

/**
 * @mixin TransformDoc
 */
class TransformDocSpec extends ObjectBehavior
{
    function it_should_open_docx_files() {
        $this->setstrFile(__DIR__ . DIRECTORY_SEPARATOR .'Sample1.docx');
        $this->generatePDF()->shouldBe('test');
    }
}
