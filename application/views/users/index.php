<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Pengguna';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
			<h1 class="h4 mb-0">Pengguna aplikasi</h1>
			<a class="btn btn-primary" href="<?php echo html_escape(site_url('users/create')); ?>">Tambah pengguna</a>
		</div>
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success" role="status"><?php echo html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>
		<div class="table-responsive">
			<table class="table table-striped align-middle">
			<thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Aksi</th></tr></thead>
				<tbody>
				<?php foreach ($users as $user): ?>
					<tr>
						<td><?php echo html_escape($user->name); ?></td>
						<td><?php echo html_escape($user->email); ?></td>
						<td>
							<?php
							$user_role_labels = array();
							foreach ($user->roles as $role)
							{
								$user_role_labels[] = isset($roles[$role]) ? $roles[$role] : 'Tidak dikenal';
							}
							echo html_escape(implode(', ', $user_role_labels));
							?>
						</td>
						<td><?php echo $user->is_active ? 'Aktif' : 'Nonaktif'; ?></td>
						<td>
							<?php if ((int) $user->id !== (int) $this->session->userdata('user_id')): ?>
								<?php echo form_open('users/toggle/'.$user->id, array('class' => 'm-0')); ?>
									<button class="btn btn-sm <?php echo $user->is_active ? 'btn-outline-danger' : 'btn-outline-success'; ?>" type="submit">
										<?php echo $user->is_active ? 'Nonaktifkan' : 'Aktifkan'; ?>
									</button>
								<?php echo form_close(); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php if (empty($users)): ?>
					<tr><td class="text-secondary" colspan="5">Belum ada akun pengguna.</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
