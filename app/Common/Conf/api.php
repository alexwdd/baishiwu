<?php
return array(
	'SUCCESS_RETURN'=> 'SUCCESS', //成功返回信息

	'infoArr'=>array(
		array('py'=>'tc','name'=>'同城活动','db'=>'Tongcheng','fid'=>1),
		array('py'=>'zf','name'=>'租房/卖房','db'=>'House','fid'=>2),
		array('py'=>'sp','name'=>'二手商品','db'=>'Ershou','fid'=>3),
		array('py'=>'zp','name'=>'招聘/求职','db'=>'Job','fid'=>4),
		array('py'=>'esc','name'=>'二手车','db'=>'Car','fid'=>5),
		array('py'=>'ms','name'=>'美食/外卖','db'=>'Food','fid'=>6),
		array('py'=>'sh','name'=>'生活服务','db'=>'Life','fid'=>7),
		array('py'=>'xp','name'=>'新品上架','db'=>'Xinpin','fid'=>8),
	),
  
  	'phoneType'=>array(
		array('id'=>1,'name'=>'SingTel','img'=>'/tpl/static/image/phone/singtel.png','aimg'=>'/tpl/static/image/phone/singtel-1.png',active=>true),
		array('id'=>2,'name'=>'StarHub','img'=>'/tpl/static/image/phone/starhub.png','aimg'=>'/tpl/static/image/phone/starhub-1.png',active=>false),
		array('id'=>3,'name'=>'M1','img'=>'/tpl/static/image/phone/m1.png','aimg'=>'/tpl/static/image/phone/m1-1.png',active=>false)
	),

	'phoneCate'=>array(
		array('id'=>1,'name'=>'充话费'),
		array('id'=>2,'name'=>'充流量'),
		array('id'=>3,'name'=>'充套餐')
	),
);