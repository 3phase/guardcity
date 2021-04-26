<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request) {
        $user = \App\User::where('email', $request->input('email'))->first();
        if ($user == null) {
            return response([
                'message' => 'Unauthenticated'
            ], 403);
        }
        
        $token = $user->createToken('utoken')->accessToken;
        return response([
            'token' => $token
        ], 200);
    }

    public function getProgress(){
        $user = \App\User::find(\Cookie::get('user_id'));
        return response()->json($user->player()->first()->progress()->first(), $user->player()->first()->nickname);
    }

    public function saveProgress($progress){
        $progress_object = json_decode($progress);

        $user = \App\User::find(\Cookie::get('user_id'));
        
        $user->player()->progress()->first()->trust = $progress_object->trust;
        $user->player()->progress()->first()->popularity = $progress_object->popularity;
        $user->player()->progress()->first()->jokers = $progress_object->jokers;
        $user->player()->progress()->first()->jokers_level = $progress_object->jokers_level;
        // $user->player()->progress()->first()->days = $progress_object->days;
        $user->player()->progress()->first()->points = $progress_object->points;
        $user->player()->progress()->first()->planet_id = $progress_object->planet_id;

        $user->plauer()->progress()->first()->save();
        return response()->json(['status'=>'saved']);
    }

    public function getAlien($id){
        return response()->json(\App\Alien::find($id));
    }

    public function getPlanet($id){
        $planet = \App\Planet::find($id);
        $aliens = \App\Alien::where(['planet_id' => $id])->select('id', 'name', 'picture_path')->get();
        unset($planet->created_at);
        unset($planet->updated_at);
        return response()->json([
            'name' => $planet->name,
            'image_filename' => $planet->image_filename,
            'background_image' => $planet->background_image,
            'aliens' => $aliens,
            'alien_coordinates' => $planet->alienCoordinates()->select('xCoord', 'yCoord')->get()]);
    }

    public function getPlanetsByPopularity($starting_popularity, $offset){
        $planets = \App\Planet::where('unlocking_popularity', '>', $starting_popularity)
            ->where('unlocking_popularity', '<', $starting_popularity + $offset)->select('id', 'image_filename', 'name')->get();

        return response()->json(['planets' => $planets]);
    }

    public function getMission($alien_id, $alien_mission_num){
        $mission = \App\Alien::find($alien_id)->missions()->skip($alien_mission_num - 1)->first();

        if ($mission != null){
            $mission = $mission->pivot;
        }
        else{
            $mission = (object) array('node_id' => -1);
        }

        return response()->json([
            'alien' => \App\Alien::find($alien_id)->name,
            'starting_node_id' => $mission->node_id,
        ]);
    }

    public function getMissionNode($node_id){
        $mission_node = \App\Node::find($node_id);
        unset($mission_node->created_at);
        unset($mission_node->updated_at);
    
        $children = $mission_node->options()->get();

        $options = [];

        foreach ($children as $child) {
            unset($child->created_at);
            unset($child->updated_at);

            $composite_object = [
                'node' => ["id" => $child->id, "dialog" => $child->dialog, 'speaker' => $child->speaker, "pivot" => $child->pivot, 'gains' => \App\Option::select('popularity', 'trust', 'energy', 'days')->where(['next_id' => $child->id], ['start_id' => $mission_node->id])
                ->first()],
                'unlocking_trust' => \App\Option::select('unlocking_trust')->where(['next_id' => $child->id], ['start_id' => $mission_node->id])->first()->unlocking_trust
            ];

            array_push($options, $composite_object);
        }

        return response()->json([
            'current_node' => $mission_node,
            'options' => $options
        ]);
    }

    public function getMissionNodes(Request $request){
        $nodeIds = $request->input('node_ids');
       
        $mission_nodes = \App\Node::whereIn('id', array_map('intval', explode(',', $nodeIds)))->get();

        return $mission_nodes;
    }
}
