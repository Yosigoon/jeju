<?
class Admin extends MY_Controller {
    
    /**
     * 탈퇴처리
     */
    public function withdrawalUser() {
        //$query = $this->db->query("delete from d_user where nickname = '요시'");

        //오픈 시 아래 주석 해제 위에 $query 삭제
        $query = $this->db->query("delete from d_user where time_last_login < date_sub(now(), interval 2 year)");
        if($query){
            $msg = "탈퇴처리 되었습니다.";
        }else{
            $msg = "오류가 발생하였습니다.";
        }
        $data = array(
            'redirect' => '/admin/user'
        , 'message' => $msg
        );
        $result = array('row' => $data);
        echo json_encode($result);
    }

    /**
     * 탈퇴안내 메일발송
     */
    public function withdrawalSendEmail() {
        $q = $this->db->select('email, name')
            ->where('userid', 'yosigoon@gmail.com')
            ->get($this->auth->table);
        //오픈 시 아래 주석 해제 위에 $q 삭제
        //$q = $this->db->query("select email, name from d_user where time_last_login < date_sub(now(), interval 2 year)");
        $successCnt = 0;
        $failCnt = 0;
        foreach ($q->result() as $row) {

            $this->load->helper('email');
            //메일문구 확인필요
            $email_content = htmlspecialchars($row->name).'님 환영합니다. '.htmlspecialchars($row->name).'님은 플레이스 캠프 제주의 PLAYER입니다.';
            $err = email('탈퇴안내. 플레이스 캠프 제주입니다.', '이용해주셔서 감사합니다.', $email_content, $row->email, $row->name);

            if($err){
                $failCnt = $failCnt + 1;
            }else{
                $successCnt = $successCnt + 1;
            }
        }

        $msg = "성공".$successCnt."건 // 실패".$failCnt."건";

        $data = array(
            'redirect' => '/admin/user'
        , 'message' => $msg
        );
        $result = array('row' => $data);
        echo json_encode($result);
    }
}
