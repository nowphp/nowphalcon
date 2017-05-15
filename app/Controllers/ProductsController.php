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
        $start = isset($data["start"]) && intval($data["start"])>0?intval($data["start"]):0;
        $limit = isset($data["length"]) && intval($data["length"])?intval($data["length"]):10;
        $column = $data['order'][0]['column'];
        $dir = $data['order'][0]['dir'];
        $order = " zdf DESC ";
        if($column == 2){
           $order = " c $dir";
        }
        if($column == 3){
            $order = " zdf $dir";
        }
        $search = isset($data["search"]["value"])?trim($data["search"]["value"]):'';
        $conditionstr = '';
        $conditionstr .= $search?" tnew.name like '$search%' ":'';
        $conditionstr .= $search?" or tnew.dm like '$search%' ":'';
        $conditionstr = $conditionstr?" and ($conditionstr) ":' and 1=1';
        session_start();
        $_SESSION['dxf_conditionstr'] = $conditionstr?:'1=1';
        $ap_model = $this->di->getShared('db');
        $date =  $ap_model->fetchAll("select data_time from record group by data_time order by data_time desc limit 2");
        $sdate = $date[1]['data_time'];
        $edate = $date[0]['data_time'];
        $arraycont = $ap_model->fetchAll(
            "SELECT count(1) c from (
SELECT ((tnew.cje-told.cje)*10)/told.cje c,tnew.dm,tnew.name,tnew.zdf,tnew.data_time FROM 
(SELECT * FROM record WHERE 
data_time='$edate'
) tnew
LEFT JOIN ( SELECT * FROM record WHERE 
data_time='$sdate'
) told 
ON tnew.dm=told.dm WHERE   tnew.zdf<9.5 AND told.zdf<9.5 AND told.cje<>0 AND tnew.zdf>0 $conditionstr HAVING  c>1 ORDER BY zdf DESC ) t "
            );
        $total = $cont = $arraycont[0]['c'];
        $res_data = $ap_model->fetchAll("SELECT dm,name,c,zdf from (
SELECT ((tnew.cje-told.cje)*10)/told.cje c,tnew.dm,tnew.name,tnew.zdf,tnew.data_time FROM 
(SELECT * FROM record WHERE 
data_time='$edate'
) tnew
LEFT JOIN ( SELECT * FROM record WHERE 
data_time='$sdate'
) told 
ON tnew.dm=told.dm WHERE tnew.zdf<9.5 AND told.zdf<9.5 AND told.cje<>0 AND tnew.zdf>0 $conditionstr HAVING  c>1 ORDER BY $order ) t ".' limit '.$start.','.$limit);
        $columns = array(
            array( 'db' => 'dm','dt' => 0 ),
            array( 'db' => 'name','dt' => 1 ),
            array( 'db' => 'c','dt' => 2 ),
            array( 'db' => 'zdf','dt' => 3 )
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
        $dm = substr($dm,2,6);
        if(!in_array(substr($dm,0,1),array('0','3','6'))){
             exit('error');
        }
        $ap_model = $this->di->getShared('db');
        $res_data = $ap_model->fetchAll("select * from dm where dm='$dm'");
        var_dump($res_data);
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
                    $row[ $column['dt'] ] = "<a href='detail?dm=$dm' >".$row[ $column['dt'] ]."</a>";
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
