<?php

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\helpers\ArrayHelper;

/* @var $type ClassDoc */
/* @var $this yii\web\View */
/* @var $renderer \yii\apidoc\templates\html\ApiRenderer */

$renderer = $this->context;

if (empty($type->events)) {
    return;
}
$events = $type->events;
ArrayHelper::multisort($events, 'name');
?>

## Events

<table>
<tr>
    <th>Event</th>
    <th>Description</th>
</tr>
<?php foreach ($events as $event): ?>
<tr<?= $event->definedBy != $type->name ? ' class="inherited"' : '' ?> id="<?= $event->name ?>">
    <td><?= $renderer->createSubjectLink($event) ?></td>
    <td>
        <?= ApiMarkdown::process($event->shortDescription, $event->definedBy, true) ?>
        <?php if (!empty($event->since)): ?>
            (available since version <?= $event->since ?>)
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>