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

namespace Shieldon\FirewallTest\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\HttpResolver;
use Shieldon\Firewall\Helpers;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function call_user_func;
use function define;
use function explode;
use function in_array;
use function str_replace;
use function trim;
use function ucfirst;

trait RouteTestTrait
{
    /**
     * Route map.
     *
     * @var array
     */
    public $registerRoutes = [
        'ajax/changeLocale',
        'ajax/tryMessenger',
        'circle/filter',
        'circle/rule',
        'circle/session',
        'home/index',
        'home/overview',
        'iptables/ip4',
        'iptables/ip4status',
        'iptables/ip6',
        'iptables/ip6status',
        'report/actionLog',
        'report/operation',
        'security/authentication',
        'security/xssProtection',
        'setting/basic',
        'setting/exclusion',
        'setting/export',
        'setting/import',
        'setting/ipManager',
        'setting/messenger',
        'user/login',
        'user/logout',
    ];

    /**
     * IP address.
     *
     * @var stromg
     */
    public $ip = '';

    /**
     * Test routes.
     */
    public function route($output = true)
    {
        $basePath = 'firewall/panel';
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->getKernel()->remove('component');

        if (!empty($this->ip)) {
            $firewall->getKernel()->setIp($this->ip);
        }

        new Helpers();

        $resolver = new HttpResolver();

        $request = get_request();
        $response = get_response();

        $path = trim($request->getUri()->getPath(), '/');
        $base = trim($basePath, '/');
        $urlSegment = trim(str_replace($base, '', $path), '/');

        if ($urlSegment === $basePath || $urlSegment === '') {
            $urlSegment = 'home/index';
        }

        $urlParts = explode('/', $urlSegment);

        $controller = $urlParts[0] ?? 'home';
        $method = $urlParts[1] ?? 'index';

        if (in_array("$controller/$method", $this->registerRoutes)) {
            if (!defined('SHIELDON_PANEL_BASE')) {
                define('SHIELDON_PANEL_BASE', $base);
            }

            $controller = '\Shieldon\Firewall\Panel\\' . ucfirst($controller);
            $controllerClass = new $controller();

            if ($output) {
                $resolver(call_user_func([$controllerClass, $method]));
            } else {
                return call_user_func([$controllerClass, $method]);
            }
            
        }

        if ($output) {
            $resolver($response->withStatus(404));
        } else {
            return $response->withStatus(404);
        }
    }

    /**
     * Check whether the page contains a string.
     *
     * @param string $uri    The page's URI path.
     * @param string $string Usually the page title.
     *
     * @return void
     */
    public function assertPageOutputContainsString(string $uri, string $string)
    {
        $_SERVER['REQUEST_URI'] = '/' . trim($uri, '/');

        ob_start();
        $this->route();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString($string, $output);
    }

    /**
     * Check whether the page contains a string.
     *
     * @param string $uri    The page's URI path.
     * @param string $string Usually the page title.
     *
     * @return void
     */
    public function getRouteResponse(string $uri): ResponseInterface
    {
        $_SERVER['REQUEST_URI'] = '/' . trim($uri, '/');

        return $this->route(false);
    }

    /**
     * Set IP address.
     *
     * @param string $ip
     *
     * @return void
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }
}