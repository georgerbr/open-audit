<?php
#  Copyright 2003-2015 Opmantek Limited (www.opmantek.com)
#
#  ALL CODE MODIFICATIONS MUST BE SENT TO CODE@OPMANTEK.COM
#
#  This file is part of Open-AudIT.
#
#  Open-AudIT is free software: you can redistribute it and/or modify
#  it under the terms of the GNU Affero General Public License as published
#  by the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#
#  Open-AudIT is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU Affero General Public License for more details.
#
#  You should have received a copy of the GNU Affero General Public License
#  along with Open-AudIT (most likely in a file named LICENSE).
#  If not, see <http://www.gnu.org/licenses/>
#
#  For further information on Open-AudIT or for a license other than AGPL please see
#  www.opmantek.com or email contact@opmantek.com
#
# *****************************************************************************

/**
* @category  Model
* @package   Open-AudIT
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   2.1.1
* @link      http://www.open-audit.org
 */

/**
 * @access   public
 *
 * @category Object
 *
 * @author   Mark Unwin <marku@opmantek.com>
 * @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 *
 * @link     http://www.open-audit.org
 */
class M_change_log extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->log = new stdClass();
        $this->log->status = 'reading data';
        $this->log->type = 'system';
    }

    /**
     * Create an alert for a given piece for a given system.
     *
     * @access  public
     *
     * @param   system_id, alert table, alert row, details, timestamp
     *
     * @return nothing
     */
    public function create($system_id, $db_table, $db_row, $db_action, $details, $timestamp)
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'creating data';
        stdlog($this->log);
        $sql = "INSERT INTO change_log (`system_id`, `db_table`, `db_row`, `db_action`, `details`, `timestamp` ) VALUES ( ?, ?, ?, ?, ?, ? )";
        $data = array("$system_id", "$db_table", "$db_row", "$db_action", "$details", "$timestamp");
        $this->run_sql($sql, $data);
    }

    /**
     * Delete all alerts in the DB.
     *
     * @access  public
     *
     * @return int
     */
    public function deleteAll()
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'deleting data';
        stdlog($this->log);
        $sql = "DELETE FROM change_log";
        $sql = $this->clean_sql($sql);
        $this->db->query($sql);
        $count = $this->db->affected_rows();
        return ($count);
    }

    /**
     * Delete all alerts older than $days in the DB.
     *
     * @access  public
     *
     * @return int
     */
    public function deleteDays($days = 365)
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'deleting data';
        stdlog($this->log);
        $sql = "DELETE FROM change_log WHERE DATE(timestamp) < DATE_SUB(curdate(), INTERVAL $days day)";
        $sql = $this->clean_sql($sql);
        $this->db->query($sql);
        $count = $this->db->affected_rows();
        return ($count);
    }

    /**
     * Count all alerts in the DB.
     *
     * @access  public
     *
     * @return int
     */
    public function count()
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        $sql = "SELECT COUNT(*) AS count FROM change_log";
        $sql = $this->clean_sql($sql);
        $query = $this->db->query($sql);
        $row = $query->row();
        return($row->count);
    }

    /**
     * All alerts in the DB older than XX days.
     *
     * @access  public
     *
     * @return int
     */
    public function countDays($days = 7)
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        $sql = "SELECT COUNT(*) AS count FROM change_log WHERE DATE(timestamp) < DATE_SUB(curdate(), INTERVAL $days day)";
        $sql = $this->clean_sql($sql);
        $query = $this->db->query($sql);
        $row = $query->row();
        return($row->count);
    }

    /**
     * Get the alert's for a given system.
     *
     * @access  public
     *
     * @param   system_id
     *
     * @return array
     */
    public function readDevice($id)
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        $id = intval($id);
        if ($id > 0) {
            $sql = "SELECT change_log.*, users.full_name FROM change_log LEFT JOIN users ON change_log.user_id = users.id WHERE change_log.system_id = ? ORDER BY timestamp";
            $sql = $this->clean_sql($sql);
            $data = array($id);
            $result = $this->run_sql($sql, $data);
            return ($result);
        }
        return;
    }

    /**
     * Get the details fo a given alert.
     *
     * @access  public
     *
     * @param   alert_id
     *
     * @return array
     */
    public function readChange($id)
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        $sql = "SELECT change_log.*, system.name, system.ip, system.description FROM change_log LEFT JOIN system ON (change_log.system_id = system.id) WHERE change_log.id = ?";
        $sql = $this->clean_sql($sql);
        $data = array("$id");
        $result = $this->run_sql($sql, $data);
        return ($result);
    }

    /**
     * Update an alert with details of a Change record.
     *
     * @access  public
     *
     * @param   array(alert id, change type, change id, external change id, external change link, alert note, user id, alert acknowledge timestamp)
     *
     * @return nothing
     */
    public function updateChange($details)
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'updating data';
        stdlog($this->log);
        foreach ($details['alerts'] as $key => $value) {
            $sql = "UPDATE change_log SET change_type = ?, change_id = ?, external_ident = ?, external_link = ?, note = ?, user_id = ?, ack_time = ? WHERE id = ?";
            $data = array($details['change_type'], $details['change_id'], $details['external_change_id'], $details['external_change_link'], $details['alert_note'], $details['user_id'], $details['alert_ack_time'], "$value");
            $this->run_sql($sql, $data);
        }
    }
}
