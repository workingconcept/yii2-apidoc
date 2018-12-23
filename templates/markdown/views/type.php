<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;

/* @var $type ClassDoc|InterfaceDoc|TraitDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\markdown\ApiRenderer */

$renderer = $this->context;
?>

# <?php echo str_replace($type->namespace.'\\', '', $type->name); ?>

<dl>
    <dt>Type</dt>
    <dd><?php

        if ($type instanceof InterfaceDoc)
        {
            echo 'Interface';
        }
        elseif ($type instanceof TraitDoc)
        {
            echo 'Trait';
        }
        else
        {
            echo 'Class';
        }

    ?></dd>
    <dt>Namespace</dt>
    <dd><?php echo $type->namespace; ?></dd>
    <?php if ($type instanceof ClassDoc): ?>
        <dt>Inherits</dt>
        <dd><?= $renderer->renderInheritance($type) ?></dd>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->interfaces)): ?>
        <dt>Implements</dt>
        <dd><?= $renderer->renderInterfaces($type->interfaces) ?></dd>
    <?php endif; ?>
    <?php if ($type instanceof InterfaceDoc && !empty($type->parentInterfaces)): ?>
        <dt>Extends</dt>
        <dd><?= $renderer->renderInterfaces($type->parentInterfaces) ?></dd>
    <?php endif; ?>
    <?php if (!($type instanceof InterfaceDoc) && !empty($type->traits)): ?>
        <dt>Uses Traits</dt>
        <dd><?= $renderer->renderTraits($type->traits) ?></dd>
    <?php endif; ?>
    <?php if ($type instanceof ClassDoc && !empty($type->subclasses)): ?>
        <dt>Subclasses</dt>
        <dd><?= $renderer->renderClasses($type->subclasses) ?></dd>
    <?php endif; ?>
    <?php if ($type instanceof InterfaceDoc && !empty($type->implementedBy)): ?>
        <dt>Implemented by</dt>
        <dd><?= $renderer->renderClasses($type->implementedBy) ?></dd>
    <?php endif; ?>
    <?php if ($type instanceof TraitDoc && !empty($type->usedBy)): ?>
        <dt>Implemented by</dt>
        <dd><?= $renderer->renderClasses($type->usedBy) ?></dd>
    <?php endif; ?>
    <?php if (!empty($type->since)): ?>
        <dt>Available since version</dt>
        <dd><?= $type->since ?></dd>
    <?php endif; ?>
    <?php if (!empty($type->deprecatedSince) || !empty($type->deprecatedReason)): ?>
        <tr class="deprecated"><th>Deprecated since version</dt><dd><?= $type->deprecatedSince ?> <?= $type->deprecatedReason ?></dd></tr>
    <?php endif; ?>
</dl>

<?php if (($sourceUrl = $renderer->getSourceUrl($type)) !== null): ?>
[View source](<?= $sourceUrl ?>)
<?php endif; ?>

<?= ApiMarkdown::process($type->description, $type) ?>

<?= $this->render('seeAlso', ['object' => $type]) ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/propertySummary', ['type' => $type, 'protected' => false]) ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/propertySummary', ['type' => $type, 'protected' => true]) ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/methodSummary', ['type' => $type, 'protected' => false]) ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/methodSummary', ['type' => $type, 'protected' => true]) ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/eventSummary', ['type' => $type]) ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/constSummary', ['type' => $type]) ?>
<?php

/*

<?= $this->render('@yii/apidoc/templates/markdown/views/propertyDetails', ['type' => $type]) ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/methodDetails', ['type' => $type]) ?>
<?php if ($type instanceof ClassDoc): ?>
<?= $this->render('@yii/apidoc/templates/markdown/views/eventDetails', ['type' => $type]) ?>
<?php endif; ?>

*/
