<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Riwayat absensi';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<a class="small" href="<?php echo html_escape(site_url('attendance')); ?>">&larr; Kembali ke absensi</a>
		<h1 class="h4 mt-2">Riwayat absensi</h1>
		<p class="text-secondary"><?php echo html_escape($record->name.' · '.$record->employee_number.' · '.$record->attendance_date); ?></p>
		<div class="table-responsive">
			<table class="table table-striped align-middle">
				<thead><tr><th>Waktu</th><th>Zona</th><th>Pelaku</th><th>Tindakan</th><th>Kolom</th><th>Sebelum</th><th>Sesudah</th><th>Alasan</th></tr></thead>
				<tbody>
				<?php foreach ($audit as $item): ?>
					<tr>
						<td><?php echo html_escape($item->created_at); ?></td>
						<td><?php echo html_escape($item->timezone_code); ?></td>
						<td><?php echo html_escape($item->changed_by_name); ?></td>
						<td><?php echo html_escape($item->action); ?></td>
						<td><?php echo html_escape($item->field_name); ?></td>
						<td><?php echo html_escape($item->old_value ?: '—'); ?></td>
						<td><?php echo html_escape($item->new_value ?: '—'); ?></td>
						<td><?php echo nl2br(html_escape($item->reason)); ?></td>
					</tr>
				<?php endforeach; ?>
				<?php if (empty($audit)): ?>
					<tr><td class="text-secondary" colspan="8">Belum ada riwayat perubahan.</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
