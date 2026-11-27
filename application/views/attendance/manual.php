<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Pengecualian absensi';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<a class="small" href="<?php echo html_escape(site_url('attendance')); ?>">&larr; Kembali ke absensi</a>
		<h1 class="h4 mt-2 mb-3">Catat pengecualian manual</h1>
		<p class="text-secondary">Gunakan jika karyawan tidak dapat memberi izin kamera/lokasi atau perangkat tidak mendukung. Waktu ditafsirkan dalam zona <?php echo html_escape($timezone_code); ?>. Setiap entri manual memerlukan alasan dan masuk ke riwayat audit.</p>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('attendance/manual'); ?>
			<div class="mb-3">
				<label class="form-label" for="user_id">Karyawan</label>
				<select class="form-select" id="user_id" name="user_id" required>
					<option value="">Pilih karyawan aktif</option>
					<?php foreach ($employees as $employee): ?>
						<option value="<?php echo (int) $employee->id; ?>" <?php echo set_select('user_id', (string) $employee->id); ?>><?php echo html_escape($employee->name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="row">
				<div class="col-md-4 mb-3">
					<label class="form-label" for="attendance_date">Tanggal</label>
					<input class="form-control" id="attendance_date" name="attendance_date" type="date" required value="<?php echo html_escape(set_value('attendance_date')); ?>">
				</div>
				<div class="col-md-4 mb-3">
					<label class="form-label" for="check_in_time">Check-in</label>
					<input class="form-control" id="check_in_time" name="check_in_time" type="time" required value="<?php echo html_escape(set_value('check_in_time')); ?>">
				</div>
				<div class="col-md-4 mb-3">
					<label class="form-label" for="check_out_time">Check-out (opsional)</label>
					<input class="form-control" id="check_out_time" name="check_out_time" type="time" value="<?php echo html_escape(set_value('check_out_time')); ?>">
				</div>
			</div>
			<div class="mb-4">
				<label class="form-label" for="reason">Alasan pencatatan manual</label>
				<textarea class="form-control" id="reason" name="reason" rows="3" maxlength="2000" required><?php echo html_escape(set_value('reason')); ?></textarea>
			</div>
			<button class="btn btn-primary" type="submit" <?php echo empty($employees) ? 'disabled' : ''; ?>>Simpan catatan manual</button>
		<?php echo form_close(); ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
