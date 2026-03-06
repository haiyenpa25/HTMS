<?php use App\Models\User; echo json_encode(User::where('email', 'superadmin@httlthanhmyloi.com')->first()->getRoleNames());
