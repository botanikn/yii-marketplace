<?php

namespace app\services;

use Yii;

class GoodService
{
    public function getGoodsJoinCatJoinCart($query) {

        $allGoodsWithCategories = $query
            ->select(['goodscatalog.*', 'categories.name AS category'])
            ->from('goodscatalog')
            ->innerJoin('categories', '"categoryID" = categories.id')
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return $allGoodsWithCategories;
    }

    public function getOneGood($query, $id) {
        $good = $query
            ->select(['goodscatalog.*', 'categories.name AS category'])
            ->from('goodscatalog')
            ->innerJoin('categories', '"categoryID" = categories.id')
            ->where(['goodscatalog.id' => $id])
            ->one();

        return $good;
    }

    public function updateOneGood($activeForm, $model, $id) {

        $currentGood = $activeForm::findOne($id);

        $currentGood->name = $model->name;
        $currentGood->description = $model->description;
        $currentGood->price = $model->price;
        $currentGood->updated_by = Yii::$app->user->id;
        $currentGood->categoryID = $model->categoryID;
        $currentGood->updateTime = date('Y-m-d H:i:s', time());

        if($currentGood->save()) {
            return $currentGood;
        }
        else return null;
    }

    public function createGoods($model, $activeForm) {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $activeForm->name = $model->name;
            $activeForm->description = $model->description;
            $activeForm->price = $model->price;
            $activeForm->categoryID = $model->categoryID;
            $activeForm->createTime = date('Y-m-d H:i:s', time());
            $activeForm->updateTime = date('Y-m-d H:i:s', time());

            if ($activeForm->save()) {
                $transaction->commit();
                return [
                    'success' => true,
                    'model' => $activeForm
                ];
            } else {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'errors' => $activeForm->getErrors()
                ];
            }
        } catch (\yii\db\IntegrityException $e) {
            $transaction->rollBack();
            
            // Check for unique constraint violation
            if (strpos($e->getMessage(), 'duplicate key value violates unique constraint') !== false) {
                return [
                    'success' => false,
                    'message' => 'Товар с таким названием уже существует.'
                ];
            }
            
            // For other database errors
            return [
                'success' => false,
                'message' => 'Произошла ошибка при сохранении товара.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'Произошла непредвиденная ошибка.'
            ];
        }
    }

}