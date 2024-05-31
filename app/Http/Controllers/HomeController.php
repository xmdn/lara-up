<?php

namespace App\Http\Controllers;

use App\Leaderboard;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private $leaderboard = null;
    public function __construct()
    {
        $this->leaderboard = new Leaderboard();
    }

    public function index() {
        $topDesigner = $this->leaderboard->with(['user:id,tagname,first_name,last_name,photo_url,email', 'user.finished_project:id,user_id,project_id'])->orderBy('points', 'desc')->where('expertise', 'UI/UX Designer')->first();
        $topFrontend = $this->leaderboard->with(['user:id,tagname,first_name,last_name,photo_url,email', 'user.finished_project:id,user_id,project_id'])->orderBy('points', 'desc')->where('expertise', 'Frontend Engineer')->first();
        $topBackend = $this->leaderboard->with(['user:id,tagname,first_name,last_name,photo_url,email', 'user.finished_project:id,user_id,project_id'])->orderBy('points', 'desc')->where('expertise', 'Backend Engineer')->first();
        $topData = $this->leaderboard->with(['user:id,tagname,first_name,last_name,photo_url,email', 'user.finished_project:id,user_id,project_id'])->orderBy('points', 'desc')->where('expertise', 'Data Expert')->first();

        $projectByStatusCount = Project::select('status', DB::raw('count(*) as total'))->groupBy('status')->get()->groupBy('status');
        $projectByStatusCounts = [
            'hiring' => isset($projectByStatusCount['Hiring']) ? $projectByStatusCount['Hiring'][0]['total'] : 0,
            'ongoing' => isset($projectByStatusCount['Ongoing']) ? $projectByStatusCount['Ongoing'][0]['total'] : 0,
            'finished' => isset($projectByStatusCount['Finished']) ? $projectByStatusCount['Finished'][0]['total'] : 0,
        ];

        return response()->json([
            'top_boards' => [
                'ui_ux_designer' => $topDesigner,
                'frontend_engineer' => $topFrontend,
                'backend_engineer' => $topBackend,
                'data_expert' => $topData
            ],
            'project_count' => $projectByStatusCounts
        ]);
    }
}
