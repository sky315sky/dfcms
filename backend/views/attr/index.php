<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Attrs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="attrs-index">

    <div class="page-title">
        <span class="title"><?= Html::encode($this->title) ?></span>
    </div>

    <p>
        <?= Html::a(Yii::t('app', 'Create Attrs'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'label',
            'type',
            'created_at',
            // 'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
