<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/11/2019
 * Time: 05:40
 */
namespace App\Modules\Library\Manager;

use App\Modules\Library\Entity\Library;
use App\Provider\ProviderFactory;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class LibraryHelper
 * @package App\Modules\Library\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class LibraryHelper
{
    /**
     * @var SessionInterface
     */
    private static $session;

    /**
     * LibraryHelper constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        self::$session = $session;
    }

    /**
     * @return SessionInterface
     */
    public static function getSession(): SessionInterface
    {
        return self::$session;
    }

    /**
     * @param SessionInterface $session
     */
    public static function setSession(SessionInterface $session): void
    {
        self::$session = $session;
    }

    /**
     * @var null|Library
     */
    private static $currentLibrary;

    /**
     * getCurrentLibrary
     * @return Library|null
     * 8/06/2020 10:06
     */
    public static function getCurrentLibrary(): ?Library
    {

        if (null !== self::$currentLibrary) {
            return self::$currentLibrary;
        }

        if (self::getSession()->has('current_library')) {
            self::$currentLibrary = ProviderFactory::getRepository(Library::class)->find(self::getSession()->get('current_library'));
            return self::$currentLibrary;
        }

        $result = ProviderFactory::getRepository(Library::class)->findOneBy(['main' => true]);

        return $result ? self::setCurrentLibrary($result) : self::setCurrentLibrary(null);
    }

    /**
     * setCurrentLibrary
     * @param Library|null $library
     * @return Library|null
     * 9/06/2020 11:13
     */
    public static function setCurrentLibrary(?Library $library): ?Library
    {
        if ($library === null || $library->getId() === null) {
            self::getSession()->remove('current_library');
            self::$currentLibrary = null;
            return null;
        } else {
            self::getSession()->set('current_library', $library->getId());
            self::$currentLibrary = $library;
        }
        return $library;
    }
}
