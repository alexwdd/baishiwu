<?php
namespace Adminx\Controller;

class QuestionCateController extends AdminController {

	public function getClassID($fid,$arr){
        $map['fid']=$fid;
        $list = M('QuestionCate')->field('id')->where($map)->select();
        foreach ($list as $key => $value) {         
            $this->arr[] = $value['id'];
            $this->getClassID($list['id'],$arr);
        }
    }

    #分类列表
    public function index() {
        $artClass = M('QuestionCate');
        $list = $artClass->where($map)->field("id,name,fid,path,sort")->order('path , sort asc , id asc')->select();
        foreach ($list as $key => $value) {
            $count = count(explode('-', $value['path'])) - 2;
            if ($value['fid'] > 0) {
                $list[$key]['style'] = 'style="padding-left:' . (($count * 10) + 10) . 'px;"';
            }
        }
        $this->assign('list', $list);
        $this->display();
    }
    
    /*添加分类*/
    public function add(){
        if($_POST){
            $cate = D('QuestionCate');
            if($data = $cate->create()){
                if ($list=$cate->add($data)) {
                    $data['path']=$data['path'].$list.'-';
                    $cate->where('id='.$list)->save($data);
                    $this->success('分类添加成功',$url);
                } else {
                    $this->error('分类添加失败');
                }
            }else{
                $this->error($cate->getError());
            }
        }else{
            $fid = I('get.fid');
            $path = I('get.path');
            $artClass = M('QuestionCate');
            $cate = $artClass->where($map)->field("id,name,fid,path")->order('path')->select();
            foreach ($cate as $key => $value) {
                $count = count(explode('-', $value['path'])) - 3;
                $cate[$key]['count'] = $count;
            }
            $this->assign('cate', $cate);
            $this->assign('fid', $fid);
            $this->assign('path', $path);
            $this->display();
        }
    }


    public function edit(){
        if($_POST){
            $cate = D('QuestionCate');
            if ($data = $cate->create()) {
                if($data['id']==$data['fid']){
                    $this->error('不能以自身为上级栏目');
                }
                $thisFID = $_POST['thisFID'];

                if($thisFID==0 && $data['fid']>0){
                    $this->error('根栏目不能移动');
                }
                if($data['fid']>0){
                    $tClass = M('QuestionCate');

                    $fdata = $tClass->field('path')->where('id='.$data['fid'])->find();

                    if(strstr($fdata['path'],$data['path'])){
                        $this->error('不能移动到自身下级栏目');
                    }
                    $data['path']=$fdata['path'].$data['id'].'-';
                }else{
                    $data['path']=I('post.path');
                }
                $oldPath = I('post.path');

                if ($cate->save($data)) {
                    $cate->execute("UPDATE __PREFIX__question_cate SET path = replace(path, '".$oldPath."','".$data['path']."')");
                    $url = U('QuestionCate/index');
                    $this->success('分类编辑成功',$url);
                } else {
                    $this->error('分类编辑失败');
                }
            } else {
                $this->error($cate->getError());
            }
        }else{
            $id = I('get.id');
            if(isset($id)){
                $list=M('QuestionCate')->where('id='.$id)->find();
                if($list){  
                    $cate = M('QuestionCate')->where($map)->field("id,name,fid,path")->order('path')->select();
                    foreach ($cate as $key => $value) {
                        $count = count(explode('-', $value['path'])) - 3;
                        $cate[$key]['count'] = $count;
                    }

                    $this->assign('list',$list);
                    $this->assign('cate', $cate);
                    $this->display();
                }else{
                    $this->error('没有该分类');
                }
            }else{
                $this->error('参数错误');
            }
        }
    }
    
    public function del(){
        $id = I('get.id');
        if(!isset($id) || !is_numeric($id)){
            $this->error('您没有选择任何分类！');
        }

        $cate = M('QuestionCate');
        $list = $cate->where('fid='.$id)->find();

        if($list){
            $this->error('请先删除子栏目');
        }

        $list = $cate->where('id='.$id)->delete();      
        if($list){
            $this->success("操作成功","reload");
        }else{
            $this->error('操作失败');
        }
    }

    public function clear(){
        $id = I('get.id');
        if(!isset($id) || !is_numeric($id)){
            $this->error('您没有选择任何分类！');
        }

        $list = M('Question')->where('cid='.$id)->select();
        foreach ($list as $key => $value) {
            M('QuestionItem')->where(array('sid'=>$value['id']))->delete();            
        }
        M('Question')->where(array('cid'=>$id))->delete();
        $this->success("操作成功","reload");
    }

    public function import(){
        if (IS_POST) {
            set_time_limit(0);
            ini_set("memory_limit", "512M"); 
            //上传文件
            $file = $this->_upload();
            import("Common.ORG.PHPExcel");

            $objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file);
            $sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            //$highestColumm= PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27
            $obj = M('Question');
            $total = 0;
            $cid = I('post.cid');
            $path = I('post.path');
            for ( $i = 2; $i <= $highestRow; $i++) {  
                $typeName = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                if ($typeName=='单选题' || $typeName=='判断题') {
                    $type = 1;
                }elseif($typeName=='多选题'){
                    $type = 2;
                }
                $data['sort'] = trim($sheet->getCellByColumnAndRow(0, $i)->getValue());
                $data['name'] = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
                $data['type'] = $type;          
                $data['cid'] = $cid; 
                $data['path'] = $path; 
                $data['score'] = trim($sheet->getCellByColumnAndRow(12, $i)->getValue()); //分值
                $data['number'] = 0;
                $data['picname'] = '';            
                $data['url'] = '';            
                $data['intr'] = trim($sheet->getCellByColumnAndRow(13, $i)->getValue());            
                $data['show'] = 1;            
                $data['createTime'] = time();
                $data['updateTime'] = time();
                if ($data['name']!='') {
                    $list = $obj->add($data);
                    if ($list) {
                        //添加答案
                        $first = 4;
                        $true = strtoupper(trim($sheet->getCellByColumnAndRow(11, $i)->getValue()));
                        $trueArr = array();
                        $answerNumber = 0;
                        for($k = 0 ; $k < strlen($true) ; $k++){
                            array_push($trueArr, $true[$k]);
                        }
                        for($j=0;$j<6;$j++){
                            $name = trim($sheet->getCellByColumnAndRow($first, $i)->getValue());
                            if (in_array(num2en($j),$trueArr)) {
                                $status = 1;
                            }else{
                                $status = 0;
                            }
                            if ($name) {
                                $d = array(
                                    'sid'=>$list,
                                    'type'=>$type,
                                    'name'=>$name,
                                    'picname'=>'',
                                    'status'=>$status,
                                    'sort'=>1,
                                    'createTime'=>time(),
                                    'updateTime'=>time(),
                                    );
                                if(M('QuestionItem')->add($d)){
                                    $answerNumber++;
                                }
                            }
                            $first++;
                        }    
                        if($answerNumber>0){
                            $obj->where(array('id'=>$list))->setField('number',$answerNumber);
                        }                    
                        $total++; 
                    }
                }                
            }
            
            $msg = '共'.($highestRow-1).'条数据，成功导入'.$total.'条';
            $this->success($msg);
        }else{
            $id = I('get.id');
            if(isset($id)){
                $list=M('QuestionCate')->where('id='.$id)->find();
                if($list){  
                    $this->assign('list',$list);
                    $this->display();
                }else{
                    $this->error('没有该分类');
                }
            }else{
                $this->error('参数错误');
            }
        }
    }

    #文件上传
    private function _upload(){
        $path = './temp/';
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 10000000;  //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
        $upload->rootPath = $path;        //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
        $upload->saveRule=uniqid;    //上传文件的文件名保存规则  time uniqid  com_create_guid  uniqid
        $upload->autoSub = true;
        $upload->replace=true;     //如果存在同名文件是否进行覆盖
        $upload->exts= array('xls','xlsx');     //准许上传的文件后缀
        //$upload->allowTypes= array('application/vnd.ms-excel'); //检测mime类型
        $info = $upload->upload();
        if($info){
            foreach($info as $file){
                $filepath = $path.$file['savepath'];
                $url = $path.$file['savepath'].$file['savename'];
            }
            return $url;          
        }else{
            //是专门来获取上传的错误信息的            
            $this->error($upload->getError());
        }
    }
}
?>