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
 * Date: 25/09/2020
 * Time: 15:38
 */
namespace App\Manager\Hidden;

use App\Twig\SidebarContentInterface;
use App\Twig\SidebarContentTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class WarningItem
 * @package App\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class WarningItem implements SidebarContentInterface
{
    use SidebarContentTrait;

    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private string $label;

    /**
     * @var string
     */
    private string $level = 'warning';

    /**
     * @var string
     */
    private string $link;

    /**
     * @var string
     */
    private $position = 'top';

    /**
     * @var int
     */
    private $priority = 1;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return isset($this->title) ? $this->title : null;
    }

    /**
     * @param string $title
     * @return WarningItem
     */
    public function setTitle(string $title): WarningItem
    {
        $this->title = $title;
        if ($this->getLabel() === null) $this->setLabel($title);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return isset($this->label) ? $this->label : null;
    }

    /**
     * @param string $label
     * @return WarningItem
     */
    public function setLabel(string $label): WarningItem
    {
        $this->label = $label;
        if ($this->getTitle() === null) $this->setTitle($label);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLevel(): ?string
    {
        return isset($this->level) ? $this->level : null;
    }

    /**
     * @param string $level
     * @return WarningItem
     */
    public function setLevel(string $level): WarningItem
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return isset($this->link) ? $this->link : null;
    }

    /**
     * @param string $link
     * @return WarningItem
     */
    public function setLink(string $link): WarningItem
    {
        $this->link = $link;
        return $this;
    }

    /**
     * toArray
     *
     * 25/09/2020 15:45
     * @return array
     */
    public function toArray(): array
    {
        return [
            'content' => $this->render([]),
        ];
    }

    /**
     * render
     *
     * 2/10/2020 08:21
     * @param array $options
     * @return string
     */
    public function render(array $options): string
    {
        try {
            return $this->getTwig()->render('components/warning.html.twig', [
                'warning' => $this,
            ]);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            throw $e;
        }
        return '';
    }

    /**
     * getName
     *
     * 2/10/2020 08:07
     * @return string
     */
    public function getName(): string
    {
        return 'warnings';
    }
}
