<?php
namespace Adminx\Controller;

class PushController extends AdminController {

	#列表
	public function index() {
		$this->display();
	}

	public function data(){
		$obj = M('Push');

		if (!$_SESSION['administrator']) {
			$map['city'] = $this->user['cityID'];
		}

        $total = $obj->where($map)->count();
        $pageSize = I('post.pageSize',20);

        $field = I('post.field','id');
        $order = I('post.order','desc');

        $pages = ceil($total/$pageSize);
        $pageNum = I('post.pageNum',1);
        $firstRow = $pageSize*($pageNum-1); 

        $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
        foreach ($list as $key => $value) {
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
	}

	public function checkData($data){
		if ($data['type']==1) {
			if ($data['url']=='') {
				$this->error("请填写文章链接地址");
			}
		}else{
			if ($data['model']=='') {
				$this->error("请选择帖子模块");
			}
			if ($data['articleID']=='') {
				$this->error("请输入帖子ID");
			}
		}
	}

	#添加
	public function add() {
		if($_POST){
	        $obj = D('Push');
	        if ($data = $obj->create()) {
	        	$this->checkData($data);
	            if ($list = $obj->add($data)) {
	            	$this->doPush($data);
	                $this->success('操作成功');
	            } else {
	                $this->error('操作失败');
	            }
	        } else {
	            $this->error($obj->getError());
	        }
		}else{
			if (!$_SESSION['administrator']) {
				$map['city'] = $this->user['cityID'];
			}
			$city = M('PushCity')->where($map)->select();
			foreach ($city as $key => $value) {
				$city[$key]['name'] = M('OptionItem')->where(array('id'=>$value['city']))->getField("name");
			}
			$this->assign('city',$city);
			$this->display();
		}
	}

	/*#编辑
	public function edit() {
		if($_POST){
	        $obj = D('Push');
	        if ($data = $obj->create()) {
	        	$this->checkData($data);
	            if ($list = $obj->save($data)) { 
	                $this->success('操作成功');
	            } else {
	                $this->error('操作失败');
	            }            
	        } else {
	            $this->error($obj->getError());
	        }
		}else{
			$id = I('get.id');
			if ($id=='' || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$obj = M('Push');
			$map['id'] = $id;
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				$this->assign('list', $list);
				$this->assign('city',M('PushCity')->select());
				$this->display();
			}
		}
	}

	public function send(){
		$id = input('get.id');
		if ($id=='' || !is_numeric($id)) {
			$this->error("参数错误");
		}
		$list = M('Push')->where(array('id'=>$id))->find();
		if (!$list) {
			$this->error("信息不存在");
		}
		return $this->doPush($list);
	}*/

	public function doPush($data){
		vendor('JPush.autoload');  // 这个是thinkphp自己的方法 vendor 连接到我们的极光
		$city = M('PushCity')->where(array('city'=>$data['city']))->find();
		if (!$city) {
			return ['code'=>0,'城市不存在'];
		}
        $app_key=$city['key']; //注册成开发者会提供这个
        $master_secret=$city['secret'];//注册成开发者会提供这个
        $client = new \JPush\Client($app_key, $master_secret);    
        $res = $client->push()
            ->setPlatform('all')
            ->addAllAudience()
            ->setNotificationAlert($data['title'], array(
                'alert' => $data['title'],
                'extras' => array(
                    'type' => $data['type'],
                    'url'=>$data['url'],
                    'model'=>$data['model'],
                    'id'=>$data['articleID']
                ),
            ))
            ->iosNotification($data['title'], array(
            	'alert' => $data['title'],
                'sound' => 'sound.caf',
                'badge' => '1',
                'extras' => array(
                    'type' => $data['type'],
                    'url'=>$data['url'],
                    'model'=>$data['model'],
                    'id'=>$data['articleID']
                ),
            ))
            ->androidNotification($data['title'], array(
            	'alert' => $data['title'],
	            'extras' => array(
	                'type' => $data['type'],
                    'url'=>$data['url'],
                    'model'=>$data['model'],
                    'id'=>$data['articleID']
	            ),
	        ))
	        ->message($data['title'], array(
                'extras' => array(
	                'type' => $data['type'],
                    'url'=>$data['url'],
                    'model'=>$data['model'],
                    'id'=>$data['articleID']
	            )
            ))
            ->options(array(
	            // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
	            // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
	            // 这里设置为 100 仅作为示例

	            // 'sendno' => 100,

	            // time_to_live: 表示离线消息保留时长(秒)，
	            // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
	            // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
	            // 这里设置为 1 仅作为示例

	            // 'time_to_live' => 1,

	            // apns_production: 表示APNs是否生产环境，
	            // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

	            'apns_production' => True,

	            // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
	            // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
	            // 这里设置为 1 仅作为示例

	            // 'big_push_duration' => 1
	        ))
            ->send();
            return ['code'=>1,$res];
	}

	#删除
	public function del() {
		$id = explode(",",I('post.id'));
		$this->all_del('Push',$id);
	}
}
?>