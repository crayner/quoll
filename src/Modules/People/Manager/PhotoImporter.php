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
 * Date: 16/12/2019
 * Time: 15:18
 */
namespace App\Modules\People\Manager;

use App\Manager\SpecialInterface;
use App\Modules\People\Entity\Person;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use App\Util\TranslationHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Util\StringUtil;

/**
 * Class PhotoImporter
 * @package App\Modules\People\Manager
 */
class PhotoImporter implements SpecialInterface
{
    /**
     * @var string
     */
    private $path = __DIR__.'/../../../../../public/uploads/imports';

    /**
     * getPath
     * @return string
     */
    public function getPath(): string
    {
        if (!is_dir($this->path))
        {
            $fs = new Filesystem();
            $fs->mkdir($this->path, 0755);
        }

        return realpath($this->path);
    }

    /**
     * clearPhotos
     */
    public function clearPhotos()
    {
        $finder = new Finder();
        $fs = new Filesystem();
        $finder->files()->in($this->getPath());
        if ($finder->hasResults())
            foreach($finder as $file)
                $fs->remove($file->getRealPath());
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        $result['people'] = ProviderFactory::create(Person::class)->groupedChoiceList();
        $result['absolute_url'] = ParameterBagHelper::get('absoluteURL');
        $result['messages'] = $this->getTranslations();
        $result['name'] = $this->getName();
        return $result;
    }

    /**
     * getTranslations
     * @return array
     */
    private function getTranslations(): array
    {
        $tx = [];
        TranslationHelper::setDomain('People');
        $tx['Drop Image Here'] = TranslationHelper::translate('Drop Image Here');
        $tx['Target Person'] = TranslationHelper::translate('Target Person');
        $tx['target_person_help'] = TranslationHelper::translate('Select the person, then drag the image from your computer to set the image for this person.');
        $tx['Remove Photo'] = TranslationHelper::translate('Remove Photo');
        $tx['error_ratio'] = TranslationHelper::translate('The image must ratio of {ratio}:1 is outside the allowed limits of 0.7:1 to 0.84:1.');
        $tx['error_height_width'] = TranslationHelper::translate('The height and width maximum is 960px x 720px. The image supplied was {height} x {width}x.');
        $tx['error_height_width_minimum'] = TranslationHelper::translate('The height and width minimum is 320px x 240px. The image supplied was {height} x {width}x.');
        $tx['error_size'] = TranslationHelper::translate('The file is too big. Max 750k. File size given is {size}.');
        $tx['aborted'] = TranslationHelper::translate('{name} upload failed...');
        $tx['Target this person...'] = TranslationHelper::translate('Target this person...');
        $tx['Replace this image'] = TranslationHelper::translate('Replace this image');
        $tx['Images [.jpg, .png, .jpeg, .gif] only'] = TranslationHelper::translate('Images [.jpg, .png, .jpeg, .gif] only');
        $tx['Import Images'] = TranslationHelper::translate('Import Images');
        $tx['placeholder'] = TranslationHelper::translate('Start typing a name...', [], 'messages');
        $tx['Notes'] = TranslationHelper::translate('Notes');
        $tx['drag_drop_page'] = TranslationHelper::translate('Use this page to drag and drop images from your computer to the site for the targeted individual. Existing images are replaced.');
        $tx['File Name - The system modifies the filename when linked to the correct person.'] = TranslationHelper::translate('File Name - The system modifies the filename when linked to the correct person.');
        $tx['File Type * - Images must be formatted as JPG or PNG.'] = TranslationHelper::translate('File Type * - Images must be formatted as JPG, GIF or PNG.');
        $tx['Image Size * - Displayed at 240px by 320px.'] = TranslationHelper::translate('Image Size * - Displayed at 240px by 320px.');
        $tx['Size Range * - Accepts images up to 720px by 960px.'] = TranslationHelper::translate('Size Range * - Accepts images up to 720px by 960px.');
        $tx['Aspect Ratio Range * - Accepts aspect ratio between 0.7:1 and 0.84:1.'] = TranslationHelper::translate('Aspect Ratio Range * - Accepts aspect ratio between 0.7:1 and 0.84:1.');
        TranslationHelper::setDomain('messages');
        return $tx;
    }

    /**
     * getName
     * @return string
     */
    public function getName(): string
    {
        return StringUtil::fqcnToBlockPrefix(static::class) ?: '';
    }
}