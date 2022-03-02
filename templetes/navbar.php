<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid text-light">
    <a class="navbar-brand" href=""><?php echo TITLE; ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $toppage_url;?>"><?php echo $toppage;?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="setting.php"> <?php echo $setting; ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">ログアウト</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
