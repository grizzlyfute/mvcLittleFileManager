<?php

// Set no cache and expires the cache
header('Expires: Sat, 01 Jan 1970 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

?>
<!DOCTYPE html>
<html encoding="UTF-8">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
  <!-- <meta name="description" content="Web based File Manager in PHP, Manage your files efficiently and easily with Tiny File Manager"/>
  <meta name="author" content=""/> -->
  <meta name="robots" content="noindex, nofollow"/>
  <meta name="googlebot" content="noindex"/>
  <link rel="icon" href="favicon.png" type="image/png"/>
  <title><?php echo $CONFIG['apptitle']; ?></title>
  <link rel="stylesheet" href="libs/bootstrap-5.1.0/css/bootstrap.min.css"/>
  <style>
    body.login-page
    {
      background-color: #f7f9fb;
      font-size: 14px;
    }
    .login-page .brand
    {
      width: 121px;
      overflow: hidden;
      margin: 0 auto;
      margin: 40px auto;
      margin-bottom: 0;
      position: relative;
      z-index: 1;
    }
    .login-page .brand img
    {
      width: 100%;
    }
    .login-page .card-wrapper
    {
      width: 360px;
    }
    .login-page .card
    {
      border-color: transparent;
      box-shadow: 0 4px 8px rgba(0,0,0,.05)
    }
    .login-page .card-title
    {
      margin-bottom: 1.5rem;
      font-size: 24px;
      font-weight: 300;
      letter-spacing: -.5px;
    }
    .login-page .form-control
    {
      border-width: 2.3px;
    }
    .login-page .form-group label
    {
      width: 100%;
    }
    .login-page .btn.btn-block
    {
      padding: 12px 10px;
    }
    .login-page .footer
    {
      margin: 40px 0;
      color: #888;
      text-align: center;
    }
    @media screen and (max-width: 425px)
    {
      .login-page .card-wrapper
      {
        width: 90%;
        margin: 0 auto;
      }
    }
    @media screen and (max-width: 320px)
    {
      .login-page .card.fat
      {
        padding: 0;
      }
      .login-page .card.fat .card-body
      {
        padding: 15px;
      }
    }
    .message
    {
      padding: 4px 7px;
      border: 1px solid #ddd;
      background-color: #fff;
    }
    .message.ok
    {
      border-color: green;
      color:green
    }
    .message.error
    {
      border-color: red;
      color:red;
    }
    .message.alert
    {
      border-color: orange;
      color:orange
    }
    .custom-control
    {
      padding: 0;
      margin: 0;
      min-width: 18px;
    }
    .show-password-btn
    {
      color: #747474;
      font-size: 1.3em;
      border: none;
      background-color: transparent;
      position: relative;
      float: right;
      margin: -0.3em 0px 0px 0px;
    }
    .show-password-btn:hover
    {
      color: #ff4754;
      transition: all 0.2s;
    }
  </style>
  <script>
    function showPassword()
    {
      document.getElementById("password").type = "text";
      document.getElementsByClassName("show-password-btn")[0].innerHTML = "&#x1F92B;"; // simley chht
    }
    function hidePassword()
    {
      document.getElementById("password").type = "password";
      document.getElementsByClassName("show-password-btn")[0].innerHTML = "&#x1F441;"; // eye
    }
  </script>
</head>
<body class="login-page">
  <div id="wrapper" class="container-fluid">
    <?php if (isset($VIEWVARS['msg']))
      echo '<p class="message error">' . $VIEWVARS['msg'] . '</p>'; ?>
    <section class="h-100">
      <div class="container h-100">
        <div class="row justify-content-md-center h-100">
          <div class="card-wrapper">
            <div class="brand">
              <img src='logo.png' alt='' />
            </div>
            <div class="text-center">
              <h1 class="card-title"><?php echo $CONFIG['apptitle']; ?></h1>
            </div>
            <div class="card fat">
              <div class="card-body">
                <form class="form-signin" action="?action=login" method="post" autocomplete="off">
                  <input type="hidden" id="wantedurl" name="wantedurl" value="<?php $VIEWVARS['wantedurl'] ?>"/>
                  <div class="form-group">
                    <label for="username" class="form-label"><?php $tr->trans('login.username'); ?></label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus/>
                  </div>

                  <div class="form-group">
                    <label for="password" class="form-label">
                      <?php $tr->trans('login.password'); ?>
                      <button class="show-password-btn"
                        title="<?php $tr->trans('login.showpassword')?>"
                        tabindex="-1" type="button"
                        style="top: 0.3em; font-family: none;"
                        onmouseup="hidePassword()" onmousedown="showPassword()" onmouseout="hidePassword()">
                        &#x1F441; <!-- eye -->
                      </button>
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required/>
                  </div>

                  <div class="form-check mt-2 custom-control">
                    <input type="checkbox" name="rememberme" id="rememberme" value="1" class="custom-control-input"/>
                    <label class="custom-control-label" for="rememberme"><?php $tr->trans('login.rememberme'); ?></label>
                  </div>

                  <div class="form-group float-end">
                    <button type="submit" class="btn btn-success btn-block" role="button">
                      <?php $tr->trans('login.login') ?>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
