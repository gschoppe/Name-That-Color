<!doctype html>
<html lang="en">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="libraries/farbtastic/farbtastic.css">

		<title>Name That Color - Automatic color namer</title>
	</head>
	<body>
		<div class="container">
			<h1>Name That Color</h1>
			<div class="row">
				<div class="col-sm-4">
					<div id="colorpicker"></div>
				</div>
				<div class="col-sm-8">
					<ul class="nav nav-tabs" id="myTab" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="hex-tab" data-toggle="tab" href="#hex" role="tab" aria-controls="hex" aria-selected="true">Hex</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="hsl-tab" data-toggle="tab" href="#hsl" role="tab" aria-controls="hsl" aria-selected="false">HSL</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="cmyk-tab" data-toggle="tab" href="#cmyk" role="tab" aria-controls="cmyk" aria-selected="false">CMYK</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="name-tab" data-toggle="tab" href="#name" role="tab" aria-controls="name" aria-selected="false">Name</a>
						</li>
					</ul>
					<div class="tab-content" id="myTabContent">
						<div class="tab-pane fade show active" id="hex" role="tabpanel" aria-labelledby="hex-tab">
							<label>
								Hex Color
								<input class="form-control" type="text" id="hexcolor" name="hexcolor" placeholder="#123456" />
							</label>
						</div>
						<div class="tab-pane fade" id="hsl" role="tabpanel" aria-labelledby="hsl-tab">
							<label>
								Hue
								<input class="form-control" type="number" id="hslcolor-h" name="hslcolor-h" placeholder="180" />
							</label>
							<label>
								Saturation
								<input class="form-control" type="number" id="hslcolor-s" name="hslcolor-s" placeholder="50%" />
							</label>
							<label>
								Lightness
								<input class="form-control" type="number" id="hslcolor-l" name="hslcolor-l" placeholder="50%" />
							</label>
						</div>
						<div class="tab-pane fade" id="cmyk" role="tabpanel" aria-labelledby="cmyk-tab">
							<label>
								Cyan
								<input class="form-control" type="number" id="cmykcolor-c" name="cmykcolor-c" placeholder="50%" />
							</label>
							<label>
								Magenta
								<input class="form-control" type="number" id="cmykcolor-m" name="cmykcolor-m" placeholder="50%" />
							</label>
							<label>
								Yellow
								<input class="form-control" type="number" id="cmykcolor-y" name="cmykcolor-y" placeholder="50%" />
							</label>
							<label>
								Key
								<input class="form-control" type="number" id="cmykcolor-y" name="cmykcolor-k" placeholder="50%" />
							</label>
						</div>
						<div class="tab-pane fade" id="name" role="tabpanel" aria-labelledby="name-tab">
							<label>
								Color Name
								<input class="form-control" type="text" id="colorname" name="colorname" placeholder="type name here" />
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<script type="text/javascript" src="libraries/farbtastic/farbtastic.js"></script>
		<script>
		$(function() {
			$('#colorpicker').farbtastic(function( color ) {
				$('#hexcolor').val( color );
			});
		});
		</script>
	</body>
</html>
