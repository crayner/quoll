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
 * Date: 2/10/2020
 * Time: 08:02
 */
namespace App\Manager;

use App\Manager\Hidden\WarningItem;
use App\Modules\School\Util\AcademicYearHelper;
use App\Twig\SidebarContent;
use App\Util\TranslationHelper;
use App\Util\UrlGeneratorHelper;
use Twig\Environment;

/**
 * Class WarningManager
 * @package App\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class WarningManager
{
    /**
     * @var SidebarContent
     */
    private SidebarContent $sideBar;

    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var array
     */
    private array $warnings = [];

    /**
     * WarningManager constructor.
     * @param SidebarContent $sideBar
     */
    public function __construct(SidebarContent $sideBar, Environment $twig)
    {
        $this->sideBar = $sideBar;
        $this->twig = $twig;
    }

    /**
     * @return SidebarContent
     */
    public function getSideBar(): SidebarContent
    {
        return $this->sideBar;
    }

    /**
     * getWarnings
     *
     * 25/09/2020 15:53
     */
    public function getWarnings(): array
    {
        if ($this->warnings === []) {
            $result = [];
            if (AcademicYearHelper::getCurrentAcademicYear()->getStatus() !== 'Current') {
                $warning = new WarningItem();
                $result['academicYear'] = $warning->setTitle(TranslationHelper::translate('The academic year {name} is not the current academic year.', ['{name}' => AcademicYearHelper::getCurrentAcademicYear()->getName()], 'School'))
                    ->setLabel(TranslationHelper::translate('Academic Year: {name}', ['{name}' => AcademicYearHelper::getCurrentAcademicYear()->getName()], 'School'))
                    ->setLink(UrlGeneratorHelper::getUrl('preferences'))
                    ->setTwig($this->getTwig());
            }
            $this->warnings = $result;

            foreach ($this->warnings as $warning) {
                $this->getSideBar()->addContent($warning);
            }
        }

        return $this->warnings;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * getStatus
     *
     * 2/10/2020 12:41
     * @return false|mixed|string
     */
    public function getStatus()
    {
        $result = false;
        foreach ($this->getWarnings() as $warning) {
            if ($result === false) $result = $warning->getLevel();
            if (!in_array($result, ['warning','error']) && in_array($warning->getLevel(), ['warning','error'])) $result = $warning->getLevel();
            if ($result === 'error') break;
        }
        return $result;
    }
}
