<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$captchaCode = 'BITRIX' === $arResult["MODULE_OPTIONS"]["CAPTCHA_TYPE"] ? $APPLICATION->CaptchaGetCode() : "";

if (! empty($captchaCode)): ?>
<script type="text/javascript">
	var form = BX("<?= $arResult['ELEMENT_AREA_ID'] ?>_form"),
		captchaWord = BX.findChild(form, {attribute: {name: "CAPTCHA_WORD"}}, true, false),
		captchaSid = BX.findChild(form, {attribute: {name: "CAPTCHA_SID"}}, true, false),
		captchaImg = BX.findChild(form, {tagName: "img"}, true, false);

	if (!!captchaWord) captchaWord.value = "";
	if (!!captchaSid) captchaSid.value = "<?= $captchaCode ?>";
	if (!!captchaImg) {
		BX.adjust(captchaImg, {
			props: {src: "/bitrix/tools/captcha.php?captcha_sid=<?= $captchaCode ?>"},
			style: {display: ""}
		});
	}
</script>
<?php endif;
