<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

#doc
#	米家控制器
#/doc

class Mijia extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->library('XiaomiMijia');// 加载米家api

    }

    public function index()
    {
        $data['title'] = '智能家居首页';
        $data['action'] = 'mijia';
        $data['command_list'] = $this->xamsg_m->get_latest_msg(20, 2);
        $data['msg_list'] = $this->xamsg_m->get_latest_msg(20, 1);
        $data['action']='mijia';

        $this->load->view('mijia_home', $data);

    }

    /**
     * msg 填写消息
     */
    public function msg()
    {
        $data['title'] = '智能家居-发送消息';
        $data['action'] = 'mijia';
        $keyword=$this->input->get('sign',true);
        $data['msg'] = $keyword;
        $data['csrf_name'] = $this->security->get_csrf_token_name();
        $data['csrf_token'] = $this->security->get_csrf_hash();
        $this->load->view('mijia_msg', $data);
    }


    /**
     * 发送指令页面
     */
    public function zhiling()
    {
        $data['title'] = '智能家居-发送指令';
        $data['action'] = 'mijia';
        $keyword=$this->input->get('sign',true);
        $data['msg'] = $keyword;
        $data['csrf_name'] = $this->security->get_csrf_token_name();
        $data['csrf_token'] = $this->security->get_csrf_hash();
        $this->load->view('mijia_command', $data);

    }


    /**
     * 接受输入的消息,并发送
     */
    public function sendMsg()
    {
        $data['title'] = '智能家居-发送文字消息';
        $data['action'] = 'mijia';
        $data['msg'] = '发送成功';

        $msg=$this->input->post('sign',true);
        $uri = "/miotspec/action";
        $json =  $this->xiaomimijia->post_request($uri,$msg,1);
        $data['msg'] = $json['message'];
//        $code = $json['code'];
        // add log
        $this->xamsg_m->insert_msg($msg, 1);
        $this->load->view('mijia_ok', $data);

    }


    /**
     * 发送指令给小爱同学
     */

    public function sendzhiling()
    {

        $data['title'] = '智能家居-发送指令';
        $data['action'] = 'mijia';
        $data['msg'] = '发送成功';

        $msg=$this->input->post('sign',true);
        $uri = "/miotspec/action";
        $json =  $this->xiaomimijia->post_request($uri,$msg,2);
        // add log
        $this->xamsg_m->insert_msg($msg, 2);

        $this->load->view('mijia_ok', $data);

    }


    /**
     * 立即执行 不需要编辑内容
     */
    public function exenow(){
        $data['title'] = '智能家居-发送指令';
        $data['action'] = 'mijia';
        $data['msg'] = '发送成功';

        $uri = "/miotspec/action";
        $msg=$this->input->get('sign',true);
//        $msg = urldecode($keyword);
        $json =  $this->xiaomimijia->post_request($uri,$msg,2);

//        $this->xamsg_m->insert_msg($msg, 2);

        $this->load->view('mijia_ok', $data);
    }




}
