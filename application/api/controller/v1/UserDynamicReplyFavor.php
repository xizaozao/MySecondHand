<?php
/**
 * 对评论的点赞相关
 * User: xjyplayer
 * Date: 2019/2/18
 * Time: 10:43
 */

namespace app\api\controller\v1;


use app\api\lib\exception\APIException;
use think\Controller;
use think\Request;

class UserDynamicReplyFavor extends Controller{

    private $userDynamicReplyModel;
    private $userDynamicReplyFavorModel;

    protected function initialize ()
    {
        $this->userDynamicReplyModel        = model('common/UserDynamicReply');
        $this->userDynamicReplyFavorModel   = model('common/UserDynamicReplyFavor');
    }

    /**
     * 查看点赞关系是否存在
     * @param Request $request
     * @return \think\response\Json
     * @throws APIException
     */
    public function readFavor(Request $request){
        $data           = $request->data;
        $from_user_id   = $request->user_id;
        //验证参数
        $validate = Validate('api/UserDynamicReplyFavor');
        if(!$validate->scene('read_favor')->check($data)){
            throw new APIException($validate->getError());
        };
        $reply_id = $data['reply_id'];

        //判断该点赞关系是否存在
        $is_favored = $this->isFavored($from_user_id,$reply_id);

        //返回数据
        $data = [
            'is_favored' => $is_favored,
        ];

        return api_result($data);
    }

    /**
     * 点赞或者取消点赞
     * @param Request $request
     * @return \think\response\Json
     * @throws APIException
     */
    public function favor(Request $request)
    {
        $data           = $request->data;
        $my_user_id     = $request->user_id;
        //验证参数
        $validate = Validate('api/UserDynamicReplyFavor');
        if(!$validate->scene('favor')->check($data)){
            throw new APIException($validate->getError());
        };
        $reply_id = $data['reply_id'];

        //判断该点赞关系是否存在
        $is_favored = $this->isFavored($my_user_id,$reply_id);

        if($is_favored){
            //更新用户点赞数目，并且检查用户是否存在
            try{
                $res = $this->userDynamicReplyModel->userDynamicReplyDec($reply_id,'favor_count');
                if(!$res) {
                    throw new APIException('增加点赞数目失败');
                }
            }catch(\Exception $e){
                throw new APIException('更新用户点赞数目出错,请重新操作');
            }

            //取消点赞联系
            try{
                $res = $this->userDynamicReplyFavorModel->remove($my_user_id,$reply_id);
                if(!$res){
                    throw new APIException('取消关注失败');
                }
            }catch(\Exception $e){
                throw new APIException('取消关注失败');
            }
        }else{
            //创建点赞联系
            $udata = [
                'user_id'       => $my_user_id,
                'reply_id'      => $reply_id
            ];

            //更新用户关注数目，并且检查用户是否存在
            try{
                $res = $this->userDynamicReplyModel->userDynamicReplyInc($reply_id,'favor_count');
                //检查
                if(!$res){
                    throw new APIException('点赞失败');
                }
            }catch(\Exception $e){
                throw new APIException('更新用户点赞数目出错,或者没有该用户');
            }

            //插入数据
            try{
                $res = $this->userDynamicReplyFavorModel->add($udata);
                //检查
                if(!$res){
                    throw new APIException('点赞失败');
                }
            }catch(\Exception $e){
                throw new APIException('点赞失败');
            }
        }
        $rdata = [
            'is_favored' => !$is_favored,
        ];

        return api_result($rdata);
    }

    /**
     * 判断特定的点赞关系是否存在
     * @param $user_id
     * @param $reply_id
     * @return bool
     */
    private function isFavored($user_id,$reply_id){
        //查看该关注关系是否存在
        try{
            $res = $this->userDynamicReplyFavorModel->checkFavor($user_id,$reply_id);
            if(!$res){
                return false;
            }
        }catch(\Exception $e){
            return false;
        }
        return true;
    }
}