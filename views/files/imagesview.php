<?php
$title = $tr->translate('file.imagesviewtitle');
$path = $VIEWVARS['curpath'];

include ('views/headers.php');

$filesList = $VIEWVARS['items'];
$filesCount = count($filesList);
$curIndex = $VIEWVARS['curindex'];
?>

<style>
* {
	box-sizing: border-box;
}

img {
	vertical-align: middle;
}

/* Position the image container (needed to position the left and right arrows) */
.container {
	position: relative;
}

/* Use flexbox. See https://css-tricks.com/snippets/css/a-guide-to-flexbox/ */
.thumbnailContainer {
	display: flex;
	flex-direction: row;
	flex-wrap: nowrap;
	justify-content: center;
	align-items: stretch;
/*	flex-basis: auto;
	align-self: auto; */
	margin: auto; /* horizontal center */
	margin-right: 5% !important;
	margin-left: 5% !important;
}

.thumbnailContainerButton {
	/* Add a pointer when hovering over the thumbnail images */
	display: flex;
	cursor: pointer;
	width: 9vw;
	height: 5.6vw; /* 16/9 format */
	background-color: 222;
	border: none;
	order: 0; /* to override */
	margin:auto;
}

.thumbnailContainerButton:hover {
 /* Managed elsewere
	background-color: rgba(0, 0, 0, 0.8); */
}

.thumbnailContainerImage {
/*	object-fit: contain; */
	display:block;
	width: auto;
	height: auto;
	max-width:100%;
	max-height:100%;
}

/* Add a transparency effect for thumnbail images */
.thumbnailContainerImage {
	opacity: 0.6;
}

.active,
.thumbnailContainerImage:hover {
	opacity: 1;
}

.thumbnailButtonControl {
	display: flex;
	width: auto;
	height: 5.56vw;
	cursor: pointer;
	position: absolute !important;
	font-weight: bold;
	color: black;
	background: transparent;
	border: none;
	text-indent: 0 !important;
	padding:5px;
	margin: auto;
	vertical-align: middle;
	font-size: 17pt;
}
.thumbnailButtonControl > .fas {
	vertical-align: text-bottom;
}

/* Number text (1/3 etc) */
.numberText {
	color: #f2f2f2;
	font-family: Arial;
	font-size: 12px;
	padding: 8px 12px;
	position: relative;
	text-align: right;
	margin: auto;
	top: -2em;
}

/* Container for image text */
.caption-container {
	text-align: center;
	background-color: #222;
	padding: 0px 0px 0px 0px;
	color: white;
	display: block !important;
	position: static;
}

.carousel img {
	max-width:100%;
	width: auto;
	height: 100%;
	margin: auto;
	vertical-align: middle;
	border: 0;
	object-fit: contain;
}

</style>

<script>
var slideIndex = <?php echo $curIndex;?>;
$(document).ready(function()
{
	onShowSlides(slideIndex);
	document.getElementById('carouselMain').addEventListener('slide.bs.carousel', function (ev)
	{
		//console.log(ev);
		//console.log(ev.from);
		onShowSlides(ev.to);
	});
});

function onShowSlides(n)
{
	console.log("onSHow: " + n);
	var i, j;
	var slides = document.getElementsByClassName("carousel-item");
	var captionText = document.getElementById("caption");
	var dwnldLink = document.getElementById("downloadLink");
	var stdViewLink = document.getElementById("stdViewLink");
	var nextImg = null;
	if (n < 0) n += slides.length;
	if (slides.length > 0) slideIndex = n % slides.length;

	nextImg = slides[slideIndex].getElementsByTagName("img")[0];
	if (!nextImg.getAttribute("src"))
	{
		nextImg.setAttribute("src", nextImg.getAttribute("srclazy"));
	}
	dwnldLink.href = nextImg.src + "&dlf";
	stdViewLink.href = nextImg.src.replace("?action=download", "?action=view");
	captionText.innerHTML = nextImg.alt;

	console.log("middle");
	<?php if ($CONFIG['thumbnail'] && function_exists('gd_info')): ?>
	var dots = document.getElementsByClassName("thumbnailContainerImage");
	var start = 0;
	if (dots.length < 5) start = 0;
	else start = (dots.length + n - 5) % dots.length;
	for (j = 0; j < dots.length; j++)
	{
		i = (j + start) % dots.length;
		if (j < 10)
		{
			dots[i].parentElement.style.display = "block";
			if (!dots[i].getAttribute("src"))
			{
				dots[i].setAttribute("src", dots[i].getAttribute("srclazy"));
			}
		}
		else
		{
			dots[i].parentElement.style.display = "none";
		}
		dots[i].parentElement.style.order = j;

		// If any other carousel indicator, shoud set active / inactive
		if (i == n)
		{
			console.log ("elt " + i + " is active");
			dots[i].className = dots[i].className += " active ";
		}
		else
		{
			dots[i].className = dots[i].className.replace(" active", "");
		}
	}
	<?php endif; ?>
	console.log("by: " + n);
}
</script>

<div class="card mb-2" style="margin:0em; padding:1em;">
	<div class="row">
		<div class="d-inline-flex"> <?php /* class break-word ? */ ?>
			<div>
				<a href="?p=<?php echo $VIEWVARS['parent'] ?>">
					<i class="fas fa-chevron-circle-left go-back"></i>&nbsp;<?php $tr->trans('common.back') ?>
				</a>&nbsp;
			</div>
			<div>
				<a id="downloadLink" href="?action=download&p=<?php echo rawurlencode($filesList[$curIndex]->getFullPath());?>&dlf">
					<i class="fas fa-cloud-download-alt"></i>&nbsp;<?php echo $tr->trans('file.download') ?>
				</a>&nbsp;
			</div>
				<a id="stdViewLink" href="?action=view&p=<?php echo rawurlencode($filesList[$curIndex]->getFullPath());?>">
					<i class="fas fa-scroll"></i>&nbsp;<?php echo $tr->trans('file.stdview');?>
				</a>&nbsp;
			<div>
				<b style="text-align:center">
					<span id="caption"><?php echo $filesList[$curIndex]->getName();?></span>
				</b>
			</div>
		</div>
	</div>

	<div class="flex-container">
		<div id="carouselMain"
			 class="carousel slide"
			 data-bs-interval="false"
		   	 style="width:95vw; height: 75vh;"
		>
			<!-- Content -->
			<div class="carousel-inner" style="height:100%">
				<?php /* should start at 0 to be coherent with from / to for lazy loading */
				for ($i = 0; $i < $filesCount; $i++):
					$fileUrl = '?action=download&p=' . rawurlencode($filesList[$i]->getFullPath());
				?>
				<div id="carouselItem<?php echo $i;?>"
					 class="carousel-item<?php if ($i == $curIndex) echo ' active';?>"
					 style="background: black; height: 100% !important;"
					>
					<img class="d-block img-fluid"
					<?php if ($i == $curIndex):?>
						src="<?php echo $fileUrl /* load with js */?>"
					<?php else:?>
						srclazy="<?php echo $fileUrl /* load with js */?>"
					<?php endif;?>
						alt="<?php echo $filesList[$i]->getName(); ?>"
						style="max-height:90% !important;"
					/>
					<!--  -->
					<div class="carousel-caption d-none d-md-block caption-container w-100"
						 style="min-height: 5%; max-height:10% !important;"
					>
						<h5 style="position:relative; margin: auto;"><?php echo $filesList[$i]->getName(); ?></h5>
						<div class="numberText"><?php echo '' . ($i + 1) . ' / ' . $filesCount; ?></div>
					</div>
				</div>
				<?php endfor; ?>
			</div>

			<!-- Controls -->
			<button class="carousel-control-prev shadow-1-strong" role="button"
					data-bs-target="#carouselMain"
					data-bs-slide="prev"
			>
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</button>
			<button class="carousel-control-next shadow-1-strong" role="button"
					data-bs-target="#carouselMain"
					data-bs-slide="next"
			>
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</button>

			<!-- Tumbnails -->
			<?php if ($CONFIG['thumbnail'] && function_exists('gd_info')): ?>
			<!-- Do not use carousel-indicators class -->
			<div class="d-none d-sm-block"> <!-- hide on small screen -->
				<div class="thumbnailContainer" style="margin-bottom: -10vh;">
				<?php for ($i = 0; $i < $filesCount; $i++):
					$fileUrl = '?action=thumbnail&p=' . rawurlencode($filesList[$i]->getFullPath()); ?>
					<button role="button"
						class="thumbnailContainerButton"
						data-bs-target="#carouselMain"
						data-bs-slide-to="<?php echo $i;?>"
						title="<?php echo $filesList[$i]->getName(); ?>"
						style="display:none; order:<?php echo $i?>;"
					>
						<img class="d-block w-100 img-fluid rounded thumbnailContainerImage"
							 srclazy="<?php echo $fileUrl ?>"
						/>
					</button>
				<?php endfor; ?>
					<button class=" thumbnailButtonControl" role="button"
							data-bs-target="#carouselMain"
							data-bs-slide="prev"
							style="left: -0%;"
					>
						<i class="fas fa-chevron-circle-left" style="padding-top: 50%"></i>
					</button>

					<button class="thumbnailButtonControl" role="button"
							data-bs-target="#carouselMain"
							data-bs-slide="next"
							style="right: -0%;"
					>
						<i class="fas fa-chevron-circle-right" style="padding-top: 50%"></i>
					</button>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php include ('views/footers.php'); ?>
