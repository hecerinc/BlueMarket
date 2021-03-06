<div class="ui stackable grid">
	@if($project->isCollaborator(Auth::id()))
		<div class="right aligned sixteen wide column">
			<button type="button" class="ui button primary" onclick="showTaskModal('new')">New task</button>
		</div>
		@include('projects.tasks.form', ['name' => 'new'])
		@include('projects.tasks.form', ['name' => 'edit'])
	@endif
	<div class="sixteen wide column">
		@include('projects.tasks.index')
	</div>
</div>
@include('projects.tasks.details')

@push('js')
<script>
	@if($project->isStakeholder(Auth::id()))
		function getUserShowHref(id){
			const endpoint = "{{ action('UserController@show', ['id' => 1]) }}";
			return endpoint.substring(0, endpoint.length-1) + id;
		}

		function fillTaskDetailsModal(task) {
			const userEndpoint = "{{ action('UserController@show', ['id' => 1]) }}";
			let $taskDetails = $("#task-details");
			$("#task-edit-btn").data("taskid", task.id);
			$taskDetails.find("#task-title").text(task.title);
			if(task.assignee) {
				$taskDetails.find("#task-assignee").text(task.assignee.name);
				let assigneeHref = getUserShowHref(task.assignee.id);
				$taskDetails.find("#task-assignee").attr("href", assigneeHref);
				$taskDetails.find("#task-assignee").show();
				$taskDetails.find("#assignee-no-one").hide();
			}
			else {
				$taskDetails.find("#task-assignee").hide();
				$taskDetails.find("#assignee-no-one").show();
			}

			// status
			switch(task.task_status) {
				case {{ Config::get('enum.task_status')['todo'] }}:
					$taskDetails.find("#task-status p").text("To-do");
					$taskDetails.find("#task-status i").removeClass().addClass("small circle green icon");
					break;
				case {{ Config::get('enum.task_status')['in-progress'] }}:
					$taskDetails.find("#task-status p").text("In progress");
					$taskDetails.find("#task-status i").removeClass().addClass("small circle yellow icon");
					break;
				case {{ Config::get('enum.task_status')['closed'] }}:
					$taskDetails.find("#task-status p").text("Closed");
					$taskDetails.find("#task-status i").removeClass().addClass("small circle blue icon");
					break;
			}
			$taskDetails.find("#task-status p").data("taskstatus", task.task_status);

			// description
			$taskDetails.find("#task-description").text(task.description);

			// due date
			const deadline = task.deadline;
			const currentDatetimeUTC = Date.now();
			const deadlineDateUTC = new Date(deadline + "Z");
			const isOverdue = deadlineDateUTC < currentDatetimeUTC;
			$taskDetails.find("#task-due-date").text(task.deadline);
			// style due date
			if(isOverdue) {
				$taskDetails.find("#task-due-date").addClass("overdue");
			}
			else {
				$taskDetails.find("#task-due-date").removeClass("overdue");
			}

			// task opened details
			$taskDetails.find("#task-opened-date").text(task.created_at);
			$taskDetails.find("#task-opened-by").text(task.creator.name);
			let openedByHref = getUserShowHref(task.creator.id);
			$taskDetails.find("#task-opened-by").attr("href", openedByHref);

			// task closed details
			if(task.task_status == {{ Config::get('enum.task_status')['closed'] }}) { // task is closed
				$taskDetails.find("#task-closed-date").text(task.completed_date);
				$taskDetails.find("#task-closed-by").text(task.closed_by.name);
				let closedByHref = getUserShowHref(task.closed_by.id);
				$taskDetails.find("#task-closed-by").attr("href", closedByHref);
				$taskDetails.find("#task-closed-details").show();
			}
			else {
				$taskDetails.find("#task-closed-details").hide();
			}

			// format all datetimes to local
			$(".task-datetime").addClass("needs-localdatetime");
			utcToLocal();
		}

		function showTaskDetailsModal(taskId) {
			$.ajax({
				type: "GET",
				url: `/tasks/${taskId}`,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (task) {
					fillTaskDetailsModal(task);
					$("#task-details-modal").modal("show");
				}
			});
		}

		$(".task-list").click((e) => {
			if(!$(e.target).hasClass("task-title")) return;
			const taskId = $(e.target).closest(".task").data("taskid");
			showTaskDetailsModal(taskId);
		});

		/* format datetimes */
		renderDateTimeAgoOnce();
		utcToLocal();
	@endif
	@if($project->isCollaborator(Auth::id()))
		/* Form binding */
		let taskFields = { // shared fields
			title: ["empty", "maxLength[255]"],
			description: ["empty"],
			dueDate: ["empty"]
		};

		/* Task assignee dropdown */
		$("#new-task-assignee").dropdown();
		$("#edit-task-assignee").dropdown();

		$("#new-task-form").form({
			fields: taskFields,
			onSuccess: function(event) {
				event.preventDefault();
				handleSuccessfulTaskFormSubmitted("new");
			},
			onFailure: function() {
				return false;
			}
		});

		taskFields.status = ["minCount[1]"];

		$("#edit-task-form").form({
			fields: taskFields,
			onSuccess: function(event) {
				event.preventDefault();
				handleSuccessfulTaskFormSubmitted("edit");
			},
			onFailure: function() {
				return false;
			}
		});

		function showTaskModal(action) {
			$(`#${action}-task-form-modal`).modal("show");
		}

		function handleSuccessfulTaskFormSubmitted(action) {
			const $form = $(`#${action}-task-form`);

			// shared values
			let dueDate = $form.find("input[name=dueDate]").val();
			let isoDueDate = new Date(dueDate).toISOString();
			let assignee = $(`#${action}-task-assignee`).dropdown("get value");

			let data = {
				'title': $form.find("input[name=title]").val(),
				'dueDate': isoDueDate,
				'description': $form.find("textarea[name=description]").val(),
				'project': $form.find("input[name=project]").val(),
				'assignee_id': assignee > 0 ? assignee : null // send null if no one is assigned
			};

			// ajax set up
			let ajax = {
				method: 'POST',
				url: '/tasks',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				data: data,
				dataType: 'json',
				success: function (task) {
					taskToAdd = getTaskComponent(task, action);

					if(action == "edit") {
						$(".task").filter(`[data-taskid="${task.id}"]`).remove();
					}

					let taskList = "";

					switch(task.task_status) {
						case {{ Config::get('enum.task_status')['todo'] }}:
							taskList = "to-do";
							break;
						case {{ Config::get('enum.task_status')['in-progress'] }}:
							taskList = "in-progress";
							break;
						case {{ Config::get('enum.task_status')['closed'] }}:
							taskList = "closed";
							break;
					}

					$(`#${taskList}-tasks > h2`).after(taskToAdd);

					// format datetimes
					renderDateTimeAgoOnce();
					utcToLocal();

					clearTaskForm();
					$(".task-form-modal").modal("hide");
				},
				error: function () {
					$(".task-form-modal").modal("hide");
					$(`#${action}-task-form-error-modal`).modal("show");
				}
			};

			if(action === "edit") {
				ajax.method = 'PUT';
				let taskId = $form.find("input[name=task-id]").val();
				ajax.url = `/tasks/${taskId}`;
				ajax.data.task_status = $form.find("select[name=status]").val();
			}

			$.ajax(ajax);
		}

		function submitTaskForm(action) {
			$(`#${action}-task-form`).submit();
		}

		function getTaskComponent(task, action) {
			const id = task.id;
			const title = task.title;
			const createdAt = task.created_at;
			// get creatorId and creatorName from Auth
			let creatorId = {!! Auth::id() !!};
			let creatorName = "{!! Auth::user()->name !!}";
			if(action === "edit") {
				// get creatorId and creatorName from old component
				const $oldTask = $(`.task[data-taskid=${id}]`);
				const creatorUrl = $oldTask.find("a").attr("href");
				creatorId = creatorUrl.substring(creatorUrl.lastIndexOf("/") + 1);
				creatorName = $oldTask.find("a").text();
			}
			const deadline = task.deadline;

			// determine if the task is overdue
			const currentDatetimeUTC = Date.now();
			const deadlineDateUTC = new Date(deadline + "Z");
			const isOverdue = deadlineDateUTC < currentDatetimeUTC;

			let endpoint = "{{ action('UserController@show', ['id' => 1]) }}";
			endpoint = endpoint.substring(0, endpoint.length-1) + creatorId;

			let taskStatusColor = "";
			let taskStatusDetail = "";
			switch(task.task_status) {
				case {{ Config::get('enum.task_status')['todo'] }}:
					taskStatusColor = "green";
					taskStatusDetail = "To-do";
					break;
				case {{ Config::get('enum.task_status')['in-progress'] }}:
					taskStatusColor = "yellow";
					taskStatusDetail = "In progress";
					break;
				case {{ Config::get('enum.task_status')['closed'] }}:
					taskStatusColor = "blue";
					taskStatusDetail = "Closed";
					break;
			}

			const taskComponent = 	`<div class="task new-task" data-taskid="${id}">
										<div class="ui grid">
											<div class="column">
												<span title="${taskStatusDetail} task"><i class="small circle ${taskStatusColor} icon"></i></span>
											</div>
											<div class="fourteen wide column">
												<p class="task-title">${title}</p>
												<p>Opened <span class="needs-datetimeago" data-datetime="${createdAt}">${createdAt}</span> by <a href="${endpoint}">${creatorName}</a></p>
												<p class="task-due ${ isOverdue ? 'overdue' : '' }">Due <span class="needs-localdatetime">${deadline}</span></p>
											</div>
										</div>
									</div>`;

			return taskComponent;
		}

		function clearTaskForm() {
			$(".task-form-modal [name='title']").val("");
			$(".task-form-modal [name='dueDate']").val("");
			$(".task-form-modal [name='description']").val("");
		}

		/* Populate edit form with task data */
		$("#task-edit-btn").click((e) => {
			const $btn = $(e.target);
			const $taskDetails = $("#task-details");
			const id = $btn.data("taskid");
			const title = $taskDetails.find("#task-title").text();
			const dueDate = $taskDetails.find("#task-due-date").text();
			const status = $taskDetails.find("#task-status p").data("taskstatus");
			const description = $taskDetails.find("#task-description").text();
			const $form = $("#edit-task-form");
			$form.find("#edit-task-assignee").dropdown('restore defaults');
			if($taskDetails.find("#task-assignee").css("display") != "none") {
				let assigneeHref = $taskDetails.find("#task-assignee").attr("href");
				assigneeId = assigneeHref.substring(assigneeHref.lastIndexOf("/") + 1);
				$form.find("#edit-task-assignee").dropdown('set selected', assigneeId);
			}
			$form.find("input[name=task-id]").val(id);
			$form.find("input[name=title]").val(title);
			$form.find("input[name=dueDate]").val(dueDate);
			$form.find("select[name=status]").dropdown('set selected', status);
			$form.find("textarea[name=description]").val(description);
			showTaskModal('edit');
		});
	@endif
</script>
@endpush
