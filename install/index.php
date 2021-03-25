<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

IncludeModuleLangFile(__FILE__);

if (class_exists('Contact_Forms')) {
    return;
}

class Contact_Forms extends CModule
{
    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        $this->MODULE_ID = 'contact.forms';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'] ?? '';
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'] ?? '';
        $this->MODULE_NAME = Loc::getMessage('CONTACT_FORMS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('CONTACT_FORMS_MODULE_DESC');
        $this->PARTNER_NAME = '';
        $this->PARTNER_URI = '';
    }

    public static function getComponentNames()
    {
        return [
            'contact.form',
        ];
    }

    /**
     * Get application folder.
     * @return string /document/local (when exists) or /document/bitrix
     */
    public static function getRoot()
    {
        $local = $_SERVER['DOCUMENT_ROOT'] . '/local';
        if (1 === preg_match('#local[\\\/]modules#', __DIR__) && is_dir($local)) {
            return $local;
        }

        return $_SERVER['DOCUMENT_ROOT'] . BX_ROOT;
    }

    function DoInstall()
    {
        global $APPLICATION;

        /**
         * Check bitrix version.
         */
        if (!CheckVersion(ModuleManager::getVersion("main"), "14.00.00")) {
            $APPLICATION->ThrowException(
                Loc::getMessage("CONTACT_FORMS_MODULE_ERROR_MAIN_VERSION")
            );
        }

        /**
         * Install Database
         */

        /**
         * Install Events
         */

        /**
         * Install Files
         */
        CopyDirFiles(__DIR__ . '/components', static::getRoot() . '/components', true, true);

        ModuleManager::RegisterModule($this->MODULE_ID);
    }

    /**
     * @param string $componentName component folder name
     */
    public function deleteComponent($componentName, $vendor = '')
    {
        DeleteDirFilesEx($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/components/' . $vendor . $componentName);
        DeleteDirFilesEx($_SERVER['DOCUMENT_ROOT'] . '/local/components/' . $vendor . $componentName);
    }

    function DoUninstall()
    {
        /**
         * Uninstall Database
         */

        /**
         * Uninstall Events
         */

        /**
         * Uninstall Files
         */
        array_walk(static::getComponentNames(), [$this, 'deleteComponent']);

        UnRegisterModule($this->MODULE_ID);
    }
}