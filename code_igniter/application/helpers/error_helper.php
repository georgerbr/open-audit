<?php  if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
 }
#
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

/*
 * @package Open-AudIT
 * @author Mark Unwin <marku@opmantek.com>
 * @version 1.8.4
 * @license http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 */
if (! function_exists('getError')) {
    /**
     * The standard log function for Open-AudIT. Writes logs to a text file in the desired format (json or syslog).
     *
     * @access	  public
     *
     * @category  Function
     *
     * @author    Mark Unwin <marku@opmantek.com>
     *
     * @param	  Object	log_details		An object containing details you wish to log
     *
     * @return NULL [logs the provided string to the log file]
     */
    function getError ($error = '') {

        if (isset($error) and $error != '') {
            if (!isset($error->model)) {
                $error->model = 'unknown model';
            }
            if (!isset($error->controller)) {
                $error->controller = 'unknown controller';
            }
        } else {
            $error = new stdClass();
            $error->controller = 'unknown controller';
            $error->model = 'unknown model';
        }

        $error_array = array();
        $CI = & get_instance();

        $error_array['ERR-0001'] = new stdClass();
        $error_array['ERR-0001']->code = 'ERR-0001';
        $error_array['ERR-0001']->type = 'HTTP/1.1 500 Internal Server Error';
        $error_array['ERR-0001']->severity = 3;
        $error_array['ERR-0001']->message = "No groups returned for user '" . $CI->user->user_name . "' when " . $error->controller . " called " . $error->model . ".";
        $error_array['ERR-0001']->extended_message = 'When requesting the list of groups the user is assigned access to, no groups were returned. This usually indicates either (rightly) that the user has no permissions on any groups (which will result in this user not being able to access any device data in Open-AudIT) or that something has gone wrong inside Open-AudIT. You might go to menu -> Admin -> Users -> List Users, click on edit for this user and make sure they have an access level on at least one group.';

        $error_array['ERR-0002'] = new stdClass();
        $error_array['ERR-0002']->code = 'ERR-0002';
        $error_array['ERR-0002']->type = 'HTTP/1.1 500 Internal Server Error';
        $error_array['ERR-0002']->severity = 3;
        $error_array['ERR-0002']->message = "No object could be retrieved when " . $error->controller . " called " . $error->model . ".";
        $error_array['ERR-0002']->extended_message = "When calling this function an identifier (usually but not always an integer based id) should be supplied. The supplied item was either blank, not an integer based id or we could not determine the corresponding object based on the details provided. Please check the log file for the controller and model this occurred on and report the issue to Opmantek.";

        $error_array['ERR-0003'] = new stdClass();
        $error_array['ERR-0003']->code = 'ERR-0003';
        $error_array['ERR-0003']->type = 'HTTP/1.1 500 Internal Server Error';
        $error_array['ERR-0003']->severity = 3;
        $error_array['ERR-0003']->message = "No group columns could be retrieved when " . $error->controller . " called " . $error->model . ".";
        $error_array['ERR-0003']->extended_message = "When requesting the columns for a group, no group columns either for the original group id, nor group id #1 were found.";

        $error_array['ERR-0004'] = new stdClass();
        $error_array['ERR-0004']->code = 'ERR-0004';
        $error_array['ERR-0004']->type = 'HTTP/1.1 500 Internal Server Error';
        $error_array['ERR-0004']->severity = 4;
        $error_array['ERR-0004']->message = "Name, not id passed when " . $error->controller . " called " . $error->model . ".";
        $error_array['ERR-0004']->extended_message = "Controllers should pass an integer based id as a first preference. Controllers should determine (where possible) the id if supplied a name.";

        $error_array['ERR-0005'] = new stdClass();
        $error_array['ERR-0005']->code = 'ERR-0005';
        $error_array['ERR-0005']->type = 'HTTP/1.1 500 Internal Server Error';
        $error_array['ERR-0005']->severity = 3;
        $error_array['ERR-0005']->message = "No data returned for user '" . $CI->user->user_name . "' when " . $error->controller . " called " . $error->model . ".";
        $error_array['ERR-0005']->extended_message = 'A request was made to a model, but no data was retrieved from the database.';

        $error_array['ERR-0006'] = new stdClass();
        $error_array['ERR-0006']->code = 'ERR-0006';
        $error_array['ERR-0006']->type = 'HTTP/1.1 403 Forbidden';
        $error_array['ERR-0006']->severity = 3;
        $error_array['ERR-0006']->message = "User '" . $CI->user->user_name . "' is not authorised to view group.";
        $error_array['ERR-0006']->extended_message = 'A user attempted to view the details of a group he is not authorised to. To enable this user to view this group, edit the user via menu -> Admin -> Users -> List Users and allow at least View Group level of access.';

        foreach ($error_array as $error_each) {
            if ($error_each->severity == '3') {
                $error_each->severity_text = 'error';
            }
            if ($error_each->severity == '4') {
                $error_each->severity_text = 'warning';
            }
        }

        if (!isset($error->code)) {
            return $error_array;
        } elseif (isset($error->code) and isset($error_array[$error->code])) {
            if (isset($error->model)) {
                $error_array[$error->code]->model = $error->model;
            } else {
                $error->model = '';
            }
            if (isset($error->controller)) {
                $error_array[$error->code]->controller = $error->controller;
            } else {
                $error->controller = '';
            }
            return $error_array[$error->code];
        }



}

/* End of file error_helper.php */
/* Location: ./system/application/helpers/error_helper.php */
}