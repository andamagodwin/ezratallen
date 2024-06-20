<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Community;
use App\CommunityMember;
use App\CommunityQuestion;
use App\CommunityQuestionReply;
use App\User;
use Image;

class GroupChat extends Controller
{
    //creating farm group controller function

    public function createFarmCommunity(Request $request){
        //return response()->json(['result'=>"OKay"]);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'notify_reply' => 'required',
            'picture' => 'required',
            'views_count' => 'required',
            'replies_count' => 'required'
        ]);

        //

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            return response()->json(['result'=>false, 'error' => $validator->errors()]);
        }


        if (!$request->hasFile('picture')) {

          return response()->json(['error'=>'Picture field is not a proper image formart']);

        }


    
        $data = $request->all();
        $group = Community::create($data);


        if ($request->hasFile('picture')) {
          
            $fileName = md5(microtime()) . '_group.' . $request->picture->getClientOriginalExtension();
            

            $image_path =  public_path("storage/community/" . $fileName);

            Image::make($request->picture)->resize(320, 240)->save($image_path);

            $group->picture = $fileName;
            $group->save();
        }

        $list = Community::join('users', 'users.id', '=', 'communities.user_id')
        ->select('communities.id',
        'communities.user_id',
        'communities.title',
        'communities.description',
        'communities.picture',
        'communities.notify_reply',
        'communities.views_count',
        'communities.replies_count',
        'communities.created_at',
        'users.full_name',
        'users.picture as user_picture'
        )
        ->where('communities.id', '=', $group->id)->first();

        return response()->json(['result'=>true, 'group'=>$group]);
    }

    public function joinFarmCommunity(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'community_id' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            return response()->json(['result'=>false, 'error' => $validator->errors()]);   
        }

        $data = $request->all();
        $res = CommunityMember::create($data);
        return response()->json(['result'=>true, 'member'=>$res]);
    }

    public function startCommunityQuestion(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'community_id' => 'required',
            'topic' => 'required',
            'details' => 'required',
            'type' => 'required',
            'notify_reply' => 'required',
            'views_count' => 'required',
            'replies_count' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            return response()->json(['result'=>false, 'error' => $validator->errors()]);
        }

        $data = $request->all();
        $res = CommunityQuestion::create($data);
        $res = CommunityQuestion::join('users', 'users.id', '=', 'community_questions.user_id')
        ->join('communities', 'communities.id', '=', 'community_questions.community_id')
        ->where('community_questions.id', $res->id)
        ->select('community_questions.id',
        'community_questions.community_id',
        'community_questions.user_id',
        'community_questions.topic',
        'community_questions.type',
        'community_questions.notify_reply',
        'community_questions.views_count',
        'community_questions.replies_count',
        'community_questions.created_at',
        'communities.title as community_group',
        'users.full_name',
        'users.picture'
        )->first();
        return response()->json(['result'=>true, 'question'=>$res]);
    }

    public function replyCommunityQuestion(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'community_question_id' => 'required',
            'likes_count' => 'required',
            'replies_count' => 'required'
        ]);

        /**Check the validation becomes fails or not*/
        if ($validator->fails()) {
            return response()->json(['result'=>false, 'error' => $validator->errors()]);
        }

        $data = $request->all();
        $res = CommunityQuestionReply::create($data);

        if($request->hasFile('image')){
            //store 

        }
        return response()->json(['result'=>true, 'reply'=>$res]);
    }


    public function getFarmCommunities(){
        //$list = Community::all();
        $list = Community::join('users', 'users.id', '=', 'communities.user_id')
        ->select('communities.id',
        'communities.user_id',
        'communities.title',
        'communities.description',
        'communities.picture',
        'communities.notify_reply',
        'communities.views_count',
        'communities.replies_count',
        'communities.created_at',
        'users.full_name',
        'users.picture as user_picture'
        )->get();
        return response()->json(['result'=>true, 'list'=>$list]);
    }

    public function getPopularQuestions(){
        //$list = CommunityQuestion::all();
        $list = CommunityQuestion::join('users', 'users.id', '=', 'community_questions.user_id')
        ->join('communities', 'communities.id', '=', 'community_questions.community_id')
        ->select('community_questions.id',
        'community_questions.community_id',
        'community_questions.user_id',
        'community_questions.topic',
        'community_questions.type',
        'community_questions.notify_reply',
        'community_questions.views_count',
        'community_questions.replies_count',
        'community_questions.created_at',
        'communities.title as community_group',
        'users.full_name',
        'users.picture'
        )->get();
        return response()->json(['result'=>true, 'list'=>$list]);
    }
    
    public function getQuestionReplies($id){
        $list = CommunityQuestionReply::where(['community_question_id'=>$id])->get();
        return response()->json(['result'=>true, 'list'=>$list]);
    }
}
