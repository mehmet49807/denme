<?php

namespace App\Http\Controllers\Admin;

use App\Support\PhotoVerification;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastPushJob;
use App\Models\AdminBroadcast;
use App\Models\AdminUserNote;
use App\Models\Message;
use App\Models\PremiumSubscription;
use App\Models\Referral;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Services\FcmPushService;
use App\Services\NotificationService;
use App\Services\PremiumPackagesService;
use App\Services\UserDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

// NOTE: Full file restored via FTP to production.
// GitHub copy is being repaired - see commit message.
// If this stub remains, re-sync from production FTP app/Http/Controllers/Admin/AdminPanelController.php

class AdminPanelController extends Controller
{
    // Placeholder restored - full implementation lives on server until full sync.
}
