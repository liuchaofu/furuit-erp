<?php


namespace app\api\controller;
/**
 * sg-小程序信息
 * User: Administrator
 * Date: 2022/3/28
 * Time: 13:24
 */

class Info extends Common
{
    /**
     * 认证信息
     *
     * @ApiTitle    (认证信息)
     * @ApiSummary  (checkInfo)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Info/checkInfo)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="crm_member_id", type="int", required=true, description=" crm_member_id")
     * @ApiParams (name="crm_shop_id", type="int", required=true, description=" crm_shop_id")
     * @ApiParams (name="shop_name", type="varchar", required=false, description="商铺名称")
     * @ApiParams (name="shop_image", type="varchar", required=false, description="门牌图片)
     * @ApiParams (name="name", type="varchar", required=false, description="真实姓名")
     * @ApiParams (name="phone", type="varchar", required=false, description="电话号码")
     * @ApiParams (name="id_card", type="varchar", required=false, description="身份证号")
     * @ApiParams (name="city", type="varchar", required=false, description="商户地址")
     * @ApiParams (name="house_num", type="varchar", required=false, description="具体门牌号")
     * @ApiReturn   {"code":1,"msg":"更新成功","time":"1648729285","data":1}
     */

    //认证信息
    public function checkInfo()
    {
        $data = input('post.');
//        halt($data);
        if (empty($data)) {
            $this->error('数据为空');
        }

        //传入数据
        if (!empty($data['shop_name'])) {
            $staffData['shop_name'] = $this->str_filter($data['shop_name']);
        }
        if (!empty($data['shop_image'])) {
            //门牌号照片
            $staffData['shop_image'] = $this->str_filter($data['shop_image']);
        }
        if (!empty($data['name'])) {
            $staffData['name'] = $this->str_filter($data['name']);
        }
        if (!empty($data['phone'])) {
            $staffData['phone'] = $this->str_filter($data['phone']);
        }
        if (!empty($data['id_card'])) {
            $staffData['id_card'] = $this->str_filter($data['id_card']);
        }
        if (!empty($data['city'])) {
            $staffData['city'] = $this->str_filter($data['city']);
        }
        if (!empty($data['house_num'])) {
            $staffData['house_num'] = $this->str_filter($data['house_num']);
        }

        if (!empty($data['member_id'])) {
            $staffData['crm_member_id'] = $this->str_filter($data['crm_member_id']);
        }

        if (!empty($data['shop_id'])) {
            $staffData['crm_shop_id'] = $this->str_filter($data['crm_shop_id']);
        }


        $res = \db('app_member_info')
            ->where('crm_member_id', $data['crm_member_id'])
            ->where('crm_shop_id', $data['crm_shop_id'])
            ->update($staffData);


        if ($res) {
            $this->success('更新成功', $res);
        }
        $this->error('信息有误');
    }


    /**
     * 获取店铺信息
     *
     * @ApiTitle    (获取店铺信息)
     * @ApiSummary  (getShop)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Info/getShop)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="shop_id", type="int", required=true, description="shop_id  测试shop_id 58")
     * @ApiReturn {
     * "code": 1,
     * "msg": "ok",
     * "time": "1648728592",
     * "data": {
     * "id": 18,
     * "member_id": null,
     * "typedata": null,
     * "shop_name": "芳芳的店铺",
     * "shop_image": "/uploads/20210716/f7f1629a23857c60f35988595caf4154.jpg",
     * "name": "菻篠曲",
     * "phone": "18683098987",
     * "id_card": "511528198301091622",
     * "city": "商贸城",
     * "address": null,
     * "house_num": "1幢-1-2-12633;1幢-1-2-12635",
     * "createtime": 1648728367,
     * "updatetime": 1648728367,
     * "catedata": "person",
     * "crm_member_id": 33,
     * "crm_shop_id": 58,
     * "state": "1"
     * }
     * }
     */


    public function getShop()
    {
        $shop_id = $this->request->request("shop_id");

        //判断id是几个 如果是2个取第一个 传来的是id 用英文逗号隔开不能用中文
        if (strpos($shop_id, ',') !== false) {
            $shop_id = $this->cut_str($shop_id, ',', 0);
            $msg = '如果两个id默认取第一个';
        }

        if (empty($shop_id)) {
            $this->error('参数有误');
        }
        $hint = isset($msg) ? $msg : '';

        $data = db('app_member_info')
            ->where('crm_shop_id', $shop_id)
//            ->field('id,crm_member_id,crm_shop_id,shop_name,shop_image,name,phone,id_card,city,house_num')
            ->find();

        if (empty($data)) {
//        调用商铺接口返回数据
            $url = "http://crm.ixinangou.com/api/shop/shopInfo";
            $param = ['shop_id' => $shop_id];

            $receive = $this->http($url, $param, 'POST');
            $info = json_decode($receive, true);
            if (!empty($info['data']['shopInfo'])) {
                //存数据库
                $memberDate['shop_name'] = $info['data']['shopInfo']['shop_name'];
                $memberDate['shop_image'] = $info['data']['shopInfo']['image'];
                $memberDate['name'] = $info['data']['shopInfo']['name'];
                $memberDate['phone'] = $info['data']['shopInfo']['phone'];
                $memberDate['id_card'] = $info['data']['shopInfo']['idcard'];
                $memberDate['city'] = $info['data']['shopInfo']['address'];
                $memberDate['house_num'] = $info['data']['shopInfo']['door'];
                $memberDate['createtime'] = time();
                $memberDate['updatetime'] = time();
                $memberDate['crm_member_id'] = $info['data']['shopInfo']['member_id'];
                $memberDate['crm_shop_id'] = $info['data']['shopInfo']['shop_id'];
                $memberDate['state'] = 1;
                $memberDate['checkdata'] = 'merchant';


                //
                $info_id = db('app_member_info')
                    ->insertGetId($memberDate);

                if ($info_id) {
                    $member_info = db('app_member_info')
                        ->where('id', $info_id)
                        ->find();

                    $member_info['msg'] = $hint;
                    $this->success('ok', $member_info);
                } else {
                    $this->error('添加失败');
                }
            } else {
                $this->error('没有该店铺');
            }
        } else {
            $data['msg']  =$hint;
            $this->success('ok', $data);
        }

    }


    /**
     * 注册协议
     *
     * @ApiTitle    (注册协议)
     * @ApiSummary  (agreement)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Info/agreement)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="tab", type="varchar", required=true, description=" zcxy")
     * @ApiReturn   {"code":1,"msg":"ok","time":"1648729285","data":1}
     */
    public function agreement()
    {
        $tab =$this->request->request('tab');
        $data =db('set_up')
            ->where('tab',$tab)
            ->find();

        if ($data) {
            $this->success('ok',$data);
        }else{
            $this->error('无数据');
        }

    }








}