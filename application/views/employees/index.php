<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Karyawan';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
			<h1 class="h4 mb-0">Data karyawan</h1>
			<a class="btn btn-primary" href="<?php echo html_escape(site_url('employees/create')); ?>">Lengkapi profil karyawan</a>
		</div>
		<?php if ($success): ?>
			<div class="alert alert-success" role="status"><?php echo html_escape($success); ?></div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php if (empty($accounts)): ?>
			<div class="alert alert-info">Semua akun dengan peran Karyawan sudah memiliki profil. Administrator mengelola akun dan penetapan perannya.</div>
		<?php endif; ?>
		<div class="table-responsive">
			<table class="table table-striped align-middle">
				<thead><tr><th>Nomor</th><th>Nama</th><th>Email</th><th>Jabatan</th><th>Departemen</th><th>Mulai</th><th>Status kerja</th><th>Akun</th><th></th></tr></thead>
				<tbody>
				<?php foreach ($employees as $employee): ?>
					<tr>
						<td><?php echo html_escape($employee->employee_number); ?></td>
						<td><?php echo html_escape($employee->name); ?></td>
						<td><?php echo html_escape($employee->email); ?></td>
						<td><?php echo html_escape($employee->job_title); ?></td>
						<td><?php echo html_escape($employee->department ?: '—'); ?></td>
						<td><?php echo html_escape($employee->start_date); ?></td>
						<td><?php echo $employee->employment_status === 'active' ? 'Aktif' : 'Nonaktif'; ?></td>
						<td><?php echo (int) $employee->account_active === 1 ? 'Aktif' : 'Dinonaktifkan admin'; ?></td>
						<td><a class="btn btn-sm btn-outline-primary" href="<?php echo html_escape(site_url('employees/'.$employee->user_id.'/edit')); ?>">Ubah</a></td>
					</tr>
				<?php endforeach; ?>
				<?php if (empty($employees)): ?>
					<tr><td class="text-secondary" colspan="9">Belum ada profil karyawan. Pastikan Administrator sudah membuat akun dengan peran Karyawan.</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
