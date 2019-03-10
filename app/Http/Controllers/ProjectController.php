<?php

namespace App\Http\Controllers;

use App\Project;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProjectController extends Controller {

	public function __construct() {
		$this->middleware('auth');
	}

	public function index() {
		$projects = Project::all();
		return view('projects', compact('projects'));
	}

	public function show($id) {
		return view('projects.show', ['project' => Project::findOrFail($id)]);
	}

	public function create()
	{
		return view('registerProject', [
			'courses' => Auth::user()->EnrolledIn,
			'categories' => Tag::where('type', 2)->get(),
			'skillsets' => Tag::where('type', 1)->get()
		]);
	}

	public function store(Request $request) {
		$attributes = request()->validate([
			'projectName' => ['required'],
			'videoPitch' => ['present'],
			'longDescription' => ['present'],
			'shortDescription'=> ['present'],
			'projectImage'=> ['present'],
			'category'=>['present', Rule::in(Tag::where('type', 2)->pluck('id'))],
			'skillsets' => 'present|array|min:1',
			'skillsets.*' => [
				'integer',
				Rule::in(Tag::where('type', 1)->pluck('id')),
			]
		]);

		$project = $this->saveRecord($attributes);
		if (!isset($project)) {
			abort(500);
		}

		if (isset($attributes['skillsets'])) {
			$project->tags()->attach($attributes['skillsets']);
		}
		if (isset($attributes['category'])) {
			$project->tags()->attach($attributes['category']);
		}

		return view('projects', ['project' => $project]);
	}

	private function saveRecord(array $attributes)
	{
		return Project::create([
			'name' => $attributes['projectName'],
			'video' => $attributes['videoPitch'],
			'long_description' => $attributes['longDescription'],
			'short_description' => $attributes['shortDescription'],
			'photo' => $attributes['projectImage']
		]);
	}
}
