<?php 

class ExternalDbTool
{
    public function __construct(&$db)
    {
        $this->db =& $db;
    }
    
    public function executeS($sql)
    {
        $TResult = array();
        
        $res = $this->db->query($sql);
        if ($res)
        {
            while ($obj = $this->db->fetch_object($res))
            {
                $TResult[] = $obj;
            }
            return $TResult;
        }
        
        return false;
        
    }
    
    public function getRow($sql)
    {
        $TResult = array();
        
        $sql .= ' LIMIT 1;';
        
        $res = $this->db->query($sql);
        if ($res)
        {
            return $this->db->fetch_object($res);
        }
        
        return false;
        
    }
    
    
    
    public function getvalue($sql)
    {
        $TResult = array();
        
        $sql .= ' LIMIT 1;';
        
        $res = $this->db->query($sql);
        if ($res)
        {
            $Tredult =  $this->db->fetch_row($res);
            return $Tredult[0];
        }
        
        return false;
        
    }
    
}
?>