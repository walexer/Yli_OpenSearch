<?php

require_once(Mage::getModuleDir('', 'Ali_OpenSearch') . DS .'lib'. DS ."CloudsearchClient.php");
require_once(Mage::getModuleDir('', 'Ali_OpenSearch') . DS .'lib'. DS ."CloudsearchIndex.php");
require_once(Mage::getModuleDir('', 'Ali_OpenSearch') . DS .'lib'. DS ."CloudsearchDoc.php");
require_once(Mage::getModuleDir('', 'Ali_OpenSearch') . DS .'lib'. DS ."CloudsearchSearch.php");
class Ali_OpenSearch_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getResult($query,$query_id)
    {
        $store_id = Mage::app()->getStore()->getId();
        $result = $this->getOpenSearchResult($query, $store_id);
        $product_ids = array();
        foreach ($result['result']['items'] as $item){
            $product_ids[] = array($query_id,$item['product_id']);
        } 
        return $product_ids;
    }
    
    private function getOpenSearchResult($query,$store_id)
    {
        $access_key = Mage::getStoreConfig('catalog/opensearch/access_key');
        $secret = Mage::getStoreConfig('catalog/opensearch/secret');
        //杭州公网API地址：http://opensearch-cn-hangzhou.aliyuncs.com
        //北京公网API地址：http://opensearch-cn-beijing.aliyuncs.com 
        $host = "http://opensearch-cn-hangzhou.aliyuncs.com";//根据自己的应用区域选择API
        $key_type = "aliyun";  //固定值，不必修改
        $opts = array('host'=>$host);
        $client = new CloudsearchClient($access_key,$secret,$opts,$key_type);

        // 实例化一个搜索类
        $search_obj = new CloudsearchSearch($client);
        // 指定一个应用用于搜索
        $apps = Mage::getStoreConfig('catalog/opensearch/apps');
        $search_obj->addIndex($apps);
        // 指定搜索关键词
        $search_obj->setQueryString("default:$query");
        
        $search_obj->addFilter("store_id=$store_id");
        // 指定返回的搜索结果的格式为json
        $search_obj->setFormat("json");
        // 执行搜索，获取搜索结果
        $json = $search_obj->search();
        // 将json类型字符串解码
        $result = json_decode($json,true);
        
        return $result;
    }
}