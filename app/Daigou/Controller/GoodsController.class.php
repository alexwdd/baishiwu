<?php
namespace Daigou\Controller;

class GoodsController extends CommonController {

    #列表
    public function index() {
        if (IS_POST) {
            $map['agentID']=$this->user['id'];
            $cateArr = M('DgCate')->where($map)->getField('id,name');
            unset($map);

            $path = I('path');
            $keyword  = I('keyword');
            if ($keyword!='') {
                $map['name'] = array('like','%'.$keyword.'%');
            }
            if($path!=''){
                $map['path'] = array('like', $path.'%');
            }

            $map['agentID'] = $this->user['id'];
            $obj = M('DgGoods');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['cate'] = $cateArr[$value['cid']];
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);
            }

            $result = array(
                'data'=>array(
                    'list'=>$list,
                    "pageNum"=>$pageNum,
                    "pageSize"=>$pageSize,
                    "pages"=>$pageSize,
                    "total"=>$total
                )
            );
            echo $this->return_json($result);       
        }else{
            $map['agentID'] = $this->user['id'];
            $cate = M("DgCate")->where($map)->order('path,id asc')->select();
            foreach ($cate as $key => $value) {
                $count = count(explode('-', $value['path'])) - 3;
                $cate[$key]['count'] = $count;
            }
            $this->assign('cate', $cate);
            $this->display();
        }           
    }

    public function add(){
        if($_POST){
            $this->all_add('DgGoods',U('Goods/index'));  
        }else{
            $map['agentID'] = $this->user['id'];
            $cate = M("DgCate")->where($map)->order('path,id asc')->select();
            foreach ($cate as $key => $value) {
                $count = count(explode('-', $value['path'])) - 3;
                $cate[$key]['count'] = $count;
            }
            $this->assign('cate', $cate);

            $server = M("Server")->where(array('agentID'=>0))->order("sort asc")->select();
            $this->assign('server', $server);

            unset($map);
            $map['agentID'] = $this->user['id'];
            $attr = M("GoodsAttribute")->where($map)->field('id,name,values')->order("sort asc")->select();
            foreach ($attr as $key => $value) {
                $attr[$key]['item'] = explode("\n", $value['values']);
            }
            $this->assign('attr', $attr);

            $wuliu = M("Wuliu")->where(array('agentID'=>0))->order("sort asc")->select();
            $this->assign('wuliu', $wuliu);

            $this->assign('tag',C('GOODS_TAG'));
            $this->display();
        }
    }

    #编辑
    public function edit() {
        if ($_POST) {
            $this->all_save('DgGoods',U('Goods/index'));  
        }else{
            $id = (int) $_GET['id'];
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('DgGoods');
            $map['agentID'] = $this->user['id'];
            $map['id'] = $id;
            $list = $obj->where($map)->find();
            if (!$list) {
                $this->error('信息不存在');
            } else {
                if ($list['image']) {
                    $image = explode(",", $list['image']);
                }else{
                    $image = [];
                }
                $this->assign('image', $image);

                if ($list['server']) {
                    $list['server'] = explode(",", $list['server']);
                }
                $this->assign('list', $list);

                $spec = M("DgGoodsIndex")->where(array('goodsID'=>$list['id'],'base'=>0))->select();
                $this->assign('spec', $spec);

                unset($map);
                $map['agentID'] = $this->user['id'];
                $cate = M("DgCate")->where($map)->order('path,id asc')->select();
                foreach ($cate as $key => $value) {
                    $count = count(explode('-', $value['path'])) - 3;
                    $cate[$key]['count'] = $count;
                }
                $this->assign('cate', $cate);

                $server = M("Server")->where(array('agentID'=>0))->order("sort asc")->select();
                $this->assign('server', $server);

                unset($map);
                $map['agentID'] = $this->user['id'];
                $attr = M("GoodsAttribute")->where($map)->field('id,name,values')->order("sort asc")->select();
                foreach ($attr as $key => $value) {
                    $attr[$key]['item'] = explode("\n", $value['values']);
                }
                $this->assign('attr', $attr);

                $wuliu = M("Wuliu")->where(array('agentID'=>0))->order("sort asc")->select();
                $this->assign('wuliu', $wuliu);

                $this->assign('tag',C('GOODS_TAG'));
                $this->display();
            }
        }
    }

    #删除
    public function del() {
        $id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $map['agentID'] = $this->user['id'];
            $obj = M('DgGoods');
            $list = $obj->where($map)->delete();
            if ($list) {
                $where['goodsID'] = array('in',$id);
                M("DgGoodsIndex")->where($where)->delete(); //商品规格
                //M("GoodsAttr")->where($where)->delete(); //商品属性
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    public function getSpec(){
        $wuliu = M("Wuliu")->order("sort asc")->select();
        $this->assign("wuliu",$wuliu);

        unset($map);
        $map['agentID'] = $this->user['id'];
        $cate = M("DgCate")->where($map)->order('path,id asc')->select();
        foreach ($cate as $key => $value) {
            $count = count(explode('-', $value['path'])) - 3;
            $cate[$key]['count'] = $count;
        }
        $this->assign('cate', $cate);

        $this->assign('tag',C('GOODS_TAG'));
        $res = $this->fetch();        
        echo $res;
    }

    public function delspec()
    {
        if ($_POST) {
            M("DgGoodsIndex")->where(array('id'=>I('post.id')))->delete();
        }
    }

    public function ags(){
        if (IS_POST) {
            set_time_limit(0);
            ini_set("memory_limit", "512M"); 
            
            $file = I('post.excel');
            import("Common.ORG.PHPExcel");
            $objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load('.'.$file);
            $sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            //$highestColumm= PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27
            $obj = M('DgGoodsIndex');
            $obj1 = M('DgGoods');
            $total = 0;
            $error = '';
            for ( $i = 2; $i <= $highestRow; $i++) {
                $agsID = trim($sheet->getCellByColumnAndRow(0, $i)->getValue());
                $price = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());         
                $obj->where(array('agsID'=>$agsID))->setField('price',$price);
                $obj1->where(array('agsID'=>$agsID))->setField('price',$price);
            }
            
            $msg = '共'.($highestRow-1).'条数据，成功导入'.$total.'条，错误信息'.$error;
            $this->success($msg);
        }else{
            $this->display();
        }
    }

    public function import(){
        if (IS_POST) {
            set_time_limit(0);
            ini_set("memory_limit", "512M"); 
            
            $file = I('post.excel');
            import("Common.ORG.PHPExcel");
            $objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load('.'.$file);
            $sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            //$highestColumm= PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27
            $obj = M('DgGoods');
            $total = 0;
            $error = '';
            for ( $i = 2; $i <= $highestRow; $i++) {
                $goodsID = trim($sheet->getCellByColumnAndRow(0, $i)->getValue());
                if ($goodsID!='' && $goodsID>0) {
                    $res = $obj->where(array('id'=>$goodsID,'agentID'=>$this->user['id']))->find();
                }

                if ($res) {
                    unset($data);               
                    $data['name'] = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());
                    $data['short'] = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                    $data['intr'] = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
                    $cid = trim($sheet->getCellByColumnAndRow(4, $i)->getValue());
                    $cid1 = trim($sheet->getCellByColumnAndRow(5, $i)->getValue());
                    if ($cid>0 && $cid!='') {
                        $path = M('DgCate')->where(array('id'=>$cid))->getField("path");
                        if ($path) {
                            $data['cid'] = $cid;
                            $data['path'] = $path;
                        }else{
                            $data['cid'] = 0;
                            $data['path'] = '';
                        }                    
                    }else{
                        $data['cid'] = 0;
                        $data['path'] = '';
                    }
                    
                    if ($cid1>0 && $cid1!='') {
                        $path1 = M('DgCate')->where(array('id'=>$cid1))->getField("path");
                        if ($path1) {
                            $data['cid1'] = $cid1;
                            $data['path1'] = $path1;
                        }else{
                            $data['cid1'] = 0;
                            $data['path1'] = '';
                        }                    
                    }else{
                        $data['cid1'] = 0;
                        $data['path1'] = '';
                    }                 
                    $data['brandID'] = trim($sheet->getCellByColumnAndRow(6, $i)->getValue());
                    $data['typeID'] = trim($sheet->getCellByColumnAndRow(7, $i)->getValue());
                    $data['price'] = trim($sheet->getCellByColumnAndRow(8, $i)->getValue());
                    $data['weight'] = trim($sheet->getCellByColumnAndRow(9, $i)->getValue());
                    $data['wuliuWeight'] = trim($sheet->getCellByColumnAndRow(10, $i)->getValue());
                    $data['endDate'] = trim($sheet->getCellByColumnAndRow(11, $i)->getValue());
                    $data['number'] = trim($sheet->getCellByColumnAndRow(12, $i)->getValue());
                    $data['show'] = trim($sheet->getCellByColumnAndRow(13, $i)->getValue());
                    $data['comm'] = trim($sheet->getCellByColumnAndRow(14, $i)->getValue());
                    $data['empty'] = trim($sheet->getCellByColumnAndRow(15, $i)->getValue());
                    $data['gst'] = trim($sheet->getCellByColumnAndRow(16, $i)->getValue());
                    $data['sellNumber'] = trim($sheet->getCellByColumnAndRow(17, $i)->getValue());
                    $data['keyword'] = trim($sheet->getCellByColumnAndRow(18, $i)->getValue());
                    $data['inprice'] = trim($sheet->getCellByColumnAndRow(19, $i)->getValue());
                    $data['say'] = trim($sheet->getCellByColumnAndRow(20, $i)->getValue());
                    $data['wuliu'] = trim($sheet->getCellByColumnAndRow(21, $i)->getValue());
                    $data['sort'] = trim($sheet->getCellByColumnAndRow(22, $i)->getValue());
                    $data['agsID'] = trim($sheet->getCellByColumnAndRow(23, $i)->getValue());
                    $obj->where(array('id'=>$goodsID))->save($data);

                    unset($map);
                    $map['goodsID'] = $goodsID;
                    $map['base'] = 1;

                    unset($data['sellNumber']);
                    unset($data['inprice']);
                    M('DgGoodsIndex')->where($map)->save($data);
                }else{
                    unset($data);               
                    $data['name'] = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());
                    $data['short'] = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                    $data['intr'] = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
                    $cid = trim($sheet->getCellByColumnAndRow(4, $i)->getValue());
                    $cid1 = trim($sheet->getCellByColumnAndRow(5, $i)->getValue());
                    if ($cid>0 && $cid!='') {
                        $path = M('DgCate')->where(array('id'=>$cid))->getField("path");
                        if ($path) {
                            $data['cid'] = $cid;
                            $data['path'] = $path;
                        }else{
                            $data['cid'] = 0;
                            $data['path'] = '';
                        }                    
                    }else{
                        $data['cid'] = 0;
                        $data['path'] = '';
                    }
                    
                    if ($cid1>0 && $cid1!='') {
                        $path1 = M('DgCate')->where(array('id'=>$cid1))->getField("path");
                        if ($path1) {
                            $data['cid1'] = $cid1;
                            $data['path1'] = $path1;
                        }else{
                            $data['cid1'] = 0;
                            $data['path1'] = '';
                        }                    
                    }else{
                        $data['cid1'] = 0;
                        $data['path1'] = '';
                    }

                    $data['brandID'] = trim($sheet->getCellByColumnAndRow(6, $i)->getValue());
                    $data['typeID'] = trim($sheet->getCellByColumnAndRow(7, $i)->getValue());
                    $data['price'] = trim($sheet->getCellByColumnAndRow(8, $i)->getValue());
                    $data['weight'] = trim($sheet->getCellByColumnAndRow(9, $i)->getValue());
                    $data['wuliuWeight'] = trim($sheet->getCellByColumnAndRow(10, $i)->getValue());
                    $data['endDate'] = trim($sheet->getCellByColumnAndRow(11, $i)->getValue());
                    $data['number'] = trim($sheet->getCellByColumnAndRow(12, $i)->getValue());
                    $data['show'] = trim($sheet->getCellByColumnAndRow(13, $i)->getValue());
                    $data['comm'] = trim($sheet->getCellByColumnAndRow(14, $i)->getValue());
                    $data['empty'] = trim($sheet->getCellByColumnAndRow(15, $i)->getValue());
                    $data['gst'] = trim($sheet->getCellByColumnAndRow(16, $i)->getValue());
                    $data['sellNumber'] = trim($sheet->getCellByColumnAndRow(17, $i)->getValue());
                    $data['keyword'] = trim($sheet->getCellByColumnAndRow(18, $i)->getValue());
                    $data['inprice'] = trim($sheet->getCellByColumnAndRow(19, $i)->getValue());
                    $data['say'] = trim($sheet->getCellByColumnAndRow(20, $i)->getValue());
                    $data['wuliu'] = trim($sheet->getCellByColumnAndRow(21, $i)->getValue());
                    $data['agsID'] = trim($sheet->getCellByColumnAndRow(23, $i)->getValue());
                    $data['sort'] = 50;
                    $data['updateTime'] = time();
                    $data['createTime'] = time();
                    $data['agentID'] = $this->user['id'];
                    $goodsID = $obj->add($data);
                    $data['goodsID'] = $goodsID;
                    $data['base'] = 1;
                    unset($data['updateTime']);
                    unset($data['createTime']);
                    unset($data['sellNumber']);
                    unset($data['inprice']);
                    M('DgGoodsIndex')->add($data);
                }
            }
            
            $msg = '共'.($highestRow-1).'条数据，成功导入'.$total.'条，错误信息'.$error;
            $this->success($msg);
        }else{
            $this->display();
        }
    } 

    public function export(){
        import("Common.ORG.PHPExcel");

        $list = M('DgGoods')->where(array('agentID'=>$this->user['id']))->order('id desc')->select();
        $objPHPExcel = new \PHPExcel();    
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '编号')
            ->setCellValue('B1', '名称')
            ->setCellValue('C1', '短名称')
            ->setCellValue('D1', '描述')
            ->setCellValue('E1', '分类1(数字)')
            ->setCellValue('F1', '分类2(数字)')
            ->setCellValue('G1', '品牌(数字)')
            ->setCellValue('H1', '包裹类型(数字)')
            ->setCellValue('I1', '价格')
            ->setCellValue('J1', '商品重量(kg)')
            ->setCellValue('K1', '物流重量(kg)')
            ->setCellValue('L1', '保质期')
            ->setCellValue('M1', '单品数量')
            ->setCellValue('N1', '状态(0隐藏1显示)')
            ->setCellValue('O1', '本周特价(0否1是)')
            ->setCellValue('P1', '售罄(0否1是)')
            ->setCellValue('Q1', '含税(0否1是)')
            ->setCellValue('R1', '初始销量')
            ->setCellValue('S1', '关键词')
            ->setCellValue('T1', '进货价')
            ->setCellValue('U1', '特色描述')
            ->setCellValue('V1', '快递')
            ->setCellValue('W1', '排序')
            ->setCellValue('X1', 'agsID');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['id'])                
                ->setCellValue('B'.$num, $v['name'])                
                ->setCellValue('C'.$num, $v['short'])
                ->setCellValue('D'.$num, $v['intr'])                 
                ->setCellValue('E'.$num, $v['cid'])
                ->setCellValue('F'.$num, $v['cid1'])
                ->setCellValue('G'.$num, $v['brandID'])
                ->setCellValue('H'.$num, $v['typeID'])
                ->setCellValue('I'.$num, $v['price'])
                ->setCellValue('J'.$num, $v['weight'])
                ->setCellValue('K'.$num, $v['wuliuWeight'])
                ->setCellValue('L'.$num, $v['endDate'])
                ->setCellValue('M'.$num, $v['number'])
                ->setCellValue('N'.$num, $v['show'])
                ->setCellValue('O'.$num, $v['comm'])
                ->setCellValue('P'.$num, $v['empty'])
                ->setCellValue('Q'.$num, $v['gst'])
                ->setCellValue('R'.$num, $v['sellNumber'])
                ->setCellValue('S'.$num, $v['keyword'])
                ->setCellValue('T'.$num, $v['inprice'])
                ->setCellValue('U'.$num, $v['say'])
                ->setCellValue('V'.$num, $v['wuliu'])
                ->setCellValue('W'.$num, $v['sort'])
                ->setCellValue('X'.$num, $v['agsID']);
        }

        $objPHPExcel->getActiveSheet()->setTitle('商品');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="商品.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }  


    public function import1(){
        if (IS_POST) {
            set_time_limit(0);
            ini_set("memory_limit", "512M"); 
            
            $file = I('post.excel');
            import("Common.ORG.PHPExcel");
            $objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load('.'.$file);
            $sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            //$highestColumm= PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27
            $obj = M('DgGoodsIndex');
            $total = 0;
            $error = '';
            for ( $i = 2; $i <= $highestRow; $i++) {
                $goodsID = trim($sheet->getCellByColumnAndRow(0, $i)->getValue());
    
                if ($goodsID>0) {
                    unset($data);               
                    $data['name'] = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());
                    $data['short'] = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                    //$data['en'] = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
                    $cid = trim($sheet->getCellByColumnAndRow(4, $i)->getValue());
                    $cid1 = trim($sheet->getCellByColumnAndRow(5, $i)->getValue());
                    if ($cid>0 && $cid!='') {
                        $path = M('DgCate')->where(array('id'=>$cid))->getField("path");
                        if ($path) {
                            $data['cid'] = $cid;
                            $data['path'] = $path;
                        }else{
                            $data['cid'] = 0;
                            $data['path'] = '';
                        }                    
                    }else{
                        $data['cid'] = 0;
                        $data['path'] = '';
                    }
                    
                    if ($cid1>0 && $cid1!='') {
                        $path1 = M('DgCate')->where(array('id'=>$cid1))->getField("path");
                        if ($path1) {
                            $data['cid1'] = $cid1;
                            $data['path1'] = $path1;
                        }else{
                            $data['cid1'] = 0;
                            $data['path1'] = '';
                        }                    
                    }else{
                        $data['cid1'] = 0;
                        $data['path1'] = '';
                    }
                    $data['price'] = trim($sheet->getCellByColumnAndRow(6, $i)->getValue());
                    $data['number'] = trim($sheet->getCellByColumnAndRow(7, $i)->getValue());
                    $data['wuliu'] = trim($sheet->getCellByColumnAndRow(8, $i)->getValue());

                    $obj->where(array('id'=>$goodsID))->save($data);
                    $total++;
                }
            }
            
            $msg = '共'.($highestRow-1).'条数据，成功导入'.$total.'条，错误信息'.$error;
            $this->success($msg);
        }else{
            $this->display();
        }
    } 

    public function export1(){
        import("Common.ORG.PHPExcel");

        $map['base']=0;
        $map['agentID'] = $this->user['id'];
        $list = M('DgGoodsIndex')->where($map)->order('id desc')->select();
        $objPHPExcel = new \PHPExcel();    
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '编号')
            ->setCellValue('B1', '名称')
            ->setCellValue('C1', '短名称')
            ->setCellValue('D1', '英文')
            ->setCellValue('E1', '分类1(数字)')
            ->setCellValue('F1', '分类2(数字)')
            ->setCellValue('G1', '价格')
            ->setCellValue('H1', '单品数量')   
            ->setCellValue('I1', '快递');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['id'])                
                ->setCellValue('B'.$num, $v['name'])                
                ->setCellValue('C'.$num, $v['short'])
                ->setCellValue('D'.$num, $v['en'])                 
                ->setCellValue('E'.$num, $v['cid'])
                ->setCellValue('F'.$num, $v['cid1'])
                ->setCellValue('G'.$num, $v['price'])
                ->setCellValue('H'.$num, $v['number'])
                ->setCellValue('I'.$num, $v['wuliu']);
        }

        $objPHPExcel->getActiveSheet()->setTitle('套餐');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="套餐.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }
}
?>