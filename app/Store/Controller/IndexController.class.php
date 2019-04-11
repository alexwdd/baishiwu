<?php
namespace Store\Controller;

class IndexController extends HomeController
{
	public function index()
	{
		$map['agentID'] = $this->agent['id'];
		$list = M('AgentAd')->cache(true)->where($map)->select();
		foreach ($list as $key => $value) {
			if ($value['goodsId']>0) {
				$list[$key]['url'] = U('store/detail',['id'=>$value['goodsId'],'agentid'=>$this->agent['id'],'token'=>$token]);
			}
		}
		$this->assign('list',$list);
		$this->assign('first',$list[count($list)-1]);
		$this->assign('last',$list[0]);

		unset($map);
		$map['agentID'] = $this->agent['id'];
		$map['comm'] = 1;
		$map['show'] = 1;
		$goods = M('Goods')->field('id,name,picname,price')->where($map)->order('sort asc,id desc')->select();
		$this->assign('goods',$goods);
		$this->display();
	}
}
