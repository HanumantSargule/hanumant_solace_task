
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Studio Frame Composer</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="resources/jquery.contextmenu/jquery.contextMenu.css" media="screen">
		<link rel="stylesheet" href="css/app.css">
	</head>
	<body class="app-body">
		<?php
		error_reporting(0);
		session_start();
		if (empty($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}
		$selectedPhotoPath = $_SESSION['picture'] ?? '';
		if (empty($selectedPhotoPath)) {
			$previewWidth = 200;
			$previewHeight = 200;
		} else {
			list($previewWidth, $previewHeight) = getimagesize($selectedPhotoPath);
		}
		$frameOptions = glob('fr*.{png,jpg,jpeg,webp,PNG,JPG,JPEG,WEBP}', GLOB_BRACE);
		$defaultFramePath = !empty($frameOptions) ? $frameOptions[0] : '';
		$samplePhotoOptions = glob('samples/*.{webp,jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
		$uploadedPhotoOptions = glob('upload/*_thump.*');
		?>

		<div class="container app-container">
			<?php if(isset($_SESSION['error'])): ?>
				<div class="alert alert-danger alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<?php echo htmlspecialchars((string) $_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
				</div>
			<?php endif; ?>

			<div class="row page-shell">
				<div class="col-md-5">
					<section class="panel-card preview-card">
						<div class="panel-heading-custom">
							<h3 class="panel-title-custom">Preview Studio</h3>
							<p class="panel-subtitle">Your canvas updates instantly as settings change.</p>
						</div>
						<div class="canvas-wrap">
							<canvas id="testCanvas"></canvas>
						</div>
					</section>
				</div>

				<div class="col-md-7">
					<section class="panel-card controls-card">
						<div class="panel-heading-custom">
							<h3 class="panel-title-custom">Design Controls</h3>
							<p class="panel-subtitle">Tune dimensions, artwork, and border style.</p>
						</div>

						<form action="action.php" method="post" enctype="multipart/form-data" id="uploadForm">
							<input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
							<div class="layer-content">
								<div class="control-section" id="sizeLayer">
									<h4>Set Canvas Dimensions</h4>
									<div class="row">
										<div class="col-sm-4">
											<div class="form-group">
												<label for="inputHeight">Canvas Height *</label>
												<input class="form-control" type="number" id="inputHeight" name="height" min="1" placeholder="Type height" value="<?= (int) $previewHeight; ?>" required>
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group">
												<label for="inputWidth">Canvas Width *</label>
												<input class="form-control" type="number" id="inputWidth" name="width" min="1" placeholder="Type width" value="<?= (int) $previewWidth; ?>" required>
											</div>
										</div>
										<div class="col-sm-4">
											<div class="form-group form-group-button">
												<label>&nbsp;</label>
												<button type="button" id="updatePreviewBtn" class="btn btn-primary btn-block">Apply Size</button>
											</div>
										</div>
									</div>
								</div>

								<div class="control-section" id="photoLayer">
									<h4>Choose Artwork</h4>
									<h5>Gallery Samples</h5>
									<div id="samplePhotos" class="tile-grid">
										<?php foreach ($samplePhotoOptions as $samplePhoto): ?>
											<?php
												$fileName = basename($samplePhoto);
												$displayName = str_replace(array('_', '.webp', '.jpg', '.jpeg', '.png'), array(' ', '', '', '', ''), $fileName);
											?>
												<img src="<?= htmlspecialchars($samplePhoto, ENT_QUOTES, 'UTF-8'); ?>" class="sample-photo photo-tile" data-photo="<?= htmlspecialchars($samplePhoto, ENT_QUOTES, 'UTF-8'); ?>" title="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>">
										<?php endforeach; ?>
									</div>

									<h5>Recent Uploads</h5>
									<div id="uploadedPhotos" class="tile-grid">
										<?php foreach ($uploadedPhotoOptions as $uploadedPhoto): ?>
											<img src="<?= htmlspecialchars($uploadedPhoto, ENT_QUOTES, 'UTF-8'); ?>" class="uploaded-photo photo-tile" data-photo="<?= htmlspecialchars($uploadedPhoto, ENT_QUOTES, 'UTF-8'); ?>" alt="Uploaded Artwork">
										<?php endforeach; ?>
									</div>

									<h5>Upload New Artwork</h5>
									<div class="form-group">
										<input class="form-control" type="file" name="image" id="fileInput" accept="image/png,image/jpeg,image/gif">
									</div>
									<div id="fileTypeNotice" class="alert alert-warning file-notice" role="alert"></div>
									<button type="submit" name="submit" class="btn btn-success">Upload and Render</button>
								</div>

								<div class="control-section" id="frameLayer">
									<h4>Select Border Texture</h4>
									<div class="tile-grid frame-grid">
										<?php if (!empty($frameOptions)): ?>
											<?php foreach ($frameOptions as $index => $frameOption): ?>
												<img class="frame-option frame-tile" src="<?= htmlspecialchars($frameOption, ENT_QUOTES, 'UTF-8'); ?>" data-frame="<?= htmlspecialchars($frameOption, ENT_QUOTES, 'UTF-8'); ?>" title="Border Style <?= $index + 1; ?>" alt="Border Style <?= $index + 1; ?>">
											<?php endforeach; ?>
										<?php else: ?>
											<p class="text-muted">No border textures found. Add files like fr1.png in the project root.</p>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</form>
					</section>
				</div>
			</div>
		</div>

		<div id="appConfig"
			data-selected-photo="<?= htmlspecialchars($selectedPhotoPath, ENT_QUOTES, 'UTF-8'); ?>"
			data-default-frame="<?= htmlspecialchars($defaultFramePath, ENT_QUOTES, 'UTF-8'); ?>"
			data-width="<?= (int) $previewWidth; ?>"
			data-height="<?= (int) $previewHeight; ?>">
		</div>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
		<script src="js/flashcanvas/canvas2png.js"></script>
		<script src="resources/jquery.contextmenu/jquery.contextMenu.js"></script>
		<script src="js/frame.js"></script>
		<script src="js/app.js"></script>
	</body>
</html>