<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Proyek';
$this->load->view('layouts/header', array('page_title' => $page_title));
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
			<h1 class="h4 mb-0">Proyek</h1>
			<?php if ($is_admin): ?>
				<a class="btn btn-primary" href="<?php echo html_escape(site_url('projects/create')); ?>">Buat proyek</a>
			<?php endif; ?>
		</div>
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success" role="status"><?php echo html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if (empty($projects)): ?>
			<p class="text-secondary mb-0">Belum ada proyek yang dapat ditampilkan untuk akun Anda.</p>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table table-striped align-middle">
					<thead><tr><th>Proyek</th><th>Status</th><th>Progres</th><th>Tugas</th></tr></thead>
					<tbody>
					<?php foreach ($projects as $project): ?>
						<?php
						$total = (int) $project->task_count;
						$completed = (int) $project->completed_count;
						$progress = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;
						?>
						<tr>
							<td><a href="<?php echo html_escape(site_url('projects/'.$project->id)); ?>"><?php echo html_escape($project->name); ?></a></td>
							<td><?php echo $project->status === 'complete' ? 'Selesai' : 'Aktif'; ?></td>
							<td>
								<div class="progress" role="progressbar" aria-label="Progres proyek" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
									<div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
								</div>
								<small class="text-secondary"><?php echo $progress; ?>%</small>
							</td>
							<td><?php echo $completed; ?> / <?php echo $total; ?> selesai</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php $this->load->view('layouts/footer'); ?>
