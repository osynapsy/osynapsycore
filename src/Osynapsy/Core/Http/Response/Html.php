<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Http\Response;

use Osynapsy\Kernel;

class Html extends Base
{
    public $template = null;

    public function __construct()
    {
        parent::__construct('text/html');
        $this->body = ['main' => []];
    }

    public function addBufferToContent($path = null, $part = 'main')
    {
        $this->addContent($this->replaceContent(self::getBuffer($path)), $part);
    }

    private function replaceContent($buffer)
    {
        $dummy = array_map(function ($v) { return '<!--'.$v.'-->'; }, array_keys($this->body));
        $parts = array_map(function ($p) { return is_array($p) ? implode("\n",$p) : $p; }, array_values($this->body));
        return str_replace($dummy, $parts, $buffer);
    }

    public function __toString()
    {
        $this->sendHeader();
        $this->buildResponse();
        if (!empty($this->template)) {
            return $this->replaceContent($this->template);
        }
        $response = '';
        foreach ($this->body as $content) {
            $response .= is_array($content) ? implode('',$content) : $content;
        }
        return $response;
    }

    //overwrite
    protected function buildResponse()
    {
        //overwrite this method for extra content manipulation
    }

    public function addJs($path, $id = false)
    {
        $this->addContent(sprintf('<script src="%s"%s></script>', $path, $id ? ' id="'.$id.'"': ''), 'js', true);
    }

    public function addJsCode($code)
    {
        $this->addContent('<script>'.PHP_EOL.$code.PHP_EOL.'</script>', 'js', true);
    }

    public function addCss($path)
    {
        $this->addContent('<link href="'.$path.'" rel="stylesheet" />', 'css', true);
    }

    public function addStyle($style)
    {
        $this->addContent('<style>'.PHP_EOL.$style.PHP_EOL.'</style>', 'css', true);
    }

    public function resetTemplate()
    {
        $this->template = '';
    }

    public function appendLibrary(array $optionalLibrary = [], $appendFormController = true)
    {
        foreach ($optionalLibrary as $pathLibrary) {
            if (strpos($pathLibrary, '.css') !== false) {
                $this->addCss('/assets/osynapsy/'.Kernel::VERSION.$pathLibrary);
                continue;
            }
            $this->addJs('/assets/osynapsy/'.Kernel::VERSION.$pathLibrary);
        }
        if (!$appendFormController) {
            return;
        }
        $this->addJs('/assets/osynapsy/'.Kernel::VERSION.'/js/Osynapsy.js', 'osynapsyjs');
        $this->addCss('/assets/osynapsy/'.Kernel::VERSION.'/css/style.css');
    }

    public function loadTemplate($filename, $object = [])
    {
        if (empty($filename)) {
            return;
        }
        $this->template = $this->getBuffer($filename, $object);
    }
}
