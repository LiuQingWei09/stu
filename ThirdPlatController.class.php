<?php  

  /**
     * 批量编辑商品状态推送前端
     * 批量增加商品状态推送前端
     * 批量删除商品状态推送前端
     * @param $sku_code  商品编码
     * @param $wms_status 商品状态 1正常品 6过季品 7一次性切货 8暂不经营 9永不经营'
     * @param $site_id    分站点 1北京 2上海 4全国
     * @param 数据格式:json
     * author:liuqingwei
     * date:‎2016‎年‎9‎月‎24‎日 ‎星期六
     */
    public function batchCURD()
    {
        //接收数据
        $res_data = $_POST["json_data"] ? $_POST["json_data"] : $_GET["json_data"];
        //记录日志
        LogHelper::debug($res_data, 'Catalog:ThirdPlat:updateWmsStatus:request');
        //转化成数组
        $param_data = json_decode($res_data,true);
        //判断是否为数组
        if(is_array($param_data)) {
        	//实例化product_warehouse_map模型
        	$waremodel = M('product_warehouse_map','','DB_CATALOG');
        	//实例化product模型
            $productmodel = M('product','','DB_CATALOG');
    		//遍历根据sku_code取数据
        	foreach($param_data as $key=>$value) {
        		 $data[] = $productmodel
                                    ->fetchSql(false)
                                    ->field('sku_code,is_deleted,product_id')
                                    ->where('sku_code='.$value['skuId'])
                                    ->select();
                   $param_data[$key]['product_id'] = $data[$key][0]['product_id'];
        	}
         
            foreach($param_data as $key=>$value) {
                //取出非空的数据  说明该product有该sku_code
                if (!empty($value['product_id'])) {
                    // 将非空的数据取出来 
                    $all_data[$key]['product_id'] = $value['product_id'];
                    $all_data[$key]['site_id'] = $value['skuSite'];
                    $all_data[$key]['wms_status']= $value['skuStatus'];
                    $all_data[$key]['is_deleted']= $value['is_deleted'];
                }
            }
            foreach($all_data as $key=>$value){
                $lookup['product_id'] = $value['product_id'];
                $lookup['site_id'] = $value['site_id'];
                //根据条件查找  如果有数据 则说明原表中有该字段  则不添加   如果为空则说明表中没有该字段 则添加
                $ware_status_data= $waremodel->fetchSql(false)->field('map_id,product_id,site_id,wms_status')->where($lookup)->select();
                if (empty($ware_status_data)) { //添加的数据
                    $add_data[$key]['product_id'] = $value['product_id'];
                    $add_data[$key]['site_id'] = $value['site_id'];
                    $add_data[$key]['wms_status'] = $value['wms_status'];
                } else {  //更新的数据
                    $update_data[$key]['product_id'] = $value['product_id'];
                    $update_data[$key]['site_id'] = $value['site_id'];
                    $update_data[$key]['wms_status'] = $value['wms_status'];
                    //取出更新的map_id
                    $update_data[$key]['map_id'] =$ware_status_data[0]['map_id'];
                    
                }
               
            }
            //$add_data是添加的数据 
            // $update_data是更新的数据
            foreach($add_data as $key=>$value) {
                if ($value['wms_status']==1) {
                	$product_update_data['product_id'] = $value['product_id'];
                    $product_update_data['is_deleted'] = 0;
                	//记录日志
                	LogHelper::debug($value, 'Catalog:ThirdPlat:batchCURD:request');
                    //直接在ware表中添加 同时将is_deleted置为0
                    $res = $waremodel->add($value);
       				//更新product中的字段
                    $link = $productmodel->save($product_update_data);
                    //判断
                    if(!empty($res) && !empty($link)) {
                    	$this->successData();
                    } else {
                    	$this->errorData();
                    }
                } else {
                    //直接ware中添加 同时在product中将is_deleted置为1
                    $product_update_data['product_id'] = $value['product_id'];
                    $product_update_data['is_deleted'] = 1;
                	//记录日志
                	LogHelper::debug($value, 'Catalog:ThirdPlat:batchCURD:request');
                    //直接在ware表中添加 同时将is_deleted置为0
                    $res = $waremodel->add($value);
       				//更新product中的字段
                    $link = $productmodel->save($product_update_data);
                    //判断
                    if(!empty($res) && !empty($link)) {
                    	$this->successData();
                    } else {
                    	$this->errorData();
                    }
                }
            }
            foreach ($update_data as $key=>$value) {
                if($value['wms_status']==1){
                    //更新ware表  同时在product中将is_deleted置为0
                    $product_update_data['product_id'] = $value['product_id'];
                    $product_update_data['is_deleted'] = 0;
                    //记录日志
                	LogHelper::debug($value, 'Catalog:ThirdPlat:batchCURD:request');
                	 //直接在ware表中添加 同时将is_deleted置为0
                    $res = $waremodel->save($value);
       				//更新product中的字段
                    $link = $productmodel->save($product_update_data);
                    //判断
                    if(!empty($res) && !empty($link)) {
                    	$this->successData();
                    } else {
                    	$this->errorData();
                    }
                } else {
                    //更新ware表  同时在product中将is_deleted置为1
                     //更新ware表  同时在product中将is_deleted置为0
                    $product_update_data['product_id'] = $value['product_id'];
                    $product_update_data['is_deleted'] = 1;
                    //记录日志
                	LogHelper::debug($value, 'Catalog:ThirdPlat:batchCURD:request');
                	 //直接在ware表中添加 同时将is_deleted置为0
                    $res = $waremodel->save($value);
       				//更新product中的字段
                    $link = $productmodel->save($product_update_data);
                    //判断
                    if(!empty($res) && !empty($link)) {
                    	$this->successData();
                    } else {
                    	$this->errorData();
                    }
                }
            }
                
        }
	}

    /**
     * 执行成功
     */
    private function successData()
    {
        $result["status"] = 1;
        $result["info"] = "成功";
        LogHelper::debug($result["info"], 'Catalog:ThirdPlat:addWmsStatus:success');
        exit(json_encode($result)); 
    }
    /**
     * 执行失败
     */
    private function errorData()
    {
         $result["status"] = 0;
         $result["info"] = "失败";
         LogHelper::debug($result["info"], 'Catalog:ThirdPlat:addWmsStatus:failed');
         exit(json_encode($result)); 
    }