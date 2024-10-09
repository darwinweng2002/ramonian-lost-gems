<section class="section dashboard">
    <div class="row">

      <!-- Left side columns -->
      <div class="col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="row">

         

            </div>
          </div>
          <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title">Missing Items <span>| Published</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $Items = $conn->query("SELECT * FROM `missing_items` where `status` = 1")->num_rows;
                    ?>
                    <h6><?= format_num($Items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div> <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title">Missing Items <span>| Pending</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $Items = $conn->query("SELECT * FROM `missing_items` where `status` = 0")->num_rows;
                    ?>
                    <h6><?= format_num($Items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title">Claim Request <span>| Pending</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $Items = $conn->query("SELECT * FROM `claimer` where `status` = 1")->num_rows;
                    ?>
                    <h6><?= format_num($Items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title"> Student User Accounts <span>| Active</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $Items = $conn->query("SELECT * FROM `user_member` where `status` = 'approved'")->num_rows;
                    ?>
                    <h6><?= format_num($Items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title"> Employee Accounts <span>| Pending</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $Items = $conn->query("SELECT * FROM `user_staff` where `status` = 'pending'")->num_rows;
                    ?>
                    <h6><?= format_num($Items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title"> Student User Accounts <span>| Pending</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $Items = $conn->query("SELECT * FROM `user_member` where `status` = 'pending'")->num_rows;
                    ?>
                    <h6><?= format_num($Items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title">Reported Found Item <span>| Published</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $items = $conn->query("SELECT * FROM `message_history` where `status` = 1")->num_rows;
                    ?>
                    <h6><?= format_num($items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
            <div class="card info-card">
              <div class="card-body">
                <h5 class="card-title">Reported Found Item <span>| Pending</span></h5>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="ps-3">
                    <?php 
                    $items = $conn->query("SELECT * FROM `message_history` where `status` = 0")->num_rows;
                    ?>
                    <h6><?= format_num($items) ?></h6>
                    <!-- <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span> -->

                  </div>
                </div>
              </div>

            </div>
          </div>

     

          <div class="col-12">
            <div class="card">
              
                <?php 
                  if(is_dir(base_app.'uploads/banner')){
                    $images = scandir(base_app.'uploads/banner');
                    foreach($images as $k=>$v){
                      if(in_array($v, ['.', '..'])){
                        unset($images[$k]);
                      }
                    }
                  }
                ?>
                <?php if(isset($images) && count($images) > 0): ?>
                <div id="banner-slider" class="carousel slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <?php foreach(array_values($images) as $k => $fname): ?>
                    <div class="carousel-item <?= ($k == 0) ? "active" : "" ?>">
                      <img src="<?= validate_image('uploads/banner/'.$fname) ?>" class="d-block w-100" alt="Banner Image <?= $k + 1 ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <?php endif; ?>


              </div>
          </div>

        </div>
      </div><!-- End Left side columns -->

    </div>
  </section>