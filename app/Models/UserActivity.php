<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Helpers\Pagination;
use App\Helpers\General;
use Illuminate\Support\Facades\DB;
use App\Models\UserAuth;

/**
 * Class UserActivity
 * 
 * Represents the Log model for tracking user activity logs.
 *
 * @package App\Models
 */
class UserActivity extends Model
{
    use HasUlids;

    /**
     * @var string $table The table associated with the model.
     */
    protected $table = 'user_activity';

    /**
     * @var string $primaryKey The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * @var string $keyType The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * @var bool $incrementing Indicates if the primary key is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * @var bool $timestamps Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * @var array $fillable The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'type',
        'ip',
        'client',
        'created_at'
    ];

    /**
     * Add a new log entry.
     *
     * @param int $userId The user ID.
     * @param string $type The type of log entry.
     * @return bool Whether the log was added successfully.
     */
    public function add($userId, $type)
    {
        if (!config('setting.save_user_log')) {
            return false;
        }
        $deviceUid = @$_COOKIE[config("setting.app_uid") . '_token'];
        if (!$deviceUid) {
        }

        $device = UserAuth::where(['device_uid' => $deviceUid])->first();
        if (!$device) {
            return false;
        }

        $client = @$_SERVER['HTTP_USER_AGENT'];
        $general = new General();
        $activity = new UserActivity();
        $ip = $general->getClientIp();
        $activity->ip = $ip;
        $activity->client = $client;

        $activity->user_id = $userId;
        $activity->type = $type;
        $activity->device_id = $device->id;


        $activity->save();
    }

    /**
     * Sends an email notification if a user logs in from a new device or location.
     *
     * @param object $user The user object.
     * @return bool Whether the email was sent successfully.
     */
    public function sendNewDeviceMail(object $user): bool
    {
        if (!config('setting.save_user_log')) {
            return false;
        }

        $general = new General();
        $deviceUid = $_COOKIE[config("setting.app_uid") . '_token'] ?? '';
        $ip = $general->getClientIp();
        $client = request()->header('User-Agent', 'Unknown Client');

        $existingLog = $this->where('user_id', $user->id)
            ->whereRaw('(device_id = ? OR ip = ?)', [$deviceUid, $ip])
            ->first();

        if (!$existingLog) {
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

    /**
     * Retrieves logs for the admin with search and pagination.
     *
     * @param array $postData The data for filtering and pagination.
     * @return array The paginated log data.
     */
    public function listAdmin($postData)
    { 
        $query = DB::table($this->table)->select(['user_activity.created_at as created_at', 'user_activity.type As type', 'user_activity.ip', 'user_activity.client', 'user.first_name', 'user.email', 'user.last_name'])
            ->join('user', 'user.id', '=', 'user_activity.user_id');
        $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($query) use ($searchText) {
                $query->where("client", 'like', $searchText)
                    ->orwhereRaw("concat(first_name,' ' ,last_name) like ?", $searchText)
                    ->orWhere("email", 'like', $searchText)
                    ->orWhere('user_activity.created_at', 'LIKE', '%' . $searchText . '%')
                    ->orWhere(function ($query) use ($searchText) {
                        if (stripos($searchText, '%fai%') !== false) {
                            $query->where('user_activity.type', '=', 0);
                        } elseif (stripos($searchText, '%succ%') !== false) {
                            $query->where('user_activity.type', '=', 1);
                        } elseif (stripos($searchText, '%reme%') !== false) {
                            $query->where('user_activity.type', '=', 2);
                        } elseif (stripos($searchText, '%Regi%') !== false) {
                            $query->where('user_activity.type', '=', 3);
                        } elseif (stripos($searchText, '%otp%') !== false) {
                            $query->where('user_activity.type', '=', 4);
                        } elseif (stripos($searchText, '%Login with social media%') !== false) {
                            $query->where('user_activity.type', '=', 5);
                        } elseif (stripos($searchText, '%Register with social media%') !== false) {
                            $query->where('user_activity.type', '=', 6);
                        }
                    });
            });
        }
        $result = (new Pagination())->getDataTable($query, $postData);
        $general = new General();
        foreach ($result['data'] as $key => $row) {
            $deviceName = $general->deviceName($row->client);
            $result['data'][$key]->first_name = $row->first_name . ' ' . $row->last_name;
            $result['data'][$key]->location = $general->getIpLocation($row->ip);
            $result['data'][$key]->device = $deviceName;
            $result['data'][$key]->type = $this->getType($row->type);
            $result['data'][$key]->created_at = $general->dateFormat($row->created_at);
            $result['data'][$key]->action = '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
            <div class="dropdown-menu">
                <label class="dropdown-item">Ip: ' . $row->ip . '</label>
                <label class="dropdown-item">Created At: ' . $row->created_at . '</label>
            </div>';
        }
        return $result;
    }

    /**
     * Retrieves logs for a specific user with search and pagination.
     *
     * @param array $postData The data for filtering and pagination.
     * @param int $userId The user ID.
     * @return array The paginated log data.
     */
    public function list(array $postData, int $userId): array
    { 
        $query = DB::table($this->table)
            ->select('*')
            ->where('user_id', $userId);

        $this->applySearchFilter($query, $postData['search']['value'] ?? '');

        $result = (new Pagination())->getDataTable($query, $postData);

        $general = new General();
        foreach ($result['data'] as $key => $row) {
            $row->location = $general->getIpLocation($row->ip);
            $row->client = $general->deviceName($row->client);
            $row->type = $this->getType($row->type);
            $row->created_at = $general->dateFormat($row->created_at);
        }

        return $result;
    }

    /**
     * Returns the user type in a formatted string.
     *
     * @param int $type The user type.
     * @return string The formatted user type.
     */
    public function getUserType(int $type): string
    {
        return $type === 1 ? '<span class="">Admin</span>' : '<span class="">User</span>';
    }

    /**
     * Returns the log type in a formatted string.
     *
     * @param int $type The log type.
     * @return string The formatted log type.
     */
    public function getType(int $type): string
    {
        return match ($type) {
            0 => '<span class="">Login failed</span>',
            1 => '<span class="">Login success</span>',
            3 => '<span class="">Register</span>',
            4 => '<span class="">Login with OTP</span>',
            5 => '<span class="">Login with social media</span>',
            default => '<span class="">Login with remember</span>',
        };
    }

    /**
     * Applies search filters to a query.
     *
     * @param \Illuminate\Database\Query\Builder $query The query builder.
     * @param string $searchText The search text.
     * @return void
     */
    private function applySearchFilter($query, string $searchText): void
    {
        if (strlen($searchText) > 2) {
            $searchText = '%' . $searchText . '%';
            $query->where(function ($query) use ($searchText) {
                $query->where('client', 'like', $searchText)
                    ->orWhere('ip', 'like', $searchText)
                    ->orWhere("created_at", 'LIKE', $searchText);
            });
        }
    }
}
