<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Helpers\Pagination;
use App\Helpers\General;
use Carbon\Carbon;

class UserAuth extends Model
{
    use HasUlids;


    /**
     * The table associated with the model.
     *
     * @var string
     */

    protected $table = 'user_auth';

    /**
     * The primary key for the model.
     *
     * @var string
     */

    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */

    protected $keyType = 'string';

    /**
     * Indicates if the IDs are incrementing.
     *
     * @var bool
     */

    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'token_expire_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'user_id',
        'device_uid',
        'session_id',
        'token',
        'token_expire_at',
        'client',
        'ip',
        'created_at',
        'updated_at'
    ];
    /**
     * Generates a random authentication token.
     *
     * @return string
     */


    public function generateAuthtoken()
    {
        return \Illuminate\Support\Str::random(64);
    }

    /**
     * Handles the login process for a user on a device.
     *
     * @param int $userId The ID of the user logging in.
     * @param int $remember Whether to remember the login session.
     * @return Device|false
     */

    public function login($userId, $remember = 1)
    {
        $deviceUid = @$_COOKIE[config("setting.app_uid") . '_token'] ?? 'fallback_' . uniqid('', true) . '_' . time();
        Log::info('Device UID used: ' . $deviceUid);

        if (!$deviceUid) {
            return false;
        }
        $client = @$_SERVER['HTTP_USER_AGENT'];
        $time = time();
        $UserAuthModel = self::where(['device_uid' => $deviceUid])->first();
        if (!$UserAuthModel) {
            $UserAuthModel = new UserAuth();
            $UserAuthModel->device_uid = $deviceUid;
        }
        $UserAuthModel->user_id = $userId;

        if ($remember) {
            $UserAuthModel->token = $this->generateAuthtoken();
            $UserAuthModel->token_expire_at = Carbon::now()->addDays(config('setting.device_expire_days'));
            \Illuminate\Support\Facades\Cookie::queue(config('setting.app_uid') . '_user_token', $UserAuthModel->token, config('setting.device_expire_days') * 24 * 60);
        } else {
            $UserAuthModel->token_expire_at = null;
        }

        $general = new General();
        $ip = $general->getClientIp();
        $UserAuthModel->ip = $ip;
        $UserAuthModel->client = $client;
        $UserAuthModel->session_id = session()->getId();
        $UserAuthModel->save();
        return $UserAuthModel;
    }

    /**
     * Logs out the current user from the device.
     *
     * @return bool
     */

    public function logout()
    {
        $deviceUid = @$_COOKIE[config("setting.app_uid") . '_token'];
        if (!$deviceUid) {
            return false;
        }
        $UserAuthModel = self::where(['device_uid' => $deviceUid])->first();
        if ($UserAuthModel) {
            $UserAuthModel->user_id = 0;
            $UserAuthModel->token = '';
            $UserAuthModel->token_expire_at = null;
            $UserAuthModel->session_id = '';
            $UserAuthModel->save();
        }
    }

    /**
     * Forces a logout for a specific device by ID.
     *
     * @param string $id The ID of the device.
     * @return void
     */

    public function forceLogout($id)
    {
        $UserAuthModel = self::where('id', $id)->first();
        if ($UserAuthModel) {
            $sessionDriver = config('session.driver');
            if ($sessionDriver == 'file') {
                if (session()->getId() == $UserAuthModel->session_id) {
                    auth()->logout();
                } else {
                    try {
                        Session::getHandler()->destroy($UserAuthModel->session_id);
                        unlink(config('session.files') . '/' . $UserAuthModel->session_id);
                    } catch (\Exception $e) {
                    }
                }
            } elseif ($sessionDriver == 'database') {
                DB::table('sessions')->where('id', $UserAuthModel->session_id)->delete();
            } elseif ($sessionDriver == 'redis') {
                $redis = app('redis');
                $prefix = config('session.prefix', 'laravel_database_');
                $redis->del($prefix . $UserAuthModel->session_id);
            } 
            $UserAuthModel->user_id = 0;
            $UserAuthModel->token = '';
            $UserAuthModel->token_expire_at = null;
            $UserAuthModel->session_id = '';
            $UserAuthModel->save();
        }
    }


    /**
     * Retrieves a paginated list of devices for a user.
     *
     * @param array $postData The data from the request.
     * @param int $userId The ID of the user.
     * @return array
     */


    public function list($postData, $userId)
    {
        $sessionDriver = config('session.driver');
        if ($sessionDriver === 'redis') {
            $query = DB::table('user_auth')->select(['user_auth.*'])
                ->where('user_auth.user_id', $userId)
                ->where(function ($query) {
                    $query->where('user_auth.token_expire_at', '>', Carbon::now());
                });
        } else {
            $query = DB::table('user_auth')->select(['user_auth.*', 'sessions.last_activity'])
                ->where('user_auth.user_id', $userId)
                ->where(function ($query) {
                    $query->where('user_auth.token_expire_at', '>', Carbon::now())
                        ->orWhere('sessions.last_activity', '>', time() - (config('session.lifetime') * 60));
                })
                ->leftJoin('sessions', 'sessions.id', 'user_auth.session_id');
        }

        $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($query) use ($searchText) {
                $query->orwhere("client", 'like', $searchText);
                $query->orwhere("ip", 'like', $searchText);
                // $query->orwhere("location", 'like', $searchText);
                $query->orWhere(DB::raw("FROM_UNIXTIME(last_activity, '%d-%m-%Y')"), 'LIKE', '%' . $searchText . '%');
            });
        }
        $general = new General();
        $result = (new Pagination())->getDataTable($query, $postData);

        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->location = $general->getIpLocation($row->ip);
            $result['data'][$key]->client = (new General())->deviceName($row->client) . ' ' . ($row->device_uid == @$_COOKIE[config("setting.app_uid") . '_token'] ? ' (This Device)' : '');
            $result['data'][$key]->last_activity = $general->dateFormat(@$row->last_activity ? $row->last_activity : $row->updated_at);
            $result['data'][$key]->action = '<button style="border: none; background: none;"  onclick="app.confirmAction(this);" data-action="account/device-logout?id=' . $row->id . '"  class="text-body pjax" title="logout">                    <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:22px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>';
        }
        return $result;
    }

    /**
     * Retrieves a paginated list of devices for admin view.
     *
     * @param array $postData The data from the request.
     * @return array
     */

    public function listAdmin($postData)
    {

        $sessionDriver = config('session.driver');
        if ($sessionDriver === 'redis') {
            $query = DB::table('user_auth')->select(['user_auth.updated_at as updated_at', 'user_auth.id', 'user_auth.ip', 'user_auth.device_uid', 'user_auth.client', 'user_auth.id as deviceId', 'user.first_name', 'user.last_name', 'user.email'])
                ->join('user', 'user.id', '=', 'user_auth.user_id')
                ->where(function ($query) {
                    $query->where('user_auth.token_expire_at', '>', Carbon::now());
                });
        } else {
            $query = DB::table('user_auth')->select(['user_auth.updated_at as updated_at', 'user_auth.id', 'user_auth.ip', 'user_auth.device_uid', 'user_auth.client', 'user_auth.id as deviceId', 'user.first_name', 'user.last_name', 'user.email'])
                ->join('user', 'user.id', '=', 'user_auth.user_id')
                ->leftJoin('sessions', 'sessions.id', 'user_auth.session_id')
                ->where(function ($query) {
                    $query->where('user_auth.token_expire_at', '>', Carbon::now())
                        ->orWhere('sessions.last_activity', '>', time() - (config('session.lifetime') * 60));
                });
        }

        $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($query) use ($searchText) {
                $query->where(function ($query) use ($searchText) {
                    $query->where(DB::raw('CONCAT(user.first_name, " ",user.last_name)  '), 'like', $searchText);
                    $query->orwhere("user_auth.client", 'like', $searchText);
                    $query->orWhere(DB::raw("FROM_UNIXTIME(last_activity, '%d-%m-%Y')"), 'LIKE', '%' . $searchText . '%');
                });
            });
        }
        $result = (new Pagination())->getDataTable($query, $postData);
        $general = new General();
        $sessionUser = auth()->user();
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->first_name = $row->first_name . ' ' . $row->last_name;
            $result['data'][$key]->location = $general->getIpLocation($row->ip);
            $result['data'][$key]->client = (new General())->deviceName($row->client) . ' ' . ($row->device_uid == @$_COOKIE[config("setting.app_uid") . '_token'] ? ' (This Device)' : '');
            $result['data'][$key]->last_activity = $general->dateFormat(@$row->last_activity ? $row->last_activity : $row->updated_at);

            $result['data'][$key]->action = '';
            if ($sessionUser->hasPermission('admin/device/logout')) {
                $result['data'][$key]->action .= '
                <button style="border: none; background: none;" onclick="app.confirmAction(this);" data-action="admin/device/logout?id=' . $row->deviceId . '" class="text-body pjax" title="logout">                    <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:22px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>';
            }
        }
        return $result;
    }

    public static function getUserDevices($userId)
    {
        return self::where('user_id', $userId)
            ->select('device_uid', 'client', 'ip')
            ->orderBy('id', 'desc')
            ->get();
    } 
}
