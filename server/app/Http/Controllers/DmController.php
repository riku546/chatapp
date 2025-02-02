<?php
namespace App\Http\Controllers;

use App\Events\ChatEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DmController extends Controller
{
    public function list_dms()
    {
        try {
            $dms = DB::select('select u.name , f.dm_id  from friends as f inner join users as u on f.friend_id = u.id  where f.user_id = ?', [auth()->id()]);
            return response()->json(["data" => $dms, "message" => "DMs listed successfully", "status" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => "failed to list dms", "status" => "error"]);
        }
    }

    public function show_specific_dm(Request $request)
    {
        try {
            $messages = DB::select('select content ,  created_at , updated_at from messages_in_dm where dm_id = ?', [$request->dm_id]);
            return response()->json(["data" => $messages, "message" => "DM show successfully", "status" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => "failed to show dm", "status" => "error"]);
        }
    }

    public function send_message(Request $request)
    {
        try {
            //websocketsを使ってメッセージを送信
            event(new ChatEvent($request->content, $request->dm_id, auth()->user()->name));

            //メッセージをDBに保存
            DB::select('insert into messages_in_dm (content, dm_id) values (?, ?)', [$request->content, $request->dm_id]);
            return response()->json(["message" => "DM sent successfully", "status" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => "failed to send dm", "status" => "error"]);
        }
    }

    public function create_dm(Request $request)
    {
        try {
            DB::select('insert into dms (id) values (?)', [$request->dm_id]);
            return response()->json(["message" => "DM created successfully", "status" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => "failed to create dm", "status" => "error"]);
        }
    }

    public function edit_message(Request $request)
    {
        try {
            DB::select('update messages_in_dm set content = ? where dm_id = ? and created_at = ?', [$request->content, $request->dm_id, $request->created_at]);
            return response()->json(["message" => "Message edited successfully", "status" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => "failed to edit message", "status" => "error"]);
        }
    }

    public function delete_message(Request $request)
    {
        try {
            DB::select('delete from messages_in_dm where dm_id = ? and created_at = ?', [$request->dm_id, $request->created_at]);
            return response()->json(["message" => "Message deleted successfully", "status" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => "failed to delete message", "status" => "error"]);
        }
    }

}
