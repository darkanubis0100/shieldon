<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Firewall;

use Shieldon\Firewall\Utils\Collection;
use Shieldon\Firewall\Utils\Session;
use Shieldon\Psr17\ResponseFactory;
use Shieldon\Psr17\ServerRequestFactory;
use Shieldon\Psr7\Response;
use Shieldon\Psr7\ServerRequest;

/*
 * An object-oriented layer for the HTTP specification.
 */
class HttpFactory
{
    /**
     * Create a server-side request.
     *
     * @return ServerRequest
     */
    public static function createRequest(): ServerRequest
    {
        return ServerRequestFactory::fromGlobal();
    }

    /**
     * Create a server-side response
     *
     * @return Response
     */
    public static function createResponse(): Response
    {
        return ResponseFactory::fromNew();
    }

    /**
     * Create a Session collection from superglobal.
     * This method is not a PSR-7 pattern.
     *
     * @param string $id Session ID
     *
     * @return Collection
     */
    public static function createSession($id = ''): Collection
    {
        $session = new Session($id);

        return $session->createFromGlobal();
    }
}