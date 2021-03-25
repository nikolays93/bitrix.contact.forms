<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/** @var string */
$moduleId = 'contact.forms';
/** @var array */
$aTabs = array(
    array(
        'DIV' => 'Captcha',
        'TAB' => Loc::getMessage('CONTACT_FORMS_TAB_SPAM_PROTECTION'),
        'OPTIONS' => array(
            array(
                'CAPTCHA_ENABLED',
                Loc::getMessage('CONTACT_FORMS_PARAM_CAPTCHA_ENABLED_TEXT'),
                null,
                array('checkbox', 0, '', 'N', '', 'N')
            ),
            array(
                'CAPTCHA_TYPE',
                Loc::getMessage('CONTACT_FORMS_PARAM_CAPTCHA_TYPE_TEXT'),
                null,
                array('selectbox', array(
                    'bitrix' => 'Bitrix built in',
                    'grecaptcha' => 'Google reCaptcha',
                )),
            )
        ),
    ),
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && strlen($_REQUEST['save'] ?? '') > 0 && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        __AdmSettingsSaveOptions($moduleId, $aTab['OPTIONS']);
    }

    LocalRedirect($APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID .
        '&mid_menu=1' .
        '&mid=' . urlencode($moduleId) .
        '&tabControl_active_tab=' . urlencode($_REQUEST['tabControl_active_tab']) .
        '&sid=' . urlencode(SITE_ID));
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);

echo '<form method="post" action="" name="bootstrap">';

$tabControl->Begin();

foreach ($aTabs as $aTab) {
    $tabControl->BeginNextTab();
    __AdmSettingsDrawList($moduleId, $aTab['OPTIONS']);
}

echo bitrix_sessid_post();

$tabControl->Buttons(array(
    'btnApply' => false,
    'btnCancel' => false,
    'btnSaveAndAdd' => false
));
$tabControl->End();

echo '</form>';
