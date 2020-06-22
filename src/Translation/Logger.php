<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 * 
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/03/2020
 * Time: 11:55
 */

namespace App\Translation;

use Monolog\Handler\StreamHandler;

/**
 * Class Logger
 * @package App\Translation
 */
class Logger extends StreamHandler
{
    /**
     * Write to stream
     * @param resource $stream
     * @param array $record
     */
    protected function streamWrite($stream, array $record): void
    {
        if (in_array($record['context']['id'], ['__name__label__','',' '])) return ;
        $result = '';
        $result .= $record['context']['domain'] . ' - ' . $record['context']['locale'] . ' - ';
        $result .= $record['context']['id'] . ': ' . $record['context']['id'] ;

        fwrite($stream, $result . "\n");
    }
}