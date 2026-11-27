<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Ubah profil karyawan';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<h1 class="h4 mb-1">Ubah profil karyawan</h1>
		<p class="text-secondary"><?php echo html_escape($employee->name.' ('.$employee->email.')'); ?></p>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('employees/'.$employee->user_id.'/edit'); ?>
			<div class="mb-3">
				<label class="form-label" for="employee_number">Nomor karyawan internal</label>
				<input class="form-control" id="employee_number" name="employee_number" maxlength="64" required value="<?php echo html_escape(set_value('employee_number', $employee->employee_number)); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="job_title">Jabatan</label>
				<input class="form-control" id="job_title" name="job_title" maxlength="120" required value="<?php echo html_escape(set_value('job_title', $employee->job_title)); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="department">Departemen (opsional)</label>
				<input class="form-control" id="department" name="department" maxlength="120" value="<?php echo html_escape(set_value('department', $employee->department)); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="start_date">Tanggal mulai kerja</label>
				<input class="form-control" id="start_date" name="start_date" type="date" required value="<?php echo html_escape(set_value('start_date', $employee->start_date)); ?>">
			</div>
			<div class="mb-4">
				<label class="form-label" for="employment_status">Status kerja</label>
				<select class="form-select" id="employment_status" name="employment_status" required>
					<option value="active" <?php echo set_select('employment_status', 'active', $employee->employment_status === 'active'); ?>>Aktif</option>
					<option value="inactive" <?php echo set_select('employment_status', 'inactive', $employee->employment_status === 'inactive'); ?>>Nonaktif — login diblokir</option>
				</select>
			</div>
			<button class="btn btn-primary" type="submit">Simpan perubahan</button>
			<a class="btn btn-link" href="<?php echo html_escape(site_url('employees')); ?>">Batal</a>
		<?php echo form_close(); ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
