<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Profil karyawan';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<h1 class="h4 mb-3">Lengkapi profil karyawan</h1>
		<p class="text-secondary">Administrator harus membuat akun dengan peran Karyawan terlebih dahulu. HRD mengelola data profil di sini.</p>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('employees/create'); ?>
			<div class="mb-3">
				<label class="form-label" for="user_id">Akun</label>
				<select class="form-select" id="user_id" name="user_id" required>
					<option value="">Pilih akun karyawan</option>
					<?php foreach ($accounts as $account): ?>
						<option value="<?php echo (int) $account->id; ?>" <?php echo set_select('user_id', (string) $account->id); ?>><?php echo html_escape($account->name.' ('.$account->email.')'); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mb-3">
				<label class="form-label" for="employee_number">Nomor karyawan internal</label>
				<input class="form-control" id="employee_number" name="employee_number" maxlength="64" required value="<?php echo html_escape(set_value('employee_number')); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="job_title">Jabatan</label>
				<input class="form-control" id="job_title" name="job_title" maxlength="120" required value="<?php echo html_escape(set_value('job_title')); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="department">Departemen (opsional)</label>
				<input class="form-control" id="department" name="department" maxlength="120" value="<?php echo html_escape(set_value('department')); ?>">
			</div>
			<div class="mb-4">
				<label class="form-label" for="start_date">Tanggal mulai kerja</label>
				<input class="form-control" id="start_date" name="start_date" type="date" required value="<?php echo html_escape(set_value('start_date')); ?>">
			</div>
			<button class="btn btn-primary" type="submit" <?php echo empty($accounts) ? 'disabled' : ''; ?>>Simpan profil</button>
			<a class="btn btn-link" href="<?php echo html_escape(site_url('employees')); ?>">Batal</a>
		<?php echo form_close(); ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
