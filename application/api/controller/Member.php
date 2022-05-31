<?php


namespace app\api\controller;


use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;

/**
 * sg-小程序用户
 * User: Administrator
 * Date: 2022/3/28
 * Time: 13:24
 */
class Member extends Common
{
    private $appId = "";
    private $appSecret = "";

    /**
     * 扫码分享进来的用户
     *
     * @ApiTitle    (扫码分享进来的用户)
     * @ApiSummary  (speread)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/speread)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="spread_id", type="int", required=true, description="spread_id 通过二维码传过来的id sence")
     * @ApiParams (name="member_id", type="int", required=false, description="member_id 当前用户的member_id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'修改成功'
    'data':array(
    )})
     */
    public function speread()
    {

        $spread_id = $this->request->request("spread_id");//传过来的id 老用户one
        $member_id = $this->request->request("member_id");//当前用户id 新用户 wo

        //当前用户已认证不做操作
//        $data =\db('')


        //不能自己扫自己的码
        if ($spread_id == $member_id) {
            $this->error('不能扫自己的码');
        }

        //不能互相扫码  新用户老用户互换扫
        $both = \db('extension')
            ->where('one_member_id', $member_id)
            ->select();

        foreach ($both as $k => $value) {
            if ($value['two_member_id'] == $spread_id) {
                $this->error('不能互扫码');
            }
        }

        //判断是不是扫了同一个码
        $check = \db('extension')
            ->where('two_member_id', $member_id)
            ->select();

        if (!empty($check)) {
            foreach ($check as $k => $value) {
                if ($value['one_member_id'] == $spread_id) {
                    $this->error('不能重复扫码');
                }
            }

        }

        //注册成功后  把数据加入推广用户表
        if ($spread_id) {
            //通过码进来的老用户（分享者）
            $extension['one_member_id'] = $spread_id;
            //新用户
            $extension['two_member_id'] = $member_id;
            $extension['createtime'] = time();
            $extension['updatetime'] = time();


            //加入推广表
            $res = \db('extension')
                ->insertGetId($extension);
            if ($res) {

                Db::startTrans();
                try {
                    //增加积分和券  增加邀请人的积分 增加被邀请人的券
                    //查出分享者积分
                    $integal = \db('app_member')
                        ->where('id', $spread_id)
                        ->find();

                    //每次分享增加的积分 读取配置得积分
                    $tab = 'fx';
                    $set = \db('set_up')
                        ->where('tab', $tab)
                        ->field('context')
                        ->find();
                    $add = $set['context'];

                    //如果邀请了5个额外获得50积分
                    $jl_count = \db('extension')
                        ->where('one_member_id', $spread_id)
                        ->count();
                    if ($jl_count == 5) {
                        $add = $add + 50;
                    }

                    //初始积分
                    $total_integral = $integal['integral_total'];
                    $now_integral = $integal['integral'];

                    $add_info['typedata'] = 'invite';
                    $add_info['member_id'] = $spread_id;
                    $add_info['start_integral'] = $now_integral;

                    $add_info['add_integral'] = $add;//暂时定10
                    $add_info['end_integral'] = $now_integral + $add;
                    $add_info['remarks'] = '邀请';
                    $time = time();
                    $add_info['createtime'] = $time;
                    $add_info['updatetime'] = $time;
                    $add_info['extension_id'] = $res;

                    $add_integal = \db('app_integral_log')
                        ->insert($add_info);

                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($add_integal) {

                    Db::startTrans();
                    try {
                        //修改用户积分
                        //每次分享增加的积分 读取配置得积分
                        $tab = 'fx';
                        $set = \db('set_up')
                            ->where('tab', $tab)
                            ->field('context')
                            ->find();
                        $add = $set['context'];

                        //如果邀请了5个额外获得50积分
                        $jl_count = \db('extension')
                            ->where('one_member_id', $spread_id)
                            ->count();
                        if ($jl_count == 5) {
                            $add = $add + 50;
                        }


                        $edit_info['integral_total'] = $total_integral + $add;
                        $edit_info['integral'] = $now_integral + $add;
                        $edit_integral = \db('app_member')
                            ->where('id', $spread_id)
                            ->update($edit_info);

                        Db::commit();
                    } catch (ValidateException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }

                    if ($edit_integral) {
                        //成功
                        $this->success('修改成功');

                    }
                    $this->error('修改失败 mm');

                }
                $this->error('修改失败 me');


            } else {
                $this->error('增加失败 log');
            }


        } else {
            $this->error('插入失败 ex');
        }


    }


    /**
     * 获取用户相关id
     *
     * @ApiTitle    (获取用户相关id)
     * @ApiSummary  (getIds)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/getIds)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="code", type="varchar", required=true, description="code")
     * @ApiParams (name="id", type="int", required=false, description="id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    )})
     */

    public function getIds()
    {
        $code = $this->request->request("code");

        if (empty($code)) {
            return $this->error('没权限');
        }

        // 得到openid,session_key 可能有unionid
        $res = $this->getSessionkey($code);


        if (isset($res)) {
            $res['errcode'] = isset($res['errcode']) ? $res['errcode'] : '';
            if ($res['errcode'] == 40163) {
                $this->error('code被使用');
            }
        }

        $openid = $res['openid'];
        $session_key = $res['session_key'];
        $unionid = isset($res['unionid']) ? $res['unionid'] : '';


        $data = \db('app_member')
            ->where('openID', $openid)
            ->field('id as member_id,typedata,nickname,realname,head_image,phone,email,integral,integral_total,code')
            ->find();


        if (empty($data)) {
            Db::startTrans();
            try {
                $time = time();
                // 新用户进来，创建用户
                $memberData = array(
                    'openID' => $openid,
                    'unionID' => $unionid,
                    'createtime' => $time,
                    'updatetime' => $time,
                    'integral_total' => 0, //默认积分
                    'integral' => 0, //默认积分
                    'state' => 1,//当前状态:0=未认证,1=正常,2=禁用,3=异常
                );
                //调用iv什么的值 去换基本信息存入数据库

                //id 是当前新增的新用户id
                $id = \db('app_member')
                    ->insertGetId($memberData);

                //把默认的10积分加入积分记录表中

                $person = \db('app_member')
                    ->where('id', $id)
                    ->find();

                //新人10积分记录
                $log['member_id'] = $id;
                $log['start_integral'] = $person['integral_total'];
                $log['add_integral'] = 10;
                $log['end_integral'] = $log['start_integral'] + 10;
                $log['remarks'] = '新用户10积分';
                $log['createtime'] = time();
                $log['typedata'] = 'new';

                $info_log = \db('app_integral_log')
                    ->insertGetId($log);

                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            if ($info_log) {
                Db::startTrans();
                try {

                    //修改积分
                    $change = array(
                        'integral_total' => 10, //默认发新人10积分
                        'integral' => 10, //默认积分
                    );
                    $change_integral = \db('app_member')
                        ->where('id', $id)
                        ->update($change);

                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                /*  if ($change_integral) {

                      //不是分享进来的  给他发券
                      //读取配置  如果配置标题为发送就发
                      $setting = \db('set_up')
                          ->where('id', 4)
                          ->find();
                      if ($setting['title'] == '发送') {
                          //增加新用户的券  $id  coupond  操作几张表？
                          $coupond['coupon_id'] = 2;//优惠券
                          $coupond['member_id'] = $id; //新用户id
                          //核销店铺新员工默认空
                          $coupond['shop_id'] = '';
                          //核销时间
                          $coupond['usetime'] = '';
                          $coupond['gettime'] = time(); //领用时间
                          $coupond['number'] = 1; //领用数量

                          //优惠券码
                          $coupond['code'] = $this->couponCode();
                          $coupond['status'] = '已领取';
                          //先加入优惠券记录表
                          $res = \db('coupond')
                              ->insertGetId($coupond);


                          if (empty($res)) {
                              $this->error('发券失败');
                          }

                      }
                  } else {
                      $this->error('修改失败');
                  }*/


            } else {
                $this->error('积分纪律增加失败');
            }


            //查询该用户下面有没有优惠券  新用户必然有一张新人优惠券  如果配置没有则没有
            $coupond = \db('coupond')
                ->where('member_id', $id)
                ->count();

            //查询该用户的拼团 的数量
            $group = \db('groupc')
                ->where('member_id', $id)
                ->count();

            //查询该数据返回
            $result = \db('app_member')
                ->where('id', $id)
                ->field('id as member_id,nickname,realname,head_image,code,integral,birthdaydate,state,createtime')
                ->find();
            $result['session_key'] = $session_key;
            //返回优惠券 数量
            $result['coupond'] = $coupond;
            //返回 拼团数量
            $result['group'] = $group;
            $this->success('ok', $result);


        } else {
            //判断状态是不是对的
//            $check = \db('app_member')
//                ->where('openID', $openid)
//                ->field('id,,nickname,realname,status')
//                ->find();
            //状态正常
//            if ($check['status'] == 1) {

            //查询有无优惠券
            $coupond = \db('coupond')
                ->where('member_id', $data['member_id'])
                ->count();

            $data['coupond_number'] = $coupond;  //查询有无优惠券 返回优惠券数量

            //查询该用户的拼团 的数量
            $group = \db('groupc')
                ->where('member_id', $data['member_id'])
                ->count();

            //有数据  返回数据和session_key
            $data['session_key'] = $session_key;
            $data['group'] = $group;
            $this->success('ok', $data);
//            } else {
//                $this->error('您的用户状态有问题，请联系客服');
//            }


        }
    }

    /**
     * 获取用户基本信息
     *
     * @ApiTitle    (获取用户相关基本信息)
     * @ApiSummary  (getNumber)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/getNumber)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="encryptedData", type="varchar", required=true, description="encryptedData")
     * @ApiParams (name="iv", type="varchar", required=true, description="iv")
     * @ApiParams (name="session_key", type="varchar", required=true, description="session_key")
     * @ApiParams (name="coupond_number", type="int", required=true, description="优惠券")
     * @ApiParams (name="group", type="int", required=true, description="拼团")
     * @ApiParams (name="id", type="int", required=true, description="用户的id 在getids返回的id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    )})
     */

    //手机 因为没认证 只能拿到部分基本信息 无法拿到手机号  拿到后再更新用户基本信息表
    public function getNumber()
    {
        $encryptedData = $this->request->request('encryptedData');
        $iv = $this->request->request('iv');
        $session_key = $this->request->request('session_key');
        $id = $this->request->request('id');
        $coupond_number = $this->request->request('coupond_number');
        $group = $this->request->request('group');

        //如果+被转意转回去
        $encryptedData = $this->define_str_replace($encryptedData);
        $iv = $this->define_str_replace($iv);
        $session_key = $this->define_str_replace($session_key);

        $data = '';

        $pc = new \app\api\library\Wxbizdatacrypt($this->appId, $session_key);

        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        //解密后的
        if ($errCode == 0) {
            $data = json_decode($data);
        }

        $info = $this->object_array($data);

        //存数据
        $detail['nickname'] = $info['nickName'];
        $detail['head_image'] = $info['avatarUrl'];

        //如果二次请求数据库里面有这2个字段直接返回
        $data = \db('app_member')
            ->where('id', $id)
            ->field('nickname')
            ->find();

        if (empty($data['nickname'])) {

            $res = \db('app_member')
                ->where('id', $id)
                ->update($detail);

            if ($res) {
                $member = \db('app_member')
                    ->where('id', $id)
                    ->field('id as member_id,nickname,realname,head_image,code,integral,birthdaydate,state,createtime')
                    ->find();

                $member['group'] = $group; //拼团
                $member['coupond_number'] = $coupond_number; //优惠券

                //判断 该用户有无提交认证申请 $id
                $member_info = \db('app_member_info')
                    ->where('member_id', $id)
                    ->find();

                if (empty($member_info)) {
                    $member['member_info'] = '未认证';
                } else {
                    $member['member_info'] = '已认证';
                }


                $this->success('ok', $member);

            }
            $this->error('修改失败');
        } else {
            $member = \db('app_member')
                ->where('id', $id)
                ->field('id as member_id,nickname,realname,head_image,code,integral,birthdaydate,state,createtime')
                ->find();

            $member['group'] = $group; //拼团
            $member['coupond_number'] = $coupond_number; //优惠券

            //判断 该用户有无提交认证申请 $id
            $member_info = \db('app_member_info')
                ->where('member_id', $id)
                ->find();

            if (empty($member_info)) {

                $member['member_info'] = '未认证';
            } else {
                $member['member_info'] = '已认证';
            }


            $this->success('ok', $member);

        }


    }

    /**
     * 上传图片
     *
     * @ApiTitle    (上传图片)
     * @ApiSummary  (upHeaderImg)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Member/upHeaderImg)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="head_image", type="file", required=true, description="head_image")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    )})
     */
    public function upHeaderImg()
    {
        $file = request()->file('head_image');
        $path = 'head_image';
        if (!empty($file)) {
            // 移动到框架应用根目录/public/uploads/ 目录下
            $filePath = ROOT_PATH . 'public' . DS . 'uploads' . DS . $path;
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $info = $file->validate(['size' => 4194304, 'ext' => 'jpg,png,gif,jpeg'])->move($filePath);
            if ($info) {
                $savename = $info->getSaveName();
                $img_url = $path . DS . $savename;
                $img = 'uploads/' . $img_url;
                $data = ['url' => $img];

                return $this->success('上传成功', $data);
            } else {
                // 上传失败
                return $this->error('上传失败,请上传不超过4M的图片');
            }
        } else {
            return $this->error('图片不存在');
        }
    }

    /**
     * 设置
     *
     * @ApiTitle    (设置)
     * @ApiSummary  (setting)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Member/setting)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="id", type="int", required=true, description="id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    )})
     */
    public function setting()
    {
        $data = input('post.');
        $id = $data['id'];

        $data = \db('app_member')
            ->where('id', $id)
            ->field('head_image,nickname,phone,birthdaydate')
            ->find();
        if (empty($data)) {
            $this->error('没有该权限');
        }
        $this->success('ok', $data);

    }

    /**
     * 更改资料
     *
     * @ApiTitle    (更改资料)
     * @ApiSummary  (changeInfo)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Member/changeInfo)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="nickname", type="varchar", required=false, description="昵称")
     * @ApiParams (name="id", type="int", required=true, description="id")
     * @ApiParams (name="head_image", type="varchar", required=false, description="头像")
     * @ApiParams (name="birthdaydate", type="int", required=false, description="生日")
     * @ApiParams (name="phone", type="varchar", required=false, description="电话")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    )})
     */
    public function changeInfo()
    {
        //头像 姓名 生日
        $data = input('post.');
        $id = $data['member_id'];
        if (empty($id)) {
            $this->error('参数错误');
        }
        if (!empty($data['nickname'])) {
            $staffData['nickname'] = $this->str_filter($data['nickname']);
        }
        if (!empty($data['head_image'])) {
            $staffData['head_image'] = $this->str_filter($data['head_image']);
        }
        if (!empty($data['birthdaydate'])) {
            $staffData['birthdaydate'] = $this->str_filter($data['birthdaydate']);
        }
        if (!empty($data['phone'])) {
            $staffData['phone'] = $this->str_filter($data['phone']);
        }


        $res = \db('app_member')
            ->where('id', $id)
            ->update($staffData);
        if ($res) {
            $this->success('ok更新成功');
        }
        $this->error('更改失败');
    }

    /**
     * 认证信息
     *
     * @ApiTitle    (认证信息)
     * @ApiSummary  (checkInfo)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Member/checkInfo)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" member_id")
     * @ApiParams (name="shop_name", type="varchar", required=false, description="商铺名称")
     * @ApiParams (name="typedata", type="varchar", required=false, description="类型:person=个体,company=公司")
     * @ApiParams (name="shop_image", type="varchar", required=false, description="门牌图片)
     * @ApiParams (name="name", type="varchar", required=false, description="真实姓名")
     * @ApiParams (name="phone", type="varchar", required=false, description="电话号码")
     * @ApiParams (name="id_card", type="varchar", required=false, description="身份证号")
     * @ApiParams (name="city", type="varchar", required=false, description="商户地址")
     * @ApiParams (name="house_num", type="varchar", required=false, description="具体门牌号")
     * @ApiReturn   {"code":1,"msg":"更新成功","time":"1648729285","data":1}
     */

    public function checkInfo()
    {
        $data = input('post.');

        if (empty($data)) {
            $this->error('数据为空');
        }

        //传入数据
        if (!empty($data['shop_name'])) {
            $staffData['shop_name'] = $this->str_filter($data['shop_name']);
        }
        if (!empty($data['typedata'])) {
            if ($data['typedata'] == "水果店主") {
                $staffData['typedata'] = 0;
            } elseif ($data['typedata'] == "水果摊贩") {
                $staffData['typedata'] = 1;
            } elseif ($data['typedata'] == "水果游商") {
                $staffData['typedata'] = 2;
            } elseif ($data['typedata'] == "超市/配送") {
                $staffData['typedata'] = 3;
            } elseif ($data['typedata'] == "消费者") {
                $staffData['typedata'] = 4;
            }
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
        $phone = isset($staffData['phone']) ? $staffData['phone'] : '';
        $phoneCount = \db("app_member_info")->where("phone", $phone)->find();
        if (empty($phoneCount)) {
            $staffData['updatetime'] = time();
//        $member_id =isset($data['member_id']) ?$data['member_id'] :'';
//        if($member_id){
//            $res = \db('app_member_info')
//                ->where('member_id',$member_id)
//                ->update($staffData);
//
//            if ($res) {
//                $this->success('ok');
//            }
//            $this->error('修改失败');
//        }

            $staffData['createtime'] = time();
            $staffData['member_id'] = $data['member_id'];
            $staffData['state'] = 1;
            $staffData['checkdata'] = 'channel';
            $res = \db('app_member_info')->insertGetId($staffData);
            if ($res) {
                //更改状态
                $this->success('更新成功', $res);
            }
        } else {
            $this->error('该电话号码已注册！');
        }
        $this->error('信息有误');
    }

    /**
     * 邀请记录
     *
     * @ApiTitle    (用户邀请记录)
     * @ApiSummary  (showExtension)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Member/showExtension)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" member_id")
     * @ApiReturn   {"code":1,"msg":"ok","time":"1648806890","data":[{"id":1,"one_member_id":1,"two__member_id":2,"createtime":1648805957,"updatetime":null,"nickname":"测试","realname":"测试","head_image":"","end_integral":15,"time":"2022 -04 -01"},{"id":2,"one_member_id":1,"two__member_id":3,"createtime":1648805957,"updatetime":null,"nickname":"测试","realname":"测试","head_image":"","end_integral":115,"time":"2022 -04 -01"}]}
     */

    public function showExtension()
    {
        $member_id = $this->request->request('member_id');
        if (empty($member_id)) {
            $this->error('参数不合法');
        }
        //查询扫过自己的用户
        $info = \db('extension')
            ->alias('e')
            ->join('app_member m', 'e.two_member_id = m.id')
            ->join('app_integral_log l', 'e.id = l.extension_id')
            ->where('e.one_member_id', $member_id)
            ->field('e.*,m.nickname,m.realname,m.head_image,l.add_integral')
            ->select();

        if (empty($info)) {
            $this->success('无数据');
        }

        foreach ($info as $k => $v) {
            $info[$k]['time'] = date("Y -m -d", $v['createtime']);
        }

        $this->success('ok', $info);

    }

    /**
     * 积分详情
     *
     * @ApiTitle    (积分详情)
     * @ApiSummary  (integralDetail)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Member/integralDetail)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" member_id")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn
     */

    public function integralDetail()
    {

        $member_id = $this->request->request('member_id');

        $pages = abs(input("pages", 1));

        $limit = 10;
        $start = ($pages - 1) * $limit;

        if (empty($member_id)) {
            $this->error('数据格式不对');
        }
        //查询积分表链表  类型 日期 积分
        $data = \db('app_integral_log')
            ->where('member_id', $member_id)
            ->order('createtime desc')
            ->limit($start, $limit)
            ->select();
        if (empty($data)) {
            $this->error('无数据');
        }

        foreach ($data as $k => $v) {
            if ($v['typedata'] == 'day') {
                $data[$k]['cate'] = '每日签到';
            } elseif ($v['typedata'] == 'buy') {
                $data[$k]['cate'] = '购买商品';
            } elseif ($v['typedata'] == 'invite') {
                $data[$k]['cate'] = '邀请用户';
            } elseif ($v['typedata'] == 'new') {
                $data[$k]['cate'] = '新人专享';
            }

            //判断一次 传的类型
            $data[$k]['time'] = date("Y-m-d ", $v['createtime']);
        }

        $this->success('ok', $data);

    }


    /**
     * 展示活动
     *
     * @ApiTitle    (展示活动)
     * @ApiSummary  (showActivity)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/showActivity)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams
     * @ApiReturn
     */

    public function showActivity()
    {
        //拼团数据
        $group = \db('group')
            ->where('status', '未开始')
            ->order('stime desc')
            ->find();

        //优惠券
        $coupond = \db('coupon')
            ->order('createtime desc')
            ->find();

        //最新的活动数据
        $activity = [
            'group' => $group,
            'coupond' => $coupond
        ];

        $this->success('ok', $activity);
    }


    /**
     * 展示用户单条信息
     *
     * @ApiTitle    (用户单条信息)
     * @ApiSummary  (showOne)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/showOne)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" 当前用户的member_id  状态为1才能显示")
     * @ApiReturn
     */

    public function showOne()
    {
        $id = $this->request->request('member_id');
        $member_info = \db('app_member')
            ->where('id', $id)
            ->where('state', 1)
            ->field('id as member_id,typedata,nickname,realname,head_image,phone,email,integral,integral_total,code,birthdaydate,power')
            ->find();

        if (empty($member_info)) {
            $this->error('没有用户或者状态不对');
        }
        //查询该用户下面有没有优惠券  新用户必然有一张新人优惠券  如果配置没有则没有
        $coupond = \db('coupond')
            ->where('member_id', $id)
            ->count();

        //查询该用户的拼团 的数量
        $group = \db('groupc')
            ->where('member_id', $id)
            ->count();
        //返回优惠券 数量
        $member_info['coupond'] = $coupond;
        //返回 拼团数量
        $member_info['group'] = $group;

        //读取配置 文件是否开放抽奖
        $a = "lottery";
        $setting = \db('set_up')
            ->where('tab', $a)
            ->field('context')
            ->find();

        if ($setting['context'] == "off") {
            $member_info['open'] = "off";
        } else {
            $member_info['open'] = "on";
        }


        $this->success('ok', $member_info);


    }


    /**
     * 生成推广码二维码
     *
     * @ApiTitle    (推广二维码)
     * @ApiSummary  (createPromote)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/createPromote)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" 当前用户的member_id ")
     * @ApiReturn
     */


    public function createPromote()
    {
        $id = $this->request->request('member_id');

        $data = \db('app_member')
            ->where('id', $id)
            ->field('code')
            ->find();


        if (empty($data['code'])) {
            //生成推广码
            //传当前id值
            $page = 'pages/home/home'; //必须是审核上线的页面不然生成不了

            //葵花二维码
            $mpcodeimg = $this->mpcode($page, $id);

            //正方形二维码
//           $mpcodeimg=$this->qrcodes($page,$cardid);
            //存本地
            $img = $this->base64_image_content($mpcodeimg, "qrcode/");

            //把二维码加入当前用户的表
            $qr = \db('app_member')->where('id', $id)->update([
                'code' => $img,
            ]);

            if ($qr) {
                $this->success('创建成功', $img);
            }
            $this->error('生成二维码失败');
        }

        $this->success('ok', $data['code']);


    }

    /**
     * 判断是否认证
     *
     * @ApiTitle    (是否认证)
     * @ApiSummary  (checkAttest)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/checkAttest)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" 当前用户的member_id ")
     * @ApiReturn
     */
    public function checkAttest()
    {
        $id = $this->request->request('member_id');

        $data = \db('app_member_info')
            ->where('member_id', $id)
            ->find();

        if (empty($data)) {
            $this->error('未认证');
        } else {
            $this->success('已认证', $data);
        }
    }


    /**
     * 判断是否勾选认证类型
     *
     * @ApiTitle    (判断是否勾选认证类型)
     * @ApiSummary  (checkInfos)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/checkInfos)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" 当前用户的member_id ")
     * @ApiReturn
     */
    public function checkInfos()
    {
        $member_id = $this->request->request('member_id');

        $data = \db('app_member_info')
            ->where('member_id', $member_id)
            ->find();


        if ($data) {
            if ($data['typedata'] == '') {
                $this->error('该用户未选择类型', 'no_check');
            }

        } else {
            $this->error('没有该员工认证信息', 'no_sign');
        }

    }

    public function test(){
        /*图一*/
        $data = \db('app_sign_detail')
            ->alias('d')
            ->join('app_sign_in s', 'd.sign_id =s.id ')
            ->join('app_member_info m', 's.member_id = m.member_id')
            ->field('d.*,s.member_id,m.typedata,m.name')
            ->select();

        foreach ($data as $k => $v) {
            $data[$k]['Times'] = date("Y-m-d", $v['sign_time']);
            if ($data[$k]['typedata'] == 0) {
                $data[$k]['typedatas'] = '水果店主';
            } elseif ($data[$k]['typedata'] == 1) {
                $data[$k]['typedatas'] = '水果摊贩';
            } elseif ($data[$k]['typedata'] == 2) {
                $data[$k]['typedatas'] = '水果游商';
            } elseif ($data[$k]['typedata'] == 3) {
                $data[$k]['typedatas'] = '超市/配送';
            } elseif ($data[$k]['typedata'] == 4) {
                $data[$k]['typedatas'] = '消费者';
            }
        }

        $list = array();

        foreach ($data as $key => $val) {
            $val['sign_time'] = date('Y-m-d H:i:s', $val['sign_time']);

            $list[$val['Times']]['time'] = $val['Times'];

            $list[$val['Times']]['data'][] = $val;

        }
        $ret = array();

        foreach ($list as $key => $value) {
            array_push($ret, $value);
        }
        //店主 摊贩 游商 超市 消费者
        $shopkeeper =1;
        $booth = 1;
        $business =1;
        $store =1;
        $user =1;
        foreach ($ret as $k =>$value){
            $ret[$k]['sign_total'] =count($value['data']);
            foreach ($value['data'] as $m =>$n){
                if($n['typedatas'] == "水果店主"){
                    $ret[$k]['shopkeeper_total'] = $shopkeeper ++;
                }elseif($n['typedatas'] == "水果摊贩"){
                    $ret[$k]['booth_total'] = $booth ++;
                }elseif($n['typedatas'] == "水果游商"){
                    $ret[$k]['business_total'] = $business ++;
                }elseif($n['typedatas'] == "超市/配送"){
                    $ret[$k]['supermarket_total'] = $store ++;
                }elseif($n['typedatas'] == "消费者"){
                    $ret[$k]['user_total'] = $user ++;
                }
            }
            $shopkeeper =1;
            $booth =1;
            $business =1;
            $store =1;
            $user =1;

        }

        /* 图2*/
        //注册用户人数每日数据
        $sign_date = \db('app_member')
//            ->where($sdata)
            ->field("DATE_FORMAT(FROM_UNIXTIME(createtime),'%Y-%m-%d') as date,count(*) as total")
            ->group("DATE_FORMAT(FROM_UNIXTIME(createtime),'%Y-%m-%d')")
            ->select();



        /*图3 */
        //优惠券 领用/核券 每周 每个月 总
        //周
        $coupon_w = \db('coupond')
//            ->where('status','NEQ','已过期')
            ->field("DATE_FORMAT(FROM_UNIXTIME(gettime),'%Y-%m-%u') as weeks,count(*) as total")
            ->group("weeks")
            ->select();

        //使用情况
        $coupon_ws = \db('coupond')
            ->where('status', ['=', '已使用'], ['=', '已结算'], 'or')
            ->field("DATE_FORMAT(FROM_UNIXTIME(gettime),'%Y-%m-%u') as weeks,count(*) as total")
            ->group("weeks")
            ->select();




        //月份领取
        $coupon_m = \db('coupond')
//            ->where('status','NEQ','已过期')
            ->field("DATE_FORMAT(FROM_UNIXTIME(gettime),'%Y-%m') as date,count(*) as total")
            ->group("DATE_FORMAT(FROM_UNIXTIME(gettime),'%Y-%m')")
            ->select();
        //使用情况
        $coupon_ms = \db('coupond')
            ->where('status', ['=', '已使用'], ['=', '已结算'], 'or')
            ->field("DATE_FORMAT(FROM_UNIXTIME(gettime),'%Y-%m') as date,count(*) as total")
            ->group("DATE_FORMAT(FROM_UNIXTIME(gettime),'%Y-%m')")
            ->select();

        //每天使用和结算情况
        $coupon_ds = \db('coupond')
            ->where('status', ['=', '已使用'], ['=', '已结算'], 'or')
            ->field("DATE_FORMAT(FROM_UNIXTIME(gettime),'%Y-%m-%d') as days,count(*) as total")
            ->group("days")
            ->select();



        /* 图4 */
        //发出去的所有券
        $send_all = \db('coupon')
//            ->where('voucherdata','luck')
            ->sum('total');

        //核销的券
        $coupond = \db('coupond')
            ->where('status', ['=', '已使用'], ['=', '已结算'], 'or')
            ->count();


        //结算券的百分比
        //结算券
        $settle_account_coupond = \db('coupond')
            ->where('status', '=', '已结算')
            ->count();
        //结算百分比  结算的券/ 已使用和已结算的券的和
        $Today_ok = $settle_account_coupond / $coupond;
        //如果除不尽保留2位
        $Today_check = sprintf("%.2f", $Today_ok);
        $Today_str1 = ($Today_check * 100) . "%";


        $arr =[
            'day_sign' => $ret,
            'day_people'=>$sign_date,
            'coupond_week' =>$coupon_w,
            'coupond_week_use' =>$coupon_ws,
            'coupond_moon' =>$coupon_m,
            'coupond_moon_use' =>$coupon_ms,
            'coupond_day_use' =>$coupon_ds,
            'coupond_send_all'=>$send_all,
            'coupond_check' =>$coupond,
            'percentage'=>$Today_str1
        ];

        $this->success('ok',$arr);
    }







    /**
     * 更改未勾选的认证类型
     *
     * @ApiTitle    (更改未勾选的认证类型)
     * @ApiSummary  (changeInfos)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/Member/changeInfos)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description=" 当前用户的member_id ")
     * @ApiParams (name="typedata", type="varchar", required=true, description=" 选择的类型 ")
     * @ApiReturn
     */
    public function changeInfos()
    {
        $member_id = $this->request->request('member_id');
        $tydata = $this->request->request('typedata');

        if (!empty($tydata)) {
            if ($tydata == "水果店主") {
                $staffData['typedata'] = 0;
            } elseif ($tydata == "水果摊贩") {
                $staffData['typedata'] = 1;
            } elseif ($tydata == "水果游商") {
                $staffData['typedata'] = 2;
            } elseif ($tydata == "超市/配送") {
                $staffData['typedata'] = 3;
            } elseif ($tydata == "消费者") {
                $staffData['typedata'] = 4;
            }
        }

        $res = \db('app_member_info')
            ->where('member_id', $member_id)
            ->update($staffData);

        if ($res) {
            $this->success('更新成功', $res);
        }
        $this->error('信息有误');


    }




}

;
