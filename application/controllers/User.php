<?
class User extends MY_Controller {

    //변동가격 업데이트
    public function update_change_price() {
        $cart_uids = $this->input->post('cart_uids');
        $this->load->model('shop_cart');
        if ($this->input->method() == 'post') {
            $this->shop_cart->update_change_price($cart_uids);

            if(empty($this->auth->userinfo->uid)){
                redirect('/user/checkout_nonmember');
            }else{
                redirect('/user/checkout');
            }

        }
    }

    //회원검증
    public function user_check(){
        $email = $this->input->post('email');
        $email = '\''.$email.'\'';
        $cmd = "select * from d_user where email = " .$email;
        $row = $this->db->query($cmd)->result();

        $RES = array(
            "cnt" => 0
        );

        if(!empty($row)) {
            $RES["cnt"] = 1;
        }
        echo json_encode($RES);
    }

    //회원삭제
    public function user_delete(){
        $email = $this->input->post('email');
        $email = '\''.$email.'\'';
        $cmd = "delete from d_user where email = " .$email;
        $query = $this->db->query($cmd);

        if($query){
            $msg = "삭제되었습니다.";
        }else{
            $msg = "오류가 발생하였습니다.";
        }

        $RES = array(
            "msg" => $msg
        );
        echo json_encode($RES);
    }

    //예약검증
    public function order_check(){
        $phone = $this->input->post('phone');
        $phone = '\''.$phone.'\'';

        $cmd = "select a.* from d_shop_order a, d_user b where a.status = 'pending_order' and a.uid_user = b.uid and b.phone = " .$phone;
        $row = $this->db->query($cmd)->result();

        $RES = array(
            "cnt" => 0
        );

        if(!empty($row)) {
            $RES["cnt"] = 1;
        }
        echo json_encode($RES);
    }

    //예약삭제
    public function order_delete(){
        $phone = $this->input->post('phone');
        $phone = '\''.$phone.'\'';

        $order = "delete from d_shop_cart where uid_order in (select uid from d_shop_order where status ='pending_order' and uid_user in (select uid from d_user where phone=" .$phone. "))";
        $this->db->query($order);

        $tmp = "delete from d_shop_cart where uid_order_tmp in (select uid from d_shop_order where status ='pending_order' and uid_user in (select uid from d_user where phone=" .$phone. "))";
        $this->db->query($tmp);

        $q = "delete from d_shop_order where status ='pending_order' and uid_user in (select uid from d_user where phone=" .$phone. ")";
        $this->db->query($q);

        $RES = array(
            "msg" => "삭제되었습니다."
        );
        echo json_encode($RES);
    }
}
