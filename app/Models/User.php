<?php

namespace App\Models;

use App\Helpers\General;
use App\Helpers\Pagination;
use App\Services\PermissionService;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'email',
        'email_verified',
        'image',
        'created_at',
        'updated_at',
        'two_factor_enabled',
        'role',
        'permission',
        'status',
        'first_name',
        'last_name',
        'phone',
        'country',
        'timezone',
        'registered_ip',
    ];

    protected $hidden = [
        'password',
        'password_reset_token',
        'email_verified',
        'otp',
    ];

    public $userRole = [4];

    public $superAdminRole = [0];

    public $adminRole = [1, 2, 3];

    public function isUser()
    {
        return in_array($this->role, $this->userRole) ? true : false;
    }

    public function isAdmin()
    {
        return in_array($this->role, $this->adminRole) ? true : false;
    }

    public function isSuperAdmin()
    {
        return $this->role == $this->superAdminRole ? true : false;
    }

    public function getPermissionListData(): array
    {
        return (new PermissionService)->getPermissionListData();
    }

    public function hasPermission($permission = '')
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (new PermissionService)->hasPermission($permission, $this->permission);
    }

    public function getStatusBadge($status)
    {
        return $status == 'active' ? '<span class="badge rounded-pill bg-label-success">Active</span>' : '<span class="badge rounded-pill bg-label-danger">Inactive</span>';
    }

    public function list($postData)
    {
        $userObj = new User;

        $query = DB::table('users')
            ->select(
                'users.*',
                'country.name as country_name',

            )
            ->join('country', 'users.country', '=', 'country.sortname')
            ->whereIn('users.role', $userObj->userRole)
            ->where('users.id', '!=', auth()->user()->id);

        $query = DB::table('users')->select('*')->where('role', 'USER');
        /**/
        $searchText = isset($postData['search']['value']) ? $postData['search']['value'] : '';
        if (strlen($searchText) > 2) {
            $searchText = '%'.$searchText.'%';
            $query->where(function ($query) use ($searchText) {
                $query->whereRaw("concat(first_name,' ' ,last_name) like ?", $searchText)->orWhere('email', 'like', $searchText)->orWhere(DB::raw("FROM_UNIXTIME(created_at, '%d-%m-%Y')"), 'LIKE', '%'.$searchText.'%')->orWhere(function ($query) use ($searchText) {
                    if (stripos($searchText, '%Act%') !== false) {
                        $query->where('status', '=', 1);
                    } elseif (stripos($searchText, '%Inac%') !== false) {
                        $query->where('status', '=', 0);
                    }
                });
            });
        }
        /**/
        $result = (new Pagination)->getDataTable($query, $postData);
        //  dd($result);
        $general = new General;
        $sessionUser = auth()->user();

        foreach ($result['data'] as $key => $row) {
            $imageUrl = (new General)->getFileUrl($row->image, 'profile');
            if ($row->image) {
                $result['data'][$key]->image = '<a href="upload/profile/'.$row->image.'" data-toggle="lightbox" data-title="Image" class = "noroute pjax" target = "_blank">
                <img style="width:30px;height:30px" src="'.$imageUrl.'" class="h-auto rounded-circle" alt="blog image"></a>';
            }
            $result['data'][$key]->first_name = $row->first_name.' '.$row->last_name;
            $result['data'][$key]->email = $row->email;
            $result['data'][$key]->phone = $row->phone;
            $result['data'][$key]->country = $row->country ?? '';
            $result['data'][$key]->status = $userObj->getStatusBadge($row->status);
            $result['data'][$key]->created_at = $general->dateFormat($row->created_at);
            $result['data'][$key]->updated_at = $general->dateFormat($row->updated_at);

            if (auth()->user()->role == 0) {
                $result['data'][$key]->action = '
            <div class="act-btns"><a href="admin/user/view?id='.$row->id.'" class="text-body pjax" title="View"><i class="bx bxs-show icon-base"></i></a>
            <a href="admin/user/update?id='.$row->id.'" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>
            <button style=" border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/user/delete" data-id="'.$row->id.'" class="text-body" title="Delete"><i class="bx bxs-trash icon-base"></i></button></div>';
            } else {

                $result['data'][$key]->action = '';
                if ($sessionUser->hasPermission('admin/user/view')) {
                    $result['data'][$key]->action .= '
                    <a href="admin/user/view?id='.$row->id.'" class="text-body pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp;</div>';
                }
                if ($sessionUser->hasPermission('admin/user/update')) {
                    $result['data'][$key]->action .= '
                    <a href="admin/user/update?id='.$row->id.'" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>';
                }
                if ($sessionUser->hasPermission('admin/user/delete')) {
                    $result['data'][$key]->action .= '
                    <button style=" border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/user/delete" data-id="'.$row->id.'" class="text-body" title="Delete"><i class="bx bxs-trash icon-base"></i></button>';
                }

            }

        }

        return $result;
    }

    public static function getCountryList()
    {
        return DB::table('country')
            ->select('id', 'name', 'sortname', 'phonecode')
            ->orderBy('name', 'asc')
            ->get();
    }
}
