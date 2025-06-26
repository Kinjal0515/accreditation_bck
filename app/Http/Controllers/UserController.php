<?php

namespace App\Http\Controllers;

use App\Models\ApprovalHistory;
use App\Models\Category;
use App\Models\Company;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\ScanHistory;
use App\Models\User;
use App\Models\UserCard;
use App\Models\Zone;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{

    public function indexlist()
    {
        $loggedInUser = Auth::user();
        $date = Carbon::now()->format('Y-m-d');

        if ($loggedInUser->hasRole('Admin')) {

            $users = User::with(['roles', 'reportingUser'])->latest()->get();
        } else {
            $users = User::with(['roles', 'reportingUser'])
                ->where('reporting_user', $loggedInUser->id)
                ->latest()->get();
        }


        $allUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'contact' => $user->number,
                'email' => $user->email,
                'role_name' => $user->roles->pluck('name')->first(),
                'status' => $user->staus,
                'approval_status' => $user->approval_status,
                'reporting_user' => $user->reportingUser ? $user->reportingUser->name : null,
                'created_at' => $user->created_at,
                'authentication' => $user->authentication,

            ];
        });
        $organizers = User::role('Organizer')->get();
        $formattedUsers = $users->map(function ($user) {
            return [
                'value' => $user->id,
                'label' => $user->name,
                'number' => $user->number,
                'email' => $user->email,
                'role_name' => $user->roles->pluck('name')->first(),
            ];
        });
        $org = $organizers->map(function ($user) {
            return [
                'value' => $user->id,
                'label' => $user->name,
            ];
        });

        return response()->json(['status' => true, 'users' => $formattedUsers, 'allData' => $allUsers, 'organizers' => $org]);
    }

    public function index(Request $request)
    {
        $loggedInUser = Auth::user();
        $eventType = $request->type;

        // Determine date range
        // if ($eventType === 'all') {
        //     $startDate = null;
        //     $endDate = null;
        // } elseif ($request->has('date')) {
        //     $dates = explode(',', $request->date);
        //     if (count($dates) === 1 || ($dates[0] === $dates[1])) {
        //         $startDate = Carbon::parse($dates[0])->startOfDay();
        //         $endDate = Carbon::parse($dates[0])->endOfDay();
        //     } elseif (count($dates) === 2) {
        //         $startDate = Carbon::parse($dates[0])->startOfDay();
        //         $endDate = Carbon::parse($dates[1])->endOfDay();
        //     } else {
        //         return response()->json(['status' => false, 'message' => 'Invalid date format'], 400);
        //     }
        // } else {
        //     $startDate = Carbon::today()->startOfDay();
        //     $endDate = Carbon::today()->endOfDay();
        // }

        // Base query
        // if ($loggedInUser->hasRole('Admin')) {
        //     $query = User::with(['roles', 'reportingUser']);
        // } else {
        //     $query = User::with(['roles', 'reportingUser'])
        //         ->where('reporting_user', $loggedInUser->id);
        // }


        if ($loggedInUser->hasRole('Admin')) {
            $query = User::with(['roles', 'reportingUser']);
        } elseif ($loggedInUser->hasRole('Organizer')) {
            $query = User::with(['roles', 'reportingUser'])
                ->where(function ($q) use ($loggedInUser) {
                    $q->where('reporting_user', $loggedInUser->id)
                        ->orWhere('user_org_id', $loggedInUser->id);
                });
        } else {
            $query = User::with(['roles', 'reportingUser'])
                ->where('reporting_user', $loggedInUser->id);
        }




        // Apply date filter only if not "all"
        // if ($eventType !== 'all' && $startDate && $endDate) {
        //     $query->whereBetween('created_at', [$startDate, $endDate]);
        // }

        $users = $query->latest()->get();



        $allUsers = $users->map(function ($user) {
            $zoneIds = json_decode($user->zone, true);

            $zoneIds = json_decode($user->zone, true);

            $zoneData = collect();
            if (is_array($zoneIds) && count($zoneIds) > 0) {
                $zoneData = Zone::whereIn('id', $zoneIds)->get(['id', 'title']);
            }
            return [
                'id' => $user->id,
                'name' => $user->name,
                'contact' => $user->number,
                'email' => $user->email,
                'photo' => $user->photo,
                'photo_id' => $user->photo_id,
                'order_id' => $user->order_id,
                'role_name' => $user->roles->pluck('name')->first(),
                'status' => $user->status,
                'approval_status' => $user->approval_status,
                'reporting_user' => $user->reportingUser ? $user->reportingUser->name : null,
                'organisation' => $user->organisation,
                'created_at' => $user->created_at,
                'authentication' => $user->authentication,
                'company_name' => $user->userCompany->company_name ?? null,
                'organiser_name' => $user->reportingUser->name ?? null,
                'organiser_company_name' => $user->company->company_name ?? null,
                'user_company_name' => $user->userCompanyName->company_name ?? null,
                // 'user_org_name' => $user->userOrgName->name ?? null,
                'zoneData' => $zoneData ?? null,
            ];
        });

        $organizers = User::role('Organizer')->get();
        $formattedUsers = $users->map(function ($user) {
            return [
                'value' => $user->id,
                'label' => $user->name,
                'number' => $user->number,
                'email' => $user->email,
                'role_name' => $user->roles->pluck('name')->first(),
            ];
        });

        $org = $organizers->map(function ($user) {
            return [
                'value' => $user->id,
                'label' => $user->name,
                'company_name' => $user->organisation->company_name ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'users' => $formattedUsers,
            'allData' => $allUsers,
            'organizers' => $org
        ]);
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'number' => [
                    'required',
                    'string',
                    Rule::unique('users', 'number')->whereNull('deleted_at'),
                ],
            ], [
                'number.unique' => 'The mobile number has already been taken.',
            ]);
            if ($request->email) {

                $request->validate([
                    'email' => 'email|unique:users,email,NULL,id,deleted_at,NULL',
                    // 'email' => 'required|email|unique:users,email',
                ], [
                    'email.unique' => 'The email has already been taken.',
                ]);
            }

            // Additional validation for other fields
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email ?? $request->number . '@gyt.co.in';
            $user->number = $request->number;
            $user->comp_id = $request->comp_id;
            $user->org_id = $request->org_id;
            $user->user_org_id = $request->user_org_id;
            // $user->company_name = $request->company_name;
            $user->designation = $request->designation;
            $user->address = $request->address;
            // $user->organisation = $request->organisation;
            // $user->alt_number = $request->alt_number;
            $user->pincode = $request->pincode;
            $user->state = $request->state;
            $user->city = $request->city;
            // $user->bank_name = $request->bank_name;
            // $user->bank_number = $request->bank_number;
            // $user->bank_ifsc = $request->bank_ifsc;
            // $user->bank_branch = $request->bank_branch;
            // $user->bank_micr = $request->bank_micr;
            // $user->tax_number = $request->tax_number;
            $user->reporting_user = $request->reporting_user;
            $user->authentication = $request->authentication ? 1 : 0;
            // $user->agent_disc = $request->agent_disc;
            $user->status = true;
            $user->approval_status = 0;
            $user->password = Hash::make($request->password);

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                if ($file->isValid()) {
                    $folder = 'photo/' . str_replace(' ', '_', $request->name);
                    $filePath = $this->storeFile($file, $folder); // uses your storeFile method
                    $user->photo = $filePath;
                }
            }
            if ($request->hasFile('photoId')) {
                $file = $request->file('photoId');
                if ($file->isValid()) {
                    $folder = 'photoId/' . str_replace(' ', '_', $request->name);
                    $filePath = $this->storeFile($file, $folder); // uses your storeFile method
                    $user->photo_id = $filePath;
                }
            }

            $user->save();
            $userId = $user->id;
            $this->updateUserRole($request, $user);
            if ($request->role_name == 'Organizer') {
                $this->OrganizerStore($request, $userId);
            }
            if ($request->role_name == 'Company') {
                $this->CompanyStore($request, $userId);
            }

            return response()->json(['status' => true, 'message' => 'User Created Successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to create user', 'error' => $e->getMessage()], 500);
        }
    }

    private function updateUserRole($request, $user)
    {
        $defaultRoleName = 'User';
        if ($request->has('role_id') && $request->role_id) {
            $role = Role::find($request->role_id);
            if ($role) {
                $user->syncRoles([]);
                $user->assignRole($role);
            }
        } else {
            $defaultRole = Role::where('name', $defaultRoleName)->first();
            if ($defaultRole) {
                $user->syncRoles([]);
                $user->assignRole($defaultRole);
            }
        }
    }

    public function edit(string $id)
    {
        $allUser = User::all();
        $roles = Role::all();

        $user = User::with([
            'reportingUser',
            'company.category',
            'organisation',
            'userOrganisation',
            'roles'
        ])->where('id', $id)->firstOrFail();

        // Decode zones column
        $zoneIds = json_decode($user->zone, true);

        // Default empty collection
        $zoneIds = json_decode($user->zone, true); // safely decode

        $zoneData = collect();
        if (is_array($zoneIds) && count($zoneIds) > 0) {
            $zoneData = Zone::whereIn('id', $zoneIds)->get(['id', 'title']);
        }

        $userWithReportingUserNames = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'number' => $user->number,
            'address' => $user->address,
            'designation' => $user->designation,
            'pincode' => $user->pincode,
            'user_org_id' => $user->user_org_id,
            'state' => $user->state,
            'zones' => $zoneData, // zone id + name
            'city' => $user->city,
            'status' => $user->status,
            'photo' => $user->photo,
            'photo_id' => $user->photo_id,
            'org_id' => $user->org_id,
            'user_org' => $user->userOrganisation->name ?? null,
            'user_org_id' => $user->userOrganisation->user_id ?? null,
            'user_comp' => $user->userCompany->company_name ?? null,
            'user_comp_id' => $user->userCompany->org_id ?? null,
            'org_name' => $user->organisation->name ?? null,
            'org_gst_certificate' => $user->organisation->gst_certificate ?? null,
            'org_gst_no' => $user->organisation->gst_no ?? null,
            'org_company_name' => $user->organisation->company_name ?? null,
            'comp_id' => $user->comp_id,
            'company_name' => $user->company->company_name ?? null,
            'company_letter' => $user->company->company_letter ?? null,
            'category' => $user->company->category->title ?? null,
            'category_id' => $user->company->category->id ?? null,
            'role' => $user->roles->first(),
            'reporting_user_id' => $user->reportingUser->id ?? null,
            'shop' => $user->shop ?? null,
            'reporting_user' => $user->reportingUser->name ?? 'Admin User',
            'authentication' => $user->authentication,
        ];

        return response()->json([
            'status' => true,
            'user' => $userWithReportingUserNames,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, string $id)
    {
        try {
            // return response()->json($request->all());
            $user = User::findOrFail($id);
            $role = null;
            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->has('number')) {
                $user->number = $request->number;
            }
            if ($request->has('address')) {
                $user->address = $request->address;
            }
            if ($request->has('company_name')) {
                $user->company_name = $request->company_name;
            }
            if ($request->has('comp_id')) {
                $user->comp_id = $request->comp_id;
            }
            if ($request->has('user_org_id')) {
                $user->user_org_id = $request->user_org_id;
            }
            if ($request->has('org_id')) {
                $user->org_id = $request->org_id;
            }
            if ($request->has('designation')) {
                $user->designation = $request->designation;
            }

            if ($request->has('reporting_user')) {
                $user->reporting_user = $request->reporting_user;
            }

            if ($request->has('pincode')) {
                $user->pincode = $request->pincode;
            }

            if ($request->has('state')) {
                $user->state = $request->state;
            }

            if ($request->has('city')) {
                $user->city = $request->city;
            }


            if ($request->has('authentication')) {
                $user->authentication = $request->authentication ? 1 : 0;
            }


            if ($request->has('status')) {
                $user->status = $request->status;
            }
            if ($request->has('approval_status')) {
                $user->approval_status = $request->approval_status;
            }

            if ($request->has('zone')) {
                $zones = is_array($request->zone) ? $request->zone : json_decode($request->zone, true);
                $user->zone = json_encode($zones);
            }


            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                if ($file->isValid()) {
                    $folder = 'photo/' . str_replace(' ', '_', $user->name);
                    $filePath = $this->storeFile($file, $folder); // store and get full URL
                    $user->photo = $filePath;
                }
            }
            if ($request->hasFile('photoId')) {
                $file = $request->file('photoId');
                if ($file->isValid()) {
                    $folder = 'photoId/' . str_replace(' ', '_', $user->name);
                    $filePath = $this->storeFile($file, $folder); // store and get full URL
                    $user->photo_id = $filePath;
                }
            }

            if ($request->has('role_id') && $request->role_id) {
                $role = Role::find($request->role_id);

                if ($role) {
                    // Remove all current roles
                    $user->syncRoles([]);

                    // Assign the new role
                    $user->assignRole($role);
                }
            }
            if ($request->role_name == 'Organizer') {
                $this->OrganizerStore($request, $id);
            }

            if ($request->role_name == 'Company') {
                $this->CompanyStore($request, $id);
            }


            $user->save();

            return response()->json(['status' => true, 'message' => 'User Updated Successfully', 'role' => $role, 'user' => $user], 200);
        } catch (\Exception $e) {

            // Return an error response
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function CheckValidUser($id)
    {
        try {
            $user = User::where('id', $id)->with(['balance', 'pricingModel'])->get();
            $user->each(function ($user) {
                $user->latest_balance = $user->balance()->latest()->first();
                $user->pricing = $user->pricingModel()->latest()->first();
                unset($user->balance);
                unset($user->pricingModel);
            });
            $user_balance = $user[0]->latest_balance->total_credits ?? 00.00;
            $marketing_price = $user[0]->pricing->marketing_price;
            if ($user_balance < $marketing_price) {
                $user_balance = $user[0]->latest_balance->total_credits ?? 0;
                return response()->json(['status' => false, 'message' => 'insufficient credits', 'balance' => $user_balance]);
            } else {
                return response()->json(['status' => true, 'balance' => $user_balance]);
            }
        } catch (QueryException $e) {
            $errorMessage = $e->getMessage();
            return response()->json(['status' => false, 'message' => 'Query Exception: ' . $errorMessage]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            return response()->json(['status' => false, 'message' => 'An error occurred while processing the request.' . $errorMessage]);
        }
    }

    public function checkEmail(Request $request)
    {
        $emailExists = false;
        $mobileExists = false;
        $email = $request->input('email');
        $mobile = $request->input('number');

        // Start by checking if both email and mobile are provided
        $query = User::query()->select('id', 'name', 'email', 'number', 'photo', 'doc', 'company_name', 'designation');

        if ($mobile) {
            $query->orWhere('number', $mobile);
        }

        if ($email) {
            $query->orWhere('email', $email);
        }

        $user = $query->first();

        if ($user) {
            // Check if email exists for the matched user
            if ($email && $user->email == $email) {
                $emailExists = true;
            }

            // Check if mobile exists for the matched user
            if ($mobile && $user->number == $mobile) {
                $mobileExists = true;
            }

            // Handle case where email and mobile belong to different users
            $isEmailAndMobileFromDifferentUsers = false;
            if ($email && $mobile) {
                $otherUser = User::where('email', $email)->first();
                $otherMobileUser = User::where('number', $mobile)->first();

                if ($otherUser && $otherMobileUser && $otherUser->id != $otherMobileUser->id) {
                    $isEmailAndMobileFromDifferentUsers = true;
                }
            }

            return response()->json([
                'exists' => true,
                'message' => 'User exists',
                'email_exists' => $emailExists,
                'mobile_exists' => $mobileExists,
                'is_email_and_mobile_different_users' => $isEmailAndMobileFromDifferentUsers,
                'user' => $user
            ]);
        } else {
            return response()->json([
                'exists' => false,
                'message' => 'Both email and mobile are available'
            ]);
        }
    }

    public function checkMobile(Request $request)
    {
        $mobile = $request->input('number');
        $user = User::where('number', $mobile)->first();

        if ($user) {
            if (empty($user->email)) {
                return response()->json(['status' => true, 'message' => 'No email exists for this number.']);
            } else {
                return response()->json(['status' => false, 'message' => 'Email exists for this number.']);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Number not found in the users table.']);
        }
    }

    public function UpdateUserSecurity(Request $request)
    {
        try {
            $user = User::where('id', $request->id)->firstOrFail();

            $user->ip_auth = $request->ip_auth == true ? 'true' : 'false';
            $user->two_fector_auth = $request->two_fector_auth == true ? 'true' : 'false';
            $user->ip_addresses = $request->ip_addresses;
            $user->save();
            return response()->json(['status' => true, 'message' => 'Security Method Updated Successfully', 'email' => $user->email]);
        } catch (QueryException $e) {
            $errorMessage = $e->getMessage();
            return response()->json(['status' => false, 'message' => 'Query Exception: ' . $errorMessage]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            return response()->json(['status' => false, 'message' => 'An error occurred while processing the request.' . $errorMessage]);
        }
    }

    public function checkPassword(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $password = $request->password;

        if (Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Password is correct, you are verified successfully'], 200);
        } else {
            return response()->json(['error' => 'Oops! Password is incorrect'], 401);
        }
    }

    public function updateAlerts(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id); // Assuming $userId is the ID of the user you want to update

            // Update the user attributes except for the password
            // $user->email_alerts = null;
            // $user->whatsapp_alerts = null;
            // $user->text_alerts = null;
            if ($request->email_alerts) {
                $user->email_alerts = $request->email_alerts;
            } else if ($request->whatsapp_alerts) {
                $user->whatsapp_alerts = $request->whatsapp_alerts;
            } else if ($request->text_alerts) {
                $user->text_alerts = $request->text_alerts;
            }
            $user->save();

            return response()->json(['status' => true, 'message' => 'User Updated Successfully'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error updating user: ' . $e->getMessage());

            // Return an error response
            return response()->json(['status' => false, 'message' => 'Failed to update user'], 500);
        }
    }

    public function getUsersByRole($role)
    {

        if ($role === 'Organizer') {
            $users = Role::where('name', 'Admin')->first()->users()->whereNull('deleted_at')->get();
        } elseif ($role === 'User' || $role === 'Company' || $role === 'Scanner') {
            $users = Role::where('name', 'Organizer')->first()->users()->whereNull('deleted_at')->get();
        } else {
            return response()->json(['error' => 'Invalid role'], 400);
        }

        $formattedUsers = $users->map(function ($user) {
            return [
                'value' => $user->id,
                'label' => $user->name,
            ];
        });

        return response()->json(['users' => $formattedUsers], 200);
    }

    public function destroy(string $id)
    {
        $userData = User::where('id', $id)->firstOrFail();
        if (!$userData) {
            return response()->json(['status' => false, 'message' => 'user not found'], 404);
        }

        $userData->delete();
        return response()->json(['status' => true, 'message' => 'user deleted successfully'], 200);
    }

    private function OrganizerStore($request, $userId)
    {

        try {
            $filePath = null;

            if ($request->hasFile('gstCertificate')) {
                $file = $request->file('gstCertificate');
                if ($file->isValid()) {
                    $folder = 'Organizer/gst_certificate/' . str_replace(' ', '_', $request->name);
                    $filePath = $this->storeFile($file, $folder);
                }
            }

            $organizerData = Organizer::updateOrCreate(
                ['user_id' => $userId], // Condition: if user_id exists, update; else create
                [
                    'name'            => $request->name,
                    'email'           => $request->email,
                    'number'          => $request->number,
                    'address'         => $request->address,
                    'gst_no'          => $request->gst_no,
                    'company_name'    => $request->organisation,
                    'gst_certificate' => $filePath // this will be null if not uploaded
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Organizer created or updated successfully',
                'data' => $organizerData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create/update organizer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function CompanyStore($request, $userId)
    {
        try {
            $filePath = null;

            if ($request->hasFile('companyLetter')) {
                $file = $request->file('companyLetter');
                if ($file->isValid()) {
                    $folder = 'company_letter/' . str_replace(' ', '_', $request->name);
                    $filePath = $this->storeFile($file, $folder);
                }
            }

            $created = Company::updateOrCreate(
                [
                    'company_name' => $request->organisation,
                    'org_id'       => $request->reporting_user,
                ],
                [
                    'user_id'       => $userId,
                    'name'          => $request->name,
                    'number'        => $request->number,
                    'email'         => $request->email,
                    'address'       => $request->address,
                    'gst_no'        => $request->gst_no,
                    'category_id'   => $request->category_id,
                    'company_letter' => $filePath,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Company created successfully',
                'data' => $created,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create/update company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function storeFile($file, $folder, $disk = 'public')
    {
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/' . $folder, $filename, $disk);
        return Storage::disk($disk)->url($path);
    }


    public function approvalUrl(Request $request, $id)
    {
        try {
            $status = $request->input('status');

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 404);
            }

            $user->approval_status = $status;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User status updated successfully.',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fatchCompany($org_id)
    {
        try {
            $company = Company::where('org_id', $org_id)->get();

            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company not found for the given organizer ID.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $company
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching company.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeApprovalHistory(Request $request)
    {
        try {
            $status = $request->status ? 1 : 0;

            $user = User::find($request->user_id);
            if ($user) {
                $user->approval_status = $status;
                if ($status == 1) {
                    $user->order_id = $this->generateRandomCode(); // Add order_id if approved
                }
                $user->save();
            }

            $approval = new ApprovalHistory();
            $approval->user_id = $request->user_id;
            $approval->approval_id = $request->approval_id;
            $approval->status = $status;

            $approval->description = $request->description;
            $approval->save();

            return response()->json([
                'status' => true,
                'message' => 'Approval history saved successfully.',
                'data' => $approval,
                'user' => $user->only(['id', 'name', 'email', 'number', 'approval_status', 'order_id'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save approval history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getImage($id)
    {
        try {
            $userId = $id;

            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 404);
            }

            $reportingUserId = $user->comp_id;
            $company = Company::findorFail($reportingUserId);
            $category = null;
            if ($company?->category_id) {
                $category = Category::find($company->category_id);
                $categoryImage = $category->background_image ?? null;
                return response()->json([
                    'status' => true,
                    'data' => $categoryImage,
                    'token' => $user->order_id,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found for the company.',
                    'data' => $company
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save category.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function imagesRetrive(Request $request)
    {

        $fullImagePath = $request->input('path');

        if (!$fullImagePath) {
            return response()->json(['error' => 'No image path provided'], 400);
        }
        $parsedUrl = parse_url($fullImagePath);
        if (isset($parsedUrl['host']) && $parsedUrl['host'] === parse_url(url('/'), PHP_URL_HOST)) {
            $relativePath = $parsedUrl['path'];
        } elseif (str_starts_with($fullImagePath, url('/'))) {

            $relativePath = str_replace(url('/'), '', $fullImagePath);
        } else {
            $relativePath = $fullImagePath;
        }

        $relativePath = urldecode(ltrim($relativePath, '/'));

        $absolutePath = public_path(ltrim($relativePath, '/'));

        if (!file_exists($absolutePath)) {
            return response()->json([
                'error' => 'Image not found',
                'path' => $absolutePath,
                'original_path' => $fullImagePath
            ], 404);
        }

        try {
            $fileContents = file_get_contents($absolutePath);
            $mimeType = mime_content_type($absolutePath);

            return response($fileContents, 200)
                ->header('Content-Type', $mimeType);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve image',
                'message' => $e->getMessage(),
                'path' => $absolutePath
            ], 500);
        }
    }

    public function storeUserCard(Request $request)
    {
        try {
            $filePath = null;

            if ($request->hasFile('card_url')) {
                $file = $request->file('card_url');
                if ($file->isValid()) {
                    $folder = 'CardUrl/' . str_replace(' ', '_', $request->name);
                    $filePath = $this->storeFile($file, $folder);
                }
            }
            $userCard = UserCard::updateOrCreate(
                ['user_id' => $request->user_id],
                ['card_url' => $filePath]
            );

            return response()->json([
                'status' => true,
                'message' => 'User card saved successfully.',
                'data' => $userCard
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save approval history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function scannerHistory(Request $request)
    {
        try {

            $scanHistory = new ScanHistory();
            $scanHistory->user_id = $request->user_id;
            $scanHistory->scan_time = $request->scan_time;
            $scanHistory->scanner_id = $request->scanner_id;

            $scanHistory->save();

            return response()->json([
                'status' => true,
                'message' => 'scan history saved successfully.',
                'data' => $scanHistory
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save scan history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateRandomCode($length = 8)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@$*';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function verifyCard(Request $request, $orderId)
    {
        try {
            $loggedInUser = Auth::user();

            $user = User::where('order_id', $orderId)
                ->with([
                    'roles',
                    'reportingUser.roles',
                    'reportingUser.reportingUser.roles'
                ])
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ]);
            }

            $roleName = $user->roles->pluck('name')->first();

            $result = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_number' => $user->number,
                'role' => $roleName,
            ];

            if ($roleName === 'Organizer') {
                $result['organizer_data'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'number' => $user->number,
                    'photo' => $user->photo,
                    'company' => $user->company_name,
                ];
            } elseif ($roleName === 'Company') {
                $company = Company::where('user_id', $user->id)->first();

                $result['company_data'] = $company ? [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'number' => $company->number,
                    'gst_no' => $company->gst_no,
                    'company_letter' => $company->company_letter,
                ] : null;
            } elseif ($roleName === 'User') {
                $companyUser = $user->reportingUser ?? null;
                $organizerUser = $companyUser?->reportingUser ?? null;

                $result['company_user'] = $companyUser ? [
                    'id' => $companyUser->id,
                    'name' => $companyUser->name,
                    'email' => $companyUser->email,
                    'role' => $companyUser->roles->pluck('name')->first(),
                    'company' => $companyUser->company ? [
                        'id' => $companyUser->company->id,
                        'company_name' => $companyUser->company->company_name,
                        'gst_no' => $companyUser->company->gst_no,
                    ] : null,
                ] : null;

                $result['organizer_user'] = $organizerUser ? [
                    'id' => $organizerUser->id,
                    'name' => $organizerUser->name,
                    'email' => $organizerUser->email,
                    'role' => $organizerUser->roles->pluck('name')->first(),
                    'company' => $organizerUser->organizerNew ? [
                        'id' => $organizerUser->organizerNew->id,
                        'company_name' => $organizerUser->organizerNew->company_name,
                        'gst_no' => $organizerUser->organizerNew->gst_no,
                    ] : null,
                ] : null;
            }

            return response()->json([
                'status' => true,
                'message' => 'Role-based data fetched successfully.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
