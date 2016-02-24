<?php
namespace BO\Zmsdb\Helper;

/**
 * Session handler for mysql
 */
class ZMS_SessionHandler extends \BO\Zmsdb\Base implements \SessionHandlerInterface
{

    public $sessionName = \App::SESSION_NAME;
    
    public function open($save_path, $name)
    {        
        $this->sessionName = $name;
    }
    
    public function close()
    {
        return true;
    }

    public function read($session_id)
    {
        $result = $this->getReader()->fetchOne('
            SELECT 
                sessioncontent as content
            FROM 
                sessiondata 
            WHERE 
                sessionid = ? AND
                sessionname = ?
            ', array(
            $session_id,
            $this->sessionName
        ));
        return $result['content'];
    }

    public function write($session_id, $session_data)
    {
        \App::$log->debug("SESSION WRITE HANDLER: ". $session_id ." - ". print_r($_SESSION,1));
        $query = '
            REPLACE INTO 
                sessiondata 
            SET 
                sessionid=?, 
                sessionname=?, 
                sessioncontent=?
        ';        
        $statement = $this->getWriter()->prepare($query);
        return $statement->execute(array(
            $session_id,
            $this->sessionName,
            $session_data
        ));
    }

    public function destroy($session_id)
    {
        $query = '
            DELETE FROM
                sessiondata
            WHERE
                sessionid=? AND
                sessionname=?
        ';
        $statement = $this->getWriter()->prepare($query);
        return $statement->execute(array(
            $session_id,
            $this->sessionName
        ));
    }
    
    public function gc($maxlifetime)
    {
        /*
         $compareTs = time() - $maxlifetime;
         $query = '
         DELETE FROM
         sessiondata
         WHERE
         UNIX_TIMESTAMP(`ts`) < ? AND
         sessionname=?
         ';
         $statement = $this->getWriter()->prepare($query);
         return $statement->execute(array(
         $compareTs,
         $this->sessionName
         ));
         */
    }
}