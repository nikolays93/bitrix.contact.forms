<?php if (!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(true);
use Bitrix\Main\Localization\Loc;

?>
<form action="<?= POST_FORM_ACTION_URI ?>" method="POST" id="<?= $arResult['ELEMENT_AREA_ID'] ?>_form">
    <?= bitrix_sessid_post() ?>

    <?php if (strlen($arResult["IBLOCK"]["NAME"])) : ?>
        <h2><?=$arResult["IBLOCK"]["NAME"]?></h2>
    <?php endif;?>

    <?php if ($arResult["IBLOCK"]["DESCRIPTION"]) : ?>
        <?php if ($arResult["IBLOCK"]["DESCRIPTION_TYPE"] == "html") : ?>
            <?=$arResult["IBLOCK"]["DESCRIPTION"]?>
        <?else : ?>
            <p><?=$arResult["IBLOCK"]["DESCRIPTION"]?></p>
        <?php endif;?>
    <?php endif;?>

    <?php if (!empty($arResult["ERROR_MESSAGE"])) : ?>
        <div class="form-error form-group">
            <div class="alert alert-danger">
                <?= implode("<br>\n", (array) $arResult["ERROR_MESSAGE"]) ?>
            </div>
        </div>
    <?php endif;?>

    <?php if (!empty($arResult["SUCCESS_MESSAGE"])) : ?>
        <div class="form-success form-group">
            <div class="alert alert-success">
                <?= implode("<br>\n", (array) $arResult["SUCCESS_MESSAGE"]) ?>
            </div>
        </div>
    <?php endif;?>
    <?php // echo '<pre>'; var_dump($arResult["IBLOCK"]["PROPERTIES"]); die(); ?>

    <?php if (!empty($arResult["IBLOCK"]["PROPERTIES"])) : ?>
        <?php foreach($arResult["IBLOCK"]["PROPERTIES"] as $obField): ?>
            <div class="row">
                <div class="col-md-12">
                <?= $obField->group() ?>
                </div>
            </div>
        <?php endforeach;?>

	    <?php if ('Y' === $arParams["SHOW_PRIVACY_POLICY"]) : ?>
	        <div class="form-group form-check">
	            <input class="form-check-input" id="agreement" type="checkbox" required>
	            <label class="form-check-label" for="agreement"><?= htmlspecialcharsBack($arParams['TEXT_PRIVACY_POLICY']) ?></label>
	        </div>
	    <?php endif;?>

	    <div class="row">
	        <div class="col-md-12">
	            <div class="form-group form-group_last">
	                <input type="hidden" name="form_submit" value="<?= Loc::getMessage("FORMS_SEND") ?>">
	                <input type="hidden" name="PARAMS_HASH" value="<?= $arResult["PARAMS_HASH"] ?>">

	                <button type="submit" id="<?=$arResult['ELEMENT_AREA_ID']?>_btn" class="btn btn-primary"><?= $arParams["BUTTON_TITLE"] ?></button>
	            </div>
	        </div>
	    </div>
    <?php endif; ?>
</form>
