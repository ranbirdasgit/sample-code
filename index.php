<?php
namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use frontend\models\SpecialistModel;
use frontend\models\DoctorgeneralinfoModel;
use frontend\models\Generalmodel;
use frontend\models\Uploaddoc;
use frontend\models\Doctornetwork;
use yii\web\UploadedFile;
use common\components\helpers\Feedhelper;
use common\components\helpers\Connect_doctorhelper;
use common\components\helpers\healthcommunities;
use common\components\helpers\healthcommunitiesSubcategories;
use frontend\models\communitiesModel;
use yii\helpers\Url;
//use common\controller\BasedoctorController;
class DoctorController extends Controller
{   public $enableCsrfValidation = false;
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['docspecialization','catsubcategorylist'],
                        'allow' => true,
                       // 'roles' => [''],
                    ],
                    [
                        'actions' => ['deleteschedule','locationhospital','datetimeschedular','feeddeletecomment','feeddelete','home','healthcommunities','acceptrequest','calltabfriendrequest','connectdoctor','doctornetwork','feedcommentlike','feedlike','home','doctorprofile','cityval','cityvalid','feedajax','feedcomment','feedcommentrply'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    
                ],
            ],
            
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionHome(){
        //Yii::$app->Doctorcheck->doctor_check(Yii::$app->user->getId());
        $modeldoc = new Generalmodel();
        $res_privacy=$modeldoc->postprivacy();
        $feed_fetch= new DoctorgeneralinfoModel();
        if ($modeldoc->load(Yii::$app->request->post())) {
        /*if ($modeldoc->validate()) {
            }else{
                $errors = $modeldoc->errors;
            }*/ 
            $doc = UploadedFile::getInstance($modeldoc, 'image_file');
            if(!empty($doc)){
                $doc_name=$doc->name;
                $type=$doc->extension;
                $path=Yii::$app->params['feed_image_path'].\Yii::$app->user->identity->id;
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $doc_name_save=Yii::$app->Datecall->imagename($doc_name,$type);
                $doc_save_path=$path."/".$doc_name_save;
                $doc->saveAs($doc_save_path);
            }else{
                $doc_name_save='';
            } 
            
            $feed_insert= new DoctorgeneralinfoModel();
            $feed_insert->insert_feed($modeldoc['privacy'],$modeldoc['post_val'],\Yii::$app->user->identity->id,$doc_name_save);
            return Yii::$app->getResponse()->redirect(Url::to('home'));
        }
 
        $modeldoc = new Generalmodel();
        return $this->render('feed',['modeldoc'=>$modeldoc,'privacy'=>$res_privacy]);
    }
    public function actionDoctorprofile(){
        $model_specilist = new SpecialistModel();
        $doctor_doc = new Uploaddoc();
        $model_general_info = new DoctorgeneralinfoModel();
        $doctor_dataFetch=DoctorgeneralinfoModel::fetchdatadoctor(\Yii::$app->user->identity->id);
        if ($doctor_dataFetch->load(Yii::$app->request->post())) {
            
            $doctor_dataFetch->save();
            if ($model_specilist->load(Yii::$app->request->post())) {
                $model_general_info->insertDocSpecialization($model_specilist);
            }
            if ($doctor_doc->load(Yii::$app->request->post())) {
                print_r($_FILES['uploaddocedu']['name']);
                echo "sdf";die;
                $doc = UploadedFile::getInstance($doctor_doc, 'doctor_profile_pic');
                if(!empty($doc)){
                    $doc_name=$doc->name;
                    $type=$doc->extension;
                    $doc_name_save=Yii::$app->Datecall->imagename($doc_name,$type);
                    $doc_save_path=Yii::$app->params['BasePath']."profileImage/".\Yii::$app->user->identity->id;
                    if (!is_dir($doc_save_path)) {
                        mkdir($doc_save_path, 0777, true);
                    }
                    $files = glob($doc_save_path.'/*');
                    foreach($files as $file){
                      if(is_file($file)) {
                        // delete file
                        unlink($file);
                      }
                    }
                    $image_save=$doc_save_path."/".$doc_name_save;
                    $doc->saveAs($image_save);
                    $image_update= new DoctorgeneralinfoModel();
                    $image_update->update_image(\Yii::$app->user->identity->id,$doc_name_save);
                }
            }
        }
        $doctor_dataFetch=DoctorgeneralinfoModel::fetchdatadoctor(\Yii::$app->user->identity->id);
        $state_list=Generalmodel::get_states_lists();
        $prefix_list=Generalmodel::get_prefix_lists();
        $city_list=Generalmodel::get_city_lists($doctor_dataFetch['state']);
        return $this->render('doctorprofile',['model'=>$model_specilist,'docinfo'=>$doctor_dataFetch,'state'=>$state_list,'city'=>$city_list,'doctor_doc'=>$doctor_doc,'prefix'=>$prefix_list]);
    }
    public function actionCityval(){
        if (Yii::$app->request->isAjax) {
            $id=Yii::$app->request->post('id');
            $city_list=Generalmodel::get_city_lists($id);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $city_list;
        }
    }
    public function actionCityvalid(){
        if (Yii::$app->request->isAjax) {
            $id=Yii::$app->request->post('id');
            $city_list=Generalmodel::get_city_lists_id($id);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $city_list;
        }
    }
    public function actionDocspecialization(){
        if (Yii::$app->request->isAjax) {
            $model_general_info = new DoctorgeneralinfoModel();
            $obj=$model_general_info->doctorspecialization();
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $obj;
        }
    }
    public function actionFeedajax(){
        if (Yii::$app->request->isAjax) {
            $start=Yii::$app->request->post('pass_limit');
            $end=5;
            $feed_fetch= new DoctorgeneralinfoModel();
            $feed_data=$feed_fetch->doctorFeedData($start,$end,\Yii::$app->user->identity->id);
            echo Feedhelper::feedajax($feed_data);
        }
    }
    public function actionFeedcomment(){
        if (Yii::$app->request->isAjax) {
            $comment=addslashes(trim(Yii::$app->request->post('get_val')));
            $feedId=trim(Yii::$app->request->post('feed_id'));
            $feed_insert= new DoctorgeneralinfoModel();
            $res=$feed_insert->doctorFeedInsert($comment,\Yii::$app->user->identity->id,$feedId);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $res;
        }
    }
    public function actionFeedcommentrply(){
        if (Yii::$app->request->isAjax) {
            $comment_rply=addslashes(trim(Yii::$app->request->post('value_area')));
            $comment_id=addslashes(trim(Yii::$app->request->post('comment_id')));
            $feed_rply= new DoctorgeneralinfoModel();
            $res_comment_rply=$feed_rply->doctorFeedrplyInsert($comment_rply,\Yii::$app->user->identity->id,$comment_id);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $res_comment_rply;
            
        }
    }

    public function actionDoctornetwork(){
        $model_network=new Doctornetwork();
        if (Yii::$app->request->isAjax) {
            $cat_id=trim(Yii::$app->request->post('cat_id'));
            $state_id=trim(Yii::$app->request->post('state_id'));
            $city_id=trim(Yii::$app->request->post('city_id'));
            $start=(trim(Yii::$app->request->post('pass_limit'))=='' ? '0':trim(Yii::$app->request->post('pass_limit')));
            $end=5;
            $array=array();
            if(Yii::$app->request->post('tab_request')=='connect'){
                $res=$model_network->doctornetwork($start,$end,$cat_id,$state_id,$city_id);
                array_push($array,'connect');
                array_push($array, $res);
            }else if(Yii::$app->request->post('tab_request')=='request_sent'){
                $res=$model_network->requestsent($start,$end,$cat_id,$state_id,$city_id);
                array_push($array,'request_sent');
                array_push($array, $res);
            }else if(Yii::$app->request->post('tab_request')=='request_received'){
                $res=$model_network->request_received($start,$end,$cat_id,$state_id,$city_id);
                array_push($array,'request_received');
                array_push($array, $res);
            }else if(Yii::$app->request->post('tab_request')=='connected'){
                $res=$model_network->connected($start,$end,$cat_id,$state_id,$city_id);
                array_push($array,'connected');
                array_push($array, $res);
            }
            return $resval=Connect_doctorhelper::doctor_connect($array);
        }else{
            $cat_id='';$state_id='';$city_id='';$end=5;$start=0;
            $res=$model_network->doctornetwork($start,$end,$cat_id,$state_id,$city_id);
            $array=array();
            array_push($array,'connect');
            array_push($array, $res);
            $resval=Connect_doctorhelper::doctor_connect($array);
            return $this->render('mydoctor_connect',['doctor_detail'=>$resval]);
        }   
    }
    public function actionConnectdoctor(){
        if (Yii::$app->request->isAjax) {
            $id=trim(Yii::$app->request->post('id'));
            $model_network=new Doctornetwork();
            $result=$model_network->addconnect($id);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $result;
        }
    }
    public function actionAcceptrequest(){
        if (Yii::$app->request->isAjax) {
            $id=trim(Yii::$app->request->post('id'));
            $model_network=new Doctornetwork();
            $result=$model_network->acceptrequest($id);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $result;
        }
    }

    public function actionCatsubcategorylist(){
        
        
        $end=5;
        if (Yii::$app->request->isAjax) {
            $obj_new=new communitiesModel();
            $start=(trim(Yii::$app->request->post('pass_limit'))=='' ? '0':trim(Yii::$app->request->post('pass_limit')));
            $cat_id=trim(Yii::$app->request->post('cat_id'));
            $subcat_id=trim(Yii::$app->request->post('subcat_id'));
            $res=$obj_new->fetchsubcategory($cat_id,$subcat_id,$start,$end);
            $resval=healthcommunitiesSubcategories::healthcommunitiesSubcategories($res);
            return $resval;
        }else{
            $start=(trim(Yii::$app->request->post('pass_limit'))=='' ? '0':trim(Yii::$app->request->post('pass_limit')));
            $cat_id=trim(Yii::$app->request->get('cat'));
            $obj_new=new communitiesModel();
            $res=$obj_new->fetchsubcategory($cat_id,'',$start,$end);
            $resval=healthcommunitiesSubcategories::healthcommunitiesSubcategories($res);
            return $this->render('healthcummunitiesSubcategory',['communities'=>$resval]);
        } 
    }
    public function actionFeeddelete(){
        if (Yii::$app->request->isAjax) {
            $feed_delete= new DoctorgeneralinfoModel();
            $feed_id=Yii::$app->request->post('id');
            $res=$feed_delete->delete_feed($feed_id);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $res;
        }
    }

    public function actionDatetimeschedular(){
        if(Yii::$app->request->post()){
            $location_name=Yii::$app->request->post('location_name');
            $autoid=Yii::$app->request->post('autoid');
            $hospital_name=Yii::$app->request->post('hospital_name');
            $day_name=Yii::$app->request->post('day_name');
            $from_hours_time=Yii::$app->request->post('from_hours_time');
            $from_minutes_time=Yii::$app->request->post('from_minutes_time');
            $from_ampm_time=Yii::$app->request->post('from_ampm_time');
            $to_hours_time=Yii::$app->request->post('to_hours_time');
            $to_minutes_time=Yii::$app->request->post('to_minutes_time');
            $to_ampm_time=Yii::$app->request->post('to_ampm_time');
            $model= new DoctorgeneralinfoModel();
            $insert_time=$model->insert_time($autoid,$location_name,$hospital_name,$day_name,$from_hours_time,$from_minutes_time,$from_ampm_time,$to_hours_time,$to_minutes_time,$to_ampm_time);
        }
        $model= new DoctorgeneralinfoModel();
        $x=$model->location_list();
        $res=$model->fetch_schedule(\Yii::$app->user->identity->id);
        return $this->render('datetimeschedular',['location'=>$x,'res'=>$res]);
    }

  
}