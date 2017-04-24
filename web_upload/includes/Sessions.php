<?php
/**
 * Sessions class.
 *
 * @package     GenericClasses
 * @subpackage  Classes
 * @version     1.0
 * @author      Kruzya <admin@crazyhackgut.ru>
 */

namespace Kruzya\Generic;

class Sessions implements \SessionHandlerInterface {
    /**
     * Database connection.
     *
     * @class   \Kruzya\GenericClasses\Database
     */
    private $db;

    /**
     * Constructor.
     *
     * @param   $db             Database class.
     * @return  void
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Initialize session
     *
     * @param   $savePath       The path where to store/retrieve the session.
     * @param   $sessionName    The session name.
     * @return  bool
     */
    public function open($savePath, $sessionName) {
        return true;
    }

    /**
     * Closes the current session.
     *
     * @return  bool
     */
    public function close() {
        return true;
    }

    /**
     * Destroys a session. Called by session_regenerate_id() (with $destroy = TRUE), session_destroy() and when session_decode() fails.
     *
     * @param   $session_id     The session ID being destroyed.
     * @return  bool
     */
    public function destroy($session_id) {
        $this->db->run("DELETE :prefix:session WHERE `session_id` = ?", array($session_id));

        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @param   $MaxLifeTime    Sessions that have not updated for the last MaxLifeTime seconds will be removed. 
     * @return  bool
     */
    public function gc($MaxLifeTime) {
        $ids = $this->db->getAll("SELECT `session_id` FROM :prefix:session WHERE `last_usage` < ?", array(time() - $MaxLifeTime));
        if (count($ids) > 0)
            foreach ($ids as $id)
                $this->db->run("DELETE FROM :prefix:session WHERE `session_id` = ?", array($id));

        return true;
    }

    /**
     * Read session data.
     *
     * @param   $session_id     The session id.
     * @return  string
     */
    public function read($session_id) {
        return $this->db->getOne("SELECT `data` FROM :prefix:session WHERE `session_id` = ?", array($session_id));
    }

    /**
     * Write session data
     *
     * @param   $session_id     The session id.
     * @param   $session_data   The encoded session data.
     * @return  bool
     */
    public function write($session_id, $session_data) {
        $this->db->run("REPLACE INTO :prefix:session (`session_id`, `data`, `last_usage`) VALUES (?, ?, ?)", array($session_id, $session_data, time()));
        return true;
    }
}
