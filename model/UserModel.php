<?php

class UserModel
{

    public function __construct()
    {
        
    }

    public function login($post){
        //$this->update();

        if($post && $post['username'] && $post['password']){
            header('Content-Type: application/json');
            global $branchDb;
            $db = $branchDb[0];

            $response = array(
                "error" => 0,
                "error_message" => '',
                "user"=> NULL
            );

            $select = "EmployeeID, EmployeeName, isApplication";

            $db->where ("UserName", $post['username']);
            $db->where ("Password", $post['password']);
            //$db->where ("isApplication",-1);
            $result = $db->getOne("tblemployee", $select);

            if($result){
                if($result['isApplication'] == -1){
                    $response['user'] = $result;
                    echo json_encode($response);
                }else{
                    $response['error'] = 1;
                    $response['error_message'] = 'ชื่อผู้ใช้งานนี้ไม่มีสิทธิ์ในการใช้งาน แอปพลิเคชัน โปรดติดต่อเจ้าหน้าที่ผู้ดูแลระบบ';
                    echo json_encode($response);
                }
                

            }else{
                $response['error_message'] = 'ไม่พบชื่อผู้ใช้งาน';
                $response['error'] = 1;
                echo json_encode($response);
            }

            //$result = $db->get('tblemployee', 10);
            
        }else{
            $response['error_message'] = 'ไม่พบชื่อผู้ใช้งาน';
            $response['error'] = 1;
            echo json_encode($response);
        }

        //$this->wh_log("Login");
    }

    public function update (){
        global $branchDb;
        $db = $branchDb[0];
        $data = Array (
            'isApplication' => -1
        );
        $db->where ('EmployeeID', '00000');
        if ($db->update ('tblemployee', $data))
            echo $db->count . ' records were updated';
        else
            echo 'update failed: ' . $db->getLastError();
    }

    public function getKepper($post){
        global $branchDb;
        $db = $branchDb[$post['branch']];
        $response = array(
            "error" => 0,
            "error_message" => '',
            "keeper"=> NULL
        );

        $db->where ("KeeperName", '%'.$post['name'].'%', 'like');
        $db->where ('IsDelete', 0);
        $keepers = $db->get('tblkeeper');
        $response['keeper'] = $keepers;
        echo json_encode($response);
    }

    public function statusUpdate($post){
        header('Content-Type: application/json');
        $response = array(
            "error" => 0,
            "error_message" => '',
            "success" => ''
        );

        $statusList = array(
            "0" => "ใบแจ้งหนี้",
            "1" => "จ่ายแล้ว"
        );

        $branchList = array(
            "0" => "สำนักงานใหญ่",
            "1" => "สาขาพัทยา",
            "2" => "สาขาโรงโป้ะ",
            "3" => "สาขาสำหรับทดสอบ"
        );

        $statusUpdate = array(
            "0" => "0",
            "1" => "-1"
        );


        if($post && $post['id']){
            $status = $post['status'];
            $branch = $post['branch'];
            $billnumber = $post['billnumber'];
            $keeper = $post['keeper'];
            $employee = $post['employee'];

            global $branchDb;
            $db = $branchDb[$branch];

            $data = Array (
                'IsPay' => $statusUpdate[$status],
                'BillApp' => $billnumber,
                'PrintDateApp' => date('Y-m-d'),
                'KeeperID' => ($statusUpdate[$status] == "-1") ? $keeper : "00000",
                'DatePay' => date('Y-m-d'),
                'EmployeeID' => $employee
            );
            $db->where ('InvoiceID', $post['id']);
            if ($db->update ('tblinvoice', $data)){
                $response['success'] = 'อับเดทสถานะของลูกค้า (ID) '.$post['id'].' '.$branchList[$branch].' เป็น '.$statusList[$status].' สำเร็จ';

                if($statusUpdate[$status] == "-1"){ //อัพเดท สถานะสมาชิก
                    $this->updateMemberStatus($branch, $post['id']);
                }
                
                $this->wh_log($response['success'].' KeeperID : '.$keeper.' EmployeeID : '.$employee);
                echo json_encode($response);
            }else{
                $response['error_message'] = 'ไม่สามารถอับเดทสถานะของลูกค้า (ID) '.$post['id'].' '.$branchList[$branch].' เป็น '.$statusList[$status];
                $response['error'] = 1;
                $this->wh_log($response['error_message']);
                echo json_encode($response);
            }

        }


    }

    public function updateMemberStatus($branch, $InvoiceID){
        
        global $branchDb;
        $db = $branchDb[$branch];

        //ดึงข้อมูลจาก tblinvoice, tblmember
        $select = "a.MemberID, b.MemberTypeID";
        $db->join("tblmember b", "a.MemberID=b.MemberID", "LEFT");
        $db->where ('InvoiceID', $InvoiceID);
        $response = $db->getOne('tblinvoice a', $select);
        $MemberID = $response['MemberID'];
        $MemberTypeID = $response['MemberTypeID'];
        

        //อัพเดท Member status
        $data = Array (
            'MemberStatusID' => '00001' 
        );
        $db->where ('MemberID', $MemberID);
        $db->update ('tblmember', $data);


        //ประวัติการเปลี่ยนแปลงสถานะ
        $dataInsert = Array ("MemberID" => $MemberID,
                    "KeeperID" => '99994',
                    "MemberStatusID" => '00001',
                    "MemberTypeID" => $MemberTypeID,
                    "SuspendWhy" => "ชำระค่าบริการ",
                    "ApplyDate" => date('Y-m-d'),
                    "LastPay" => date('Y-m-d'),
                    "InvoiceID" => $InvoiceID
        );
        $db->insert ('tblsuspend', $dataInsert);
       
    }

 
    private function wh_log($log_msg) {
       	global $branchDb;
        $db = $branchDb[0];
        $data = Array ("message" => $log_msg,
                    "date_time" => date('Y-m-d H:i:s'),
                    "del" => 0
        );
        $id = $db->insert ('app_logs', $data);
        if($id){
            return $id;
        }else{
            return false;
        }
    }

    public function get_logs($date){

        header('Content-Type: application/json');
        global $branchDb;
        $db = $branchDb[0];

        if($date){
          $db->where ( 'DATE(date_time)', $date);  
        }
        
        $results = $db->get("app_logs");

        echo json_encode($results);

    }

}