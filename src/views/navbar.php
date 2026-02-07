<?php
$isMainPage = isset($VIEWVARS['items']);
// $path: the current file path.
// $title: the nav bar title
?>

<nav class="navbar navbar-expand-md navbar-light bg-white mb-2 main-nav fixed-top" style="position:relative; z-index: 5;">
  <div class="container-fluid">
     <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-none d-sm-block" href=""><?php echo isset($title) ? $title : ''?></a>
    <?php
    if (!isset($path) || $path == null)
    {
      $path = '';
    }
    $root = '/';
    if ($this->getCurrentUser() != null)
    {
      $root .= $this->getCurrentUser()->getRootDirectory();
    }
    if ($path != '')
    {
      $exploded = explode('/', $path);
      $count = count($exploded);
      $array = array();
      $parent = '';
    }
    else
    {
      $count = 0;
    }
    ?>
    <nav aria-label="breadcrumb" class="navbar">
      <ol class="breadcrumb align-center mt-2 mb-0">
        <li class="breadcrumb-item">
          <a href="?action=ls&p=<?php echo rawurlencode($root)?>">
            <i class="fas fa-home" aria-hidden="true" style="position: relative; top: -0.2em" title="<?php $tr->trans('file.home') ?>"></i>
          </a>
        </li>
        <?php
        for ($i = 0; $i < $count; $i++)
        {
          if (!$exploded[$i]) continue;
          $parent = $parent . '/' . $exploded[$i];
          if (!utils_isPathIncludeInto($root, $parent, true)) continue;
          if ($i == $count -1)
          {
            echo '<li class="breadcrumb-item active" aria-current="page">' . PHP_EOL;
            echo '  ' . utils_pathToHtml($exploded[$i]) . PHP_EOL;
            echo '</li>' . PHP_EOL;
          }
          else
          {
            echo '<li class="breadcrumb-item">' . PHP_EOL;
            echo '  <a href="?action=ls&p=' . rawurlencode($parent) . '">' . utils_pathToHtml($exploded[$i]) . '</a>' . PHP_EOL;
            echo '</li>' . PHP_EOL;
          }
        }
        ?>
      </ol>
    </nav>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <div class="col text-end">
        <ul class="navbar-nav mr-auto float-end">
        <?php if ($isMainPage): ?>
          <li class="nav-item mr-3">
            <div class="input-group input-group-sm mr-1" style="margin-top:4px;">
              <input type="text" class="form-control" placeholder="<?php $tr->trans('file.search'); ?>" aria-label="<?php $tr->trans('file.search'); ?>" aria-describedby="search-addon2" id="search-addon">
              <div class="input-group-append">
                <span class="input-group-text" id="search-addon2">
                  <i class="fas fa-search"></i>
                </span>
              </div>
            </div>
          </li>
          <?php if ($perm->isGranted(Permission::MODIFY, $path)): ?>
          <li class="nav-item">
            <a title="<?php $tr->trans('file.upload'); ?>" class="nav-link" href="#uploadModal" data-bs-toggle="modal" data-bs-target="#uploadModal">
              <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>&nbsp;<?php $tr->trans('file.upload') ?>
            </a>
          </li>
          <li class="nav-item">
            <a title="<?php $tr->trans('file.newitem'); ?>" class="nav-link" href="#createNewItemModal" data-bs-toggle="modal" data-bs-target="#createNewItemModal">
              <i class="fas fa-plus-square"></i>&nbsp;<?php $tr->trans('file.newitem'); ?>
            </a>
          </li>
          <?php endif; ?>
        <?php else:
          if (isset($VIEWVARS['files']) && $VIEWVARS['file'] && $perm->isGranted(Permission::VIEW, $path) && $count > 0)
          {
            $file = $VIEWVARS['file'];
            $filename = utils_pathToHtml($file->getName());
            $filesize = utils_getFileSizeSuffix($file->getSize());
            $mimetype = isset ($VIEWVARS['mimetype']) ? ' (' . $VIEWVARS['mimetype'] . ')' : '';
          ?>
          <li class="nav-item">
            <a title="<?php echo $filename . ' - ' . $filesize . $mimetype?>" class="nav-link" href="?action=download&p=<?php echo rawurlencode($file->getFullPath()) ?>&dlf">
              <i class="fas fa-cloud-download-alt"></i>&nbsp;<?php $tr->trans('file.download') ?>
            </a>
          </li>
          <?php } ?>
        <?php endif; // isMainPage ?>

          <li class="nav-item avatar dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLinkSetting" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-user-circle"></i>&nbsp;<?php echo $this->getCurrentUser() != null ? $this->getCurrentUser()->getUserName(): '' ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLinkSetting">
              <?php if ($perm->isGranted(Permission::USERS, $this->getCurrentUser())): ?>
              <li>
                <a title="<?php $tr->trans('setting.usersettings'); ?>" class="dropdown-item nav-link"
                  href="?action=edituser&username=<?php echo utils_pathToHtml($this->getCurrentUser() != null ? $this->getCurrentUser()->getUserName() : '-') ?>">
                  <i class="fas fa-user-cog" aria-hidden="true"></i>&nbsp;<?php $tr->trans('setting.usersettings'); ?>
                </a>
              </li>
              <?php endif; ?>
              <?php if ($perm->isGranted(Permission::PREFERENCES, $this->getCurrentUser())): ?>
              <li>
                <a title="<?php $tr->trans('setting.appsettings'); ?>" class="dropdown-item nav-link" href="?action=settings">
                  <i class="fas fa-cog" aria-hidden="true"></i>&nbsp;<?php $tr->trans('setting.appsettings'); ?>
                </a>
              </li>
              <?php endif; ?>
              <?php if ($perm->isGranted(Permission::USERS, null)): ?>
              <li>
                <a title="<?php $tr->trans('setting.userslist'); ?>" class="dropdown-item nav-link" href="?action=userslist">
                  <i class="fas fa-users" aria-hidden="true"></i>&nbsp;<?php $tr->trans('setting.userslist'); ?>
                </a>
              </li>
              <?php endif; ?>
              <li>
                <a title="<?php $tr->trans('setting.about'); ?>" class="dropdown-item nav-link" href="version.php">
                  <i class="fas fa-connectdevelop" aria-hidden="true"></i>&nbsp;<?php $tr->trans('setting.about'); ?>
                </a>
              </li>
              <?php if ($this->getCurrentUser() != null): ?>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a title="<?php $tr->trans('login.logout'); ?>" class="dropdown-item nav-link" href="?action=logout">
                  <i class="fas fa-sign-out-alt" aria-hidden="true"></i>&nbsp;<?php $tr->trans('login.logout'); ?>
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
