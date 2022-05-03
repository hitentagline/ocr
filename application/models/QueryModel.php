<?php
class QueryModel extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->setTimezone();
    }

    function setTimezone()
    {
        date_default_timezone_set('Asia/Kolkata');
    }

    function dateFormat($date)
    {
        $newDate = date('d-m-Y', strtotime($date));
        return $newDate;
    }

    function timeFormat($time)
    {
        $newTime = date('h:i A', strtotime($time));
        return $newTime;
    }

    function dbDateFormat($date)
    {
        $newDate = date('Y-m-d', strtotime($date));
        return $newDate;
    }

    function dbTimeFormat($time)
    {
        $newTime = date('H:i:s', strtotime($time));
        return $newTime;
    }

    function dateTimeFormat($dateTime)
    {
        $newDateTime = date('F d, Y h:i:s A', strtotime($dateTime));
        return $newDateTime;
    }

    function dateWordFormat($dateTime)
    {
        $newDateTime = date('j M, Y', strtotime($dateTime));
        return $newDateTime;
    }

    function generateToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    function passwordHash($password)
    {
        $options = array(
            'cost' => 12,
        );
        $hashedPass = password_hash($password, PASSWORD_BCRYPT, $options);

        return $hashedPass;
    }

    function getIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    function dbDate()
    {
        return date('Y-m-d h:i:s');
    }

    function insertRecord($tbl_name, $data)
    {
        $data['ip_address'] = $this->getIp();
        $data['created_at'] = $this->dbDate();
        $data['updated_at'] = $this->dbDate();
        $this->db->insert($tbl_name, $data);
        $insert_id = $this->db->insert_id();
        return  $insert_id;
    }

    function insertMultipleRecord($tbl_name, $data)
    {
        $dbData = array();
        $dbData['ip_address'] = $this->getIp();
        $dbData['created_at'] = $this->dbDate();
        $dbData['updated_at'] = $this->dbDate();
        $cntRecords = count($data);
        for ($i = 0; $i < $cntRecords; $i++) {
            $newData[] = array_merge_recursive($data[$i], $dbData);
        }
        $action = $this->db->insert_batch($tbl_name, $newData);
        if ($action == true) {
            return 1;
        } else {
            return 0;
        }
    }

    function updateRecord($tbl_name, $data, $where)
    {
        $data['ip_address'] = $this->getIp();
        $data['updated_at'] = $this->dbDate();
        $this->db->where($where);
        $update = $this->db->update($tbl_name, $data);
        if ($update == true) {
            return 1;
        } else {
            return 0;
        }
    }

    function selectSingelRecord($tbl_name, $where)
    {
        $this->db->select('*');
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get();
        return $record->row_array();
    }

    function selectSelectedSingelRecord($tbl_name, $where, $columns = '*')
    {
        $this->db->select($columns);
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get();
        return $record->row_array();
    }

    function getLastRecord($tbl_name, $where)
    {
        $date = new DateTime("now");
        $curr_date = $date->format('Y-m-d ');

        $this->db->select('*');
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->where('created_at = ', $curr_date);
        $this->db->order_by('created_at', 'desc');
        $this->db->limit(1);
        $this->db->from($tbl_name);
        $record = $this->db->get();
        return $record->row_array();
    }

    function deleteRecord($tbl_name, $where, $img_field, $path)
    {
        $this->db->select('*');
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get();
        $rows = $record->result_array();
        $ttl_record = $record->num_rows();
        if ($ttl_record > 0) {
            foreach ($rows as $row) {

                if ($img_field != "") {
                    if (!empty($row[$img_field])) {
                        $prev_file_path = $path . $row[$img_field];
                        if (file_exists($prev_file_path)) {
                            unlink($prev_file_path);
                        }
                    }
                }
            }
            $this->db->where($where);
            $record = $this->db->delete($tbl_name);
            if ($record == true) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    function selectMultipleRecord($tbl_name, $where)
    {
        $this->db->select('*');
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get();
        return $record->result_array();
    }

    function selectMultipleRecordFilter($tbl_name, $where = '', $orderBy = '', $start = '', $limit = '')
    {
        $this->db->select('*');
        if ($where != "") {
            $this->db->where($where);
        }
        if ($orderBy != "") {
            $this->db->order_by($orderBy);
        }
        if ($limit != "") {
            $this->db->limit($limit, $start);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get();
        return $record->result_array();
    }

    function countRecord($tbl_name, $where)
    {
        $this->db->select('*');
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get();
        return $record->num_rows();
    }

    function sumRecord($tbl_name, $field, $where, $like)
    {
        $this->db->select_sum($field, 'total');
        if ($where != "") {
            $this->db->where($where);
        }
        if ($like != "") {
            $this->db->like($like);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get();
        //echo $this->db->last_query();exit();
        $res = $record->row_array();
        return $res['total'] == '' ? '0' : $res['total'];
    }


    function removeImage($tbl_name, $where, $field, $path)
    {
        $this->db->select($field);
        if ($where != "") {
            $this->db->where($where);
        }
        $this->db->from($tbl_name);
        $record = $this->db->get()->result_array();

        foreach ($record as $row) {
            $prev_file_path = $path . $row[$field];
            if (!empty($row[$field])) {
                if (file_exists($prev_file_path)) {
                    unlink($prev_file_path);
                }
            }
        }
        return 1;
    }

    function removeTmpImage($path, $images)
    {
        if (is_array($images)) {
            foreach ($images as $image) {
                $prev_file_path = $path . $image;
                if (file_exists($prev_file_path)) {
                    unlink($prev_file_path);
                }
            }
        } else {
            $prev_file_path = $path . $images;
            if (file_exists($prev_file_path)) {
                unlink($prev_file_path);
            }
        }
    }
}
