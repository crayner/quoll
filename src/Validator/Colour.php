<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Colour
 * @package App\Validator
 * @Annotation
 */
class Colour extends Constraint
{
    const COLOUR_VALIDATION_ERROR = '3e398a9b-6d9a-4a93-b824-032eb91bb82a';
    const COLOUR_FORMAT_ERROR = '40ad26de-b631-4eb7-9279-8332f53dd185';

    protected static $errorNames = [
        self::COLOUR_VALIDATION_ERROR => 'COLOUR_VALIDATION_ERROR',
        self::COLOUR_FORMAT_ERROR => 'COLOUR_FORMAT_ERROR',
    ];

    /**
     * @var string
     */
    public $message = 'The colour {colour} is not valid. Must be format {format}.';

    /**
     * @var string
     */
    public $enforceMessage = 'The colour not the correct type. Is {format} but must be one of {formats}';

    /**
     * @var string
     */
    public $transDomain = 'messages';

    /**
     * @var string
     */
    public $enforceType = 'any';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return ColourValidator::class;
    }
}