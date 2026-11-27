<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Absensi';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<?php if ($success): ?>
	<div class="alert alert-success" role="status"><?php echo html_escape($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
	<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
<?php endif; ?>
<?php if ($can_punch): ?>
	<section class="card shadow-sm">
		<div class="card-body p-4">
			<h1 class="h4">Absensi hari ini</h1>
			<p class="text-secondary"><?php echo html_escape($today); ?> · Zona waktu <?php echo html_escape($record ? $record->timezone_code : $timezone_code); ?></p>
			<?php if ( ! $record): ?>
				<p>Belum ada check-in hari ini. Foto dan koordinat akan disimpan sebagai bukti absensi terenkripsi di basis data. Karyawan dapat melihat bukti miliknya; HRD/Payroll dapat melihatnya untuk verifikasi. Tidak ada pratinjau video langsung.</p>
				<?php $action = 'check_in'; ?>
			<?php elseif ($record->check_out_at === NULL): ?>
				<p>Check-in: <strong><?php echo html_escape($record->check_in_at); ?></strong>. Silakan check-out saat pekerjaan selesai. Bukti foto/lokasi juga disimpan untuk tindakan ini.</p>
				<?php $action = 'check_out'; ?>
			<?php else: ?>
				<p>Check-in: <strong><?php echo html_escape($record->check_in_at); ?></strong><br>Check-out: <strong><?php echo html_escape($record->check_out_at); ?></strong></p>
				<?php $action = NULL; ?>
			<?php endif; ?>
			<?php if ($action !== NULL): ?>
				<?php echo form_open('attendance/punch', array('id' => 'attendance-form')); ?>
					<input type="hidden" name="action" value="<?php echo html_escape($action); ?>">
					<input type="hidden" id="photo_data" name="photo_data" value="">
					<input type="hidden" id="latitude" name="latitude" value="">
					<input type="hidden" id="longitude" name="longitude" value="">
					<input type="hidden" id="accuracy_m" name="accuracy_m" value="">
					<div class="form-check mb-3">
						<input class="form-check-input" id="evidence_consent" name="evidence_consent" type="checkbox" value="1" required>
						<label class="form-check-label" for="evidence_consent">Saya memahami bahwa satu foto dan koordinat presisi akan disimpan sebagai bukti absensi dan dapat dilihat oleh HRD/Payroll.</label>
					</div>
					<video id="camera-capture" class="visually-hidden" autoplay playsinline muted aria-hidden="true"></video>
					<canvas id="camera-canvas" class="visually-hidden" aria-hidden="true"></canvas>
					<div id="attendance-message" class="small text-danger mb-2" role="alert" aria-live="polite"></div>
					<button class="btn btn-primary" id="attendance-submit" type="button" data-action="<?php echo html_escape($action); ?>">
						<?php echo $action === 'check_in' ? 'Check-in dengan foto dan lokasi' : 'Check-out dengan foto dan lokasi'; ?>
					</button>
				<?php echo form_close(); ?>
				<script>
				(function () {
					'use strict';
					const form = document.getElementById('attendance-form');
					const button = document.getElementById('attendance-submit');
					const message = document.getElementById('attendance-message');
					const video = document.getElementById('camera-capture');
					const canvas = document.getElementById('camera-canvas');

					function getPosition() {
						return new Promise(function (resolve, reject) {
							if (!navigator.geolocation) {
								reject(new Error('Perangkat tidak mendukung lokasi. Hubungi HRD untuk pencatatan manual.'));
								return;
							}
							navigator.geolocation.getCurrentPosition(resolve, function () {
								reject(new Error('Izin lokasi ditolak atau lokasi tidak tersedia. Hubungi HRD untuk pencatatan manual.'));
							}, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
						});
					}

					function capturePhoto() {
						return new Promise(function (resolve, reject) {
							if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
								reject(new Error('Perangkat tidak mendukung kamera. Hubungi HRD untuk pencatatan manual.'));
								return;
							}
							navigator.mediaDevices.getUserMedia({
								audio: false,
								video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } }
							}).then(function (stream) {
								video.srcObject = stream;
								return video.play().then(function () { return stream; });
							}).then(function (stream) {
								if (!video.videoWidth || !video.videoHeight) {
									throw new Error('Kamera belum siap. Silakan coba kembali.');
								}
								const scale = Math.min(1, 640 / Math.max(video.videoWidth, video.videoHeight));
								canvas.width = Math.max(1, Math.round(video.videoWidth * scale));
								canvas.height = Math.max(1, Math.round(video.videoHeight * scale));
								canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
								return new Promise(function (resolveBlob, rejectBlob) {
									canvas.toBlob(function (blob) {
										if (blob) {
											resolveBlob({ blob: blob, stream: stream });
										} else {
											rejectBlob(new Error('Foto tidak dapat diambil. Silakan coba kembali.'));
										}
									}, 'image/jpeg', 0.75);
								});
							}).then(function (result) {
								result.stream.getTracks().forEach(function (track) { track.stop(); });
								if (result.blob.size > 512000) {
									reject(new Error('Ukuran foto terlalu besar. Silakan coba kembali.'));
									return;
								}
								const reader = new FileReader();
								reader.onload = function () { resolve(reader.result); };
								reader.onerror = function () { reject(new Error('Foto tidak dapat diproses.')); };
								reader.readAsDataURL(result.blob);
							}).catch(function (error) {
								if (video.srcObject) {
									video.srcObject.getTracks().forEach(function (track) { track.stop(); });
									video.srcObject = null;
								}
								reject(error);
							});
						});
					}

					button.addEventListener('click', async function () {
						const consent = document.getElementById('evidence_consent');
						if (!consent.checked) {
							message.textContent = 'Berikan persetujuan untuk melanjutkan, atau hubungi HRD untuk pencatatan manual.';
							return;
						}
						button.disabled = true;
						message.textContent = 'Meminta izin lokasi dan mengambil satu foto…';
						try {
							const position = await getPosition();
							const photo = await capturePhoto();
							document.getElementById('photo_data').value = photo;
							document.getElementById('latitude').value = String(position.coords.latitude);
							document.getElementById('longitude').value = String(position.coords.longitude);
							document.getElementById('accuracy_m').value = String(position.coords.accuracy);
							message.textContent = 'Bukti siap dikirim dengan aman.';
							form.submit();
						} catch (error) {
							message.textContent = error.message || 'Bukti tidak dapat diambil. Hubungi HRD untuk pencatatan manual.';
							button.disabled = false;
						}
					});
				}());
				</script>
			<?php endif; ?>
			<?php if ($record): ?>
				<div class="mt-3 d-flex flex-wrap gap-3">
					<?php if ($record->check_in_location !== NULL): ?>
						<span class="small text-secondary">Check-in lokasi: <?php echo html_escape($record->check_in_location['latitude'].', '.$record->check_in_location['longitude']); ?> (akurasi ±<?php echo html_escape($record->check_in_location['accuracy_m']); ?> m)</span>
						<a href="<?php echo html_escape(site_url('attendance/'.$record->id.'/evidence/check-in')); ?>">Lihat foto check-in</a>
					<?php endif; ?>
					<?php if ($record->check_out_location !== NULL): ?>
						<span class="small text-secondary">Check-out lokasi: <?php echo html_escape($record->check_out_location['latitude'].', '.$record->check_out_location['longitude']); ?> (akurasi ±<?php echo html_escape($record->check_out_location['accuracy_m']); ?> m)</span>
						<a href="<?php echo html_escape(site_url('attendance/'.$record->id.'/evidence/check-out')); ?>">Lihat foto check-out</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<p class="small text-secondary mt-3 mb-0">Waktu dicatat menurut zona perusahaan. Tidak ada geofence, perhitungan keterlambatan, atau lembur pada tahap ini. Jika perangkat/izin tidak tersedia, HRD dapat mencatat pengecualian dengan alasan.</p>
		</div>
	</section>
<?php elseif ( ! $is_hr): ?>
	<div class="alert alert-info">Profil karyawan Anda belum aktif atau belum dilengkapi HRD. Hubungi HRD untuk bantuan.</div>
<?php endif; ?>
<?php if ($is_hr): ?>
	<section class="card shadow-sm <?php echo $can_punch ? 'mt-4' : ''; ?>">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
				<h2 class="h5 mb-0">Catatan absensi terbaru</h2>
				<a class="btn btn-primary btn-sm" href="<?php echo html_escape(site_url('attendance/manual')); ?>">Catat pengecualian manual</a>
			</div>
			<div class="table-responsive">
				<table class="table table-striped align-middle">
					<thead><tr><th>Tanggal</th><th>No. karyawan</th><th>Karyawan</th><th>Check-in</th><th>Check-out</th><th></th></tr></thead>
					<tbody>
					<?php foreach ($recent_records as $item): ?>
						<tr>
							<td><?php echo html_escape($item->attendance_date.' '.$item->timezone_code); ?></td>
							<td><?php echo html_escape($item->employee_number); ?></td>
							<td><?php echo html_escape($item->name); ?></td>
							<td><?php echo html_escape($item->check_in_at ?: '—'); ?><?php echo $item->check_in_source === 'manual' ? ' · manual' : ''; ?></td>
							<td><?php echo html_escape($item->check_out_at ?: '—'); ?><?php echo $item->check_out_source === 'manual' ? ' · manual' : ''; ?></td>
							<td class="d-flex gap-2">
								<a class="btn btn-sm btn-outline-primary" href="<?php echo html_escape(site_url('attendance/'.$item->id.'/correct')); ?>">Koreksi</a>
								<a class="btn btn-sm btn-outline-secondary" href="<?php echo html_escape(site_url('attendance/'.$item->id.'/audit')); ?>">Riwayat</a>
							</td>
						</tr>
					<?php endforeach; ?>
					<?php if (empty($recent_records)): ?>
						<tr><td class="text-secondary" colspan="6">Belum ada catatan absensi.</td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
			<p class="small text-secondary mb-0">Akses catatan dan bukti dibatasi untuk HRD/Payroll. Perubahan waktu memerlukan alasan dan tersimpan di riwayat audit.</p>
		</div>
	</section>
<?php endif; ?>
<?php $this->load->view('layouts/footer'); ?>
