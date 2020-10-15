<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\HrQuery */

$this->title = 'Create Hr Query';
$this->params['breadcrumbs'][] = ['label' => 'Hr Queries', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hr-query-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
