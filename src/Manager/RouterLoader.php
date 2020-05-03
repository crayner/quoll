<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 16/04/2020
 * Time: 16:25
 */

namespace App\Manager;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouterLoader
 * @package App\Manager
 */
class RouterLoader extends Loader
{
    /**
     * @var bool
     */
    private $isLoaded = false;

    /**
     * load
     * @param mixed $resource
     * @param string|null $type
     * @return RouteCollection
     */
    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded)
            throw new \RuntimeException('Do not add the "quoll" loader twice');

        $routes = new RouteCollection();
        $finder = new Finder();
        $bundles = $finder->directories()->depth('0')->in(__DIR__ . '/../Modules');
        if (false === $finder->hasResults())
            return $routes;

        foreach($bundles as $bundle)
        {
            $resource = realpath($bundle . '/Controller/');

            if (false !== $resource) {
                $type = 'annotation';

                $importedRoutes = $this->import($resource, $type);

                $routes->addCollection($importedRoutes);
            }
        }

        $this->isLoaded = true;

        return $routes;

    }

    /**
     * supports
     * @param mixed $resource
     * @param string|null $type
     * @return bool
     */
    public function supports($resource, string $type = null)
    {
        return 'quoll' === $type;
    }

}