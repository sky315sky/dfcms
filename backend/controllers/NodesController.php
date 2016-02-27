<?php

namespace backend\controllers;

use Yii;
use backend\models\Nodes;
use backend\models\search\NodesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\libtool\ZTreeDataTransfer;

/**
 * NodesController implements the CRUD actions for Nodes model.
 */
class NodesController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Nodes models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NodesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'allNodes'=>$allNodesJson,
        ]);
    }

    /**
     * Displays a single Nodes model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'allNodes'=>$allNodesJson
        ]);
    }

    /**
     * Creates a new Nodes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($pid=1)
    {
        $model = new Nodes();

        //获得父级节点
        $pModel=$this->findModel($pid);

        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes);

        $save_flag=false;

        if ($model->load(Yii::$app->request->post()) ) {
            $model->status=1;
            $model->created_at=time();
            $model->updated_at=$model->created_at;
            if($model->save())
            {
                $save_flag=true;
            }
        }
        if($save_flag)
        {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        else {
            $model->pid=$pModel->id;
            return $this->render('create', [
                'model' => $model,
                'allNodes'=>$allNodesJson,
                'pModel'=>$pModel,
            ]);
        }
    }

    /**
     * Updates an existing Nodes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        //获得父级节点
        $pModel=$this->findModel($model->pid);
        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes);

        $update_flag=false;
        if ($model->load(Yii::$app->request->post())) {
            $model->updated_at=time();
            if($model->save())
            {
                $update_flag=true;
            }

        }
        if($update_flag)
        {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        else {
            return $this->render('update', [
                'model' => $model,
                'allNodes'=>$allNodesJson,
                'pModel'=>$pModel
            ]);
        }
    }

    /**
     * Deletes an existing Nodes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Nodes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Nodes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Nodes::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}