<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User as User;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use JWTFactory;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redis;

use Swagger\Annotations as SWG;




/**
 * @OA\Info(title="My Rest Api Project", version="0.1")
 */



class UserController extends Controller
{

    //
    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     @OA\Response(response="200", description="get a user"),
     *      tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     * )
     */

    public function showUser($id){
        $user = User::find($id);
        Redis::publish('channel-list-user', json_encode($user));
        if ($user) {
            return Response::json([
                'data' => $user 
            ], 200);
        } else {
            return Response::json([
                'message' => "no user found" 
            ])->setStatusCode(404);;
        }
        
        
        
        
    }
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     @OA\Response(response="200", description="get all users"),
     *     tags={"User"},
     * )
     */
    public function showUsers() {
        Redis::publish('channel-list-user', json_encode(User::paginate(5)));
        return Response::json(
            User::all()
        , 200);
        
    }
    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     @OA\Response(response="200", description="add a user"),
     *     tags={"User"},
     *      @OA\Parameter(
     *         name="request",
     *         in="query",
     *         description="data of user to add",
     *         required=true,
     *         @OA\Schema(
     *             type="array",
     *             default="available",
     *             @OA\Items(
     *                 type="string",
     *                 
     *             )
     *         )
     *     ),
     * )
     */
    public function addUser(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'firstname' => 'required',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        $newuser = new User($request->all());
        $newuser->save();
        Redis::publish('channel-list-user', json_encode($newuser));
        return Response::json([
            'data' => $newuser 
        ], 201);   
    }

    private function validateInsertUser($request){
        
        $validation = Validator::make($request->toArray(), [
            'firstname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }
    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     @OA\Response(response="200", description="update a user"),
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ), 
     *      @OA\Parameter(
     *         name="request",
     *         in="query",
     *         description="data of user to update",
     *         required=true,
     *         @OA\Schema(
     *             type="array",
     *             default="available",
     *             @OA\Items(
     *                 type="string",
     *                 
     *             )
     *         )
     *     ),
     * )
     */
    public function updateUser($id,Request $request)
    {
        // validate
        // read more on validation at http://laravel.com/docs/validation
        
            // store
            $user = User::find($id);
            // return count( $request->toArray() );
            // return $user->password;
            if ( count( $request->toArray() ) == 0 ) {
                return Response::json([
                    'data' => "nothing to update" 
                ])->setStatusCode( 400 );    
            }
            
            if ( $user ) {
                foreach ( $request->toArray() as $key => $value ) {
                    
                    $user[$key] = $value;
                }
                
                // return $request;
                $user->update($user->toArray());
                
                Redis::publish('channel-list-user', json_encode($user));
                return Response::json([
                    'data' => $user 
                ])->setStatusCode( 200 );;

            }
            else {
                return Response::json([
                    'data' => "no user found" 
                ])->setStatusCode( 404 );
            }
         
    }
    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     @OA\Response(response="200", description="delete a user"),
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     * )
     */
    public function delete($id){      

        $user = User::find($id);

        if ($user) {
            $user->delete();
            Redis::publish('channel-list-user', json_encode($user));
            return response()->json(['success' => 'successfully deleted'], 200);
            
        } else {
            return Response::json([
                'message' => "no user found" 
            ])->setStatusCode(404);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $user = User::where('email','=',$request->get('email'))->first();
        
        
        $token = compact('token')['token'];
        $user->token = $token;
        $response = $user;
        return response()->json($response);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'firstname' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
            {
                    try {

                            if (! $user = JWTAuth::parseToken()->authenticate()) {
                                    return response()->json(['user_not_found'], 404);
                            }

                    } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

                            return response()->json(['token_expired'], $e->getStatusCode());

                    } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                            return response()->json(['token_invalid'], $e->getStatusCode());

                    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

                            return response()->json(['token_absent'], $e->getStatusCode());

                    }

                    return response()->json(compact('user'));
            }
    
}


