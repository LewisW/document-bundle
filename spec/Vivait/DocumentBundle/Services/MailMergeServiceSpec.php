<?php

namespace spec\Vivait\DocumentBundle\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Vivait\DocumentBundle\Services\MailMergeService;

/**
 * @mixin MailMergeService
 */
class MailMergeServiceSpec extends ObjectBehavior
{
    private $sample1;

    function let() {
        $this->sample1 = realpath(__DIR__ .'/../../../Sample1.docx');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Vivait\DocumentBundle\Services\MailMergeService');
    }

    function it_can_merge_docx_files()
    {
        $this->mergeFile($this->sample1);
    }

    function it_can_extract_root_elements() {
        $node1 = [
          'id' => 1,
          'name' => 'test',
        ];
        $root1 = [
          'id' => 2,
          'name' => 'test2'
        ];

        $this->extractRoots([
              'node' => $node1 + ['root1' => $root1]
          ], ['root1', 'root2'])
        ->shouldReturn([
              'node' => $node1,
              'root1' => $root1
          ]);
    }
}
