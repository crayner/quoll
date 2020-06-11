<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 19/07/2019
 * Time: 10:05
 */
namespace App\Util;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class ErrorHelper
 * @package App\Util
 */
class ErrorHelper
{
    /**
     * @var Environment
     */
    private static $twig;

    /**
     * ErrorHelper constructor.
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        self::$twig = $twig;
    }

    /**
     * ErrorResponse
     * @param string $extendedError
     * @param array $extendedParams
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function ErrorResponse(string $extendedError = '', array $extendedParams = [], $manager = null)
    {
        $content = self::$twig->render('legacy/error.html.twig',
            [
                'extendedError' => $extendedError,
                'extendedParams' => $extendedParams,
                'manager' => $manager,
            ]
        );

        $response = new Response($content);
        return $response;
    }
}