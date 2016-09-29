<?php 
 	/**
     * @param $sku_code 
     * @param $wms_status 
     * @param $site_id   
     * @param is_delete   
     * @param author liuqingwei
     *
     */
    public function addDelChangeProductStatus()
    {
        $res_data = $_POST["json_data"] ? $_POST["json_data"] : $_GET["json_data"];
        $param_data = json_decode($res_data,true);
        if (is_array($param_data))
        {
            $waremodel      = M('product_warehouse_map','','DB_CATALOG');
            $productmodel   = M('product','','DB_CATALOG');
            $all_data       = array();
            foreach($param_data as $key=>$value)
            {   
               $all_data[] = $productmodel->field('product_id')->where('sku_code='.$value['skuId'])->select();
               $param_data[$key]['product_id'] = $all_data[$key][0]['product_id'];
            }
            $lookup      = array(); 
            $add_data    = array(); 
            $update_data = array(); 
            foreach($param_data as $key=>$value)
            {   
                if (!empty($value['product_id']))
                {   
                    $lookup['product_id'] = $value['product_id'];
                    $lookup['site_id']    = $value['skuSite'];
                    $ware_status_data     = $waremodel->fetchSql(false)->field('map_id')->where($lookup)->select();
                    if (empty($ware_status_data)) 
                    { 
                        $add_data[$key]['product_id'] = $value['product_id'];
                        $add_data[$key]['site_id']    = $value['skuSite'];
                        $add_data[$key]['wms_status'] = $value['skuStatus'];
                        $add_data[$key]['mtime']      = date("Y-m-d H:i:s",time());
                        $add_data[$key]['is_delete']  = $value['isValid']==1 ? 0 : 1;
                    }
                    else
                    {
                        $update_data[$key]['product_id'] = $value['product_id'];
                        $update_data[$key]['site_id']    = $value['skuSite'];
                        $update_data[$key]['wms_status'] = $value['skuStatus'];
                        $update_data[$key]['map_id']     = $ware_status_data[0]['map_id'];
                        $update_data[$key]['mtime']      = date("Y-m-d H:i:s",time());
                        $update_data[$key]['is_delete']  = $value['isValid']==1 ? 0 : 1;
                    } 
                }
            }
            if (!empty($add_data))
            {
                if (!empty($add_data[0]))
                {
                    $res = $waremodel->fetchSql(false)->addAll($add_data);
                    if ($res === false)
                    {
                        $result["status"] = 0;
                        $result["info"]   = '添加失败';
                        exit(json_encode($result));  
                    }
                } 
                else
                {
                    foreach ($add_data as $key=>$value)
                    {   
                        $res = $waremodel->add($value);
                        if($res === false )
                        {
                            $result["status"] = 0;
                            $result["info"]   = "添加失败";
                            exit(json_encode($result));  
                        }
                    }
                }
            }
            if (!empty($update_data))
            {
               
                foreach ($update_data as $key=>$value )
                {   
                    $link = $waremodel->save($value);
                    if ($link === false )
                    {
                        $result["status"] = 0;
                        $result["info"]   = '更新失败';
                        exit(json_encode($result));  
                    }
                }
            }
            if ($res || $link )
            {
                $result["status"] = 1;
                $result["info"]   = '成功';
                exit(json_encode($result)); 
            }
        }  
    }
     /**
    * @param $wms_status  
    * @param $sku_code   
    * @param $site_id    
    * @author liuqingwei
    */
    public function newchange_product_status()
    {

        $wms_status = (int) I('wms_status');
        $sku_code  	= I('sku_code', 0, int);  
        $site_id  	 = (int) I('site_id');      
         
        
        $model_product 		    = M('product','','DB_CATALOG'); 
        $model_warehouse_map = M('product_warehouse_map','','DB_CATALOG');
        $product_ids 		      = $model_product->field('product_id')->where('sku_code='.$sku_code)->select();
        $warehouse_data 	    = $model_warehouse_map->fetchSql(false)
                                                    ->where('product_id='.$product_ids[0]['product_id'].' AND '.'site_id='.$site_id)
                                                    ->select();
        $update_warehouse_map = array();
        if ($wms_status === 9)
        {
           //1为无效
           $update_warehouse_map['is_delete'] = 1;
        }
        else
        {
           $update_warehouse_map['is_delete'] = 0; 
        }
        
       
        $update_warehouse_map['wms_status'] = $wms_status;
        $update_warehouse_map['map_id']	    = $warehouse_data[0]['map_id'];
        $update_warehouse_map['site_id']    = $site_id;
        $update_warehouse_map['product_id'] = $product_ids[0]['product_id'];    

        if (strlen(I("sku_code")) > 0 && strlen(I('site_id')) > 0 )
        {
            $model_warehouse_map->save($update_warehouse_map);
            
            exit(json_encode(true));
        }

        exit(json_encode(false));
    }
     /**
     * 
     * @param product_id
     * @param site_id   
     * @param author liuqingwei
     */
    public function newDelete()
    {
        $product_id          = I('product_id', 0, int);
        $site_id             = (int) I('site_id'); 
        $model_product       = D('Product');
        $model_warehouse_map = M('product_warehouse_map','','DB_CATALOG');
        $rules = array(
            array('product_id', 'require', 'PRODUCT_ID_IS_EMPTY', 1),
        );
        if (!$model_product->validate($rules)->create(I()))
        {
            $info['flag']  = 0;
            $info['error'] = $model_product->getError();
            exit(json_encode($info));
        }
        
       
        $where['product_id']    = $product_id;
        $where['site_id']       = $site_id;
        $warehouse_data         = $model_warehouse_map ->where($where)->select();
        $setdata['map_id']      = $warehouse_data[0]['map_id'];
        $setdata['product_id']  = $product_id;
        $setdata['site_id']     = $site_id;
        $setdata["is_delete"]   = 1;
        $setdata["mtime"]       = date('Y-m-d H:i:s',time());
        if (strlen(I('site_id')) > 0)
        {
            $count  = $model_warehouse_map->save($setdata);
        }
        
        if ($count)
        {
            $info['flag'] = 1;
        }
        else
        {
            $info['flag'] = 0;
            $info['error'] = "DELETE_ERROR";
        }

        exit(json_encode($info));
    }
