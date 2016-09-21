<?php 
namespace Home\Controller;
use Think\Controller;
class AreaController extends Controller 
{
	//三级联动效果
	public function index()
	{
		//实例化数据库
		$areamodel = M('cyk_region_copy1'); 
		//获取省/直辖市的数据
		$province_data = $areamodel -> where('levels=1') -> select();
		//回显
		$this -> assign('provinceData',$province_data);
		//渲染
		$this -> display();
	}
	public function getAreaById()
	{
		//接受地区id
		$regionid = I('post.regionid');
		//echo $regionid;
		//实例化数据库
		$areamodel = M('cyk_region_copy1'); 
		//所有pid等于地区id的值  意思就是说 商丘的pid 为河南 现在pid和商丘一样的数据
		$ajax_data = $areamodel -> where('pid='.$regionid) -> select();
		//发给ajax
		$this -> ajaxReturn($ajax_data);
	}
}