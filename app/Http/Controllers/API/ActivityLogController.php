<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Get all activity logs (only for Super Admin).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Check if user is Super Admin (role_id = 1)
        if (!$user || $user->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = (int) $request->input('per_page', 50);
        $search = $request->input('search');
        $logName = $request->input('action'); // Using 'action' for compatibility

        $query = Activity::with(['causer'])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($logName) {
            // Filter by description content based on action type
            $query->where(function ($builder) use ($logName) {
                switch ($logName) {
                    case 'login':
                        $builder->where('description', 'like', '%logged in%')
                            ->orWhere('description', 'like', '%login%');
                        break;
                    case 'logout':
                        $builder->where('description', 'like', '%logged out%')
                            ->orWhere('description', 'like', '%logout%');
                        break;
                    case 'insert':
                        $builder->where('description', 'like', '%created%')
                            ->orWhere('description', 'like', '%registered%')
                            ->orWhere('description', 'like', '%insert%');
                        break;
                    case 'update':
                        $builder->where('description', 'like', '%updated%')
                            ->orWhere('description', 'like', '%update%');
                        break;
                    case 'delete':
                        $builder->where('description', 'like', '%deleted%')
                            ->orWhere('description', 'like', '%delete%');
                        break;
                    case 'view':
                        $builder->where('description', 'like', '%view%')
                            ->orWhere('description', 'like', '%accessed%');
                        break;
                }
            });
        }

        // If per_page is 0, return all records without pagination
        if ($perPage === 0) {
            $logs = $query->get();
            return response()->json([
                'data' => $logs,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $logs->count(),
                'total' => $logs->count(),
            ]);
        }

        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }
}

