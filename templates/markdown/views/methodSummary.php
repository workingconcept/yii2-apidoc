<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc|InterfaceDoc|TraitDoc */
/* @var $protected bool */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if ($protected && count($type->getProtectedMethods()) == 0 || !$protected && count($type->getPublicMethods()) == 0) {
    return;
} ?>

## <?= $protected ? 'Protected Methods' : 'Public Methods' ?>

<table>
<tr>
    <th>Method</th>
    <th>Description</th>
</tr>
<?php
$methods = $type->methods;
ArrayHelper::multisort($methods, 'name');
foreach ($methods as $method): ?>
    <?php if ($protected && $method->visibility == 'protected' || !$protected && $method->visibility != 'protected'): ?>
    <tr<?= $method->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $method->name ?>()">
        <td>
            <?= $renderer->createSubjectLink($method, $method->name.'()') ?>
        </td>
        <td>
            <?= ApiMarkdown::process($method->shortDescription, $method->definedBy, true) ?>
        </td>
    </tr>
    <?php endif; ?>
<?php endforeach; ?>
</table>
