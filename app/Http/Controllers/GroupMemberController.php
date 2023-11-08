<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Illuminate\Support\Carbon; 
use Illuminate\Support\Facades\DB;


class GroupMemberController extends Controller
{
    /**
     * Display a listing of the members of the specified group.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
public function index(Group $group)
{
    $members = $group->groupMembers;

    
    return view('group_members.index', ['members' => $members, 'group' => $group]);
}

public function showActivities(Group $group, User $user)
{
    $activities = $user->activities()->where('reflect', 1)->get();
    return view('group_members.activities', ['activities' => $activities, 'user' => $user]);
}

public function showUserMonthActivities(User $user)
{
    $oneMonthAgo = Carbon::now()->subMonth(); // 現在から1か月前の日付を取得

    $results = DB::table('activities')
                 ->select(
                     'user_id', 
                     DB::raw('DATE(studied_at) as study_date'), 
                     DB::raw('COALESCE(SUM(duration), 0) as total_duration')
                 )
                 ->where('user_id', $user->id) // 特定のユーザーのデータに絞り込む
                 ->where('reflect', 1) // reflectカラムが1のレコードのみ取得
                 ->whereBetween('studied_at', [$oneMonthAgo, Carbon::now()]) // 過去1か月間のデータを取得
                 ->groupBy('user_id', 'study_date') // ユーザーIDと勉強日ごとにグループ化
                 ->orderBy('study_date', 'asc') // 日付で昇順にソート
                 ->get();

    return view('group_members.index_month', compact('results', 'user'));
}

public function showUserweekActivities(User $user)
{
    $oneWeekAgo = Carbon::now()->subweek(); 

    $results = DB::table('activities')
                 ->select(
                     'user_id', 
                     DB::raw('DATE(studied_at) as study_date'), 
                     DB::raw('COALESCE(SUM(duration), 0) as total_duration')
                 )
                 ->where('user_id', $user->id) // 特定のユーザーのデータに絞り込む
                 ->where('reflect', 1) // reflectカラムが1のレコードのみ取得
                 ->whereBetween('studied_at', [$oneWeekAgo, Carbon::now()]) 
                 ->groupBy('user_id', 'study_date') // ユーザーIDと勉強日ごとにグループ化
                 ->orderBy('study_date', 'asc') // 日付で昇順にソート
                 ->get();

    return view('group_members.index_week', compact('results', 'user'));
}


public function showUserActivitiesForToday(User $user)
{
    // 今日の日付を取得
    $today = Carbon::today();

    // 特定のユーザーと今日の日付に関連するアクティビティを取得
    $results = DB::table('activities')
                 ->select(
                     'user_id',
                     DB::raw('DATE(studied_at) as study_date'),
                     DB::raw('COALESCE(SUM(duration), 0) as total_duration')
                 )
                 ->where('user_id', $user->id)
                 ->where('reflect', 1)
                 ->whereDate('studied_at', $today)
                 ->groupBy('user_id', 'study_date')
                 ->get();

    // ビューにデータを渡して表示
    return view('group_members.index_day', compact('results', 'user'));
}


}