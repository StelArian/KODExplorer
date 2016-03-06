<?php 
/*
* @link http://www.kalcaddle.com/
* @author warlee | e-mail:kalcaddle@qq.com
* @copyright warlee 2014.(Shanghai)Co.,Ltd
* @license http://kalcaddle.com/tools/licenses/license.txt
* @Secured by Ben Khlifa Fahmi
*/

class app extends Controller{
    function __construct()    {
        parent::__construct();
        $this->sql=new fileCache(USER_SYSTEM.'apps.php');
    }

    /**
     * Home users Display
     */
    public function index() {
        $this->display(TEMPLATE.'app/index.php');
    }

    public function init_app($user_info){
        $list = $this->sql->get();
        $new_user_app = $this->config['setting_system']['new_user_app'];
        $default = explode(',',$new_user_app);
        $info = array();
        foreach ($default as $key) {
            $info[$key] = $list[$key];
        }
        $desktop = USER_PATH.$user_info['name'].'/home/desktop/';
        mk_dir($desktop);
        foreach ($info as $key => $data) {
            if (!is_array($data)) {
                continue;
            }
            $path = iconv_system($desktop.$key.'.oexe');
            unset($data['name']);
            unset($data['desc']);
            unset($data['group']);
            file_put_contents($path, json_encode($data));
        }
        $user_info['status'] = 1;
        $member = new fileCache(USER_SYSTEM.'member.php');
        $member->update($user_info['name'],$user_info);
    }

    /**
     * App user to add, edit
     */
    public function user_app() {
        $path = _DIR($this->in['path']);
        if (isset($this->in['action']) && $this->in['action'] == 'add'){
            $path .= '.oexe';
        }
        
        if (!checkExt($path)) {
            show_json($this->L['error']);exit;
        }

        $data = json_decode(rawurldecode($this->in['data']),true);
        unset($data['name']);unset($data['desc']);unset($data['group']);
        $res  = file_put_contents($path, json_encode($data));
        show_json($this->L['success']);
    }

    /**
     * Get List
     */
    public function get() {
        $list = array();
        if (!isset($this->in['group']) || $this->in['group']=='all') {
            $list = $this->sql->get();
        }else{
            $list = $this->sql->get('group','',$this->in['group']);
        }
        $list = array_reverse($list);
        show_json($list);
    }

    /**
     * Add to
     */
    public function add() {  
         if ($_SERVER['HTTP_REFERER'] != $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
       $res=$this->sql->add(rawurldecode($this->in['name']),$this->_init());
        if($res) show_json($this->L['success']);
        show_json($this->L['error_repeat'],false);}}else{
    header('Location: 403.php');
}
    }

    /**
     * edit
     */
    public function edit() {
        //Find a record, modified to the array
         if ($_SERVER['HTTP_REFERER'] != $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
if($this->sql->replace_update(
            rawurldecode($this->in['old_name']),
            rawurldecode($this->in['name']),$this->_init())){
            show_json($this->L['success']);
        }
        show_json($this->L['error_repeat'],false);}}else{
header('Location: 403.php');}
    }
    /**
     * delete
     */
    public function del() {
         if ($_SERVER['HTTP_REFERER'] != $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
       if($this->sql->delete(rawurldecode($this->in['name']))){
            show_json($this->L['success']);
        }
        show_json($this->L['error'],false);}}else{
    header('Location: 403.php');
    }
    }

    public function get_url_title(){
        $html = curl_get_contents($this->in['url']);
        $result = match($html,"<title>(.*)<\/title>");
        if (strlen($result)>50) {
            $result = mb_substr($result,0,50,'utf-8');
        }
        if (!$result || strlen($result) == 0) {
            $result = $this->in['url'];
            $result = str_replace(array('http://','&','/'),array('','@','-'), $result);
        }
        show_json($result);
    }

    private function _init(){
        return  json_decode(rawurldecode($this->in['data']));
    }
}
