<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {

        // App::setLocale('en');


        $notifications = auth()->user()->notifications()->get();
        return response($notifications);
    }

    #unread notifications
    public function indexUnreadNotifications(Request $request)
    {
        $unread_notifications = auth()->user()->unreadNotifications()->get();
        return response($unread_notifications);
    }

    #mark notification as read
    public function markNotification(Request $request, $id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
        return response();
    }

    #mark all notifications as read
    public function markAllNotifications()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response();
    }

    #delete specific notification
    public function destroy(Request $request, $id)
    {
        auth()->user()->notifications()->where('id', $id)->delete();
        return response();
    }
}
