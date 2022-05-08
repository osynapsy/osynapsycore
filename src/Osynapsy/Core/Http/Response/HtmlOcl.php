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

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

/**
 * Build a special Html Response fro View with Ocl and Bcl view
 *
 */
class HtmlOcl extends Html
{
    protected function buildResponse()
    {
        $componentIds = [];
        if (!empty($_SERVER['HTTP_OSYNAPSY_HTML_COMPONENTS'])) {
            $componentIds = explode(';', filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_HTML_COMPONENTS'));
        }
        if (!empty($componentIds)) {
            $this->buildComponents($componentIds);
            return;
        }
        $this->processComponentRequirements(Component::getRequire());
    }

    private function buildComponents($componentIds)
    {
        $this->resetTemplate();
        $this->resetContent();
        $response = new Tag('div','response');
        foreach($componentIds as $id) {
            $response->add(Component::getById($id));
        }
        $this->addContent($response);
    }

    private function processComponentRequirements($requires)
    {
        if (empty($requires)) {
            return;
        }
        foreach ($requires as $type => $urls) {
            $this->appendRequirement($type, $urls);
        }
    }

    private function appendRequirement($type, $urls)
    {
        foreach ($urls as $url){
            switch($type) {
                case 'js':
                    $this->addJs($url);
                    break;
                case 'jscode':
                    $this->addJsCode($url);
                    break;
                case 'css':
                    $this->addCss($url);
                    break;
            }
        }
    }
}
