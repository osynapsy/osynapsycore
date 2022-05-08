<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Core\Http;

use Osynapsy\Data\Dictionary;

class Request extends Dictionary
{
    /**
     * Constructor.
     *
     * @param array           $get        The GET parameters
     * @param array           $post       The POST parameters
     * @param array           $request    The REQUEST attributes (parameters parsed from the PATH_INFO, ...)
     * @param array           $cookies    The COOKIE parameters
     * @param array           $files      The FILES parameters
     * @param array           $server     The SERVER parameters
     * @param string|resource $content    The raw body data
     *
     * @api
     */
    public function __construct(array $get = [], array $post = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->set('get', $get)
             ->set('post', $post)
             ->set('request', $request)
             ->set('cookies', $cookies)
             ->set('files', $files)
             ->set('server', $server)
             ->set('content', $content);
        $rawHost = (isset($server['HTTPS']) && $server['HTTPS'] == 'on') ? 'https://' : 'http://';
        $rawHost .= $this->get('server.HTTP_HOST');
        $url = $rawHost.$this->get('server.REQUEST_URI');
        $this->set('page.url', $url);
        $this->set('page.uri', $this->get('server.REQUEST_URI'));
        $this->set('server.RAW_URL_PAGE', $url);
        $this->set('server.RAW_URL_SITE', $rawHost);
        $this->set('server.url', $rawHost);
        if (!empty($server['HTTP_ACCEPT'])) {
            $this->set('client.accept', explode(',', $server['HTTP_ACCEPT']));
        }
    }

    public function getAcceptedContentType()
    {
        return $this->get('client.accept');
    }
}
