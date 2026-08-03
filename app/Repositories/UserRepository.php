<?php

namespace App\Repositories;

use App\Helpers\General;
use App\Helpers\Pagination;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserRepository
{
    public function findById(string|int $id): ?User
    {
        return User::find($id);
    }

    public function listAdmin(array $postData): array
    {
        $userObj = new User;

        $query = DB::table('users')->select('*')->whereIn('role', $userObj->adminRole);
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $searchText = '%'.$searchText.'%';
            $query->where(function ($query) use ($searchText) {
                $query->whereRaw("concat(users.first_name,' ' ,users.last_name) like ?", $searchText)
                    ->orWhere('email', 'like', $searchText);
            });
        }

        $result = (new Pagination)->getDataTable($query, $postData);
        $sessionUser = auth()->user();
        $general = new General;
        foreach ($result['data'] as $key => $row) {
            $imageUrl = $general->getFileUrl($row->image, 'profile');
            if ($row->image) {
                $result['data'][$key]->image = '<a href="upload/profile/'.$row->image.'" data-toggle="lightbox" data-title="Image" class = "noroute pjax" target = "_blank">
                <img style="width:30px;height:30px" src="'.$imageUrl.'" class="h-auto rounded-circle" alt="blog image"></a>';
            }
            $result['data'][$key]->first_name = $row->first_name.' '.$row->last_name;
            $result['data'][$key]->permission = $row->permission;
            $result['data'][$key]->status = $userObj->getStatusBadge($row->status);
            $result['data'][$key]->updated_at = $general->dateFormat($row->updated_at);

            if (auth()->user()->role == 0) {
                $result['data'][$key]->action = '<div class="act-btns">
                <a href="admin/admin/view?id='.$row->id.'" class="text-body pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp
                <a href="admin/admin/update?id='.$row->id.'" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>
                <button style=" border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/admin/delete" data-id="'.$row->id.'" class="text-body pjax" title="Delete"><i class="bx bxs-trash icon-base"></i></button></div>';
            } else {
                $result['data'][$key]->action = '';
                if ($sessionUser->hasPermission('admin/admin/view')) {
                    $result['data'][$key]->action .= '
                    <a href="admin/admin/view?id='.$row->id.'" class="text-body  pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp';
                }
                if ($sessionUser->hasPermission('admin/admin/update')) {
                    $result['data'][$key]->action .= '
                    <a href="admin/admin/update?id='.$row->id.'" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>';
                }
                if ($sessionUser->hasPermission('admin/admin/delete')) {
                    $result['data'][$key]->action .= '
                    <button style=" border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/admin/delete" data-id="'.$row->id.'" class="text-body" title="Delete"><i class="bx bxs-trash icon-base"></i></button>';
                }
            }
        }

        return $result;
    }

    public function list(array $postData): array
    {
        $userObj = new User;

        $query = DB::table('users')
            ->select('users.*')
            ->whereIn('users.role', $userObj->userRole)
            ->where('users.id', '!=', auth()->user()->id);

        $searchText = $postData['search']['value'] ?? '';
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

        $result = (new Pagination)->getDataTable($query, $postData);
        $sessionUser = auth()->user();
        $general = new General;
        foreach ($result['data'] as $key => $row) {
            $imageUrl = $general->getFileUrl($row->image, 'profile');
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

    public function storeAdmin(array $postData): array
    {
        $general = new General;
        $id = $postData['id'] ?? null;
        $existingPassword = $postData['pass'] ?? null;

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'phone' => 'required|digits:10|numeric',
            'status' => 'required|boolean',
            'permission' => 'required|array',
            'role' => 'required|string|max:255',
        ];

        if (! $id) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }

        $model = $id ? User::find($id) : new User;

        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'profile');
            if (! $uploadResult['status']) {
                return $uploadResult;
            }
            $image = $uploadResult['file_name'];
            if ($image) {
                if ($model->image) {
                    $general->deleteFile($model->image, 'profile');
                }
                $model->image = $image;
            }
        }

        $model->first_name = $postData['first_name'];
        $model->last_name = $postData['last_name'];
        $model->email = $postData['email'];
        $model->phone = $postData['phone'];
        $model->country = $postData['country'] ?? null;
        $model->status = (bool) $postData['status'];
        $model->permission = implode(',', $postData['permission']);
        $model->role = $postData['role'];
        $model->registered_ip = $general->getClientIp();

        $service = new AuthService;
        $model->password = ! empty($postData['password'])
            ? $service->encryptPassword($postData['password'])
            : $existingPassword;

        $model->save();
        $message = $id ? 'Admin updated successfully.' : 'Admin created successfully.';

        return [
            'status' => 1,
            'message' => $message,
            'next' => 'load',
            'url' => 'admin/admin',
        ];
    }

    public function store(array $postData): array
    {
        $general = new General;
        $id = $postData['id'] ?? null;
        $pass = $postData['pass'] ?? null;

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'phone' => 'required|digits:10|numeric',
            'status' => 'required|boolean',
        ];

        if (! $id) {
            $rules['email'] .= '|unique:users';
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }

        $model = $id ? User::find($id) : new User;

        if (isset($postData['image']) && $postData['image']->isValid()) {
            $uploadResult = $general->uploadFile($postData['image'], 'profile');
            if (! $uploadResult['status']) {
                return $uploadResult;
            }
            $image = $uploadResult['file_name'];
            if ($image) {
                if ($model->image) {
                    $general->deleteFile($model->image, 'profile');
                }
                $model->image = $image;
            }
        }

        $model->first_name = $postData['first_name'];
        $model->last_name = $postData['last_name'];
        $model->email = $postData['email'];
        $model->phone = $postData['phone'];
        $model->country = $postData['country'] ?? null;
        $model->status = $postData['status'];
        $model->role = $postData['role'];
        $model->registered_ip = $general->getClientIp();

        if (! empty($postData['password'])) {
            $model->password = (new AuthService)->encryptPassword($postData['password']);
        } else {
            $model->password = $pass;
        }

        $model->save();

        return [
            'status' => 1,
            'message' => $id ? 'User updated successfully.' : 'User created successfully.',
            'next' => 'load',
            'url' => 'admin/user',
        ];
    }
}
