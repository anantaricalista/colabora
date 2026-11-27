<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Tambah tugas';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<a class="small" href="<?php echo html_escape(site_url('projects/'.$project->id)); ?>">&larr; Kembali ke proyek</a>
		<h1 class="h4 mt-2 mb-3">Tambah tugas</h1>
		<p class="text-secondary">Proyek: <?php echo html_escape($project->name); ?></p>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('projects/'.$project->id.'/tasks/create'); ?>
			<div class="mb-3">
				<label class="form-label" for="title">Nama tugas</label>
				<input class="form-control" id="title" name="title" maxlength="180" required value="<?php echo html_escape(set_value('title')); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="description">Deskripsi (opsional)</label>
				<textarea class="form-control" id="description" name="description" rows="4" maxlength="10000"><?php echo html_escape(set_value('description')); ?></textarea>
			</div>
			<div class="mb-3">
				<label class="form-label" for="assignee_user_id">Penanggung jawab</label>
				<select class="form-select" id="assignee_user_id" name="assignee_user_id" required>
					<option value="">Pilih karyawan</option>
					<?php foreach ($employees as $employee): ?>
						<option value="<?php echo (int) $employee->id; ?>" <?php echo set_select('assignee_user_id', (string) $employee->id); ?>><?php echo html_escape($employee->name); ?><?php echo (int) $employee->id === (int) $this->session->userdata('user_id') ? ' (saya)' : ''; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="row">
				<div class="col-md-6 mb-3">
					<label class="form-label" for="urgency">Urgensi</label>
					<select class="form-select" id="urgency" name="urgency" required>
						<option value="normal" <?php echo set_select('urgency', 'normal', TRUE); ?>>Normal</option>
						<option value="urgent" <?php echo set_select('urgency', 'urgent'); ?>>Mendesak</option>
					</select>
				</div>
				<div class="col-md-6 mb-3">
					<label class="form-label" for="due_date">Tenggat waktu (opsional)</label>
					<input class="form-control" id="due_date" name="due_date" type="date" value="<?php echo html_escape(set_value('due_date')); ?>">
				</div>
			</div>
			<button class="btn btn-primary" type="submit" <?php echo empty($employees) ? 'disabled' : ''; ?>>Tambah tugas</button>
			<a class="btn btn-link" href="<?php echo html_escape(site_url('projects/'.$project->id)); ?>">Batal</a>
		<?php echo form_close(); ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
