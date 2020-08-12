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
 * Date: 11/12/2019
 * Time: 10:04
 */

namespace App\Util;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Class ErrorMessageHelper
 * @package App\Util
 */
class ErrorMessageHelper
{
    /**
     * onlyInvalidInputsMessage
     * error1 = return.error.1 = Your request failed because your inputs were invalid.
     * @param bool $translate
     * @return array|string
     */
    public static function onlyInvalidInputsMessage(bool $translate = false)
    {
        return $translate ? TranslationHelper::translate('return.error.1', [], 'messages') : ['return.error.1', [], 'messages'];
    }

    /**
     * getInvalidInputsMessage
     * @param array $data
     * @param bool $translate
     * @return array
     */
    public static function getInvalidInputsMessage(array $data = [], bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.error.1', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'error', 'message' => self::onlyInvalidInputsMessage($translate)];
        $data['status'] = 'error';
        $data['key'][] = 'return.error.1';
        return $data;
    }

    /**
     * onlyDatabaseErrorMessage
     * error2 = return.error.2 = Your request failed due to a database error.
     * @param bool $translate
     * @return array|string
     */
    public static function onlyDatabaseErrorMessage(bool $translate = false)
    {
        return $translate ? TranslationHelper::translate('return.error.2', [], 'messages') : ['return.error.2', [], 'messages'];
    }

    /**
     * getInvalidInputsMessage
     * @param array $data
     * @param bool $translate
     * @return array
     */
    public static function getDatabaseErrorMessage(array $data = [], bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.error.2', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'error', 'message' => self::onlyDatabaseErrorMessage($translate)];
        $data['status'] = 'error';
        $data['key'][] = 'return.error.2';
        return $data;
    }

    /**
     * onlySuccessMessage
     * success0 = return.success.0 = Your request was completed successfully.
     * @param bool $translate
     * @return array|string
     */
    public static function onlySuccessMessage(bool $translate = false)
    {
        return $translate ? TranslationHelper::translate('return.success.0', [], 'messages') : ['return.success.0', [], 'messages'];
    }

    /**
     * getSuccessMessage
     * @param array $data
     * @param bool $translate
     * @return array
     */
    public static function getSuccessMessage(array $data = [], bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.success.0', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'success', 'message' => self::onlySuccessMessage($translate)];
        $data['key'][] = 'return.success.0';
        $data['status'] = 'success';
        return $data;
    }

    /**
     * getInvalidInputsMessage
     * @param array $data
     * @param bool $translate
     * @return array
     */
    public static function getInvalidTokenMessage(array $data = [], bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.error.csrf', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'error', 'message' => ($translate ? TranslationHelper::translate('return.error.csrf', [], 'messages') : ['return.error.csrf', [], 'messages'])];
        $data['status'] = 'error';
        $data['key'][] = 'return.error.csrf';
        return $data;
    }


    /**
     * uniqueErrors
     * @param array $data
     * @param bool $translate
     * @return array
     */
    public static function uniqueErrors(array $data, bool $translate = false): array
    {
        $data['errors'] = array_unique(isset($data['errors']) ? $data['errors'] : [], SORT_REGULAR);

        if ($translate){
            foreach($data['errors'] as $q=>$error) {
                if (is_array($error['message']) && count($error['message']) === 3)
                    $data['errors'][$q]['message'] = TranslationHelper::translate($error['message'][0],$error['message'][1],$error['message'][2]);
                else
                    $data['errors'][$q]['message'] = TranslationHelper::translate($error['message']);
            }
        }

        $data['errors'] = array_unique($data['errors'], SORT_REGULAR);
        return $data;
    }

    /**
     * convertToFlash
     * @param array $data
     * @param FlashBagInterface $flashBag
     */
    public static function convertToFlash(array $data, FlashBagInterface $flashBag)
    {
        foreach(self::uniqueErrors($data)['errors'] as $error) {
            $flashBag->add($error['class'], $error['message']);
        }
    }

    /**
     * onlyFileTransferMessage
     * return.error.file_transfer = Your request failed due to a file transfer issue.
     * @param bool $translate
     * @return array|string
     */
    public static function onlyFileTransferMessage(bool $translate = false)
    {
        return $translate ? TranslationHelper::translate('return.error.file_transfer', [], 'messages') : ['return.error.file_transfer', [], 'messages'];
    }

    /**
     * getInvalidInputsMessage
     * @param array $data
     * @param bool $translate
     * @return array
     */
    public static function getFileTransferMessage(array $data = [], bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.error.file_transfer', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'error', 'message' => self::onlyFileTransferMessage($translate)];
        $data['status'] = 'error';
        $data['key'][] = 'return.error.file_transfer';
        return $data;
    }

    /**
     * onlyLockedRecordMessage
     * return.warning.3 = warning3 = The record "{id}" is locked and will not be deleted from class "{class}"
     * @param string $id
     * @param string $class
     * @param bool $translate
     * @return string|array
     */
    public static function onlyLockedRecordMessage(string $id, string $class, bool $translate = false)
    {
        return $translate ? TranslationHelper::translate('return.warning.3', ['{id}' => $id, '{class}' => $class], 'messages') : ['return.warning.3', ['{id}' => $id, '{class}' => $class], 'messages'];
    }

    /**
     * getLockedRecordMessage
     * @param array $data
     * @param string $id
     * @param string $class
     * @param bool $translate
     * @return array
     */
    public static function getLockedRecordMessage(array $data = [], string $id, string $class, bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.warning.3', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'warning', 'message' => self::onlyLockedRecordMessage($id, $class, $translate)];
        $data['status'] = 'warning';
        $data['key'][] = 'return.warning.3';
        return $data;
    }

    /**
     * onlyNothingToDoMessage
     * @param bool $translate
     * @return array|string|null
     * 19/07/2020 12:24
     */
    public static function onlyNothingToDoMessage(bool $translate = false)
    {
        return $translate ? TranslationHelper::translate('return.warning.4', [], 'messages') : ['return.warning.4', [], 'messages'];
    }

    /**
     * getNothingToDoMessage
     * @param array $data
     * @param bool $translate
     * @return array
     * 19/07/2020 12:24
     */
    public static function getNothingToDoMessage(array $data = [], bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.warning.4', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'warning', 'message' => self::onlyNothingToDoMessage($translate)];
        $data['status'] = 'warning';
        $data['key'][] = 'return.warning.4';
        return $data;
    }

    /**
     * onlyNoAccessMessage
     * @param bool $translate
     * @return array|string|null
     * 22/07/2020 11:53
     */
    public static function onlyNoAccessMessage(bool $translate = false)
    {
        return $translate ? TranslationHelper::translate('return.error.0', [], 'messages') : ['return.error.0', [], 'messages'];
    }

    /**
     * getNoAccessMessage
     * @param array $data
     * @param bool $translate
     * @return array
     * 22/07/2020 11:53
     */
    public static function getNoAccessMessage(array $data = [], bool $translate = false): array
    {
        if (key_exists('key', $data) && array_search('return.error.0', $data['key'])) {
            return $data;
        }
        $data['errors'][] = ['class' => 'error', 'message' => self::onlyNoAccessMessage($translate)];
        $data['status'] = 'error';
        $data['key'][] = 'return.error.0';
        return $data;
    }


    /**
     *
    $returns['success0'] = __('Your request was completed successfully.'); return.success.0
    $returns['error0'] = __('Your request failed because you do not have access to this action.'); return.error.0
    $returns['error1'] = __('Your request failed because your inputs were invalid.'); return.error.1
    $returns['error2'] = __('Your request failed due to a database error.'); return.error.2
    $returns['error3'] = __('Your request failed because your inputs were invalid.'); return.error.3
    $returns['error4'] = __('Your request failed because your passwords did not match.'); return.error.4
    $returns['error5'] = __('Your request failed because there are no records to show.'); return.error.5
    $returns['error6'] = __('Your request was completed successfully, but there was a problem saving some uploaded files.'); return.error.6
    $returns['error7'] = __('Your request failed because some required values were not unique.'); return.error.7
    $returns['error8'] = __('Your request failed because some values are still in use within the data.'); return.error.8
    $returns['warning0'] = __('Your optional extra data failed to save.'); return.warning.0
    $returns['warning1'] = __('Your request was successful, but some data was not properly saved.'); return.warning.1
    $returns['warning2'] = __('Your request was successful, but some data was not properly deleted.'); return.warning.2
    $returns['warning3'] = __('The record "{id}" is locked and will not be deleted from class "{class}".'); return.warning.3
    $returns['warning4'] = __('Your request did not require any action.'); return.warning.4

     */
}