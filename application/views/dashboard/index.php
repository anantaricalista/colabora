<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = 'Beranda';
$this->load->view('layouts/header', array('page_title' => $page_title));
$role_labels = array(
	'company_admin' => 'Administrator Perusahaan',
	'hr_payroll' => 'HRD / Payroll',
	'finance' => 'Finance',
	'employee' => 'Karyawan',
);
$display_roles = array();
foreach ($user_roles as $role)
{
	if (isset($role_labels[$role]))
	{
		$display_roles[] = $role_labels[$role];
	}
}
?>
<section class="card shadow-sm">
	<div class="card-body p-4">
		<h1 class="h4">Selamat datang, <?php echo html_escape($user_name); ?></h1>
		<p class="text-secondary mb-0">Peran: <?php echo html_escape(implode(', ', $display_roles)); ?></p>
	</div>
</section>
<section class="mt-4">
	<h2 class="h5">Co-Labora</h2>
	<p class="text-secondary">Fondasi aplikasi mandiri sedang disiapkan. Modul karyawan, penggajian, dan proyek akan ditambahkan bertahap setelah aturan kerja disepakati.</p>
</section>
<?php $this->load->view('layouts/footer'); ?>
