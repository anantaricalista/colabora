<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Masuk | Co-Labora</title>
	<link rel="stylesheet" href="<?php echo html_escape(base_url('assets/adminlte/css/adminlte.min.css')); ?>">
</head>
<body class="bg-body-secondary">
<main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
	<section class="card shadow-sm w-100" style="max-width: 28rem">
		<div class="card-body p-4 p-md-5">
			<h1 class="h3 mb-1">Co-Labora</h1>
			<p class="text-secondary mb-4">Employee &amp; Project Management</p>
			<?php if ($error !== ''): ?>
				<div class="alert alert-danger" role="alert"><?php echo html_escape($error); ?></div>
			<?php endif; ?>
			<?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
			<?php echo form_open('login', array('autocomplete' => 'on')); ?>
				<div class="mb-3">
					<label class="form-label" for="email">Email</label>
					<input class="form-control" id="email" name="email" type="email" maxlength="190" autocomplete="username" required value="<?php echo html_escape(set_value('email')); ?>">
				</div>
				<div class="mb-4">
					<label class="form-label" for="password">Kata sandi</label>
					<input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
				</div>
				<button class="btn btn-primary w-100" type="submit">Masuk</button>
			<?php echo form_close(); ?>
		</div>
	</section>
</main>
</body>
</html>
