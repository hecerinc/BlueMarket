@extends('layouts.app')

@section('title', 'Add Project')

@section('content')

@section('header', 'Register a new project')
<style>
	.imgUploader {
		margin-bottom: 20px;
	}

</style>
<div class="padded content">
	<h1>Create new project</h1>
	@if($errors->any())
		<div class="ui error message">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif
	<form class="ui form {{ $errors->any() ? 'error': '' }}" method="POST" action="/projects">
		@csrf
		<!-- Project Image -->
		<div class="projectImage field {{ $errors->has('projectImage') ? 'error': '' }}">
			<label for="projectImage">Project image</label>
			<div class="projectImageUploaderContainer">
				<div class="imgUploader" class="imagePreviewContainer">
					{{-- <img src="https://lorempixel.com/400/400" alt="Project image" class="ui medium image" id="projectImagePreview"/> --}}
				</div>
				<button class="imgUploader upload-image-button ui button primary" type="button">Upload image</button>
			</div>
			<input id="imgInput" name="projectImage" type="file" style="display:none" accept="image/x-png,image/jpeg,image/png" onchange="updateImage(this)">
		</div>
		<button class="ui button primary" onclick="fillDummy()" type="button">Fill with dummy data </button>
		<!-- Project name -->
		<div class="field {{ $errors->has('projectName') ? 'error': '' }}">
			<label for="projectName">Project name</label>
			<input type="text" name="projectName" placeholder="e.g. Best Project">
		</div>
		<!-- Associated course -->
		<div class="field {{ $errors->has('courses') ? 'error': '' }}">
			<label for="course">Associated course</label>
			<select name="course" id="course" class="ui search dropdown">
				<option value="">e.g. Web development class</option>
				@if(isset($courses))
					@foreach($courses->all() as $course)
						<option value="{{ $course->id }}">{{ $course->name }}</option>
					@endforeach
				@endif
			</select>
		</div>
		<!-- Associated Team -->
		<div class="field">
			<label for="teamName">Associated Team</label>
			<div class="fields">
				<div class="seven wide field">
					<select name="teamId" class="ui search dropdown" value="">
						<option value="">Associate an existing team</option>
						@if(isset($teams))
							@foreach($teams->all() as $team)
								<option value="{{$team->id}}">{{$team->name}}</option>
							@endforeach
						@endif
					</select>
				</div>
				<div class="two wide field">
					<p style="font-weight: bold; font-size: 2rem; color: black; text-align: center;">OR</p>
				</div>
				<div class="seven wide field">
					<input type="text" name="createTeam" placeholder="New team name">
				</div>
			</div>
		</div>
		<!-- Labels -->
		<div class="field {{ $errors->has('label') ? 'error': '' }}">
			<label for="label">Labels</label>
			<select name="labels[]" id="label" multiple class="ui search dropdown">
				<option value="">e.g. Finance</option>
				@if(isset($labels))
					@foreach($labels->all() as $label)
						<option value="{{ $label->id }}">{{ $label->name }}</option>
					@endforeach
				@endif
			</select>
		</div>
		<!-- Skillset -->
		<div class="field {{ $errors->has('skillsets') ? 'error': '' }}">
			<label for="skillsets">Skillset</label>
			<select id="skillsets" name="skillsets[]" multiple class="ui fluid dropdown">
				<option value="">e.g. Java, HTML</option>
				@if(isset($skillsets))
					@foreach($skillsets->all() as $skillset)
						<option value="{{ $skillset->id }}">{{ $skillset->name }}</option>
					@endforeach
				@endif
			</select>
		</div>
		<!-- Milestone -->
		<div class="field {{ $errors->has('milestone') ? 'error': '' }}">
			<label for="milestone">Public milestone</label>
			<input type="text" name="milestone" placeholder="e.g. Design">
		</div>
		<!-- Short description -->
		<div class="field {{ $errors->has('shortDescription') ? 'error': '' }}">
			<label for="shortDescription">Brief description</label>
			<textarea name="shortDescription" rows="2" placeholder="e.g. The project is a web page for personal financial organization"></textarea>
		</div>
		<!-- Long description -->
		<div class="field {{ $errors->has('longDescription') ? 'error': '' }}">
			<label for="longDescription">Detailed description</label>
			<textarea name="longDescription" placeholder="e.g. The project consists of a single page application where users can sign up or login. It includes..."></textarea>
		</div>
		<!-- Pitch video -->
		<div class="field {{ $errors->has('videoPitch') ? 'error': '' }}">
			<label for="videoPitch">Pitch video</label>
			<input type="text" name="videoPitch" placeholder="e.g. https://youtube.com/watch?v=238028302">
		</div>
		<!-- Register button -->
		<button id="registerButton" type="submit" class="ui primary submit button">Register project</button>
		<!-- Error message -->
		<div class="ui error message"></div>
	</form>
</div>
@section('scripts')
<script>
	/* Upload new image */
	$(".imgUploader").click(function(event) {
		event.preventDefault();
		$("#imgInput").click();
	});
	function validateImage(file) {
		const maxImageSize = 1000000; // 1MB
		if((file.type == "image/png" || file.type == "image/jpg") && file.size <= maxImageSize) {
			return true;
		}
		else {
			return false;
		}
	}
	function fillDummy() {
		$('input[name=projectName]').val('Some project title');
		$("select[name=courses]").val('1');
		$('input[name=createTeam]').val('Some team name');
		$('#label').dropdown('set selected', ['1','2','3']);
		let skillsets = $('#skillsets option').slice(1,5).toArray().map(t => t.value);
		$('#skillsets').dropdown('set selected', skillsets);
		$('input[name=milestone]').val('Some milestone');
		$('textarea[name=shortDescription]').val('This is the short description');
		$('textarea[name=longDescription]').val('This is the long description');
		$('input[name=videoPitch]').val('https://www.youtube.com/watch?v=QsaNaZy3SSA');
	}
	function updateImage(imageInput) {
		let reader = new FileReader();
		let file = imageInput.files[0];
		let preview = $("#projectImagePreview");
		reader.addEventListener("load", function() {
			if(validateImage(file)) {
				$("#projectImagePreview").attr("src", reader.result);
			}
		});
		if(file) {
			reader.readAsDataURL(file);
		}
	}
	/* Register button validation */
	$('.ui.dropdown').dropdown();
	$('.ui.form').form({
		fields: {
			// projectImage: {
			// 	identifier:'projectImage',
			// 	rules:[{
			// 			type:'empty',
			// 			prompt:'Please enter a project image'
			// 		}
			// 	]
			// },
			projectName:{
				identifier:'projectName',
				rules:[{
						type:'empty',
						prompt:'Please enter a project name'
					}
				]
			},
			// teamName:{
			// 	identifier:'teamName',
			// 	rules:[{
			// 			type:'empty',
			// 			prompt:'Please select a team name'
			// 		}
			// 	]
			// },
			// courses:{
			// 	identifier:'courses',
			// 	rules:[{
			// 			type:'minCount[1]',
			// 			prompt:'Please select an associated course'
			// 		}
			// 	]
			// },
			label:{
				identifier:'label[]',
				rules:[{
						type:'minCount[1]',
						prompt:'Please select at least one label'
					}
				]
			},
			skillsets:{
				identifier:'skillsets[]',
				rules:[{
						type:'minCount[1]',
						prompt:'Please select at least one skillset'
					}
				]
			},
			milestone:{
				identifier:'milestone',
				rules:[{
						type:'empty',
						prompt:'Please enter current milestone'
					}
				]
			},
			shortDescription:{
				identifier:'shortDescription',
				rules:[{
						type:'empty',
						prompt:'Please enter a brief project description'
					}
				]
			},
			longDescription:{
				identifier:'longDescription',
				rules:[{
						type:'empty',
						prompt:'Please enter a detailed project description'
					}
				]
			},
			videoPitch:{
				identifier:'videoPitch',
				rules:[{
						type:'empty',
						prompt:'Please enter a link to pitch video'
					},
					{
						type:'regExp',
						value:'/^((http(s)?:\\/\\/)?)(www\\.)?((youtube\\.com\\/)|(youtu.be\\/))[\\S]+$/',
						prompt:'Please enter a valid youtube url'
					}
				]
			}
		},
		onFailure:function() {
			return false;
		},
		onSuccess:function() {
		}
	});
	/* Associated Team Validation */


</script>
@endsection
@endsection
