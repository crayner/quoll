<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 14/10/2019
 * Time: 16:59
 */
namespace App\Manager\Hidden;

use App\Util\TranslationHelper;

/**
 * Class Message
 * @package App\Manager\Entity
 */
class Message
{
    /**
     * @var string|null
     */
    private ?string $message;

    /**
     * @var string
     */
    private ?string $level;


    /**
     * @var string
     */
    private string $domain = 'messages';

    /**
     * @var array
     */
    private array $options = [];

    /**
     * @var null|string
     */
    private ?string $translatedMessage = null;

    /**
     * Message constructor.
     * @param string|null $level
     * @param string|null $message
     * @param array $options
     * @param string $domain
     */
    public function __construct(?string $level = null, ?string $message = null, array $options = [], string $domain = 'messages')
    {
        $this->message = $message;
        $this->level = $level;
        $this->domain = $domain;
        $this->options = $options;
    }


    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Message.
     *
     * @param string $message
     * @return Message
     */
    public function setMessage(string $message): Message
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * Level.
     *
     * @param string $level
     * @return Message
     */
    public function setLevel(string $level): Message
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Domain.
     *
     * @param string $domain
     * @return Message
     */
    public function setDomain(string $domain): Message
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * getOptions
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options = $this->options ?: [];
    }

    /**
     * Options.
     *
     * @param array $options
     * @return Message
     */
    public function setOptions(array $options): Message
    {
        $this->options = $options;
        return $this;
    }

    /**
     * addOption
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addOption(string $name, string $value): Message
    {
        $this->getOptions();
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getTranslatedMessage(): string
    {
        if (null === $this->translatedMessage) {
            $this->translatedMessage = TranslationHelper::translate($this->getMessage(), $this->getOptions(), $this->getDomain());
        }
        return $this->translatedMessage;
    }

    /**
     * TranslatedMessage.
     *
     * @param string|null $translatedMessage
     * @return Message
     */
    public function setTranslatedMessage(?string $translatedMessage): Message
    {
        $this->translatedMessage = $translatedMessage;
        return $this;
    }
}