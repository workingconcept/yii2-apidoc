<?php

use yii\apidoc\templates\markdown\ApiRenderer;

/* @var $this yii\web\View */
/* @var $types array */
/* @var $content string */

/** @var $renderer ApiRenderer */
$renderer = $this->context;
$this->beginContent('@yii/apidoc/templates/markdown/layouts/main.php', isset($type) ? ['type' => $type] : []); ?>
<?= $content ?>
<?php $this->endContent(); ?>
