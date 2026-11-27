<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Pengaturan perusahaan';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<h1 class="h4 mb-3">Pengaturan perusahaan</h1>
		<p class="text-secondary">Zona waktu digunakan untuk menentukan tanggal serta waktu absensi.</p>
		<?php if ($success !== ''): ?>
			<div class="alert alert-success" role="status"><?php echo html_escape($success); ?></div>
		<?php endif; ?>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('settings'); ?>
			<div class="mb-3">
				<label class="form-label" for="timezone_code">Zona waktu perusahaan</label>
				<select class="form-select" id="timezone_code" name="timezone_code" required>
					<?php foreach ($timezones as $code => $label): ?>
						<option value="<?php echo html_escape($code); ?>" <?php echo set_select('timezone_code', $code, $timezone_code === $code); ?>><?php echo html_escape($label); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<button class="btn btn-primary" type="submit">Simpan pengaturan</button>
		<?php echo form_close(); ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
