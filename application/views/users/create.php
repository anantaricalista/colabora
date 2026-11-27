<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Tambah pengguna';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<h1 class="h4 mb-3">Tambah pengguna</h1>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('users/create', array('autocomplete' => 'off')); ?>
			<div class="mb-3">
				<label class="form-label" for="name">Nama</label>
				<input class="form-control" id="name" name="name" maxlength="150" required value="<?php echo html_escape(set_value('name')); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="email">Email</label>
				<input class="form-control" id="email" name="email" type="email" maxlength="190" required value="<?php echo html_escape(set_value('email')); ?>">
			</div>
			<div class="mb-3">
				<label class="form-label" for="role">Peran</label>
				<select class="form-select" id="role" name="role" required>
					<option value="">Pilih peran</option>
					<?php foreach ($roles as $value => $label): ?>
						<option value="<?php echo html_escape($value); ?>" <?php echo set_select('role', $value); ?>><?php echo html_escape($label); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mb-4">
				<label class="form-label" for="password">Kata sandi awal (minimal 12 karakter)</label>
				<input class="form-control" id="password" name="password" type="password" minlength="12" maxlength="255" autocomplete="new-password" required>
			</div>
			<button class="btn btn-primary" type="submit">Simpan pengguna</button>
			<a class="btn btn-link" href="<?php echo html_escape(site_url('users')); ?>">Batal</a>
		<?php echo form_close(); ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
