<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ColourValidator
 * @package App\Validator
 */
class ColourValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private static $colourTypes = ['any', 'hex', 'rgb', 'rgba', 'hsl', 'hsla', 'name'];

    /**
     * @param mixed $colour
     * @param Constraint $constraint
     */
    public function validate($colour, Constraint $constraint)
    {
        if (null === $colour || '' === $colour)
            return;

        if (! in_array($constraint->enforceType, self::$colourTypes)) {
            $this->context->buildViolation($constraint->enforceMessage)
                ->setParameter('{format}', $constraint->enforceType)
                ->setParameter('{formats}', implode("','", self::$colourTypes))
                ->setCode(Colour::COLOUR_FORMAT_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
            return ;
        }

        $testedColour = self::isColour($colour, $constraint->enforceType);
        if ($testedColour !== false)
            return $testedColour;

        $this->context->buildViolation($constraint->message)
            ->setParameter('{colour}', $colour)
            ->setCode(Colour::COLOUR_VALIDATION_ERROR)
            ->setParameter('{format}', $constraint->enforceType)
            ->setTranslationDomain($constraint->transDomain )
            ->addViolation();
    }

    /**
     * isColour
     *
     * @param null|string $colour
     * @param string $enforceType
     * @return string|false
     */
    public static function isColour(?string $colour, string $enforceType = 'any') 
    {
        if (! in_array($enforceType, self::$colourTypes) || empty($colour))
            return false;

        $names = ['AliceBlue','AntiqueWhite','Aqua','Aquamarine','Azure','Beige','Bisque','Black','BlanchedAlmond','Blue','BlueViolet','Brown','BurlyWood','CadetBlue','Chartreuse','Chocolate','Coral','CornflowerBlue','Cornsilk','Crimson','Cyan','DarkBlue','DarkCyan','DarkGoldenRod','DarkGray','DarkGrey','DarkGreen','DarkKhaki','DarkMagenta','DarkOliveGreen','DarkOrange','DarkOrchid','DarkRed','DarkSalmon','DarkSeaGreen','DarkSlateBlue','DarkSlateGray','DarkSlateGrey','DarkTurquoise','DarkViolet','DeepPink','DeepSkyBlue','DimGray','DimGrey','DodgerBlue','FireBrick','FloralWhite','ForestGreen','Fuchsia','Gainsboro','GhostWhite','Gold','GoldenRod','Gray','Grey','Green','GreenYellow','HoneyDew','HotPink','IndianRed','Indigo','Ivory','Khaki','Lavender','LavenderBlush','LawnGreen','LemonChiffon','LightBlue','LightCoral','LightCyan','LightGoldenRodYellow','LightGray','LightGrey','LightGreen','LightPink','LightSalmon','LightSeaGreen','LightSkyBlue','LightSlateGray','LightSlateGrey','LightSteelBlue','LightYellow','Lime','LimeGreen','Linen','Magenta','Maroon','MediumAquaMarine','MediumBlue','MediumOrchid','MediumPurple','MediumSeaGreen','MediumSlateBlue','MediumSpringGreen','MediumTurquoise','MediumVioletRed','MidnightBlue','MintCream','MistyRose','Moccasin','NavajoWhite','Navy','OldLace','Olive','OliveDrab','Orange','OrangeRed','Orchid','PaleGoldenRod','PaleGreen','PaleTurquoise','PaleVioletRed','PapayaWhip','PeachPuff','Peru','Pink','Plum','PowderBlue','Purple','RebeccaPurple','Red','RosyBrown','RoyalBlue','SaddleBrown','Salmon','SandyBrown','SeaGreen','SeaShell','Sienna','Silver','SkyBlue','SlateBlue','SlateGray','SlateGrey','Snow','SpringGreen','SteelBlue','Tan','Teal','Thistle','Tomato','Turquoise','Violet','Wheat','White','WhiteSmoke','Yellow','YellowGreen'];
        foreach($names as $q=>$w)
            $names[$q] = strtolower($w);

        $colour = trim(str_replace(' ', '', $colour));

        if (in_array(strtolower($colour), $names) && in_array($enforceType, ['any', 'name']))
            return strtolower($colour);

        $regex = "/^(\#?){0,1}([a-f0-9]{3}){1,2}$/i";
        if (preg_match($regex, $colour) && in_array($enforceType, ['any', 'hex'])) {
            if (strlen($colour) === 6)
                $colour = '#'.$colour;
            if (strlen($colour) === 3) {
                $x = '#';
                for($i=0; $i<3; $i++)
                    $x .= $colour[$i].$colour[$i];
                $colour = $x;
            }
            return strtoupper($colour);
        }

        $regex = "/^rgb\((0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d)\)$/";
        if (preg_match($regex, $colour) && in_array($enforceType, ['any', 'rgb']))
            return $colour;

        $regex = "/^rgba\((0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),(0?\.([\d]{1,2})|1(\.0)?)\)$/";
        if (preg_match($regex, $colour) && in_array($enforceType, ['any', 'rgba']))
            return $colour;

        $regex = "/^hsl\((0|360|35\d|3[0-4]\d|[12]\d\d|0?\d?\d),(0|100|\d{1,2})%,(0|100|\d{1,2})%\)$/";
        if (preg_match($regex, $colour) && in_array($enforceType, ['any', 'hsl']))
            return $colour;

        $regex = "/^hsla\((0|360|35\d|3[0-4]\d|[12]\d\d|0?\d?\d),(0|100|\d{1,2})%,(0|100|\d{1,2})%,(0?\.\d|1(\.0)?)\)$/";
        if (preg_match($regex, $colour) && in_array($enforceType, ['any', 'hsla']))
            return $colour;

        return false;
    }
}