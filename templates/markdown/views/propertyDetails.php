<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc|TraitDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer   = $this->context;
$properties = $type->getNativeProperties();

if (empty($properties))
{
    return;
}
ArrayHelper::multisort($properties, 'name');
?>

## Property Details

<?php foreach ($properties as $property): ?>

### <?= $renderer->createSubjectLink($property, '<span class="glyphicon icon-hash"></span>', [
    'title' => 'direct link to this method',
    'class' => 'tool-link hash',
]) ?>

<?php if (($sourceUrl = $renderer->getSourceUrl($property->definedBy, $property->startLine)) !== null): ?>
    <a href="<?= $sourceUrl ?>" title="view source on github">View Source</a>
<?php endif; ?>

`<?= $property->name ?>`

    <?php

    /*
<span class="detail-header-tag small">
    <?= $property->visibility ?>
    <?= $property->isStatic ? 'static' : '' ?>
    <?php if ($property->getIsReadOnly()) echo ' <em>read-only</em> '; ?>
    <?php if ($property->getIsWriteOnly()) echo ' <em>write-only</em> '; ?>
    property
    <?php if (!empty($property->since)): ?>
        (available since version <?= $property->since ?>)
    <?php endif; ?>
</span>
*/
    ?>


<?php if (!empty($property->deprecatedSince) || !empty($property->deprecatedReason)): ?>
**Deprecated <?php
    if (!empty($property->deprecatedSince))  { echo 'since version ' . $property->deprecatedSince . ': '; }
    if (!empty($property->deprecatedReason)) { echo ApiMarkdown::process($property->deprecatedReason, $type, true); }
    ?>**
<?php endif; ?>

<?= ApiMarkdown::process($property->description, $type) ?>

<?= $this->render('seeAlso', ['object' => $property]) ?>

<?php echo $renderer->renderPropertySignature($property, $type); ?>

<?php endforeach; ?>
