<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\TraitDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc|TraitDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

$methods = $type->getNativeMethods();
if (empty($methods)) {
    return;
}
ArrayHelper::multisort($methods, 'name');
?>

## Method Details

<?php foreach ($methods as $method): ?>

### <?= $renderer->createSubjectLink($method, '<span class="glyphicon icon-hash"></span>', [
            'title' => 'direct link to this method',
            'class' => 'tool-link hash',
]) ?>

<?php if (($sourceUrl = $renderer->getSourceUrl($method->definedBy, $method->startLine)) !== null): ?>
    <a href="<?= str_replace('/blob/', '/edit/', $sourceUrl) ?>" class="tool-link" title="edit on github"><span class="glyphicon glyphicon-pencil"></span></a>
    <a href="<?= $sourceUrl ?>" class="tool-link" title="view source on github"><span class="glyphicon glyphicon-eye-open"></span></a>
<?php endif; ?>

<?= $method->name ?>()
<span class="detail-header-tag small">
    <?= $method->visibility ?>
    <?= $method->isAbstract ? 'abstract' : '' ?>
    <?= $method->isStatic ? 'static' : '' ?>
    method
    <?php if (!empty($method->since)): ?>
        (available since version <?= $method->since ?>)
    <?php endif; ?>
</span>

<?php if (!empty($method->deprecatedSince) || !empty($method->deprecatedReason)): ?>
**Deprecated <?php
    if (!empty($method->deprecatedSince))  { echo 'since version ' . $method->deprecatedSince . ': '; }
    if (!empty($method->deprecatedReason)) { echo ApiMarkdown::process($method->deprecatedReason, $type, true); }
    ?>**
<?php endif; ?>

**<?= ApiMarkdown::process($method->shortDescription, $type, true) ?>**

<?= ApiMarkdown::process($method->description, $type) ?>

<?= $this->render('seeAlso', ['object' => $method]) ?>

<table class="detail-table table table-striped table-bordered table-hover">
    <tr><td colspan="3" class="signature"><?= $renderer->renderMethodSignature($method, $type) ?></td></tr>
    <?php if (!empty($method->params) || !empty($method->return) || !empty($method->exceptions)): ?>
        <?php foreach ($method->params as $param): ?>
            <tr>
                <td class="param-name-col"><?= ApiMarkdown::highlight($param->name, 'php') ?></td>
                <td class="param-type-col"><?= $renderer->createTypeLink($param->types) ?></td>
                <td class="param-desc-col"><?= ApiMarkdown::process($param->description, $type) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!empty($method->return)): ?>
            <tr>
                <th class="param-name-col">return</th>
                <td class="param-type-col"><?= $renderer->createTypeLink($method->returnTypes, $type) ?></td>
                <td class="param-desc-col"><?= ApiMarkdown::process($method->return, $type) ?></td>
            </tr>
        <?php endif; ?>
        <?php foreach ($method->exceptions as $exception => $description): ?>
            <tr>
                <th class="param-name-col">throws</th>
                <td class="param-type-col"><?= $renderer->createTypeLink($exception) ?></td>
                <td class="param-desc-col"><?= ApiMarkdown::process($description, $type) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php endforeach; ?>
