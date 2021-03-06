<!-- Name -->
<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	<label for="name" class="control-label">{{ 'Name' }}</label>
	<input class="form-control" name="name" type="text" id="name" value="{{ isset($user->name) ? $user->name : ''}}" >
	{!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>

<!-- Email -->
<div class="form-group {{ $errors->has('email') ? 'has-error' : ''}}">
	<label for="email" class="control-label">{{ 'Email' }}</label>
	<input class="form-control" name="email" type="text" id="email" value="{{ isset($user->email) ? $user->email : ''}}" >
	{!! $errors->first('email', '<p class="help-block">:message</p>') !!}
</div>

<!-- Role -->
<div class="form-group {{ $errors->has('role') ? 'has-error' : ''}}">
	<label for="role" class="control-label">{{ 'Role' }}</label>
	<select class="form-control" name="role" id="role">
		<option value="">Role</option>
		<option {{ isset($user) && $user->role == Config::get('enum.user_roles')['admin'] ? 'selected' : ''}} value="{{ Config::get('enum.user_roles')['admin'] }}">Administrator</option>
		<option {{ isset($user) && $user->role == Config::get('enum.user_roles')['teacher'] ? 'selected' : ''}} value="{{ Config::get('enum.user_roles')['teacher'] }}">Teacher</option>
		<option {{ isset($user) && $user->role == Config::get('enum.user_roles')['student'] ? 'selected' : ''}} value="{{ Config::get('enum.user_roles')['student'] }}">Student</option>
	</select>
	{!! $errors->first('role', '<p class="help-block">:message</p>') !!}
</div>
