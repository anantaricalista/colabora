<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Buat proyek';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<h1 class="h4 mb-3">Buat proyek</h1>
		<p class="text-secondary">Proyek langsung aktif. Koordinator dapat membuat tugas dan menetapkan penanggung jawab.</p>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('projects/create'); ?>
			<div class="mb-3">
				<label class="form-label" for="name">Nama proyek</label>
				<input class="form-control" id="name" name="name" maxlength="180" required value="<?php echo html_escape(set_value('name')); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="description">Deskripsi (opsional)</label>
				<textarea class="form-control" id="description" name="description" rows="4" maxlength="10000"><?php echo html_escape(set_value('description')); ?></textarea>
			</div>
			<div class="mb-4">
				<label class="form-label" for="coordinator_user_id">Koordinator</label>
				<select class="form-select" id="coordinator_user_id" name="coordinator_user_id" required>
					<option value="">Pilih karyawan koordinator</option>
					<?php foreach ($employees as $employee): ?>
						<option value="<?php echo (int) $employee->id; ?>" <?php echo set_select('coordinator_user_id', (string) $employee->id); ?>><?php echo html_escape($employee->name); ?></option>
					<?php endforeach; ?>
				</select>
				<?php if (empty($employees)): ?>
					<div class="form-text text-danger">Buat akun dengan peran Karyawan sebelum membuat proyek.</div>
				<?php endif; ?>
			</div>
			<button class="btn btn-primary" type="submit" <?php echo empty($employees) ? 'disabled' : ''; ?>>Buat proyek</button>
			<a class="btn btn-link" href="<?php echo html_escape(site_url('projects')); ?>">Batal</a>
		<?php echo form_close(); ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
