<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\notification\core;

class TxlApi {

    private $access_token;

    public function __construct() {
        $this->access_token = new AccessToken("txl");
    }

    /**
     * 在请求的企业微信接口后面自动附加token信息   
     */
    private function appendToken($url) {
        $token = $this->access_token->getAccessToken();

        if (strrpos($url, "?", 0) > -1) {
            return $url . "&access_token=" . $token;
        } else {
            return $url . "?access_token=" . $token;
        }
    }

    /**
     * 根据部门ID来查询下属的所有子部门    
     * @param  [Number] $id 部门ID
     */
    public function getDepartmentsById($id) {
        if ($id > 0) {
            return Helper::http_get($this->appendToken("https://qyapi.weixin.qq.com/cgi-bin/department/list?id=$id"))["content"];
        } else {
            return '{"errcode":-1,"errmsg":"departmentId is invalid"}';
        }
    }

    /**
     * 根据用户查询用户信息
     * @param  [Number] $id 查询的目标用户ID
     */
    public function queryUserById($id = "") {
        if ($id) {
            return Helper::http_get($this->appendToken("https://qyapi.weixin.qq.com/cgi-bin/user/get?userid=$id"))["content"];
        } else {
            return '{"errcode":-1,"errmsg":"userId is invalid"}';
        }
    }

    /**
     * 根据部门ID查询用户信息
     * @param  [Number]  $depId    查询的部门ID
     * @param  [integer] $fetchChild 是否遍历子部门
     * @param  [boolean] $simple   是否只查询用户的基本信息
     */
    public function queryUsersByDepartmentId($depId = 1, $fetchChild = 1, $simple = 1) {
        if ($depId > 0) {
            $interface = $simple == 1 ? "simplelist" : "list";

            return Helper::http_get($this->appendToken("https://qyapi.weixin.qq.com/cgi-bin/user/$interface?department_id=$depId&fetch_child=1"))["content"];
        } else {
            return '{"errcode":-1,"errmsg":"departmentId is invalid"}';
        }
    }

}
