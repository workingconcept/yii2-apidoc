<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if (empty($type->constants)) {
    return;
}
$constants = $type->constants;
ArrayHelper::multisort($constants, 'name');
?>

## Constants

<table>
<tr>
    <th>Constant</th>
    <th>Description</th>
</tr>
<?php foreach ($constants as $constant): ?>
    <tr<?= $constant->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $constant->name ?>">
        <td id="<?= $constant->name ?>-detail"><code><?= $constant->name ?></code></td>
        <td><?= $constant->value ?> <?= ApiMarkdown::process($constant->shortDescription . "\n" . $constant->description, $constant->definedBy, true) ?>
            <?php if (!empty($constant->deprecatedSince) || !empty($constant->deprecatedReason)): ?>
                <strong>Deprecated <?php
                    if (!empty($constant->deprecatedSince))  { echo 'since version ' . $constant->deprecatedSince . ': '; }
                    if (!empty($constant->deprecatedReason)) { echo ApiMarkdown::process($constant->deprecatedReason, $type, true); }
                    ?></strong>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</table>
