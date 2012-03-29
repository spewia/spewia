<?php

namespace Tests\Spewia\Template;

use Spewia\Template\TwigTemplate;
use org\bovigo\vfs\vfsStream;

/**
 * @runTestsInSeparateProcesses
 */
class TwigTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwigTemplate
     */
    protected $object;
    protected $base_folder;
    protected $file;

    public function setUp()
    {
        // Setup dummy filesystem.
        $this->base_folder = vfsStream::setup('root');

        $this->file = vfsStream::newFile('dummy.tpl');
        $this->base_folder->addChild($this->file);

        // SetUp the SUT.
        $this->object = new TwigTemplate();
        $this->object->addFolder(vfsStream::url('root'));
    }

    public function testRenderWithoutProperties()
    {
        $file_content = <<<EOF
dummy
EOF;

        $this->file->setContent($file_content);

        $this->object->setTemplateFile('dummy.tpl');
        $result = $this->object->render();

        $this->assertEquals('dummy', $result);
    }

    public function testRenderWithAParameter()
    {
        $file_content = <<<EOF
du{{ var }}
EOF;

        $this->file->setContent($file_content);

        $this->object->setTemplateFile('dummy.tpl');
        $this->object->assign('var', 'mmy');

        $result = $this->object->render();

        $this->assertEquals('dummy', $result);
    }
}
