<?php
$title = $tr->translate('file.imagesgridtitle');
$path = $VIEWVARS['curpath'];
include ('views/headers.php'); ?>
<div class="card mb-2" style="margin:0em; padding:1em;">
	<div class="row">
<?php
	$filesList = $VIEWVARS['items'];
	$filesCount = count($filesList);
?>

<style>
* {
	box-sizing: border-box;
}

img
{
	vertical-align: middle;
}

/* Position the image container (needed to position the left and right arrows) */
.container
{
	position: relative;
}

/* Hide the images by default */
.imgBigSlides
{
	display: none;
}

/* Add a pointer when hovering over the thumbnail images */
.cursor
{
	cursor: pointer;
}

/* Next & previous buttons */
.prev,
.next {
	cursor: pointer;
	position: absolute;
	top: 40%;
	width: auto;
	padding: 16px;
	margin-top: -50px;
	color: white;
	font-weight: bold;
	font-size: 20px;
	border-radius: 0 3px 3px 0;
	user-select: none;
	-webkit-user-select: none;
}

/* Position the "next button" to the right */
.next
{
	right:26px; /* padding+font-size/2 */
	border-radius: 3px 0 0 3px;
}

/* On hover, add a black background color with a little bit see-through */
.prev:hover,
.next:hover
{
	background-color: rgba(0, 0, 0, 0.8);
}

/* Number text (1/3 etc) */
.numbertext
{
	color: #f2f2f2;
	font-family: Arial;
	font-size: 12px;
	padding: 8px 12px;
	position: absolute;
	top: 0;
}

/* Container for image text */
.caption-container
{
	text-align: center;
	background-color: #222;
	padding: 2px 16px;
	color: white;
	width:100%;
}

.row:after {
	content: "";
	display: table;
	clear: both;
}

/* dynamic thumbnail colum */
.column {
	float: left;
	padding: 0px;
	width: 128px;
	/*width: 16.66%;*/
}

/* Add a transparency effect for thumnbail images */
.thumbnail {
	opacity: 0.6;
}

.active,
.thumbnail:hover {
	opacity: 1;
}
</style>

<script>
var slideIndex = <?php echo $VIEWVARS['curindex'];?>;
$(document).ready(function()
{
	showSlides(slideIndex);
});

function plusSlides(n)
{
	showSlides(slideIndex += n);
}

function currentSlide(n)
{
	showSlides(slideIndex = n);
}

function showSlides(n)
{
	var i;
	var slides = document.getElementsByClassName("imgBigSlides");
	var dots = document.getElementsByClassName("thumbnail");
	var captionText = document.getElementById("caption");
	var dwnldLink = document.getElementById("downloadLink");
	if (n < 0) n += slides.length;
	if (slides.length > 0) slideIndex = n % slides.length;
	for (i = 0; i < slides.length; i++)
	{
		slides[i].style.display = "none";
	}
	for (i = 0; i < dots.length; i++)
	{
		dots[i].className = dots[i].className.replace(" active", "");
	}
	slides[slideIndex].style.display = "block";
	dots[slideIndex].className += " active";
	captionText.innerHTML = dots[slideIndex].alt;
	dwnldLink.href = dots[slideIndex].src + "&dlf";
}
</script>

<h2 style="text-align:center"><?php $VIEWVARS['curpath']?></h2>
<div class="card mb-2" style="margin:0em; padding:1em;">
	<div class="row">
		<p class=break-word">
			<b><a href="?p=<?php echo $VIEWVARS['parent'] ?>"><i class="fas fa-chevron-circle-left go-back"></i>&nbsp;<?php $tr->trans('common.back') ?></a></b>
			&nbsp;
			<b><a id="downloadLink" href="#"><i class="fas fa-cloud-download-alt"></i>&nbsp;<?php echo $tr->trans('file.download') ?></a></b> &nbsp;
		</p>
	</div>
</div>

<div class="container">
	<div class="container">
		<?php for ($i = 0; $i < $filesCount; $i++):
			$fileUrl = $_SERVER['PHP_SELF'] . '?action=download&p=' . rawurlencode($filesList[$i]->getFullPath());
		?>
		<div class="imgBigSlides">
			<div class="numbertext"><?php echo '' . ($i + 1) . ' / ' . $filesCount; ?></div>
			<img
				src="<?php echo $fileUrl; ?>"
				alt="<?php echo $filesList[$i]->getName(); ?>"
				style="width:100%;height:100%;max-width:90vw;max-height:60vw;"
			/>
		</div>
		<?php endfor; ?>

		<?php if ($filesCount > 1): ?>
		<a class="prev" onclick="plusSlides(-1)"><i class="fas fa-chevron-circle-left"></i></a>
		<a class="next" onclick="plusSlides( 1)"><i class="fas fa-chevron-circle-right"></i></a>
		<?php endif; ?>
		<div class="caption-container">
			<p id="caption"></p>
		</div>

		<div class="row" style="padding:0">
		<?php for ($i = 0; $i < $filesCount; $i++):
			$fileUrl = $_SERVER['PHP_SELF'] . '?action=thumbnail&p=' . rawurlencode($filesList[$i]->getFullPath());
		?>
			<div class="column">
				<img
					class="thumbnail cursor"
					src="<?php echo $fileUrl ?>"
					alt="<?php echo $filesList[$i]->getName(); ?>"
					style="width:100%"
					onclick="currentSlide(<?php echo $i; ?>)"
				/>
			</div>
		<?php endfor; ?>
		</div>
	</div>
</div>

<?php include ('views/footers.php'); ?>
