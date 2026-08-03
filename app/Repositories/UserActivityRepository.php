<?php

namespace App\Repositories;

use App\Helpers\General;
use App\Helpers\Pagination;
use App\Models\UserActivity;
use App\Models\UserAuth;
use Illuminate\Support\Facades\DB;

class UserActivityRepository
{
    public function add($userId, $type)
    {
        if (! config('setting.save_user_log')) {
            return false;
        }
        $deviceUid = $_COOKIE[config('setting.app_uid').'_token'] ?? null;
        if (! $deviceUid) {
            return false;
        }

        $device = UserAuth::where(['device_uid' => $deviceUid])->first();
        if (! $device) {
            return false;
        }

        $client = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $general = new General;
        $activity = new UserActivity;
        $ip = $general->getClientIp();
        $activity->ip = $ip;
        $activity->client = $client;

        $activity->user_id = $userId;
        $activity->type = $type;
        $activity->device_id = $device->id;

        return $activity->save();
    }

    public function sendNewDeviceMail(object $user): bool
    {
        if (! config('setting.save_user_log')) {
            return false;
        }

        $general = new General;
        $deviceUid = $_COOKIE[config('setting.app_uid').'_token'] ?? '';
        $ip = $general->getClientIp();
        $client = request()->header('User-Agent', 'Unknown Client');

        $existingLog = UserActivity::where('user_id', $user->id)
            ->whereRaw('(device_id = ? OR ip = ?)', [$deviceUid, $ip])
            ->first();

        if (! $existingLog) {
            $general->sendEmail($user->email, 'new_device_login', [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'ip' => $ip,
                'client' => $general->deviceName($client),
                'location' => $general->getIpLocation($ip),
            ]);
        }

        return true;
    }

    public function listAdmin(array $postData): array
    {
        $query = DB::table('user_activities')
            ->select(['user_activities.created_at as created_at', 'user_activities.type As type', 'user_activities.ip', 'user_activities.client', 'users.first_name', 'users.email', 'users.last_name'])
            ->join('users', 'users.id', '=', 'user_activities.user_id');

        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $searchText = '%'.$searchText.'%';
            $query->where(function ($query) use ($searchText) {
                $query->where('client', 'like', $searchText)
                    ->orWhereRaw("concat(first_name,' ' ,last_name) like ?", $searchText)
                    ->orWhere('email', 'like', $searchText)
                    ->orWhere('user_activities.created_at', 'LIKE', '%'.$searchText.'%')
                    ->orWhere(function ($query) use ($searchText) {
                        if (stripos($searchText, '%fai%') !== false) {
                            $query->where('user_activities.type', '=', 0);
                        } elseif (stripos($searchText, '%succ%') !== false) {
                            $query->where('user_activities.type', '=', 1);
                        } elseif (stripos($searchText, '%reme%') !== false) {
                            $query->where('user_activities.type', '=', 2);
                        } elseif (stripos($searchText, '%Regi%') !== false) {
                            $query->where('user_activities.type', '=', 3);
                        } elseif (stripos($searchText, '%otp%') !== false) {
                            $query->where('user_activities.type', '=', 4);
                        } elseif (stripos($searchText, '%Login with social media%') !== false) {
                            $query->where('user_activities.type', '=', 5);
                        } elseif (stripos($searchText, '%Register with social media%') !== false) {
                            $query->where('user_activities.type', '=', 6);
                        }
                    });
            });
        }

        $result = (new Pagination)->getDataTable($query, $postData);
        $general = new General;
        $model = new UserActivity;

        foreach ($result['data'] as $key => $row) {
            $deviceName = $general->deviceName($row->client);
            $result['data'][$key]->first_name = $row->first_name.' '.$row->last_name;
            $result['data'][$key]->location = $general->getIpLocation($row->ip);
            $result['data'][$key]->device = $deviceName;
            $result['data'][$key]->type = $model->getType($row->type);
            $result['data'][$key]->created_at = $general->dateFormat($row->created_at);
            $result['data'][$key]->action = '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
            <div class="dropdown-menu">
                <label class="dropdown-item">Ip: '.$row->ip.'</label>
                <label class="dropdown-item">Created At: '.$row->created_at.'</label>
            </div>';
        }

        return $result;
    }

    public function list(array $postData, string|int $userId): array
    {
        $query = DB::table('user_activities')
            ->select('*')
            ->where('user_id', (string) $userId);

        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $searchText = '%'.$searchText.'%';
            $query->where(function ($query) use ($searchText) {
                $query->where('client', 'like', $searchText)
                    ->orWhere('ip', 'like', $searchText)
                    ->orWhere('created_at', 'LIKE', $searchText);
            });
        }

        $result = (new Pagination)->getDataTable($query, $postData);
        $general = new General;
        $model = new UserActivity;

        foreach ($result['data'] as $key => $row) {
            $row->location = $general->getIpLocation($row->ip);
            $row->client = $general->deviceName($row->client);
            $row->type = $model->getType($row->type);
            $row->created_at = $general->dateFormat($row->created_at);
        }

        return $result;
    }
}
