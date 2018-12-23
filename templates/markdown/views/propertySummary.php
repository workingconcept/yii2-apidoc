<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc|TraitDoc */
/* @var $protected bool */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if ($protected && count($type->getProtectedProperties()) == 0 || !$protected && count($type->getPublicProperties()) == 0) {
    return;
} ?>

## <?= $protected ? 'Protected Properties' : 'Public Properties' ?>


<table>
<tr>
    <th>Property</th>
    <th>Description</th>
</tr>
<?php
$properties = $type->properties;
ArrayHelper::multisort($properties, 'name');
foreach ($properties as $property): ?>
    <?php if ($protected && $property->visibility == 'protected' || !$protected && $property->visibility != 'protected'): ?>
    <tr<?= $property->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $property->name ?>">
        <td><?= $renderer->createSubjectLink($property) ?></td>
        <td>
            <?= $renderer->createTypeLink($property->types) ?>
            <?= ApiMarkdown::process($property->shortDescription, $property->definedBy, true) ?>
        </td>
    </tr>
    <?php endif; ?>
<?php endforeach; ?>
</table>
