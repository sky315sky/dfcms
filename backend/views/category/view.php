<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\widgets\ZTreeWidget;
use backend\libtool\ModelError;

/* @var $this yii\web\View */
/* @var $model backend\models\Category */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Categories'), 'url' => ['view?id=1']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-view">
    <div class="row">
        <div class="col-md-3 tree_left">
            <?= ZTreeWidget::widget(['treeData' => json_encode($allCategory),'selectID'=>$model->id]) ?>
        </div>
        <div class="col-md-9 col-md-offset-3">
            <?=ModelError::generateErrors($model->getErrors()); ?>
            <?=ModelError::generateErrors(\backend\services\error\FlashError::getFlashError()); ?>
            <div class="page-title">
                <span class="title"><?= Html::encode($this->title) ?></span>
            </div>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?')."\r".Yii::t('app', 'All it\'s descendant will delete too.'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Create Child'), ['create', 'pid' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Assign Attr Group'), ['assign-attr-group','id'=>$model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Assign Check Group'), ['assign-check-group', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'description',
            ['attribute'=>'type','value'=> \backend\models\Category::generateType($model->type)],
            ['attribute'=>'attr_group_id','value'=>\backend\models\AttrGroup::getAttrGroupNameById($model->attr_group_id),],
            ['attribute'=>'check_group_id','value'=>\backend\models\CheckGroup::getCheckGroupNameById($model->check_group_id)],
            'path',
            'article_t_path',
            'index_t_path',
            'cover_t_path',
            ['attribute'=>'status','value'=> \backend\models\Category::generateStatus($model->status)],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i:s'],
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'php:Y-m-d H:i:s'],
            ],
        ],
    ]) ?>


        </div>
    </div>
</div>

