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
 * Date: 1/08/2019
 * Time: 13:58
 */

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * Class SubstringIndexFunction
 * @package App\Doctrine
 */
class SubstringIndexFunction extends FunctionNode
{
    public $str = null;
    public $delim = null;
    public $count = null;

    /**
     * @override
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'SUBSTRING_INDEX(' .
            $this->str->dispatch($sqlWalker) . ', ' .
            $this->delim->dispatch($sqlWalker) . ', ' .
            $this->count->dispatch($sqlWalker) .
            ')';
    }

    /**
     * @override
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->str = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->delim = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->count = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
