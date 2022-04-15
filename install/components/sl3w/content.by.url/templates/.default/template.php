<?php

/** @var array $arResult */
/** @var array $arParams */

$this->setFrameMode(true);
?>

<div <?= $arResult['ADDITIONAL_CLASS'] ? 'class="' . $arResult['ADDITIONAL_CLASS'] . '"' : '' ?>>
    <?= $arResult['TEXT'] ?>
</div>