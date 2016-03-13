<?php

namespace backend\controllers;

use backend\models\ContentAttr;
use backend\libtool\ObjectArrayParse;
use backend\services\activeAttr\ActiveAttrFactory;
use Yii;
use backend\models\Content;
use backend\models\search\ContentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Nodes;
use backend\libtool\ZTreeDataTransfer;

/**
 * ContentController implements the CRUD actions for Content model.
 */
class ContentController extends BaseController
{
    public function init()
    {
        parent::init();
        $this->checkRBAC("contentModule");
    }
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
     * Lists all Content models.
     * @return mixed
     */
    public function actionIndex($id=0)
    {
        $node=null;
        $search_array=Yii::$app->request->queryParams;
        try{
            $node=Nodes::findOne($id);
            if(isset($search_array['ContentSearch']))
            {
                $search_array['ContentSearch']['node_id']=$node->id;
            }
        }catch(\Exception $e){
            exit();
        }


        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes,array('id','pid','name'), array(),array('URL_PRE'=>'index?id='));

        $searchModel = new ContentSearch();
        $dataProvider = $searchModel->search($search_array);


        $array_data=array('node'=>$node,'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'allNodes'=>$allNodesJson);

        return $this->render('index', $array_data);
    }

    /**
     * Displays a single Content model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model=$this->findModel($id);
        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes,array('id','pid','name'), array(),array('URL_PRE'=>'index?nodeid='));
        $node=Nodes::findOne(["id"=>$model->node_id]);

        Yii::info( Yii::t('app/log', "View Content(Content title:{contentTitle})", ['contentTitle' =>$model->title]), 'operations');

        return $this->render('view', [
            'node'=>$node,
            'allNodes'=>$allNodesJson,
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Content model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($nodeid=0)
    {
        $node=null;
        try{
            $node=Nodes::findOne($nodeid);
        }catch(\Exception $e){
            exit();
        }


        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes,array('id','pid','name'), array(),array('URL_PRE'=>'create?nodeid='));
        $attr_array=Nodes::getAssignedAttrByNode($nodeid);


        $model = new Content();
        $contentAttrModel=new ContentAttr();
        $activeAttrModel=ActiveAttrFactory::build($attr_array);
        $create_flag=false;

        if ($model->load(Yii::$app->request->post()) &&
            $activeAttrModel->load(Yii::$app->request->post()) &&
            $contentAttrModel->load(Yii::$app->request->post())
        ) {

            if($activeAttrModel->validate())
            {
                //attr_array这个数组对应数据库里的每一行记录，如果是新建文章的话，有这些内容就已经够了，但是如果是出错了
                //再重定向到新建页面的话，需要把上次的value也赋值进去，方便编辑，因此这里要处理一下，把value加进去

                for($i=0;$i<count($attr_array);$i++)
                {

                    if($activeAttrModel->$attr_array[$i]['name'])
                    {
                        $attr_array[$i]['value']=$activeAttrModel->$attr_array[$i]['name'];
                    }
                }

                //把收集到的附加属性的值转成JSON字符串传给$contentAttrModel  然后就可以往contentAttr表save了
                $contentAttrModel->attr= json_encode(array('content_attr'=>$activeAttrModel.'','attr_data_model'=>$attr_array));
            }

            $model->created_at=$model->updated_at=time();
            $model->editor_name=Yii::$app->user->identity->username;
            $model->editor_id= Yii::$app->user->id;
            $model->node_id=$nodeid;
            $model->status=Content::STATUS_PUB;
            $create_flag = $model->save();
            if($create_flag)
            {
                $contentAttrModel->content_id=$model->id;
                $create_flag=$contentAttrModel->save();
            }

        }

        $array_data=array(
            'model' => $model,
            'node'=>$node,
            'attr_array'=>$attr_array,
            'allNodes'=>$allNodesJson,
            'contentAttrModel'=>$contentAttrModel,
            'activeAttrModel'=>$activeAttrModel);

        if($create_flag){
            Yii::info( Yii::t('app/log', "Create content(content title:{contentTitle},content id:{contentId})", ['contentTitle' =>$model->title,'contentId'=>$model->id]), 'operations');
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', $array_data);
        }
    }

    /**
     * Updates an existing Content model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $contentAttrModel=new ContentAttr();
        $allNodes=Nodes::getAllNodesData();
        $allNodesJson=ZTreeDataTransfer::array2simpleJson($allNodes,array('id','pid','name'), array(),array('URL_PRE'=>'create?nodeid='));
        $node=Nodes::findOne(["id"=>$model->node_id]);
        $addonAttrObj=ContentAttr::findOne(['content_id'=>$id]);
        $contentAttrModel->content=$addonAttrObj->content;
        $addonAttr=ObjectArrayParse::Object2Array((json_decode($addonAttrObj->attr)));
        $attr_array=$addonAttr['attr_data_model'];
        $activeAttrModel=ActiveAttrFactory::build($attr_array);



        $update_flag=false;


        if ($model->load(Yii::$app->request->post()) &&
            $activeAttrModel->load(Yii::$app->request->post()) &&
            $contentAttrModel->load(Yii::$app->request->post())) {


            if($activeAttrModel->validate())
            {
                //attr_array这个数组对应数据库里的每一行记录，如果是新建文章的话，有这些内容就已经够了，但是如果是出错了
                //再重定向到新建页面的话，需要把上次的value也赋值进去，方便编辑，因此这里要处理一下，把value加进去

                for($i=0;$i<count($attr_array);$i++)
                {

                    if($activeAttrModel->$attr_array[$i]['name'])
                    {
                        $attr_array[$i]['value']=$activeAttrModel->$attr_array[$i]['name'];
                    }
                }

                //把收集到的附加属性的值转成JSON字符串传给$contentAttrModel  然后就可以往contentAttr表save了
                $contentAttrModel->attr= json_encode(array('content_attr'=>$activeAttrModel.'','attr_data_model'=>$attr_array));
            }
            $model->updated_at=time();

            $update_flag = $model->save();
            if($update_flag)
            {
                $contentAttrModel->content_id=$model->id;
               // $contentAttrModel->setIsNewRecord(false);

                //$update_flag=$contentAttrModel->save();
                //var_dump($contentAttrModel->getErrors());exit();
                $update_flag=ContentAttr::updateContentAttr($contentAttrModel);
            }
        }

        $arrayUpdateData=['model' => $model,
            'allNodes'=>$allNodesJson,
            'contentAttrModel'=>$contentAttrModel,
            'node'=>$node,
            'attr_array'=>$attr_array,
            'activeAttrModel'=>$activeAttrModel];


        if($update_flag)
        {
            Yii::info( Yii::t('app/log', "Update content(content title:{contentTitle},content id:{contentId})", ['contentTitle' =>$model->title,'contentId'=>$model->id]), 'operations');
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', $arrayUpdateData);
        }
    }

    /**
     * Deletes an existing Content model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model=$this->findModel($id);
        Yii::info( Yii::t('app/log', "Delete content(content title:{ContentTitle},content id:{contentId})", ['contentTitle' =>$model->title,'contentId'=>$model->id]), 'operations');
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Content model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Content the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Content::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
