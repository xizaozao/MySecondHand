<?php
/**
 * 商品评论点赞.
 * User: xjyplayer
 * Date: 2019/2/19
 * Time: 20:25
 */

namespace app\api\controller\v1;

use app\api\lib\exception\APIException;
use think\Controller;
use think\Request;

class GoodsCommentsFavor extends Controller
{
    //todo 商品评论点赞，商品收藏、收藏者列表
    private $goodsCommentsModel;
    private $goodsCommentsFavorModel;
    
    protected function initialize ()
    {
        $this->goodsCommentsModel       = model('common/GoodsComments');
        $this->goodsCommentsFavorModel  = model('common/GoodsCommentsFavor');
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
        $validate = Validate('api/GoodsCommentsFavor');
        if(!$validate->scene('favor')->check($data)){
            throw new APIException($validate->getError());
        };
        $comment_id = $data['comment_id'];

        //判断该点赞关系是否存在
        $is_favored = $this->isFavored($my_user_id,$comment_id);

        if($is_favored){
            //更新用户点赞数目，并且检查动态是否存在
            try{
                $res = $this->goodsCommentsModel->goodsCommentsDec($comment_id,'favor_count');
                if(!$res) {
                    throw new APIException('增加点赞数目失败');
                }
            }catch(\Exception $e){
                throw new APIException('更新动态点赞数目出错,请重新操作');
            }

            //取消关注联系
            try{
                $res = $this->goodsCommentsFavorModel->remove($my_user_id,$comment_id);
                if(!$res){
                    throw new APIException();
                }
            }catch(\Exception $e){
                throw new APIException('取消点赞失败');
            }
        }else{
            //创建点赞联系
            $udata = [
                'user_id'       => $my_user_id,
                'comment_id'    => $comment_id
            ];

            //更新用户关注数目，并且检查用户是否存在
            try{
                $res = $this->goodsCommentsModel->goodsCommentsInc($comment_id,'favor_count');
                //检查
                if(!$res){
                    throw new APIException();
                }
            }catch(\Exception $e){
                throw new APIException('更新动态点赞数目出错,或者没有该动态');
            }

            //插入数据
            try{
                $res = $this->goodsCommentsFavorModel->add($udata);
                //检查
                if(!$res){
                    throw new APIException();
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
     * 查看点赞关系是否存在
     * @param Request $request
     * @return \think\response\Json
     * @throws APIException
     */
    public function readFavor(Request $request){
        $data           = $request->data;
        $my_user_id     = $request->user_id;
        //验证参数
        $validate = Validate('api/GoodsFavor');
        if(!$validate->scene('favor')->check($data)){
            throw new APIException($validate->getError());
        };
        $comment_id = $data['comment_id'];

        //判断该点赞关系是否存在
        $is_favored = $this->isFavored($my_user_id,$comment_id);

        //返回数据
        $data = [
            'is_favored' => $is_favored,
        ];

        return api_result($data);
    }

    /**
     * 查看点赞状态
     * @param $user_id
     * @param $comment_id
     * @return bool
     */
    private function isFavored($user_id,$comment_id){
        //查看该关注关系是否存在
        try{
            $res = $this->goodsCommentsFavorModel->checkFavor($user_id,$comment_id);
            if(!$res){
                return false;
            }
        }catch(\Exception $e){
            return false;
        }
        return true;
    }
}