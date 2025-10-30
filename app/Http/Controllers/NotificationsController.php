<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\MasterfileModels\User;
use App\Models\Notifications;
use App\Models\TransactionModels\PaymentDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationsController extends Controller
{

    // public function index(Request $request)
    // {
    //     $notifications = Notifications::where('user_id', $request->user()->id)
    //         ->where('notified_at', '<', Carbon::now())
    //         ->orderBy('notified_at', 'desc')
    //         ->get();

    //     return response()->json($notifications);
    // }

    public function unreadNotifs(Request $request)
    {
        $this->index($request);

        $user = $request->user();

        // Get both the unread notifications AND the count
        $unreadNotifications = Notifications::where('user_id', $user->id)
            ->where('notified_at', '<=', Carbon::now())
            ->where('read', false)
            ->orderBy('notified_at', 'desc')
            ->count();


        return response()->json([
            'unread_count' => $unreadNotifications,
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        // 1. Get existing notifications
        $notifications = Notifications::where('user_id', $user->id)
            ->where('notified_at', '<=', Carbon::now())
            ->orderBy('notified_at', 'desc')
            ->get();

        // 2. Process payments for Admin/Accounting
        if (in_array($user->role, ['Admin', 'Accounting'])) {
            $this->syncPaymentNotifications($user, $today);
        }

        // 3. Return fresh notifications
        return response()->json(
            Notifications::where('user_id', $user->id)
                ->where('notified_at', '<=', Carbon::now())
                ->orderBy('notified_at', 'desc')
                ->get()
        );
    }

    protected function syncPaymentNotifications($user, $today)
    {
        // System-wide cache keys (not user-specific)
        $checkCacheKey = "system:last_check_count:$today";
        $whtCacheKey = "system:last_wht_count:$today";

        // Get current counts
        $currentChecks = PaymentDetails::where('payment_type', 'Check')
            ->where('status', 'floating')
            ->whereDate('due_date', '<=', $today)
            ->count();

        $currentWHT = PaymentDetails::where('payment_type', 'Creditable(WHT)')
            ->where('status', 'floating')
            ->count();

        // Compare with system state
        $lastChecks = cache()->get($checkCacheKey, 0);
        $lastWHT = cache()->get($whtCacheKey, 0);

        if ($currentChecks != $lastChecks) {
            $this->updateNotification(
                'Admin',
                'Accounting',
                "floating check payments due today/past due. Please check and verify.",
                "$currentChecks floating check payments due today/past due. Please check and verify."
            );
            cache()->put($checkCacheKey, $currentChecks, now()->addDay());
        }

        if ($currentWHT != $lastWHT) {
            $this->updateNotification(
                'Admin',
                'Accounting',
                "floating WHT payments need clearance. Please check and verify.",
                "$currentWHT floating WHT payments need clearance. Please check and verify."
            );
            cache()->put($whtCacheKey, $currentWHT, now()->addDay());
        }
    }

    protected function updateNotification($role1, $role2, $likemessage, $message)
    {
        // 1. Delete previous version if exists
        Notifications::where('message', 'LIKE', "%$likemessage%")
            ->whereDate('created_at', today())
            ->delete();

        // 2. Create new notification for all target users
        $userIds = User::whereIn('role', [$role1, $role2])
            ->pluck('id');

        foreach ($userIds as $id) {
            Notifications::create([
                'user_id' => $id,
                'message' => $message,
                'notified_at' => Carbon::now(),
                'read' => false
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'roles' => 'required|array',
            'roles.admin' => 'sometimes|boolean',
            'roles.invoicing' => 'sometimes|boolean',
            'roles.accounting' => 'sometimes|boolean',
            'roles.bookkeeper' => 'sometimes|boolean',
            'notified_at' => 'nullable|date'
        ]);

        // Get user IDs based on selected roles
        $userIds = collect();

        if ($validated['roles']['admin'] ?? false) {
            $userIds = $userIds->merge(
                User::where('role', 'Admin')->pluck('id')
            );
        }

        if ($validated['roles']['invoicing'] ?? false) {
            $userIds = $userIds->merge(
                User::where('role', 'Invoicing')->pluck('id')
            );
        }

        if ($validated['roles']['accounting'] ?? false) {
            $userIds = $userIds->merge(
                User::where('role', 'Accounting')->pluck('id')
            );
        }

        if ($validated['roles']['bookkeeper'] ?? false) {
            $userIds = $userIds->merge(
                User::where('role', 'Bookkeeper')->pluck('id')
            );
        }

        // Remove duplicates
        $userIds = $userIds->unique();

        // Create notifications for each user
        $createdNotifications = collect();
        $notifiedAt = Carbon::parse($validated['notified_at'])->format('Y-m-d H:i:s');

        foreach ($userIds as $userId) {
            $notification = Notifications::create([
                'user_id' => $userId,
                'message' => $validated['message'],
                'notified_at' => $notifiedAt,
                'read' => false
            ]);

            $createdNotifications->push($notification);

            // Broadcast to each user's private channel
            $channel = 'notification-update.' . Str::random(20);
            broadcast(new NotificationEvent(
                $userId,
                $channel,
            ));
        }

        return response()->json([
            'message' => 'Notifications created successfully',
            'notifications' => $createdNotifications,
            'recipient_count' => $userIds->count()
        ]);
    }


    public function markAsRead(Request $request, $id)
    {
        $notification = Notifications::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read' => true]);
        $channel = 'notification-update.' . Str::random(20);

        broadcast(new NotificationEvent(
            $request->user()->id,
            $channel
        ));

        return response()->json([
            'channel' => 'textfile-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        Notifications::where('user_id', $request->user()->id)
            ->where('read', false)
            ->update(['read' => true]);

        $channel = 'notification-update.' . Str::random(20);
        broadcast(new NotificationEvent(
            $request->user()->id,
            $channel
        ));
        return response()->json([
            'channel' => 'textfile-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'message' => 'Notification marked as read',
        ]);
    }


    public function clearAll(Request $request)
    {
        Notifications::where('user_id', $request->user()->id)->delete();

        $channel = 'notification-update.' . Str::random(20);
        broadcast(new NotificationEvent(
            $request->user()->id,
            $channel
        ));
        return response()->json([
            'channel' => 'textfile-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'message' => 'Notification marked as read',
        ]);
    }
}
