
@extends('layouts.app')

@section('title', 'Users')

@section('content')
	<div class="padded content">
		<h1>Users</h1>
		<div class="container">
			<div class="row">
				{{-- @include('admin.sidebar') --}}

				<div class="col-md-9">
					<div class="card">
						<div class="card-body">
							<a href="{{ url('/users/create') }}">
								<button class="ui primary button" title="Add New User">New</button>
							</a>

							<div class="ui search">
								<div class="ui icon input">
									<input class="prompt" type="text" placeholder="Search users" value="{{ request('search') }}">
									<i class="search icon"></i>
								</div>
								<div class="results"></div>
							</div>

							{{-- <form method="GET" action="{{ url('/users') }}" accept-charset="UTF-8" class="form-inline my-2 my-lg-0 float-right" role="search">
								<div class="input-group">
									<input type="text" class="form-control" name="search" placeholder="Search..." value="{{ request('search') }}">
									<span class="input-group-append">
										<button class="btn btn-secondary" type="submit">
											<i class="fa fa-search"></i>
										</button>
									</span>
								</div>
							</form> --}}

							<div class="table-responsive">
								<table class="ui single line selectable table">
									<thead>
										<tr>
											<th>Name</th>
											<th>Role</th>
											<th>Email</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
									@foreach($users as $item)
										<tr>
											<td class="selectable">
												<a href="{{ url('/users/' . $item->id) }}" title="View User">
													{{ $item->name }}
												</a>
											</td>

											<td class="selectable">
												<a href="{{ url('/users/' . $item->id) }}" title="View User">
													<!-- Aquí tengo que confirmar cómo me van a pasar el rol -->
													@switch($item->role)
														@case(1)
														<span class="tag student">Student</span>
														@break

														@case(2)
														<span class="tag teacher">Teacher</span>
														@break

														@case(3)
														<span class="tag admin">Administrator</span>
														@break

														@default
														<span class="tag student">Student</span>
													@endswitch
												</a>
											</td>

											<td class="selectable">
												<a href="{{ url('/users/' . $item->id) }}" title="View User">
													{{ $item->email }}
												</a>
											</td>

											<td>
												<a href="{{ url('/users/' . $item->id . '/edit') }}" title="Edit user">
													<button class="ui primary basic button btn btn-primary btn-sm">
													<i class="fa fa-pencil-square-o" aria-hidden="true"></i>Edit
													</button>
												</a>
											</td>
										</tr>
									@endforeach
									</tbody>
								</table>
								<div class="pagination-wrapper"> {!! $users->appends(['search' => Request::get('search')])->render() !!} </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
