<?php

namespace Single\Controllers;

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
//导出csv
    function export_csv($filename,$data) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
    }
    
    //获取导出数据
    public function outputAction(){
        session_start();
        if(isset($_SESSION['dxf_conditionstr'])){
            $conditionstr =  $_SESSION['dxf_conditionstr'] ;
            $ap_model = new DxfTtySoilAppointment();
            $res_data = $ap_model->find([
                "conditions" => $conditionstr,
                "columns" => "id,mobile,username,concat(provincename,cityname,countryname,addr) addr,ctime,concat(cropname,'/',area) area",
                "order"  => "id desc",
            ])->toArray();
            $str = iconv('utf-8','gb2312',"编号,手机,姓名,详细地址,提交时间,作物/面积\n");
            foreach ($res_data as $v){
                $username = iconv('utf-8','gb2312',$v['username']);
                $addr = iconv('utf-8','gb2312',$v['addr']);
                $area = iconv('utf-8','gb2312',$v['area']);
                $str .= $v['id'].",".$v['mobile'].",".$username.",".$addr.",".$v['ctime'].",".$area."\n"; //用引文逗号分开
            }
            $filename = date('Ymd').'.csv'; //设置文件名
            $this->export_csv($filename,$str); //导出
        }else{
            echo '非法请求';exit;
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
            }
    
            $out[] = $row;
        }
    
        return $out;
    }
}
