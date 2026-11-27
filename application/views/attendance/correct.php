<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Koreksi absensi';
$this->load->view('layouts/header', array('page_title' => $page_title));
$check_in_value = substr($record->check_in_at, 11, 5);
$check_out_value = $record->check_out_at ? substr($record->check_out_at, 11, 5) : '';
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<a class="small" href="<?php echo html_escape(site_url('attendance')); ?>">&larr; Kembali ke absensi</a>
		<h1 class="h4 mt-2">Koreksi absensi</h1>
		<p class="text-secondary"><?php echo html_escape($record->name.' · '.$record->employee_number.' · '.$record->attendance_date.' · '.$record->timezone_code); ?></p>
		<?php if ($error !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
		<?php endif; ?>
		<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
		<?php echo form_open('attendance/'.$record->id.'/correct'); ?>
			<div class="row">
				<div class="col-md-6 mb-3">
					<label class="form-label" for="check_in_time">Check-in (<?php echo html_escape($record->check_in_source); ?>)</label>
					<input class="form-control" id="check_in_time" name="check_in_time" type="time" required value="<?php echo html_escape(set_value('check_in_time', $check_in_value)); ?>">
				</div>
				<div class="col-md-6 mb-3">
					<label class="form-label" for="check_out_time">Check-out (<?php echo html_escape($record->check_out_source ?: 'belum dicatat'); ?>)</label>
					<input class="form-control" id="check_out_time" name="check_out_time" type="time" value="<?php echo html_escape(set_value('check_out_time', $check_out_value)); ?>">
				</div>
			</div>
			<div class="mb-4">
				<label class="form-label" for="reason">Alasan koreksi (wajib)</label>
				<textarea class="form-control" id="reason" name="reason" rows="3" maxlength="2000" required><?php echo html_escape(set_value('reason')); ?></textarea>
			</div>
			<button class="btn btn-primary" type="submit">Simpan koreksi</button>
		<?php echo form_close(); ?>
		<hr>
		<h2 class="h6">Bukti asli (tidak berubah saat waktu dikoreksi)</h2>
		<div class="d-flex flex-wrap gap-4">
			<?php if ($record->check_in_consent_at !== NULL): ?>
				<div>
					<p class="small">Check-in<?php if ($record->check_in_location !== NULL): ?> · <?php echo html_escape($record->check_in_location['latitude'].', '.$record->check_in_location['longitude']); ?> (±<?php echo html_escape($record->check_in_location['accuracy_m']); ?> m)<?php endif; ?></p>
					<img class="img-fluid rounded border" style="max-width: 20rem" src="<?php echo html_escape(site_url('attendance/'.$record->id.'/evidence/check-in')); ?>" alt="Bukti foto check-in">
				</div>
			<?php endif; ?>
			<?php if ($record->check_out_consent_at !== NULL): ?>
				<div>
					<p class="small">Check-out<?php if ($record->check_out_location !== NULL): ?> · <?php echo html_escape($record->check_out_location['latitude'].', '.$record->check_out_location['longitude']); ?> (±<?php echo html_escape($record->check_out_location['accuracy_m']); ?> m)<?php endif; ?></p>
					<img class="img-fluid rounded border" style="max-width: 20rem" src="<?php echo html_escape(site_url('attendance/'.$record->id.'/evidence/check-out')); ?>" alt="Bukti foto check-out">
				</div>
			<?php endif; ?>
			<?php if ($record->check_in_consent_at === NULL && $record->check_out_consent_at === NULL): ?>
				<p class="small text-secondary">Catatan ini dibuat atau dilengkapi manual; bukti foto/lokasi tidak tersedia.</p>
			<?php endif; ?>
		</div>
		<a class="btn btn-sm btn-outline-secondary mt-3" href="<?php echo html_escape(site_url('attendance/'.$record->id.'/audit')); ?>">Lihat riwayat audit</a>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
