<?php 

class Ali_OpenSearch_Model_Resource_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    public function prepareResult($object, $queryText, $query)
    {                
        $adapter = $this->_getWriteAdapter();
        if (!$query->getIsProcessed()) {
           
            $table = $this->getTable('catalogsearch/result');
            $sql = "insert into $table (query_id, product_id) values";
            $result = Mage::helper('opensearch')->getResult($queryText,$query->getId());
            $value='';
            
            //遍历数据拼装sql语句
            foreach($result as $key=>$value) {
                $sql .= '(';
                foreach($value as $k=>$v) {
                //判断当前遍历的是否是最后一个元素
                if ($k < count($value)-1) {
                $sql .= "'". $v . "',";
                } else {
                $sql .= "'" . $v . "'";
                }
                }
                //判断当前遍历的是否不是最后一行
                if ($key < count($result)-1) {
                $sql .= '),';
                } else {
                $sql .= ')';
                }
            }
            
            $sql .='ON DUPLICATE KEY UPDATE `relevance` = VALUES(`relevance`)';
            
            $adapter->query($sql);

            $query->setIsProcessed(1);
        }

        return $this;
    }
}

