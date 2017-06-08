<?php

namespace Single\Controllers;

use Phalcon\Mvc\Controller;
use Single\Models\Products as Products;
use Single\Models\Record;
use Single\Models\Dm;

class ProductsController extends Controller
{
    public function indexAction()
    {
        $this->view->product = Products::find();
        $this->view->setVar('test', 111);
    }
    
    public function listAction(){
        //var_dump($this->di->getShared('db')->fetchAll('select count(1) from dm '));exit;
        //$connect = $this->di->db->query('select * from dm limit 1');
    }
    
    //ajax获取预约列表
    public function ajaxGetAppointmentListAction(){
        $data = $this->request->get();
        session_start();
        $start = isset($data["start"]) && intval($data["start"])>0?intval($data["start"]):0;
        $limit = isset($data["length"]) && intval($data["length"])?intval($data["length"]):10;
        $column = isset($data['order'][0]['column'])?$data['order'][0]['column']:'';
        $dir = isset($data['order'][0]['dir'])?$data['order'][0]['dir']:'';
        if(isset($_SESSION['order'])){
            $order = $_SESSION['order'];
        }else{
            $order = " c DESC ";
        }       
        if($column == 2){
           $order = " c $dir";
        }
        if($column == 3){
            $order = " zdf $dir";
        }
        if($column == 4){
            $order = " zxj $dir";
        }
        $_SESSION['order'] = $order; 
        $search = isset($data["search"]["value"])?trim($data["search"]["value"]):'';
        $conditionstr = '';
        $conditionstr .= $search?" (name like '$search%' ":'';
        $conditionstr .= $search?" or dm like '$search%') ":'';
        $conditionstr .= $conditionstr?" and zdf>0 ":" zdf>0";
        $conditionstr = $conditionstr?$conditionstr:' and 1=1';
        $ap_model = $this->di->getShared('db');
        /*$date =  $ap_model->fetchAll("select data_time from record group by data_time order by data_time desc limit 2");
        $sdate = $date[1]['data_time'];
        $edate = $date[0]['data_time'];*/
        $redis = $this->di->getShared('redis');
        $key = str_replace(' ', '', $start.$limit.$column.$dir.$search);
        $count_key = $key.'_count';
        $data_key = $key.'_data';
        $expire = 300; 
        $cache_count = $redis->get($count_key);
        
        $cache_data = $redis->get($data_key);
        if($cache_count && $cache_data){
            $arraycont = unserialize($cache_count);
            $res_data = unserialize($cache_data);
        }else{
        $arraycont = $ap_model->fetchAll(
            "SELECT count(1) c from lb where $conditionstr"
            );
        $redis->setex($count_key,$expire,serialize($arraycont));
        $total = $cont = $arraycont[0]['c'];
        $res_data = $ap_model->fetchAll("SELECT * from lb where $conditionstr  ORDER BY $order ".' limit '.$start.','.$limit);
        $redis->setex($data_key,$expire,serialize($res_data));
        }
        $total = $cont = $arraycont[0]['c'];
        
        $columns = array(
            array( 'db' => 'dm','dt' => 0 ),
            array( 'db' => 'name','dt' => 1 ),
            array( 'db' => 'c','dt' => 2 ),
            array( 'db' => 'zdf','dt' => 3 ),
            array( 'db' => 'zxj','dt' => 4 )
        );
        $ap_list['data'] = $this->data_output($columns, $res_data);
        $ap_list['data'] = array_values($ap_list['data']);
        $ap_list['recordsTotal'] = $total;
        $ap_list['recordsFiltered'] = $cont;
        $ap_list['draw'] = isset($data['draw'])?$data['draw']:1;
        $this->rspJson($ap_list);
    }
    
    public function detailAction(){
        $dm = trim($this->request->get('dm'));
        //$dm = substr($dm,2,6);
        if(!in_array(substr($dm,0,1),array('0','3','6'))){
             exit('error');
        }
        $ap_model = $this->di->getShared('db');
        $redis = $this->di->getShared('redis');
        $data_key = $dm.'_data';
        $expire = 86400; 
        $cache_data = $redis->get($data_key);
        if($cache_data){
          echo $cache_data;
        }else{       
            $res_data = $ap_model->fetchAll("select * from dm where dm='$dm'");
            $str = '代码：'.$res_data[0]['dm'];
            $str .= '名字：'.$res_data[0]['name'];
            $str .= "<br>f10信息：<br>".$res_data[0]['content']."<br>".$res_data[0]['des'];
            $str .= "<br>业绩：<br>".$res_data[0]['yj'];
            $redis->setex($data_key,$expire,serialize($str));
            echo $str;
        }
    }
    
    static function data_output ( $columns, $data )
    {
        $out = array();
    
        for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
            $row = array();
    
            for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                $column = $columns[$j];
    
                // Is there a formatter?
                if ( isset( $column['formatter'] ) ) {
                    $row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
                }
                else {
                    $row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
                }
                if($column['dt'] == 'dm'){
                    $dm = $data[$i][ $column['db'] ];
                    $row[ $column['dt'] ] = "<a href='detail?dm=$dm' target='_blank'>".$row[ $column['dt'] ]."</a>";
                }
            }
    
            $out[] = $row;
        }
    
        return $out;
    }
    /**
     * @brief rspJson
     * @param $code
     * @param $data
     * @return
     */
    public function rspJson( $data = [])
    {
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setJsonContent([
            'draw' => $data['draw'],
            'recordsTotal' => $data['recordsTotal'],
            'recordsFiltered' => $data['recordsFiltered'],
            'data' => $data['data']
        ]);
        $this->response->send();
        exit();
    }
}
