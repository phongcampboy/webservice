<?php



class CustomerModel
{


    public function __construct()
    {
    }

    public function search($post)
    {

        if ($post && $post['field'] && $post['value']) {

            global $branchDb;

            $select = "a.MemberID, a.FirstName, a.LastName, a.StreetNo, a.Moo, a.Mooban, a.Soi, a.Street, a.Kwaeng,a.Note,";
            $select .= " a.Kaet, a.Province, a.ZipCode, a.Tel1, a.DescriptionRate, a.Fax1, b.InvoiceID, b.InvoiceNo, b.LastPay, b.NextPay, b.Total, b.BillingCode, b.IsPay";

            $db = $branchDb[$post['branch']];
            $field = "a." . $post['field'];
            $value = $post['value'];

            if ($field == 'a.fullname') {
                $field = "CONCAT( a.FirstName, ' ', a.LastName )";
            }

            $db->join("tblinvoice b", "a.MemberID=b.MemberID", "LEFT");
            $db->where($field, '%' . $value . '%', 'like');
            $db->where("b.Total", null, 'IS NOT');
            $db->where("b.IsWork", 0);
            $db->where('a.MemberStatusID', array('00001', '00007'), 'IN');
            $db->orderBy("b.IsPay", "desc");
            $db->orderBy("b.LastPay", "desc");
            //$db->groupBy("a.MemberID");
            $result = $db->get("tblmember a", 24, $select);

            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            echo "[]";
        }

    }

    public function getBy($branch, $member_id)
    {

        global $branchDb;

        $select = "a.MemberID, a.FirstName, a.LastName, a.StreetNo, a.Moo, a.Mooban, a.Soi, a.Street, a.Kwaeng,";
        $select .= " a.Kaet, a.Province, a.ZipCode, a.Tel1, a.DescriptionRate, a.Fax1, b.InvoiceNo, b.LastPay, b.NextPay, b.Total, b.BillingCode";

        $db = $branchDb[$branch];

        $db->join("tblinvoice b", "a.MemberID=b.MemberID", "LEFT");
        $db->where('a.MemberID', $member_id);
        $result = $db->getOne("tblmember a", $select);
        return $result;
    }


    public function promotions($branch){
        global $branchDb;
        $db = $branchDb[$branch];
        $result = $db->getOne("tblpromotion");
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function receiptNumber($branch){
         
        $output = array(
            'invoice' => $this->getBillNumber($branch, 'A'),
            'receipt' => $this->getBillNumber($branch, 'B')
        );
        header('Content-Type: application/json');
        echo json_encode($output, JSON_PRETTY_PRINT);
    }

    private function getBillNumber($branch, $type){
        global $branchDb;
        $db = $branchDb[$branch];
        
        $select = "RIGHT(BillApp, 4) as BillNumber";
        $db->where ('BillApp', $type.'%', 'like');
        $db->where ('PrintDateApp', date('Y-m-d'));
        $db->orderBy("BillApp", "desc");
        $result = $db->getOne("tblinvoice", $select);
        
        $number = $result['BillNumber'] ? str_replace(array("A","B"), "", $result['BillNumber']) + 1 : 1; 
    
        return $type.substr("00000{$number}", -4);
    
     }

}
