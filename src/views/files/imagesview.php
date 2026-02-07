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

body {
  height: 100vh;
  width: 100vw;
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
  margin: auto; /* horizontal center */
  margin-right: 5% !important;
  margin-left: 5% !important;
}

.thumbnailContainerButton {
  /* Add a pointer when hovering over the thumbnail images */
  /*display: flex;*/
  cursor: pointer;
  /* 16/9 format */
  width: 9vw;
  height: 5.6vw;
  background-color: 222;
  border: none;
  order: 0; /* to override */
  margin:auto;
}

.thumbnailContainerImage {
/*  object-fit: contain; */
  display: block;
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
  color: white;
  background: transparent;
  border: none;
  text-indent: 0 !important;
  padding:5px;
  vertical-align: middle;
  font-size: 17pt;
}
.thumbnailButtonControl > .fas {
  vertical-align: text-bottom;
}

.myCarouselControlButton {
  color: #eee;
  background-color: transparent;
  border: none;
  font-size: 20px;
  height: 100%;
  margin: 1em;
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
}

.my-carousel-item img {
  display: block;
  /* aspect-ratio: 1; */
  margin: auto;
  border: 0;
  position: relative;
  width: 100%;
  height: 100% !important;
  object-fit: contain;
}

/* Run on a touchscreen */
@media (hover: none) {
 .my-carousel-item img {
  width: 100%;
}

/* Zoom carousel */
.my-carousel-items img .pointer-event {
/* touch zoom */
  touch-action: pan-y pinch-zoom;
}

</style>

<script>
var slideIndex = <?php echo $curIndex;?>;

// Gestures swipes
var touchStart = {x:-1, y:-1};

$(document).ready(function()
{
  onShowSlides(slideIndex);

  // Gestures swipes
  document.addEventListener ("touchstart", e =>
  {
    if (e.changedTouches.length != 1)
    {
      touchStart = {x:-1, y:-1};
    }
    else
    {
      touchStart = {x: e.changedTouches[0].screenX, y: e.changedTouches[0].screenY};
    }
  });
  document.addEventListener ("touchend", e =>
  {
    // e.preventDefault();
    if (e.changedTouches.length != 1 || touchStart.x < 0) return;
    var touchEnd = {x: e.changedTouches[0].screenX, y: e.changedTouches[0].screenY};
    var deltaX = 100*(touchEnd.x - touchStart.x)/window.screen.width;
    var deltaY = 100*(touchEnd.y - touchStart.y)/window.screen.height;
    var ratio = (deltaY > 1 || deltaY < -1) ? deltaX / deltaY : 100;
    ratio = ratio > 0 ? ratio : -ratio;
    if (ratio > 2)
    {
      if (deltaX > 7)
      {
        onShowSlides(slideIndex - 1);
      }
      else if (deltaX < -7)
      {
        onShowSlides(slideIndex + 1);
      }
    }
    touchstart = {x:-1, y:-1};
  });
});

function updateUrl(url, title)
{
  window.history.pushState({"title":title, "url":url}, "", url);
  if (title) document.title = title;
}

function onShowSlides(n)
{
  // console.log("onShow: " + n);
  var i, j;
  var slides = document.getElementsByClassName("my-carousel-item");
  var dwnldLink = document.getElementById("downloadLink");
  var stdViewLink = document.getElementById("stdViewLink");
  var imgTitle = document.getElementById("imgTitle");
  var imgNumber = document.getElementById("imgNumber");
  var nextImg = null;
  if (n < 0) n += slides.length;
  if (slides.length > 0) slideIndex = n % slides.length;

  nextImg = slides[slideIndex].getElementsByTagName("img")[0];
  var dwnldUrl = null;
  if (!nextImg.getAttribute("src"))
  {
    dwnldUrl = nextImg.getAttribute("srclazy");
    nextImg.setAttribute("src", dwnldUrl);
  }
  else
  {
    dwnldUrl = nextImg.getAttribute("src");
  }
  if (dwnldUrl != null)
  {
    updateUrl(dwnldUrl.replace("action=download", "action=imagesview"), "<?php echo $CONFIG['apptitle'];?> - " + nextImg.getAttribute("alt"));
  }
  dwnldLink.href = nextImg.src + "&dlf";
  stdViewLink.href = nextImg.src.replace("?action=download", "?action=view");
  imgTitle.innerHTML = nextImg.getAttribute("alt");
  imgNumber.innerHTML = "" + (slideIndex + 1) + " / " + slides.length;

  for (i = 0; i < slides.length; i++)
  {
    if (i == slideIndex) slides[i].style.display = "block";
    else slides[i].style.display = "none";
  }

  <?php if ($CONFIG['thumbnail'] && function_exists('gd_info')):?>
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
      // console.log ("elt " + i + " is active");
      dots[i].className = dots[i].className += " active ";
    }
    else
    {
      dots[i].className = dots[i].className.replace(" active", "");
    }
  }
  <?php endif;?>
}

// Rotate, Skew, Scale, ...
function extractReplaceTransformParam(name, unit, callback)
{
  // Get current image
  var slides = document.getElementsByClassName("my-carousel-item");;
  image = slides[slideIndex].getElementsByTagName("img")[0];
  // var transform = image.style.transform; is subject to unit variation and
  // probably browser dependant
  var transform = image.getAttribute ("mytransform");
  if (transform == null) transform = "";

  // Extract
  var reg = new RegExp("\\b" + name + "\\b\\s*\\(([0-9.]*)(" + unit + ")?\\)", "g");
  var regResult = reg.exec(transform);
  var result = 0;
  if (regResult != null && regResult.length > 1) result = parseInt(regResult[1]);

  // Update
  result = callback(result);

  // Set
  transform = transform.replace(reg, '');
  if (!transform.endsWith(" ")) transform += " ";
  transform += name + "(" + result + unit + ")";
  transform = transform.trim();
  image.style.transform = transform;
  image.setAttribute ("mytransform", transform);

    console.log ("transform = " + transform);
}

function imgRotate(angleDeg)
{
  extractReplaceTransformParam ("rotate", "deg", function(currentAngle)
  {
    currentAngle += angleDeg;
    if (currentAngle < 0) currentAngle += 360;
    currentAngle %= 360;
    return currentAngle;
  });
}

function imgScale(percent)
{
  extractReplaceTransformParam ("scale", "%", function(currentPercent)
  {
    if (currentPercent == 0) currentPercent = 100;
    currentPercent +=  percent;
    if (currentPercent < 25) currentPercent = 25;
    if (currentPercent > 300) currentPercent = 300;
    return currentPercent;
  });
}
</script>

<div class="container-fluid d-flex flex-column"
     style="height: 100% !important; width: 100%; background-color: black; padding: 0px; margin: 0px">
  <!-- First row of the main column, flex-grow-0: autosize, flex-grow-1: size100% -->
  <div class="flex-grow-0 d-flex flex-row align-items-start"
       style="height:6vh;">
    <div class="flex-grow-0" style="padding: 5px">
      <a id="downloadLink"
         href="?action=download&p=<?php echo rawurlencode($filesList[$curIndex]->getFullPath());?>&dlf">
        <i class="fas fa-cloud-download-alt" title="<?php echo $tr->trans('file.download');?>"></i>
        <span class="d-none d-sm-inline">&nbsp<?php echo $tr->trans('file.download');?></span>
      </a>&nbsp;
      <a id="stdViewLink"
         href="?action=view&p=<?php echo rawurlencode($filesList[$curIndex]->getFullPath());?>">
        <i class="fas fa-scroll"></i>
        <span class="d-none d-sm-inline">&nbsp;<?php echo $tr->trans('file.stdview');?></span>
      </a>&nbsp;
      <a id="parentLink"
        href="?action=ls&p=<?php echo rawurlencode($VIEWVARS['parent']);?>">
        <i class="fas fa-arrow-left"></i>
        <span class="d-none d-sm-inline">&nbsp;<?php echo $tr->trans('common.back');?></span>
      </a>&nbsp;

      <a href="javascript:void()" onclick="imgRotate(-90)">
        <i class="fas fa-undo"></i>
        <span class="d-none d-sm-inline">&nbsp;<?php echo $tr->trans('file.rotateleft');?></span>
      </a>&nbsp;
      <a href="javascript:void()" onclick="imgRotate(90)">
        <i class="fas fa-redo"></i>
        <span class="d-none d-sm-inline">&nbsp;<?php echo $tr->trans('file.rotateright');?></span>
      </a>&nbsp;
      <a href="javascript:void()" onclick="imgScale(-25)">
        <i class="fas fa-search-minus"></i>
        <span class="d-none d-sm-inline">&nbsp;<?php echo $tr->trans('file.zoomout');?></span>
      </a>&nbsp;
      <a href="javascript:void()" onclick="imgScale(+25)">
        <i class="fas fa-search-plus"></i>
        <span class="d-none d-sm-inline">&nbsp;<?php echo $tr->trans('file.zoomin');?></span>
      </a>&nbsp;
    </div>
    <div class="flex-grow-1">
      <span>&nbsp;</span>
      <!-- Padding -->
    </div>
  </div>
  <!-- Second row of the main column -->
  <div class="flex-grow-1 d-flex flex-row align-item-center" style="height:calc(100vh - 6vh - 5vh - 5.6vw - 5px);">
    <!-- Previous left button -->
    <div class="flex-grow-0 d-flex align-items-center justify-content-start">
      <button class="shadow-1-strong myCarouselControlButton" role="button"
              onclick="onShowSlides(slideIndex - 1);"
              style="width:3vw;";
              title="<?php echo $tr->trans('file.previous');?>">
        &lt;
      </button>
    </div>
    <!-- Carousel content -->
    <div class="flex-grow-1 d-flex flex-row align-items-center justify-content-center">
      <?php /* should start at 0 to be coherent with from / to for lazy loading */
      for ($i = 0; $i < $filesCount; $i++):
        $fileUrl = '?action=download&p=' . rawurlencode($filesList[$i]->getFullPath());
      ?>
      <div style="overflow: scroll";
           class="my-carousel-item w-100 h-100">
        <img class="sidebar img-fluid"
        <?php if ($i == $curIndex):?>
          src="<?php echo $fileUrl; /* load with js */?>"
        <?php else:?>
          srclazy="<?php echo $fileUrl; /* load with js */?>"
        <?php endif;?>
          alt="<?php echo $filesList[$i]->getName();?>"
        />
      </div>
      <?php endfor;?>
    </div>
    <!-- Next right button -->
    <div class="flex-grow-0 d-flex align-items-center justify-content-end">
      <button class="shadow-1-strong myCarouselControlButton" role="button"
              style="width:3vw;";
              onclick="onShowSlides(slideIndex + 1);"
              title="<?php echo $tr->trans('file.next');?>">
        &gt;
      </button>
    </div>
  </div>
  <!-- Third row of the main column -->
  <div class="flex-grow-0 flex-row d-flex">
    <div class="d-none d-md-block caption-container w-100"
         style="height:5vh;">
      <h5 id="imgTitle" style="position:relative; margin: auto;"><?php echo $filesList[$curIndex]->getName();?></h5>
      <div id="imgNumber" class="numberText"><?php echo '' . ($curIndex + 1) . ' / ' . $filesCount;?></div>
    </div>
  </div>
  <!-- 4th row of the main column -->
  <?php if ($CONFIG['thumbnail'] && function_exists('gd_info')):?>
  <div class="flex-grow-0 flex-row d-flex caption-container d-none d-sm-block" style="height:5.6vw;">
    <div class="d-flex flex-grow-1 align-items-center justify-content-center">
      <!-- Tumbnails -->
      <?php if ($CONFIG['thumbnail'] && function_exists('gd_info')):?>
      <!-- Do not use carousel-indicators class -->
      <div class="d-flex align-items-center flex-row thumbnailContainer">
        <button class="thumbnailButtonControl me-auto" role="button"
                onclick="onShowSlides(slideIndex - 1);"
                title="<?php echo $tr->trans('file.previous');?>"
                style="order:-1;">
          <i class="fas fa-chevron-circle-left" style="margin: auto;"></i>
        </button>
      <?php for ($i = 0; $i < $filesCount; $i++):
        $fileUrl = '?action=thumbnail&p=' . rawurlencode($filesList[$i]->getFullPath());?>
        <button class="thumbnailContainerButton align-self-center" role="button"
                onclick="onShowSlides(<?php echo $i;?>);"
                title="<?php echo $filesList[$i]->getName();?>"
                style="display:none; order:<?php echo $i;?>;">
          <img class="d-block w-100 img-fluid rounded thumbnailContainerImage"
             srclazy="<?php echo $fileUrl;?>"/>
        </button>
      <?php endfor;?>
        <button class="thumbnailButtonControl ms-auto" role="button"
                onclick="onShowSlides(slideIndex + 1);"
                title="<?php echo $tr->trans('file.next');?>"
                style="order:<?php echo ($filesCount);?>;">
          <i class="fas fa-chevron-circle-right" style="margin: auto:"></i>
        </button>
      </div>
      <?php endif;?>
    </div>
  </div>
  <?php else:?>
  <div class="flex-grow-0 flex-row d-flex" style="max-height:17%;"></div>
  <?php endif;?>
</div>

<?php include ('views/footers.php');?>
