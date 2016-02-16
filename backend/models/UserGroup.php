<?php
/**
 * Created by PhpStorm.
 * User: sheldon
 * Date: 2016/2/15
 * Time: 16:25
 */

namespace backend\models;


use backend\models\giimodels\UserGroupBase;
use Yii;

class UserGroup extends UserGroupBase
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['pid', 'path', 'name', 'created_at', 'updated_at', 'pos', 'status'], 'required'],
            [['pid', 'created_at', 'updated_at', 'pos', 'status'], 'integer'],
            [['path'], 'string', 'max' => 250],
            [['name', 'description'], 'string', 'max' => 200]
        ];
    }
    /**
     * @param boolean $include_default true for key 0 value root group
     */
    public static function getAllGroup($include_default)
    {
        $connection = \Yii::$app->db;
        $command = $connection->createCommand('SELECT * FROM user_group WHERE status>0');
        $allGroup = $command->queryAll();

        $groupArr=array();

        if($include_default)
        {
            $groupArr['0']=Yii::t('app','As root group');
        }

        foreach($allGroup as $aGroup)
        {
            $pathDeep=substr_count($aGroup['path'],'/');
            $pathStr='';
            if($pathDeep>1)
            {
                $pathStr='|';
                for($i=1;$i<$pathDeep;$i++)
                {
                    $pathStr.='-';
                }
            }
            $groupArr[$aGroup['id']]=$pathStr.$aGroup['name'];
        }




        return $groupArr;
    }

    public static function generatePath($model)
    {
        if($model->pid===0)
        {
            return '/'.$model->id;
        }
        else
        {
            $parent_userGroup = UserGroup::find()
                ->where(['id' => $model->pid])
                ->one();

            return $parent_userGroup->path.'/'.$model->id;
        }

    }

    /**
     * @param $model
     * @return mixed
     */
    public static function generateDefaultValue($model)
    {
        $model->created_at=time();
        $model->updated_at=0;
        $model->path='/';
        $model->status=1;
        return $model;
    }
}