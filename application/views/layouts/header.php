<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo html_escape(isset($page_title) ? $page_title : 'Beranda'); ?> | Co-Labora</title>
	<link rel="stylesheet" href="<?php echo html_escape(base_url('assets/adminlte/css/adminlte.min.css')); ?>">
</head>
<body class="bg-body-secondary">
<div class="container py-3 py-md-5">
	<header class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
		<a class="text-decoration-none text-dark" href="<?php echo html_escape(site_url('dashboard')); ?>">
			<strong class="fs-4">Co-Labora</strong>
			<span class="d-block text-secondary small">Employee &amp; Project Management</span>
		</a>
		<nav class="d-flex align-items-center flex-wrap gap-3" aria-label="Navigasi utama">
			<a href="<?php echo html_escape(site_url('dashboard')); ?>">Beranda</a>
			<?php if ($this->session->userdata('user_role') === 'company_admin'): ?>
				<a href="<?php echo html_escape(site_url('users')); ?>">Pengguna</a>
			<?php endif; ?>
			<span class="text-secondary"><?php echo html_escape($this->session->userdata('user_name')); ?></span>
			<?php echo form_open('logout', array('class' => 'm-0')); ?>
				<button class="btn btn-outline-secondary btn-sm" type="submit">Keluar</button>
			<?php echo form_close(); ?>
		</nav>
	</header>
	<main>
