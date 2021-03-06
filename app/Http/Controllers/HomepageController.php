<?php

namespace App\Http\Controllers;

use App\Models\PostModel;
use App\Models\SubjectModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class HomepageController extends Controller
{
    protected $list_classes;
    public function __construct()
    {
        $this->list_classes = [
            'latest',
            'lớp 9',
            'lớp 8'
        ];
    }

    public function homepage()
    {
        $input['page'] = 'homepage';
        $data = $this->dataComponents($input);
        $data['list_buttons'] = $this->getSubjectData();
        $data['data_content'] = $this->getPostData($data['list_buttons']);
        $data['list_classes'] = $this->list_classes;
        if (Auth::check()) {
            $data['userLogin'] = true;
        }
        return view('homepage', $data);
    }

    public function getSubjectData()
    {
        $list_buttons = [];
        foreach ($this->list_classes as $class) {
            $index = array_search($class, $this->list_classes);
            $list_buttons[$index] = SubjectModel::where('class', $class)->limit(4)->get();
        }
        return $list_buttons;
    }

    public function getPostData($list_buttons)
    {
        $data_content = [];
        foreach ($this->list_classes as $class_name) {
            $index = array_search($class_name, $this->list_classes);
            $data_content[$index] = [];
            if ($class_name == 'latest') {
                $data_content[$index] = PostModel::with('user')
                    ->orderBy('posts.id', 'desc')
                    ->limit(6)
                    ->get();
            } else {
                $subject = $list_buttons[$index][0]->subject;
                $data_content[$index] = PostModel::with('user')
                    ->join('subjects', 'subjects.id', '=', 'subject_id')
                    ->orderBy('posts.id', 'desc')
                    ->where([
                        ['class', $class_name],
                        ['subject', $subject],
                    ])
                    ->limit(6)
                    ->get();
            }
        }
        return $data_content;
    }
}
