<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = $project->name;
$this->load->view('layouts/header', array('page_title' => $page_title));
$total = (int) $project->task_count;
$completed = (int) $project->completed_count;
$progress = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
			<div>
				<a class="small" href="<?php echo html_escape(site_url('projects')); ?>">&larr; Semua proyek</a>
				<h1 class="h3 mt-2"><?php echo html_escape($project->name); ?></h1>
				<p class="text-secondary"><?php echo nl2br(html_escape($project->description ?: 'Belum ada deskripsi.')); ?></p>
			</div>
			<span class="badge <?php echo $project->status === 'complete' ? 'text-bg-success' : 'text-bg-primary'; ?>">
				<?php echo $project->status === 'complete' ? 'Selesai' : 'Aktif'; ?>
			</span>
		</div>
		<?php if ($success): ?>
			<div class="alert alert-success" role="status"><?php echo html_escape($success); ?></div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<div class="d-flex justify-content-between mb-1"><span>Progres tugas</span><strong><?php echo $progress; ?>%</strong></div>
		<div class="progress mb-2" role="progressbar" aria-label="Progres proyek" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
			<div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
		</div>
		<small class="text-secondary"><?php echo $completed; ?> dari <?php echo $total; ?> tugas selesai. Setiap tugas berbobot sama.</small>
		<?php if ($is_coordinator): ?>
			<div class="d-flex flex-wrap gap-2 mt-4">
				<?php if ($project->status === 'active'): ?>
					<a class="btn btn-primary" href="<?php echo html_escape(site_url('projects/'.$project->id.'/tasks/create')); ?>">Tambah tugas</a>
					<?php echo form_open('projects/'.$project->id.'/complete', array('class' => 'm-0')); ?>
						<button class="btn btn-outline-success" type="submit">Tandai proyek selesai</button>
					<?php echo form_close(); ?>
				<?php else: ?>
					<?php echo form_open('projects/'.$project->id.'/reopen', array('class' => 'm-0')); ?>
						<button class="btn btn-outline-primary" type="submit">Buka kembali proyek</button>
					<?php echo form_close(); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ($is_admin): ?>
			<hr>
			<form class="row g-2 align-items-end" method="post" action="<?php echo html_escape(site_url('projects/'.$project->id.'/coordinator')); ?>">
				<input type="hidden" name="<?php echo html_escape($this->security->get_csrf_token_name()); ?>" value="<?php echo html_escape($this->security->get_csrf_hash()); ?>">
				<div class="col-md-5">
					<label class="form-label" for="coordinator_user_id">Ganti koordinator</label>
					<select class="form-select" id="coordinator_user_id" name="coordinator_user_id" required>
						<?php foreach ($employees as $employee): ?>
							<option value="<?php echo (int) $employee->id; ?>" <?php echo (int) $employee->id === (int) $project->coordinator_user_id ? 'selected' : ''; ?>><?php echo html_escape($employee->name); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-auto">
					<button class="btn btn-outline-primary" type="submit">Perbarui koordinator</button>
				</div>
			</form>
		<?php endif; ?>
	</div>
</section>
<section class="card shadow-sm mt-4">
	<div class="card-body p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
			<h2 class="h5 mb-0">Tugas</h2>
			<small class="text-secondary">Urgensi hanya menunjukkan prioritas, bukan prasyarat pekerjaan.</small>
		</div>
		<?php if (empty($tasks)): ?>
			<p class="text-secondary mb-0">Belum ada tugas yang dapat ditampilkan.</p>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table table-striped align-middle">
					<thead><tr><th>Tugas</th><th>Penanggung jawab</th><th>Urgensi</th><th>Tenggat</th><th>Status</th><th>Aksi</th></tr></thead>
					<tbody>
					<?php foreach ($tasks as $task): ?>
						<tr>
							<td>
								<strong><?php echo html_escape($task->title); ?></strong>
								<?php if ($task->description): ?>
									<div class="small text-secondary"><?php echo nl2br(html_escape($task->description)); ?></div>
								<?php endif; ?>
							</td>
							<td><?php echo html_escape($task->assignee_name); ?></td>
							<td><?php echo $task->urgency === 'urgent' ? 'Mendesak' : 'Normal'; ?></td>
							<td><?php echo $task->due_date ? html_escape($task->due_date) : '—'; ?></td>
							<td><?php echo $task->status === 'complete' ? 'Selesai' : 'Belum selesai'; ?></td>
							<td>
								<?php if ($project->status === 'active' && ($is_coordinator || ($task->status === 'incomplete' && (int) $task->assignee_user_id === (int) $this->session->userdata('user_id')))): ?>
									<?php echo form_open('projects/tasks/'.$task->id.'/status', array('class' => 'm-0')); ?>
										<input type="hidden" name="status" value="<?php echo $task->status === 'complete' ? 'incomplete' : 'complete'; ?>">
										<button class="btn btn-sm <?php echo $task->status === 'complete' ? 'btn-outline-secondary' : 'btn-outline-success'; ?>" type="submit">
											<?php echo $task->status === 'complete' ? 'Buka kembali tugas' : 'Tandai selesai'; ?>
										</button>
									<?php echo form_close(); ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
