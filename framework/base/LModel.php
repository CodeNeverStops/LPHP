<?php
/**
 * 
 * @author loki
 *
 */
class LModel
{
    protected $_table = '';
    
    /**
     * @return LDB
     */
    protected function getDefaultDB() {
        $db = LDB::getInstance(L::getApp()->config['db']);
        $db->connect();
        return $db;
    }
}
?>